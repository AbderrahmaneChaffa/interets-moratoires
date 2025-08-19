<?php

namespace App\Services;

use App\Models\Facture;
use App\Models\Interet;
use Carbon\Carbon;

class InteretService
{
    /**
     * Calculer les jours de retard pour une facture
     */
    public static function calculerJoursRetard(Facture $facture): int
    {
        if (!$facture->date_depot) {
            return 0;
        }
        $delai = $facture->delai_legal_jours ?? 30;
        $dateLimite = $facture->date_depot->copy()->addDays($delai);
        $dateRef = $facture->date_reglement ?: now();
        return max(0, $dateRef->diffInDays($dateLimite, false));
    }

    /**
     * Calculer les mois de retard pour une facture
     */
    public static function calculerMoisRetard(Facture $facture): int
    {
        $jours = self::calculerJoursRetard($facture);
        return (int) max(0, ceil($jours / 30));
    }

    /**
     * Calculer les intérêts pour une période donnée
     */
    public static function calculerInteretsPourPeriode(Facture $facture, Carbon $dateDebut, Carbon $dateFin, float $taux, string $formule): array
    {
        $montant = (float) $facture->montant_ht;
        $joursRetard = $dateFin->diffInDays($dateDebut);
        
        $interet_ht = 0.0;
        if (stripos($formule, 'jours') !== false) {
            // (Montant × Jours × Taux) / 360
            $interet_ht = ($montant * $joursRetard * $taux) / 360.0;
        } elseif (stripos($formule, 'mois') !== false) {
            // (Montant × Taux × 1 mois)
            $interet_ht = $montant * $taux;
        } else {
            // Par défaut: jours/360
            $interet_ht = ($montant * $joursRetard * $taux) / 360.0;
        }

        $interet_ht = round($interet_ht, 2);
        $interet_ttc = round($interet_ht * 1.19, 2); // TVA 19%

        return [
            'jours_retard' => $joursRetard,
            'interet_ht' => $interet_ht,
            'interet_ttc' => $interet_ttc,
        ];
    }

    /**
     * Générer les périodes d'intérêts pour une facture
     */
    public static function genererPeriodesInterets(Facture $facture): array
    {
        if (!$facture->peutGenererInterets()) {
            return [];
        }

        $delai = $facture->delai_legal_jours ?? 30;
        $dateDebutGrace = $facture->date_depot->copy()->addDays($delai);
        $moisRetard = $facture->mois_retard;
        
        $periodes = [];
        
        for ($mois = 1; $mois <= $moisRetard; $mois++) {
            $dateDebutPeriode = $dateDebutGrace->copy()->addMonths($mois - 1);
            $dateFinPeriode = $dateDebutPeriode->copy()->addMonth();
            
            // Si la facture est payée, ajuster la date de fin
            if ($facture->date_reglement && $dateFinPeriode > $facture->date_reglement) {
                $dateFinPeriode = $facture->date_reglement;
            }
            
            $periodes[] = [
                'mois' => $mois,
                'date_debut_periode' => $dateDebutPeriode,
                'date_fin_periode' => $dateFinPeriode,
            ];
        }
        
        return $periodes;
    }

    /**
     * Calculer et sauvegarder les intérêts pour une période
     */
    public static function calculerEtSauvegarderInterets(Facture $facture, Carbon $dateDebut, Carbon $dateFin): ?Interet
    {
        // Vérifier si l'intérêt existe déjà pour cette période
        if (Interet::existsForPeriod($facture->id, $dateDebut, $dateFin)) {
            return null;
        }

        $client = $facture->client;
        $taux = (float) ($client->taux ?? 0);
        $formule = (string) ($client->formule ?? '');
        
        if ($taux <= 0) {
            return null;
        }

        $resultat = self::calculerInteretsPourPeriode($facture, $dateDebut, $dateFin, $taux, $formule);
        
        return Interet::create([
            'facture_id' => $facture->id,
            'date_debut_periode' => $dateDebut,
            'date_fin_periode' => $dateFin,
            'jours_retard' => $resultat['jours_retard'],
            'interet_ht' => $resultat['interet_ht'],
            'interet_ttc' => $resultat['interet_ttc'],
        ]);
    }

    /**
     * Calculer et sauvegarder tous les intérêts pour une facture
     */
    public static function calculerEtSauvegarderTousInterets(Facture $facture): array
    {
        $periodes = self::genererPeriodesInterets($facture);
        $interetsCrees = [];
        
        foreach ($periodes as $periode) {
            $interet = self::calculerEtSauvegarderInterets(
                $facture, 
                $periode['date_debut_periode'], 
                $periode['date_fin_periode']
            );
            
            if ($interet) {
                $interetsCrees[] = $interet;
            }
        }
        
        return $interetsCrees;
    }

    /**
     * Obtenir les intérêts calculés pour une facture
     */
    public static function getInteretsCalcules(Facture $facture): array
    {
        $periodes = self::genererPeriodesInterets($facture);
        $resultat = [];
        
        foreach ($periodes as $periode) {
            $interetExistant = Interet::where('facture_id', $facture->id)
                ->where('date_debut_periode', $periode['date_debut_periode'])
                ->where('date_fin_periode', $periode['date_fin_periode'])
                ->first();
            
            $resultat[] = [
                'mois' => $periode['mois'],
                'date_debut_periode' => $periode['date_debut_periode'],
                'date_fin_periode' => $periode['date_fin_periode'],
                'interet_existant' => $interetExistant,
                'peut_calculer' => !$interetExistant,
            ];
        }
        
        return $resultat;
    }

    /**
     * Formater un montant en DA
     */
    public static function formaterMontant($montant): string
    {
        return number_format($montant, 2, ',', ' ') . ' DA';
    }
}
