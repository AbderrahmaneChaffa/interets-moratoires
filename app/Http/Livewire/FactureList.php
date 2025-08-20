<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Facture;
use App\Models\Client;
use App\Services\InteretService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FacturesExport;

class FactureList extends Component
{
    use WithPagination;

    public $expandedId = null;
    public $selectedClient = '';
    public $selectedStatut = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $montantMin = '';
    public $montantMax = '';

    // Champs d'édition
    public $facture_id, $reference, $prestation, $date_facture, $montant_ht, $date_depot, $date_reglement, $net_a_payer, $statut, $delai_legal_jours = 30;
    public $showModal = false;
    public $showDetailsModal = false;
    public $showDeleteModal = false;
    public $factureDetails = null;

    protected $rules = [
        'reference' => 'required|string|max:255',
        'prestation' => 'nullable|string|max:255',
        'date_facture' => 'required|date',
        'montant_ht' => 'required|numeric|min:0',
        'date_depot' => 'required|date',
        'date_reglement' => 'nullable|date',
        'net_a_payer' => 'required|numeric|min:0',
        'statut' => 'required|string|in:En attente,Payée,Retard de paiement,Impayée',
        'delai_legal_jours' => 'required|integer|min:1|max:365',
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
        $query = Facture::with(['client', 'interets'])
            ->where('type', 'principale');

        // Filtres
        if (!empty($this->selectedClient)) {
            $query->where('client_id', $this->selectedClient);
        }
        if (!empty($this->selectedStatut)) {
            $query->where('statut', $this->selectedStatut);
        }
        if (!empty($this->dateFrom)) {
            $query->whereDate('date_facture', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('date_facture', '<=', $this->dateTo);
        }
        if (!empty($this->montantMin)) {
            $query->where('montant_ht', '>=', $this->montantMin);
        }
        if (!empty($this->montantMax)) {
            $query->where('montant_ht', '<=', $this->montantMax);
        }

        $factures = $query->orderBy('date_facture', 'desc')->paginate(15);
        $clients = Client::orderBy('raison_sociale')->get();

        return view('livewire.facture-list', compact('factures', 'clients'));
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
        $this->factureDetails = Facture::with(['client', 'interets'])->findOrFail($id);
        $this->showDetailsModal = true;
        $this->dispatchBrowserEvent('open-facture-details');

    }

    public function openEdit($id)
    {
        $f = Facture::findOrFail($id);
        $this->facture_id = $f->id;
        $this->reference = $f->reference;
        $this->prestation = $f->prestation;
        $this->date_facture = optional($f->date_facture)->format('Y-m-d');
        $this->montant_ht = $f->montant_ht;
        $this->date_depot = optional($f->date_depot)->format('Y-m-d');
        $this->date_reglement = optional($f->date_reglement)->format('Y-m-d');
        $this->net_a_payer = $f->net_a_payer;
        $this->statut = $f->statut;
        $this->delai_legal_jours = $f->delai_legal_jours ?? 30;
         $this->showModal = true;
        $this->dispatchBrowserEvent('open-facture-modal');

    }

    public function save()
    {
        $this->validate();
        $f = Facture::findOrFail($this->facture_id);
        $f->update([
            'reference' => $this->reference,
            'prestation' => $this->prestation,
            'date_facture' => $this->date_facture,
            'montant_ht' => $this->montant_ht,
            'date_depot' => $this->date_depot,
            'date_reglement' => $this->date_reglement ?: null,
            'net_a_payer' => $this->net_a_payer,
            'statut' => $this->statut,
            'delai_legal_jours' => $this->delai_legal_jours,
        ]);
        $f->mettreAJourStatut();
        $this->showModal = false;
        // ferme le modal d’édition
        $this->dispatchBrowserEvent('close-facture-modal');
        session()->flash('message', 'Facture mise à jour avec succès.');
    }

    public function confirmDelete($id)
    {
        $this->facture_id = $id;
        $this->dispatchBrowserEvent('open-facture-delete');

         $this->showDeleteModal = true;
    }
    public function closeDetails()
    {
        $this->factureDetails = null;
        $this->dispatchBrowserEvent('close-facture-details');
    }
    public function delete()
    {
        $f = Facture::findOrFail($this->facture_id);
        if ($f->type !== 'principale') {
            session()->flash('error', 'Suppression autorisée uniquement pour les factures principales.');
            return;
        }
        $f->delete();
         $this->showDeleteModal = false;
        $this->dispatchBrowserEvent('close-facture-delete');

        session()->flash('message', 'Facture supprimée avec succès.');
    }

    public function calculerInteret($factureId)
    {
        $facture = Facture::with('client')->findOrFail($factureId);

        if (!$facture->peutGenererInterets()) {
            session()->flash('error', 'Cette facture ne peut pas générer d\'intérêts moratoires.');
            return;
        }

        $interetsCrees = InteretService::calculerEtSauvegarderTousInterets($facture);

        if (empty($interetsCrees)) {
            session()->flash('info', 'Tous les intérêts pour cette facture ont déjà été calculés.');
        } else {
            session()->flash('message', count($interetsCrees) . ' période(s) d\'intérêts calculée(s) et sauvegardée(s).');
        }
    }

    public function calculerInteretPeriode($factureId, $dateDebut, $dateFin)
    {
        $facture = Facture::with('client')->findOrFail($factureId);
        $dateDebut = \Carbon\Carbon::parse($dateDebut);
        $dateFin = \Carbon\Carbon::parse($dateFin);

        $interet = InteretService::calculerEtSauvegarderInterets($facture, $dateDebut, $dateFin);

        if ($interet) {
            session()->flash('message', 'Intérêt calculé et sauvegardé pour cette période.');
        } else {
            session()->flash('info', 'Intérêt déjà calculé pour cette période.');
        }
    }

    public function exportExcel()
    {
        return Excel::download(new FacturesExport($this->selectedClient, $this->dateFrom, $this->dateTo), 'factures-interets.xlsx');
    }

    public function exportPdf()
    {
        $query = Facture::with('client');
        if (!empty($this->selectedClient)) {
            $query->where('client_id', $this->selectedClient);
        }
        if (!empty($this->dateFrom)) {
            $query->whereDate('date_facture', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('date_facture', '<=', $this->dateTo);
        }
        $factures = $query->orderBy('date_facture', 'desc')->get();
        $pdf = PDF::loadView('exports.factures-pdf', compact('factures'));
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'factures-interets.pdf');
    }
}
