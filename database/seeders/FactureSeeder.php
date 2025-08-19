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

        // Facture payée à temps
        Facture::create([
            'client_id' => $clients->first()->id,
            'reference' => 'FACT-2024-001',
            'date_facture' => Carbon::now()->subDays(60),
            'montant_ht' => 1500.00,
            'date_depot' => Carbon::now()->subDays(50),
            'date_reglement' => Carbon::now()->subDays(20),
            'net_a_payer' => 1785.00,
            // 'statut_paiement' supprimé
            'statut' => 'Payée',
            'interets' => 0.00,
            'delai_legal_jours' => 30,
        ]);

        // Facture en retard de paiement
        Facture::create([
            'client_id' => $clients->first()->id,
            'reference' => 'FACT-2024-002',
            'date_facture' => Carbon::now()->subDays(80),
            'montant_ht' => 2500.00,
            'date_depot' => Carbon::now()->subDays(70),
            'date_reglement' => Carbon::now()->subDays(10),
            'net_a_payer' => 2975.00,
            // 'statut_paiement' supprimé
            'statut' => 'Retard de paiement',
            'interets' => 0.00, // Sera calculé automatiquement
            'delai_legal_jours' => 30,
        ]);

        // Facture impayée
        Facture::create([
            'client_id' => $clients->first()->id,
            'reference' => 'FACT-2024-003',
            'date_facture' => Carbon::now()->subDays(90),
            'montant_ht' => 3000.00,
            'date_depot' => Carbon::now()->subDays(80),
            'date_reglement' => null,
            'net_a_payer' => 3570.00,
            // 'statut_paiement' supprimé
            'statut' => 'Impayée',
            'interets' => 0.00, // Sera calculé automatiquement
            'delai_legal_jours' => 30,
        ]);

        // Facture en attente
        Facture::create([
            'client_id' => $clients->first()->id,
            'reference' => 'FACT-2024-004',
            'date_facture' => Carbon::now()->subDays(20),
            'montant_ht' => 800.00,
            'date_depot' => Carbon::now()->subDays(15),
            'date_reglement' => null,
            'net_a_payer' => 952.00,
            // 'statut_paiement' supprimé
            'statut' => 'En attente',
            'interets' => 0.00,
            'delai_legal_jours' => 30,
        ]);

        // Mettre à jour les intérêts pour toutes les factures
        $factures = Facture::all();
        foreach ($factures as $facture) {
            $facture->mettreAJourStatutEtInterets();
        }

        $this->command->info('Factures créées avec succès !');
    }
}
