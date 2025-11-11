<?php

namespace App\Http\Livewire\Releve;

use Livewire\Component;
use App\Models\Releve;
use App\Models\Interet;
use Illuminate\Support\Facades\DB;

class RelevePayModal extends Component
{
    public $releveId;
    public $open = false;
    public $markInteretsAsPaid = false;
    public $commentaire = '';

    protected $listeners = [
        'openRelevePayModal' => 'openModal', // pour $emitTo('releve.releve-pay-modal', 'openRelevePayModal', id)
    ];

    public function mount($releveId = null)
    {
        $this->releveId = $releveId;
    }

    public function openModal($releveId = null)
    {
        if ($releveId)
            $this->releveId = $releveId;
        $this->open = true;
    }

    public function markAsPaid()
    {
        DB::beginTransaction();
        try {
            $releve = Releve::findOrFail($this->releveId);
            $oldStatut = $releve->statut;
            $releve->update(['statut' => 'Payé']);

            // Log audit si existant
            if (method_exists($releve, 'logChange')) {
                $releve->logChange('status_changed', 'statut', $oldStatut, 'Payé', 'Relevé marqué comme payé');
            }

            if ($this->markInteretsAsPaid) {
                $count = Interet::where('releve_id', $this->releveId)
                    ->where('statut', '!=', 'Payé')
                    ->update(['statut' => 'Payé']);

                // si tu as une méthode logAudit sur Interet
                if (method_exists(Interet::class, 'logAudit')) {
                    Interet::logAudit(
                        'bulk_status_changed',
                        null,
                        'statut',
                        'Impayé',
                        'Payé',
                        sprintf('%d intérêt(s) marqué(s) comme payé(s) avec le relevé', $count),
                        ['releve_id' => $this->releveId, 'count' => $count]
                    );
                }
            }

            DB::commit();

            $this->open = false;
            session()->flash('message', 'Relevé marqué comme payé' . ($this->markInteretsAsPaid ? ' avec tous les intérêts associés' : '') . '.');

            // informer et rafraîchir les composants concernés (ReleveDetails et ReleveInterets)
            $this->emitTo('releve.releve-details', 'refreshReleveDetails');
            $this->emitTo('releve-interets', 'refreshComponent'); // si releve-interets est à la racine
            $this->dispatchBrowserEvent('releve:reload');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.releve.releve-pay-modal');
    }
}
