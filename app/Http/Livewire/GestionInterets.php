<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Facture;
use App\Models\Interet;
use App\Services\InteretService;
use Carbon\Carbon;

class GestionInterets extends Component
{
    public $factureId;
    public $facture = null;
    public $periodesInterets = [];
    public $showCalculModal = false;
    public $periodeSelectionnee = null;

    protected $listeners = ['refreshInterets' => 'refreshData'];

    public function mount($factureId = null)
    {
        if ($factureId) {
            $this->factureId = $factureId;
            $this->loadFacture();
        }
    }

    public function loadFacture()
    {
        if ($this->factureId) {
            $this->facture = Facture::with(['client', 'interets'])->find($this->factureId);
            $this->calculerPeriodes();
        }
    }

    public function calculerPeriodes()
    {
        if (!$this->facture) {
            return;
        }

        $this->periodesInterets = InteretService::getInteretsCalcules($this->facture);
    }

    public function calculerInteret($factureId)
    {
        $facture = Facture::with('client')->findOrFail($factureId);
        
        if (!$facture->peutGenererInterets()) {
            session()->flash('error', 'Cette facture ne peut pas générer d\'intérêts moratoires.');
            return;
        }

        $interetsCrees = InteretService::calculerEtSauvegarderTousInterets($facture);
        
        if (empty($interetsCrees)) {
            session()->flash('info', 'Tous les intérêts pour cette facture ont déjà été calculés.');
        } else {
            session()->flash('message', count($interetsCrees) . ' période(s) d\'intérêts calculée(s) et sauvegardée(s).');
        }

        $this->loadFacture();
        $this->emit('refreshInterets');
    }

    public function calculerInteretPeriode($dateDebut, $dateFin)
    {
        if (!$this->facture) {
            return;
        }

        $dateDebut = Carbon::parse($dateDebut);
        $dateFin = Carbon::parse($dateFin);
        
        $interet = InteretService::calculerEtSauvegarderInterets($this->facture, $dateDebut, $dateFin);
        
        if ($interet) {
            session()->flash('message', 'Intérêt calculé et sauvegardé pour cette période.');
        } else {
            session()->flash('info', 'Intérêt déjà calculé pour cette période.');
        }

        $this->loadFacture();
        $this->emit('refreshInterets');
    }

    public function supprimerInteret($interetId)
    {
        $interet = Interet::findOrFail($interetId);
        $interet->delete();
        
        session()->flash('message', 'Intérêt supprimé avec succès.');
        $this->loadFacture();
        $this->emit('refreshInterets');
    }

    public function refreshData()
    {
        $this->loadFacture();
    }

    public function render()
    {
        return view('livewire.gestion-interets');
    }
}
