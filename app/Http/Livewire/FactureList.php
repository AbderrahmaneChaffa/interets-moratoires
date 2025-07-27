<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Facture;

class FactureList extends Component
{
    public function render()
    {
        $factures = Facture::with('client')->orderBy('date_facture', 'desc')->paginate(10);
        return view('livewire.facture-list', compact('factures'));
    }
    
    public function calculInteret($factureId)
    {
        $facture = Facture::findOrFail($factureId);
        $jours_retards = 0;
        if ($facture->date_depot && is_null($facture->date_reglement)) {
            $jours_retards = now()->diffInDays(
                \Carbon\Carbon::parse($facture->date_depot)
            );
        }
        $result = $facture->calculerInteretsMoratoires($jours_retards);
        session()->flash('interet', [
            'facture_id' => $facture->id,
            'interet_ht' => $result['interet_ht'],
            'interet_ttc' => $result['interet_ttc'],
            'jours_retards' => $jours_retards,
            'taux_utilise' => $result['taux_utilise'],
        ]);
    }
}
