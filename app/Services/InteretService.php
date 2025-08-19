<?php

namespace App\Services;

use App\Models\Facture;
use Carbon\Carbon;

class InteretService
{
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

    public static function calculerMoisRetard(Facture $facture): int
    {
        $jours = self::calculerJoursRetard($facture);
        return (int) max(0, ceil($jours / 30));
    }

    public static function calculerInterets(Facture $facture, float $taux, string $formule): array
    {
        $montant = (float) $facture->montant_ht;
        $jours = self::calculerJoursRetard($facture);
        $mois = self::calculerMoisRetard($facture);

        $interet_ht = 0.0;
        if (stripos($formule, 'jours') !== false) {
            // (Montant × Jours × Taux) / 360
            $interet_ht = ($montant * $jours * $taux) / 360.0;
        } elseif (stripos($formule, 'mois') !== false) {
            // (Montant × Taux × Mois)
            $interet_ht = $montant * $taux * $mois;
        } else {
            // Par défaut: jours/360
            $interet_ht = ($montant * $jours * $taux) / 360.0;
        }

        $interet_ht = round($interet_ht, 2);
        $interet_ttc = round($interet_ht * 1.19, 2); // TVA 19%

        return [
            'jours' => $jours,
            'mois' => $mois,
            'interet_ht' => $interet_ht,
            'interet_ttc' => $interet_ttc,
        ];
    }
}
