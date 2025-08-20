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

        return max(0, $dateLimite->diffInDays($dateRef, false));
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
     * Calculer les intérêts selon le client
     */
    public static function calculerInteretsPourPeriode(Facture $facture, Carbon $dateDebut, Carbon $dateFin): array
    {
        $client = strtoupper($facture->client->raison_sociale);
        $montant = (float) $facture->montant_ht;
        $joursRetard = $dateFin->diffInDays($dateDebut);
       //$moisRetard = self::calculerMoisRetard($facture);

        $interet_ht = 0.0;

        switch (true) {
            case str_contains($client, 'ALGERIE POSTE'):
                // (Montant × Jours × 9%) / 360
                $interet_ht = ($montant * $joursRetard * 0.09) / 360.0;
                break;

            case str_contains($client, 'CPA'):
                // (Montant × 5% × Mois)
                $interet_ht = $montant * 0.05 * 1;
                break;

            case str_contains($client, 'BNA'):
            case str_contains($client, 'BDL'):
            case str_contains($client, 'CNEP'):
                // (Montant × 10% × Mois)
                $interet_ht = $montant * 0.10 * 1;
                break;

            default:
                // fallback = jours/360 avec taux générique
                $taux = (float) ($facture->client->taux ?? 0.1);
                $interet_ht = ($montant * $joursRetard * $taux) / 360.0;
        }

        $interet_ht = round($interet_ht, 2);
        $interet_ttc = round($interet_ht * 1.19, 2); // TVA 19%

        return [
            'jours_retard' => $joursRetard,
            'mois_retard' => 1,
            'interet_ht' => $interet_ht,
            'interet_ttc' => $interet_ttc,
        ];
    }

    /**
     * Générer les périodes d'intérêts (utile pour CPA, BNA, BDL, CNEP)
     */
    public static function genererPeriodesInterets(Facture $facture): array
    {
        if (!$facture->peutGenererInterets()) {
            return [];
        }

        $delai = $facture->delai_legal_jours ?? 30;
        $dateDebutGrace = $facture->date_depot->copy()->addDays($delai);
        $moisRetard = self::calculerMoisRetard($facture);

        $periodes = [];

        for ($mois = 1; $mois <= $moisRetard; $mois++) {
            $dateDebutPeriode = $dateDebutGrace->copy()->addMonths($mois - 1);
            $dateFinPeriode = $dateDebutPeriode->copy()->addMonth();

            // Si facture payée avant la fin de la période → couper
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
        if (Interet::existsForPeriod($facture->id, $dateDebut, $dateFin)) {
            return null;
        }

        $resultat = self::calculerInteretsPourPeriode($facture, $dateDebut, $dateFin);

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
