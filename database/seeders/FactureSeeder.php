<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Facture;
use App\Models\Client;

class FactureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = Client::all();
        if ($clients->count() < 3) {
            $this->command->warn('Veuillez d’abord exécuter le ClientSeeder.');
            return;
        }
        $factures = [
            [
                'client_id' => $clients[0]->id,
                'reference' => 'FAC-001',
                'date_facture' => now()->subMonths(3),
                'montant_ht' => 100000,
                'date_depot' => now()->subMonths(2)->subDays(10),
                'date_reglement' => null,
                'net_a_payer' => 119000,
                'statut_paiement' => 'en retard',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clients[1]->id,
                'reference' => 'FAC-002',
                'date_facture' => now()->subMonths(4),
                'montant_ht' => 200000,
                'date_depot' => now()->subMonths(3)->subDays(5),
                'date_reglement' => null,
                'net_a_payer' => 238000,
                'statut_paiement' => 'en retard',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clients[2]->id,
                'reference' => 'FAC-003',
                'date_facture' => now()->subMonths(2),
                'montant_ht' => 150000,
                'date_depot' => now()->subMonths(1)->subDays(20),
                'date_reglement' => null,
                'net_a_payer' => 178500,
                'statut_paiement' => 'en retard',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clients[0]->id,
                'reference' => 'FAC-004',
                'date_facture' => now()->subMonths(1),
                'montant_ht' => 50000,
                'date_depot' => now()->subDays(40),
                'date_reglement' => null,
                'net_a_payer' => 59500,
                'statut_paiement' => 'en retard',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clients[1]->id,
                'reference' => 'FAC-005',
                'date_facture' => now()->subMonths(5),
                'montant_ht' => 300000,
                'date_depot' => now()->subMonths(4)->subDays(15),
                'date_reglement' => null,
                'net_a_payer' => 357000,
                'statut_paiement' => 'en retard',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        Facture::insert($factures);
    }
}
