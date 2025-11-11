<?php

namespace App\Http\Livewire\Releve;

use App\Models\Facture;
use Livewire\Component;
use App\Models\Releve;
use Illuminate\Support\Facades\DB;

class ReleveDetails extends Component
{
    public $releveId;
    public $releve;
    // Factures modals
    public $showFacturePayModal = false;
    public $payFactureId = null;
    public $showFacturesPayAllModal = false;

    // protected $listeners = [
    //     'calculerInteretsReleve' => 'calculer',
    //     'openRelevePayModal' => 'openRelevePayModal',

    // ];
    protected $listeners = [
        'refreshReleveDetails' => 'loadReleve',
        'openFacturePayModal' => 'openFacturePayModal',
        'openFacturesPayAllModal' => 'openFacturesPayAllModal',
        'marquerFactureImpaye' => 'marquerFactureImpaye',// pour refresh depuis d'autres composants
    ];
    public function mount(Releve $releve)
    {
        $this->releve = $releve;
        $this->releveId = $releve->id;
    }


    public function loadReleve()
    {
        $this->releve = Releve::with(['client', 'factures'])->find($this->releveId);
        if (!$this->releve) {
            session()->flash('error', 'Relevé introuvable.');
            return;
        }
    }

    // émettre vers le composant releve-interets pour lancer le calcul
    public function calculerInteretsReleve()
    {
        $this->emitTo('releve-interets', 'calculerInteretsReleve', $this->releveId);
    }

    // marquer le relevé comme impayé (action simple depuis l'UI)
    public function marquerReleveImpaye()
    {
        DB::beginTransaction();
        try {
            $old = $this->releve->statut ?? null;
            $this->releve->update(['statut' => 'Impayé']);

            // log custom si tu as cette méthode (adaptation possible)
            if (method_exists($this->releve, 'logChange')) {
                $this->releve->logChange('status_changed', 'statut', $old, 'Impayé', 'Relevé marqué comme impayé');
            }

            DB::commit();

            // recharger le modèle
            $this->loadReleve();

            // informer autres composants et éventuellement reload navigateur
            $this->emitTo('releve-interets', 'refreshComponent');
            session()->flash('message', 'Relevé marqué comme impayé.');
            $this->dispatchBrowserEvent('releve:reload'); // optionnel : force un reload (si tu veux)
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    // helper pour compter factures non payées (utilisé en vue)
    public function getNonPayeesProperty()
    {
        if (!$this->releve)
            return 0;
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

            // Si on marque comme payé, mettre aussi la date de règlement à aujourd'hui si elle n'existe pas
            if (!$facture->date_reglement) {
                $facture->update([
                    'statut' => 'Payé',
                    'date_reglement' => now(),
                ]);
            } else {
                $facture->update(['statut' => 'Payé']);
            }

            // Log audit
            $facture->logChange(
                'status_changed',
                'statut',
                $oldStatut,
                'Payé',
                'Facture marquée comme payée'
            );

            // Mettre à jour le statut du relevé si nécessaire
            $this->releve->refresh();
            $this->releve->calculerStatut();
            $this->releve->save();

            DB::commit();
            $this->releve->refresh();
            $this->loadData();
            $this->showFacturePayModal = false;
            session()->flash('message', 'Facture marquée comme payée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
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
                'date_reglement' => null, // Retirer la date de règlement si on marque comme impayé
            ]);

            // Log audit
            $facture->logChange(
                'status_changed',
                'statut',
                $oldStatut,
                'Impayé',
                'Facture marquée comme impayée'
            );

            // Mettre à jour le statut du relevé si nécessaire
            $this->releve->refresh();
            $this->releve->calculerStatut();
            $this->releve->save();

            DB::commit();
            $this->releve->refresh();
            $this->loadData();
            session()->flash('message', 'Facture marquée comme impayée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
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

                // Mettre la date de règlement si elle n'existe pas
                if (!$facture->date_reglement) {
                    $facture->update([
                        'statut' => 'Payé',
                        'date_reglement' => now(),
                    ]);
                } else {
                    $facture->update(['statut' => 'Payé']);
                }

                $count++;

                // Log audit
                $facture->logChange(
                    'status_changed',
                    'statut',
                    $oldStatut,
                    'Payé',
                    'Facture marquée comme payée (toutes les factures)'
                );
            }

            // Mettre à jour le statut du relevé
            $this->releve->refresh();
            $this->releve->calculerStatut();
            $this->releve->save();

            // Log audit pour le relevé
            $this->releve->logChange(
                'status_changed',
                'statut',
                $this->releve->getOriginal('statut'),
                'Payé',
                sprintf('Toutes les factures (%d) marquées comme payées', $count)
            );

            DB::commit();
            $this->releve->refresh();
            $this->loadData();
            $this->showFacturesPayAllModal = false;
            session()->flash('message', sprintf('%d facture(s) marquée(s) comme payée(s) avec succès.', $count));
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }
    public function render()
    {
        return view('livewire.releve.releve-details');
    }
}
