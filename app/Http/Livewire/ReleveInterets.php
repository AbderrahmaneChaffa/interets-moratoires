<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Releve;
use App\Models\Interet;

class ReleveInterets extends Component
{
    public $releveId;
    public $releve;
    public $periodes = [];

    protected $listeners = ['calculerInteretsReleve' => 'calculer'];

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
        $interet = Interet::findOrFail($interetId);
        $interet->update(['valide' => true]);
        $this->refreshPeriodes();
        session()->flash('message', 'Intérêt validé avec succès.');
    }

    public function supprimerInteret($interetId)
    {
        $interet = Interet::findOrFail($interetId);
        $interet->delete();
        $this->refreshPeriodes();
        session()->flash('message', 'Intérêt supprimé avec succès.');
    }

    public function marquerRelevePaye()
    {
        $this->releve->update(['statut' => 'Payée']);
        $this->releve->refresh();
        session()->flash('message', 'Relevé marqué comme payé.');
    }

    public function marquerReleveImpaye()
    {
        $this->releve->update(['statut' => 'Impayée']);
        $this->releve->refresh();
        session()->flash('message', 'Relevé marqué comme impayé.');
    }

    public function render()
    {
        return view('livewire.releve-interets');
    }
}


