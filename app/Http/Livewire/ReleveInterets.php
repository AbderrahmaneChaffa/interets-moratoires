<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Releve;
use App\Models\Interet;
use Illuminate\Support\Facades\DB;

class ReleveInterets extends Component
{
    public $releveId;
    public $releve;
    public $periodes = [];

    // Modals
    public $showPayModal = false;
    public $payInteretId = null;
    public $showValidateAllModal = false;
    public $showRelevePayModal = false;
    public $markInteretsAsPaid = false;

    protected $listeners = [
        'calculerInteretsReleve' => 'calculer',
        'openRelevePayModal' => 'openRelevePayModal',
    ];

    public function mount($releveId)
    {
        $this->releveId = $releveId;
        $this->loadData();
    }

    public function loadData()
    {
        $this->releve = Releve::with(['client'])->findOrFail($this->releveId);
        $this->refreshPeriodes();
    }

    public function refreshPeriodes()
    {
        $interets = Interet::where('releve_id', $this->releveId)
            ->orderBy('date_debut_periode')
            ->get();

        $this->periodes = [];
        $mois = 1;
        foreach ($interets as $i) {
            $this->periodes[] = [
                'mois' => $mois++,
                'date_debut' => $i->date_debut_periode,
                'date_fin' => $i->date_fin_periode,
                'jours_retard' => $i->jours_retard,
                'interet_ht' => $i->interet_ht,
                'interet_ttc' => $i->interet_ttc,
                'reference' => $i->reference,
                'pdf_path' => $i->pdf_path,
                'statut' => $i->statut,
                'valide' => $i->valide,
                'id' => $i->id,
            ];
        }
    }

    public function calculer()
    {
        $this->releve->calculerInterets();
        $this->releve->refresh();
        $this->refreshPeriodes();
        $this->dispatchBrowserEvent('notification', ['message' => 'Intérêts du relevé calculés.']);
    }

    public function validerInteret($interetId)
    {
        DB::beginTransaction();
        try {
            $interet = Interet::findOrFail($interetId);
            $oldValide = $interet->valide;
            $interet->update(['valide' => true]);
            
            // Log audit
            $interet->logChange('validated', 'valide', $oldValide, true, 
                'Intérêt validé');

            DB::commit();
            $this->refreshPeriodes();
            session()->flash('message', 'Intérêt validé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la validation: ' . $e->getMessage());
        }
    }

    public function supprimerInteret($interetId)
    {
        DB::beginTransaction();
        try {
            $interet = Interet::findOrFail($interetId);
            
            // Log audit before deletion
            $interet->logChange('deleted', null, null, null, 
                'Intérêt supprimé', ['interet_data' => $interet->toArray()]);
            
            $interet->delete();
            DB::commit();
            $this->refreshPeriodes();
            session()->flash('message', 'Intérêt supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    public function openRelevePayModal()
    {
        $this->showRelevePayModal = true;
        $this->markInteretsAsPaid = false;
    }

    public function marquerRelevePaye()
    {
        DB::beginTransaction();
        try {
            $oldStatut = $this->releve->statut;
            $this->releve->update(['statut' => 'Payé']);
            
            // Log audit
            $this->releve->logChange('status_changed', 'statut', $oldStatut, 'Payé', 
                'Relevé marqué comme payé');

            // Si l'utilisateur veut aussi marquer les intérêts comme payés
            if ($this->markInteretsAsPaid) {
                $count = Interet::where('releve_id', $this->releveId)
                    ->where('statut', '!=', 'Payé')
                    ->update(['statut' => 'Payé']);
                
                // Log audit pour les intérêts
                Interet::logAudit('bulk_status_changed', null, 'statut', 'Impayé', 'Payé',
                    sprintf('%d intérêt(s) marqué(s) comme payé(s) avec le relevé', $count),
                    ['releve_id' => $this->releveId, 'count' => $count]);
            }

            DB::commit();
            $this->releve->refresh();
            $this->refreshPeriodes();
            $this->showRelevePayModal = false;
            session()->flash('message', 'Relevé marqué comme payé' . ($this->markInteretsAsPaid ? ' avec tous les intérêts associés' : '') . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function marquerReleveImpaye()
    {
        DB::beginTransaction();
        try {
            $oldStatut = $this->releve->statut;
            $this->releve->update(['statut' => 'Impayé']);
            
            // Log audit
            $this->releve->logChange('status_changed', 'statut', $oldStatut, 'Impayé', 
                'Relevé marqué comme impayé');

            DB::commit();
            $this->releve->refresh();
            session()->flash('message', 'Relevé marqué comme impayé.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function openPayModal($interetId)
    {
        $this->payInteretId = $interetId;
        $this->showPayModal = true;
    }

    public function marquerInteretPaye()
    {
        DB::beginTransaction();
        try {
            $interet = Interet::findOrFail($this->payInteretId);
            
            if ($interet->statut === 'Payé') {
                session()->flash('info', 'Cet intérêt est déjà marqué comme payé.');
                $this->showPayModal = false;
                return;
            }

            $oldStatut = $interet->statut;
            $interet->update(['statut' => 'Payé']);
            
            // Log audit
            $interet->logChange('status_changed', 'statut', $oldStatut, 'Payé', 
                'Intérêt marqué comme payé');

            DB::commit();
            $this->refreshPeriodes();
            $this->showPayModal = false;
            session()->flash('message', 'Intérêt marqué comme payé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function openValidateAllModal()
    {
        $this->showValidateAllModal = true;
    }

    public function validerTousInterets()
    {
        DB::beginTransaction();
        try {
            $interets = Interet::where('releve_id', $this->releveId)
                ->where('valide', false)
                ->get();

            $count = 0;
            foreach ($interets as $interet) {
                $interet->update(['valide' => true]);
                $count++;
                
                // Log audit
                $interet->logChange('validated', 'valide', false, true, 
                    'Intérêt validé');
            }

            DB::commit();
            $this->refreshPeriodes();
            $this->showValidateAllModal = false;
            session()->flash('message', sprintf('%d intérêt(s) validé(s) avec succès.', $count));
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la validation: ' . $e->getMessage());
        }
    }

    public function closeModals()
    {
        $this->showPayModal = false;
        $this->showValidateAllModal = false;
        $this->showRelevePayModal = false;
        $this->payInteretId = null;
        $this->markInteretsAsPaid = false;
    }

    public function render()
    {
        return view('livewire.releve-interets');
    }
}


