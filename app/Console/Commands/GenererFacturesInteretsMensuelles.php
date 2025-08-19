<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Facture;
use App\Services\InteretService;

class GenererFacturesInteretsMensuelles extends Command
{
    protected $signature = 'interets:generer-mensuel';
    protected $description = 'Génère automatiquement les sous-factures d\'intérêts moratoires par mois pour les factures impayées';

    public function handle(): int
    {
        $factures = Facture::with('client')
            ->where('type', 'principale')
            ->whereNull('date_reglement')
            ->get();

        $compte = 0;
        foreach ($factures as $facture) {
            $moisRetard = InteretService::calculerMoisRetard($facture);
            if ($moisRetard <= 0) {
                continue;
            }

            // Vérifier combien de sous-factures existent déjà
            $existantes = $facture->sousFactures()->count();
            $aCreer = max(0, $moisRetard - $existantes);
            if ($aCreer <= 0) {
                continue;
            }

            for ($i = 1; $i <= $aCreer; $i++) {
                $calc = InteretService::calculerInterets($facture, (float) ($facture->client->taux ?? 0), (string) ($facture->client->formule ?? ''));
                $ref = $facture->reference . '/INT-' . str_pad($existantes + $i, 2, '0', STR_PAD_LEFT);

                Facture::create([
                    'client_id' => $facture->client_id,
                    'parent_id' => $facture->id,
                    'type' => 'interet',
                    'reference' => $ref,
                    'prestation' => 'Intérêts moratoires',
                    'date_facture' => now(),
                    'montant_ht' => 0,
                    'date_depot' => $facture->date_depot,
                    'net_a_payer' => 0,
                    'statut' => 'En attente',
                    'delai_legal_jours' => $facture->delai_legal_jours,
                    'interets_ht' => $calc['interet_ht'],
                    'interets_ttc' => $calc['interet_ttc'],
                    'interets' => $calc['interet_ttc'],
                ]);
                $compte++;
            }
        }

        $this->info("Sous-factures générées: {$compte}");
        return Command::SUCCESS;
    }
}
