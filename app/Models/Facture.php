<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'reference',
        'date_facture',
        'montant_ht',
        'date_depot',
        'date_reglement',
        'net_a_payer',
        'statut_paiement',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function interet()
    {
        return $this->hasOne(Interet::class);
    }

    /**
     * Calcule les intérêts moratoires pour cette facture.
     *
     * @param int $jours_retards
     * @param float $taux_annuel (en pourcentage, ex: 6 pour 6%)
     * @param float $tva (en pourcentage, ex: 19 pour 19%)
     * @return array [interet_ht, interet_ttc, taux_utilise]
     */
    public function calculerInteretsMoratoires($jours_retards, $taux_annuel = 6.0, $tva = 19.0)
    {
        $montant_ht = $this->montant_ht;
        $taux_utilise = $taux_annuel / 100;
        $interet_ht = ($montant_ht * $taux_utilise * $jours_retards) / 360;
        $interet_ttc = $interet_ht * (1 + $tva / 100);
        return [
            'interet_ht' => round($interet_ht, 2),
            'interet_ttc' => round($interet_ttc, 2),
            'taux_utilise' => $taux_annuel
        ];
    }
} 