<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Facture;
use App\Models\Client;
use Livewire\WithPagination;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FacturesExport;

class FactureTable extends Component
{
    use WithPagination;

    public $selectedClient = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $clients = [];

    protected $queryString = [
        'selectedClient' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount()
    {
        $this->clients = Client::orderBy('raison_sociale')->get();
    }

    public function updatedSelectedClient()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function exportPdf()
    {
        $factures = $this->getFilteredFactures();
        $pdf = PDF::loadView('exports.factures-pdf', compact('factures'));
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'factures-interets.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new FacturesExport($this->selectedClient, $this->dateFrom, $this->dateTo), 'factures-interets.xlsx');
    }

    private function getFilteredFactures()
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

        return $query->orderBy('date_facture', 'desc')->get();
    }

    private function calculateInterets($facture)
    {
        $jours_retards = 0;
        if ($facture->date_depot && is_null($facture->date_reglement)) {
            $jours_retards = now()->diffInDays(Carbon::parse($facture->date_depot));
        }
        
        $result = $facture->calculerInteretsMoratoires($jours_retards);
        return [
            'jours_retards' => $jours_retards,
            'interet_ht' => $result['interet_ht'],
            'interet_ttc' => $result['interet_ttc']
        ];
    }

    public function render()
    {
        $factures = $this->getFilteredFactures();
        
        // Calculer les intérêts pour chaque facture
        $facturesWithInterets = $factures->map(function ($facture) {
            $interets = $this->calculateInterets($facture);
            $facture->jours_retards = $interets['jours_retards'];
            $facture->interet_ht = $interets['interet_ht'];
            $facture->interet_ttc = $interets['interet_ttc'];
            return $facture;
        });

        return view('livewire.facture-table', [
            'factures' => $facturesWithInterets
        ]);
    }
}
