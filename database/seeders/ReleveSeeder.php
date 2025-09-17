<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Facture;
use App\Models\Releve;
use Carbon\Carbon;

class ReleveSeeder extends Seeder
{
    public function run()
    {
        $clients = Client::all();
        if ($clients->isEmpty()) {
            return;
        }

        foreach ($clients as $client) {
            $dateFin = Carbon::now();
            $dateDebut = $dateFin->copy()->subMonth();

            $reference = 'REL-' . strtoupper(preg_replace('/[^A-Z0-9]/', '', substr($client->raison_sociale ?? 'CLT', 0, 6))) . '-' . $dateFin->format('Ym') . '-' . $client->id;

            $releve = Releve::create([
                'client_id' => $client->id,
                'reference' => $reference,
                'date_debut' => $dateDebut->toDateString(),
                'date_fin' => $dateFin->toDateString(),
                'date_creation' => Carbon::now()->toDateString(),
                'categorie' => 'Automatique',
                'montant_total_ht' => 0,
                'statut' => 'En attente',
            ]);

            $factures = Facture::where('client_id', $client->id)
                ->whereBetween('date_facture', [$dateDebut->toDateString(), $dateFin->toDateString()])
                ->get();

            $total = 0;
            foreach ($factures as $facture) {
                $facture->releve_id = $releve->id;
                $facture->save();
                $total += (float) $facture->montant_ht;
            }

            $releve->montant_total_ht = $total;
            $releve->mettreAJourStatut();
        }
    }
}


