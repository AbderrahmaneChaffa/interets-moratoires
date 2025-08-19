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
                'delai_legal_jours' => 30,
            ])->mettreAJourStatut();

            // 2) impayée avec 65 jours de retard
            $facture = Facture::create([
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
                'delai_legal_jours' => 30,
            ]);
            $facture->mettreAJourStatut();

            $index++;
        }

        $this->command->info('Factures créées avec succès !');
    }
}
