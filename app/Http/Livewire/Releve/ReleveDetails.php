<?php

namespace App\Http\Livewire\Releve;

use Livewire\Component;
use App\Models\Releve;
use Illuminate\Support\Facades\DB;

class ReleveDetails extends Component
{
    public $releveId;
    public $releve;

    protected $listeners = [
        'refreshReleveDetails' => 'loadReleve', // pour refresh depuis d'autres composants
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

    public function render()
    {
        return view('livewire.releve.releve-details');
    }
}
