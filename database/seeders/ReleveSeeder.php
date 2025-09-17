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

            $releve = Releve::create([
                'client_id' => $client->id,
                'date_debut' => $dateDebut->toDateString(),
                'date_fin' => $dateFin->toDateString(),
                'date_creation' => Carbon::now()->toDateString(),
                'statut' => 'Impayé',
            ]);

            $factures = Facture::where('client_id', $client->id)
                ->whereBetween('date_facture', [$dateDebut->toDateString(), $dateFin->toDateString()])
                ->get();

            foreach ($factures as $facture) {
                $facture->releve_id = $releve->id;
                $facture->save();
            }

            $releve->mettreAJourStatut();
        }
    }
}


