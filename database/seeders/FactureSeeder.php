<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facture;
use App\Models\Client;
use Carbon\Carbon;

class FactureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        
        if ($clients->isEmpty()) {
            $this->command->info('Aucun client trouvé. Créez d\'abord des clients.');
            return;
        }

        // Créer des factures tests pour les 5 premiers clients
        $index = 1;
        foreach ($clients->take(5) as $client) {
            // 1) payée à temps
            Facture::create([
                'client_id' => $client->id,
                'type' => 'principale',
                'reference' => 'F-' . $index . '-001',
                'prestation' => 'Maintenance GAB',
                'date_facture' => Carbon::now()->subDays(60),
                'montant_ht' => 13568915.50,
                'date_depot' => Carbon::now()->subDays(59),
                'date_reglement' => Carbon::now()->subDays(25),
                'net_a_payer' => 13568915.50 * 1.19,
                'statut' => 'Payée',
                'interets' => 0.00,
                'delai_legal_jours' => 30,
            ])->mettreAJourStatutEtInterets();

            // 2) impayée avec 65 jours de retard (devrait générer 2 sous-factures)
            $principale = Facture::create([
                'client_id' => $client->id,
                'type' => 'principale',
                'reference' => 'F-' . $index . '-002',
                'prestation' => 'Maintenance Contrat',
                'date_facture' => Carbon::now()->subDays(90),
                'montant_ht' => 8000000.00,
                'date_depot' => Carbon::now()->subDays(85),
                'date_reglement' => null,
                'net_a_payer' => 8000000.00 * 1.19,
                'statut' => 'Impayée',
                'interets' => 0.00,
                'delai_legal_jours' => 30,
            ]);
            $principale->mettreAJourStatutEtInterets();

            // Générer 2 sous-factures d'intérêts
            $calc = app(\App\Services\InteretService::class)::calculerInterets($principale, (float) ($client->taux ?? 0), (string) ($client->formule ?? ''));
            for ($m = 1; $m <= 2; $m++) {
                Facture::create([
                    'client_id' => $client->id,
                    'parent_id' => $principale->id,
                    'type' => 'interet',
                    'reference' => 'F-' . $index . '-002/INT-' . str_pad($m, 2, '0', STR_PAD_LEFT),
                    'prestation' => 'Intérêts moratoires',
                    'date_facture' => Carbon::now()->subDays(30 * (2 - $m)),
                    'montant_ht' => 0,
                    'date_depot' => $principale->date_depot,
                    'date_reglement' => null,
                    'net_a_payer' => 0,
                    'statut' => 'En attente',
                    'delai_legal_jours' => $principale->delai_legal_jours,
                    'interets_ht' => $calc['interet_ht'],
                    'interets_ttc' => $calc['interet_ttc'],
                    'interets' => $calc['interet_ttc'],
                ]);
            }

            $index++;
        }

        $this->command->info('Clients et factures (principales + intérêts) créés avec succès !');
    }
}
