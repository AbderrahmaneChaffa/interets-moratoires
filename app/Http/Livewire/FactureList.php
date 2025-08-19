<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Facture;
use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FacturesExport;

class FactureList extends Component
{
    public $expandedId = null;
    public $selectedClient = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Champs d'édition
    public $facture_id, $reference, $prestation, $date_facture, $montant_ht, $date_depot, $date_reglement, $net_a_payer, $statut, $delai_legal_jours = 30;
    public $showModal = false;

    protected $rules = [
        'reference' => 'required|string|max:255',
        'prestation' => 'nullable|string|max:255',
        'date_facture' => 'required|date',
        'montant_ht' => 'required|numeric',
        'date_depot' => 'required|date',
        'date_reglement' => 'nullable|date',
        'net_a_payer' => 'required|numeric',
        'statut' => 'required|string|in:En attente,Payée,Retard de paiement,Impayée',
        'delai_legal_jours' => 'required|integer|min:1|max:365',
    ];

    public function render()
    {
        $query = Facture::with(['client','sousFactures' => function($q){ $q->orderBy('date_facture'); }])
            ->where('type','principale');

        if (!empty($this->selectedClient)) {
            $query->where('client_id', $this->selectedClient);
        }
        if (!empty($this->dateFrom)) {
            $query->whereDate('date_facture', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('date_facture', '<=', $this->dateTo);
        }

        $factures = $query->orderBy('date_facture','desc')->paginate(10);
        $clients = Client::orderBy('raison_sociale')->get();
        return view('livewire.facture-list', compact('factures','clients'));
    }
    
    public function calculInteret($factureId)
    {
        $facture = Facture::findOrFail($factureId);
        $resultat = $facture->mettreAJourStatutEtInterets();
        
        session()->flash('interet', [
            'facture_id' => $facture->id,
            'statut' => $resultat['statut'],
            'interets' => $resultat['interets'],
            'message' => 'Statut mis à jour: ' . $resultat['statut'] . ', Intérêts: ' . number_format($resultat['interets'], 2) . ' DA'
        ]);
    }
    
    public function mettreAJourToutesFactures()
    {
        $factures = Facture::all();
        $compteur = 0;
        
        foreach ($factures as $facture) {
            $facture->mettreAJourStatutEtInterets();
            $compteur++;
        }
        
        session()->flash('message', $compteur . ' factures mises à jour avec succès.');
    }

    public function toggle($id)
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
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
        $f->mettreAJourStatutEtInterets();
        $this->showModal = false;
        session()->flash('message', 'Facture mise à jour.');
    }

    public function deleteParent($id)
    {
        $f = Facture::findOrFail($id);
        if ($f->type !== 'principale') {
            session()->flash('message', 'Suppression autorisée uniquement pour les factures principales.');
            return;
        }
        $f->delete();
        session()->flash('message', 'Facture principale supprimée (et ses sous-factures).');
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
