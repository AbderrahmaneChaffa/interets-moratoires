<?php

namespace App\Exports;

use App\Models\Facture;
use Carbon\Carbon;

class FacturesExport
{
    protected $selectedClient;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($selectedClient = '', $dateFrom = '', $dateTo = '')
    {
        $this->selectedClient = $selectedClient;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function getData()
    {
        $query = Facture::with('client');

        if ($this->selectedClient) {
            $query->where('client_id', $this->selectedClient);
        }

        if ($this->dateFrom) {
            $query->where('date_facture', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('date_facture', '<=', $this->dateTo);
        }

        $factures = $query->orderBy('date_facture', 'desc')->get();

        $rows = [];

        // Ligne d'entêtes
        $rows[] = [
            'Référence',
            'Date facture',
            'Client',
            'Montant HT',
            'Jours de retard',
            'Intérêt HT',
            'Intérêt TTC'
        ];

        // Lignes de données
        foreach ($factures as $facture) {
            $jours_retards = 0;
            if ($facture->date_depot && is_null($facture->date_reglement)) {
                $jours_retards = now()->diffInDays(Carbon::parse($facture->date_depot));
            }

            $result = app('App\Services\InteretService')
                ::calculerInteretsPourPeriode($facture, $facture->date_depot ?: now(), now());

            $rows[] = [
                $facture->reference,
                Carbon::parse($facture->date_facture)->format('d/m/Y'),
                $facture->client->raison_sociale ?? '-',
                number_format($facture->montant_ht, 2) . ' DA',
                $jours_retards . ' jours',
                number_format($result['interet_ht'], 2) . ' DA',
                number_format($result['interet_ttc'], 2) . ' DA'
            ];
        }

        return $rows;
    }
}
