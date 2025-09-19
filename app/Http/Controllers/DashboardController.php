<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Facture;
use App\Models\Interet;
use App\Models\Releve;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalClients = Client::count();
        $totalReleves = Releve::count();
        $totalFacturesInReleves = Facture::whereNotNull('releve_id')->count();
        $totalInterets = Interet::sum('interet_ht');

        $lastClient = Client::latest()->first();
        $lastClientDate = $lastClient ? $lastClient->created_at->format('d/m/Y') : 'Aucune';

        $monthlyReleves = Releve::whereMonth('created_at', now()->month)->count();
        $monthlyInterests = Interet::whereMonth('created_at', now()->month)->sum('interet_ht');
        $monthlyClients = Client::whereMonth('created_at', now()->month)->count();
        $averageRate = Client::avg('taux') ?? 0;

        // Activités récentes incluant les relevés et factures
        $recentReleves = Releve::latest()->take(3)->get();
        $recentFactures = Facture::latest()->take(2)->get();
        
        $recentActivities = collect()
            ->merge($recentReleves->map(function ($releve) {
                return [
                    'icon' => 'list-alt',
                    'title' => "Relevé #{$releve->reference}",
                    'description' => "Client : {$releve->client->raison_sociale} - {$releve->factures->count()} factures",
                    'time' => $releve->created_at->diffForHumans(),
                    'type' => 'releve'
                ];
            }))
            ->merge($recentFactures->map(function ($facture) {
                return [
                    'icon' => 'file-invoice',
                    'title' => "Facture #{$facture->id}",
                    'description' => "Client : {$facture->client->raison_sociale}",
                    'time' => $facture->created_at->diffForHumans(),
                    'type' => 'facture'
                ];
            }))
            ->sortByDesc('time')
            ->take(5)
            ->values();

        return view('welcome', compact(
            'totalClients',
            'totalReleves',
            'totalFacturesInReleves',
            'totalInterets',
            'lastClientDate',
            'monthlyReleves',
            'monthlyInterests',
            'monthlyClients',
            'averageRate',
            'recentActivities'
        ));
    }
}
