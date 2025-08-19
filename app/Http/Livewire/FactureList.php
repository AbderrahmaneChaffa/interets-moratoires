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
        $resultat = $facture->mettreAJourStatutEtInterets();
        
        session()->flash('interet', [
            'facture_id' => $facture->id,
            'statut' => $resultat['statut'],
            'interets' => $resultat['interets'],
            'message' => 'Statut mis à jour: ' . $resultat['statut'] . ', Intérêts: ' . number_format($resultat['interets'], 2) . ' DA'
        ]);
    }
    
    public function mettreAJourToutesFactures()
    {
        $factures = Facture::all();
        $compteur = 0;
        
        foreach ($factures as $facture) {
            $facture->mettreAJourStatutEtInterets();
            $compteur++;
        }
        
        session()->flash('message', $compteur . ' factures mises à jour avec succès.');
    }
}
