<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Releve;
use App\Models\Client;
use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReleveList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    public $expandedId = null;
    public $selectedClient = '';
    public $selectedStatut = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $montantMin = '';
    public $montantMax = '';

    // Champs d'édition
    public $releve_id, $reference, $date_debut, $date_fin, $date_creation, $categorie, $montant_total_ht, $statut, $date_derniere_facture;
    public $showModal = false;
    public $showDetailsModal = false;
    public $showDeleteModal = false;
    public $releveDetails = null;

    protected $rules = [
        'reference' => 'required|string|max:255',
        'date_debut' => 'required|date',
        'date_fin' => 'required|date|after:date_debut',
        'date_creation' => 'nullable|date',
        'categorie' => 'nullable|string|max:255',
        'montant_total_ht' => 'nullable|numeric|min:0',
        'statut' => 'required|string|in:En attente,Payé,Impayé',
        'date_derniere_facture' => 'nullable|date',
    ];

    protected $queryString = [
        'selectedClient' => ['except' => ''],
        'selectedStatut' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'montantMin' => ['except' => ''],
        'montantMax' => ['except' => ''],
    ];

    public function render()
    {
        $query = Releve::with(['client', 'factures']);

        // Filtres
        if (!empty($this->selectedClient)) {
            $query->where('client_id', $this->selectedClient);
        }
        if (!empty($this->selectedStatut)) {
            $query->where('statut', $this->selectedStatut);
        }
        if (!empty($this->dateFrom)) {
            $query->whereDate('date_debut', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('date_fin', '<=', $this->dateTo);
        }
        if (!empty($this->montantMin)) {
            $query->where('montant_total_ht', '>=', $this->montantMin);
        }
        if (!empty($this->montantMax)) {
            $query->where('montant_total_ht', '<=', $this->montantMax);
        }

        $releves = $query->orderBy('date_creation', 'desc')->paginate(15);
        $clients = Client::orderBy('raison_sociale')->get();

        return view('livewire.releve-list', compact('releves', 'clients'));
    }

    public function resetFilters()
    {
        $this->reset(['selectedClient', 'selectedStatut', 'dateFrom', 'dateTo', 'montantMin', 'montantMax']);
        $this->resetPage();
    }

    public function toggle($id)
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function showDetails($id)
    {
        $this->releveDetails = Releve::with(['client', 'factures'])->findOrFail($id);
        $this->showDetailsModal = true;
        $this->dispatchBrowserEvent('open-releve-details');
    }

    public function openEdit($id)
    {
        $r = Releve::findOrFail($id);
        $this->releve_id = $r->id;
        $this->reference = $r->reference;
        $this->date_debut = optional($r->date_debut)->format('Y-m-d');
        $this->date_fin = optional($r->date_fin)->format('Y-m-d');
        $this->date_creation = optional($r->date_creation)->format('Y-m-d');
        $this->categorie = $r->categorie;
        $this->montant_total_ht = $r->montant_total_ht;
        $this->statut = $r->statut;
        $this->date_derniere_facture = optional($r->date_derniere_facture)->format('Y-m-d');
        $this->showModal = true;
        $this->dispatchBrowserEvent('open-releve-modal');
    }

    public function save()
    {
        $this->validate();
        $r = Releve::findOrFail($this->releve_id);
        $r->update([
            'reference' => $this->reference,
            'date_debut' => $this->date_debut,
            'date_fin' => $this->date_fin,
            'date_creation' => $this->date_creation,
            'categorie' => $this->categorie,
            'montant_total_ht' => $this->montant_total_ht,
            'statut' => $this->statut,
            'date_derniere_facture' => $this->date_derniere_facture,
        ]);
        $this->showModal = false;
        $this->dispatchBrowserEvent('close-releve-modal');
        session()->flash('message', 'Relevé mis à jour avec succès.');
    }

    public function confirmDelete($id)
    {
        $this->releve_id = $id;
        $this->dispatchBrowserEvent('open-releve-delete');
        $this->showDeleteModal = true;
    }

    public function closeDetails()
    {
        $this->releveDetails = null;
        $this->dispatchBrowserEvent('close-releve-details');
    }

    public function delete()
    {
        $r = Releve::findOrFail($this->releve_id);
        $r->delete();
        $this->showDeleteModal = false;
        $this->dispatchBrowserEvent('close-releve-delete');
        session()->flash('message', 'Relevé supprimé avec succès.');
    }

    public function calculerInterets($releveId)
    {
        $releve = Releve::with('client')->findOrFail($releveId);
        $interetsCrees = $releve->calculerInterets();

        if (empty($interetsCrees)) {
            session()->flash('info', 'Tous les intérêts pour ce relevé ont déjà été calculés.');
        } else {
            session()->flash('message', count($interetsCrees) . ' période(s) d\'intérêts calculée(s) et sauvegardée(s).');
        }
    }

    public function exportExcel()
    {
        $query = Releve::with(['client', 'factures']);

        if ($this->selectedClient) {
            $query->where('client_id', $this->selectedClient);
        }
        if ($this->dateFrom) {
            $query->where('date_debut', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('date_fin', '<=', $this->dateTo);
        }

        $releves = $query->orderBy('date_creation', 'desc')->get();

        return Excel::create('releves', function ($excel) use ($releves) {
            $excel->sheet('Relevés', function ($sheet) use ($releves) {
                $rows = [];
                $rows[] = [
                    'Référence',
                    'Client',
                    'Date début',
                    'Date fin',
                    'Date création',
                    'Montant total HT',
                    'Statut',
                    'Date dernière facture',
                    'Nombre factures'
                ];

                foreach ($releves as $releve) {
                    $rows[] = [
                        $releve->reference,
                        $releve->client->raison_sociale ?? '-',
                        $releve->date_debut->format('d/m/Y'),
                        $releve->date_fin->format('d/m/Y'),
                        $releve->date_creation ? $releve->date_creation->format('d/m/Y') : '-',
                        number_format($releve->montant_total_ht, 2) . ' DA',
                        $releve->statut,
                        $releve->date_derniere_facture ? $releve->date_derniere_facture->format('d/m/Y') : '-',
                        $releve->factures->count()
                    ];
                }

                $sheet->fromArray($rows, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPdf()
    {
        $query = Releve::with(['client', 'factures']);
        if (!empty($this->selectedClient)) {
            $query->where('client_id', $this->selectedClient);
        }
        if (!empty($this->dateFrom)) {
            $query->whereDate('date_debut', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('date_fin', '<=', $this->dateTo);
        }
        $releves = $query->orderBy('date_creation', 'desc')->get();
        
        $pdf = PDF::loadView('exports.releves-pdf', compact('releves'));
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'releves.pdf');
    }
}

