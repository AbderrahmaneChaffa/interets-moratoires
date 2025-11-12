<?php

namespace App\Http\Livewire\Releve;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Releve;
use App\Models\Facture;
use Exception;

class ReleveDetails extends Component
{
    public $releveId;
    public $releve;

    // Factures modals
    public $showFacturePayModal = false;
    public $payFactureId = null;
    public $showFacturesPayAllModal = false;

    protected $listeners = [
        'refreshReleveDetails' => 'loadReleve',
        'openFacturePayModal' => 'openFacturePayModal',
        'openFacturesPayAllModal' => 'openFacturesPayAllModal',
        'marquerFactureImpaye' => 'marquerFactureImpaye', // pour refresh depuis d'autres composants
    ];

    /**
     * Mount the component with a Releve instance (route-model binding possible).
     */
    public function mount(Releve $releve)
    {
        $this->releve = $releve;
        $this->releveId = $releve->id;
    }

    /**
     * Recharge le relevé depuis la base.
     */
    public function loadReleve()
    {
        $this->releve = Releve::with(['client', 'factures'])->find($this->releveId);
        if (!$this->releve) {
            session()->flash('error', 'Relevé introuvable.');
            return;
        }
    }

    /**
     * Émet à un sous-composant pour lancer le calcul des intérêts.
     */
    public function calculerInteretsReleve()
    {
        $this->emitTo('releve-interets', 'calculerInteretsReleve', $this->releveId);
    }

    /**
     * Marquer le relevé comme impayé directement depuis l'UI.
     */
    public function marquerReleveImpaye()
    {
        DB::beginTransaction();
        try {
            $old = $this->releve->statut ?? null;
            $this->releve->update(['statut' => 'Impayé']);

            if (method_exists($this->releve, 'logChange')) {
                $this->releve->logChange('status_changed', 'statut', $old, 'Impayé', 'Relevé marqué comme impayé');
            }

            DB::commit();

            $this->loadReleve();

            $this->emitTo('releve-interets', 'refreshComponent');
            session()->flash('message', 'Relevé marqué comme impayé.');
            $this->dispatchBrowserEvent('releve:reloaded');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur marquerReleveImpaye: ' . $e->getMessage());
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Compteur de factures impayées (propriété calculée).
     */
    public function getNonPayeesProperty()
    {
        if (!$this->releve) {
            return 0;
        }
        return $this->releve->factures->where('statut', '=', 'Impayé')->count();
    }

    // ========== GESTION DES FACTURES ==========

    public function openFacturePayModal($factureId)
    {
        $this->payFactureId = $factureId;
        $this->showFacturePayModal = true;
    }

    public function marquerFacturePaye()
    {
        DB::beginTransaction();
        try {
            $facture = Facture::findOrFail($this->payFactureId);

            if ($facture->statut === 'Payé') {
                session()->flash('info', 'Cette facture est déjà marquée comme payée.');
                $this->showFacturePayModal = false;
                return;
            }

            $oldStatut = $facture->statut;

            $payload = ['statut' => 'Payé'];
            if (!$facture->date_reglement) {
                $payload['date_reglement'] = now();
            }
            $facture->update($payload);

            if (method_exists($facture, 'logChange')) {
                $facture->logChange('status_changed', 'statut', $oldStatut, 'Payé', 'Facture marquée comme payée');
            }

            // Mettre à jour le relevé
            $this->releve->refresh();
            if (method_exists($this->releve, 'calculerStatut')) {
                $this->releve->calculerStatut();
            }
            $this->releve->save();

            DB::commit();

            $this->loadReleve();
            $this->showFacturePayModal = false;
            $this->payFactureId = null;
            session()->flash('message', 'Facture marquée comme payée avec succès.');
            $this->emit('refreshReleveDetails'); // si d'autres composants écoutent
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur marquerFacturePaye: ' . $e->getMessage());
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function marquerFactureImpaye($factureId)
    {
        DB::beginTransaction();
        try {
            $facture = Facture::findOrFail($factureId);

            if ($facture->statut === 'Impayé') {
                session()->flash('info', 'Cette facture est déjà marquée comme impayée.');
                return;
            }

            $oldStatut = $facture->statut;
            $facture->update([
                'statut' => 'Impayé',
                'date_reglement' => null,
            ]);

            if (method_exists($facture, 'logChange')) {
                $facture->logChange('status_changed', 'statut', $oldStatut, 'Impayé', 'Facture marquée comme impayée');
            }

            $this->releve->refresh();
            if (method_exists($this->releve, 'calculerStatut')) {
                $this->releve->calculerStatut();
            }
            $this->releve->save();

            DB::commit();

            $this->loadReleve();
            session()->flash('message', 'Facture marquée comme impayée avec succès.');
            $this->emit('refreshReleveDetails');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur marquerFactureImpaye: ' . $e->getMessage());
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function openFacturesPayAllModal()
    {
        $this->showFacturesPayAllModal = true;
    }

    public function marquerToutesFacturesPayees()
    {
        DB::beginTransaction();
        try {
            $factures = Facture::where('releve_id', $this->releveId)
                ->where('statut', '!=', 'Payé')
                ->get();

            $count = 0;
            foreach ($factures as $facture) {
                $oldStatut = $facture->statut;
                $payload = ['statut' => 'Payé'];
                if (!$facture->date_reglement) {
                    $payload['date_reglement'] = now();
                }
                $facture->update($payload);

                if (method_exists($facture, 'logChange')) {
                    $facture->logChange('status_changed', 'statut', $oldStatut, 'Payé', 'Facture marquée comme payée (toutes les factures)');
                }

                $count++;
            }

            // Mettre à jour le relevé
            $this->releve->refresh();
            if (method_exists($this->releve, 'calculerStatut')) {
                $this->releve->calculerStatut();
            }
            $this->releve->save();

            if (method_exists($this->releve, 'logChange')) {
                $this->releve->logChange('status_changed', 'statut', $this->releve->getOriginal('statut'), 'Payé', sprintf('Toutes les factures (%d) marquées comme payées', $count));
            }

            DB::commit();

            $this->loadReleve();
            $this->showFacturesPayAllModal = false;
            session()->flash('message', sprintf('%d facture(s) marquée(s) comme payée(s) avec succès.', $count));
            $this->emit('refreshReleveDetails');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur marquerToutesFacturesPayees: ' . $e->getMessage());
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Ferme uniquement les modals gérés par ce composant.
     */
    public function closeModals()
    {
        $this->showFacturePayModal = false;
        $this->showFacturesPayAllModal = false;
        $this->payFactureId = null;
    }

    public function render()
    {
        return view('livewire.releve.releve-details');
    }
}
