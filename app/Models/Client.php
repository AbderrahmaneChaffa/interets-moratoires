<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'raison_sociale',
        'contrat_maintenance',
        'formule',
        'taux',
        'nif',
        'rc',
        'ai',
        'adresse',
        'email',
    ];

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    /**
     * Calcule les intérêts pour une facture selon la formule du client.
     * Stocke le résultat (DA) dans $facture->interets sans sauvegarder.
     */
    // public function calculerInterets(Facture $facture): float
    // {
    //     $taux = (float) ($this->taux ?? 0);
    //     $formule = (string) ($this->formule ?? $this->formule_calcul ?? '');
    //     $result = \App\Services\InteretService::calculerInterets($facture, $taux, $formule);
    //     $facture->interets_ht = $result['interet_ht'];
    //     $facture->interets_ttc = $result['interet_ttc'];
    //     $facture->interets = $result['interet_ttc'];
    //     return $facture->interets;
    // }
}
