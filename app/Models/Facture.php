<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'parent_id', 'type', 'prestation',
        'reference',
        'date_facture',
        'montant_ht',
        'date_depot',
        'date_reglement',
        'net_a_payer',
        'pdf_path',
        'statut',
        'interets',
        'interets_ht',
        'interets_ttc',
        'delai_legal_jours',
    ];

    protected $casts = [
        'date_facture' => 'date',
        'date_depot' => 'date',
        'date_reglement' => 'date',
        'interets' => 'decimal:2',
        'interets_ht' => 'decimal:2',
        'interets_ttc' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function parent()
    {
        return $this->belongsTo(Facture::class, 'parent_id');
    }

    public function sousFactures()
    {
        return $this->hasMany(Facture::class, 'parent_id');
    }

    protected static function booted()
    {
        // Recalcul automatique des intérêts après création/mise à jour
        static::saved(function (Facture $facture) {
            if ($facture->relationLoaded('client') || $facture->client) {
                $facture->client->calculerInterets($facture);
                $facture->saveQuietly();
            }
        });
    }

    // Relation Interet supprimée car la logique est désormais stockée sur la facture (champ interets)

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

    /**
     * Calcule et met à jour le statut de la facture automatiquement.
     *
     * @return string Le statut calculé
     */
    public function calculerStatut()
    {
        $delai_legal = $this->delai_legal_jours ?? 30;
        
        if (!$this->date_depot) {
            $this->statut = 'En attente';
            return $this->statut;
        }

        $date_limite = $this->date_depot->addDays($delai_legal);
        $aujourd_hui = now();

        if ($this->date_reglement) {
            // Facture réglée
            if ($this->date_reglement <= $date_limite) {
                $this->statut = 'Payée';
            } else {
                $this->statut = 'Retard de paiement';
            }
        } else {
            // Facture non réglée
            if ($aujourd_hui > $date_limite) {
                $this->statut = 'Impayée';
            } else {
                $this->statut = 'En attente';
            }
        }

        return $this->statut;
    }

    /**
     * Calcule les intérêts moratoires et les stocke dans la facture.
     *
     * @param float $taux_annuel (en pourcentage, ex: 6 pour 6%)
     * @param float $tva (en pourcentage, ex: 19 pour 19%)
     * @return float Le montant des intérêts calculés
     */
    public function calculerInterets($taux_annuel = 6.0, $tva = 19.0)
    {
        if ($this->statut !== 'Retard de paiement' && $this->statut !== 'Impayée') {
            $this->interets = 0.00;
            return 0.00;
        }

        $delai_legal = $this->delai_legal_jours ?? 30;
        $date_limite = $this->date_depot->addDays($delai_legal);
        
        if ($this->date_reglement) {
            // Calcul basé sur la date de règlement
            $jours_retards = $this->date_reglement->diffInDays($date_limite);
        } else {
            // Calcul basé sur la date actuelle
            $jours_retards = now()->diffInDays($date_limite);
        }

        if ($jours_retards <= 0) {
            $this->interets = 0.00;
            return 0.00;
        }

        $resultat = $this->calculerInteretsMoratoires($jours_retards, $taux_annuel, $tva);
        $this->interets_ht = $resultat['interet_ht'];
        $this->interets_ttc = $resultat['interet_ttc'];
        $this->interets = $resultat['interet_ttc'];
        
        return $this->interets;
    }

    /**
     * Met à jour automatiquement le statut et les intérêts.
     *
     * @param float $taux_annuel
     * @param float $tva
     * @return array [statut, interets]
     */
    public function mettreAJourStatutEtInterets($taux_annuel = 6.0, $tva = 19.0)
    {
        $statut = $this->calculerStatut();
        $interets = $this->calculerInterets($taux_annuel, $tva);
        
        $this->save();
        
        return [
            'statut' => $statut,
            'interets' => $interets
        ];
    }

    /**
     * Obtient la liste des statuts disponibles.
     *
     * @return array
     */
    public static function getStatutsDisponibles()
    {
        return [
            'En attente' => 'En attente',
            'Payée' => 'Payée',
            'Retard de paiement' => 'Retard de paiement',
            'Impayée' => 'Impayée'
        ];
    }
}