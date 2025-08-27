<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Facture;
use App\Models\Interet;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalClients   = Client::count();
        $totalFactures  = Facture::count();
        $totalInterets  = Interet::sum('interet_ht');

        $lastClient     = Client::latest()->first();
        $lastClientDate = $lastClient ? $lastClient->created_at->format('d/m/Y') : 'Aucune';

        $monthlyInvoices  = Facture::whereMonth('created_at', now()->month)->count();
        $monthlyInterests = Interet::whereMonth('created_at', now()->month)->sum('interet_ht');
        $monthlyClients   = Client::whereMonth('created_at', now()->month)->count();
        $averageRate      = Client::avg('taux') ?? 0;

        $recentActivities = Facture::latest()->take(5)->get()->map(function ($facture) {
            return [
                'icon' => 'file-invoice',
                'title' => "Nouvelle facture #{$facture->id}",
                'description' => "Client : {$facture->client->raison_sociale}",
                'time' => $facture->created_at->diffForHumans(),
            ];
        });

        return view('welcome', compact(
            'totalClients',
            'totalFactures',
            'totalInterets',
            'lastClientDate',
            'monthlyInvoices',
            'monthlyInterests',
            'monthlyClients',
            'averageRate',
            'recentActivities'
        ));
    }
}
