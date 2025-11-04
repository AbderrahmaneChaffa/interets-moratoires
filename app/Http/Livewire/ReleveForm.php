<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Releve;
use App\Models\Client;
use App\Models\Facture;
use Illuminate\Validation\Rule;

class ReleveForm extends Component
{
    use WithFileUploads;
    public $client_id;
    public $reference;
    public $date_debut;
    public $date_fin;
    public $date_creation;
    public $categorie;
    public $montant_total_ht;
    public $statut = 'Impayé';
    public $date_derniere_facture;
    public $releve_pdf;

    public $factures = [];

    protected function rules()
    {
        $hasFactures = !empty(array_filter($this->factures, function($f) {
            return !empty($f['reference']) || !empty($f['montant_ht']) || !empty($f['date_facture']);
        }));

        return [
            'client_id' => 'required|exists:clients,id',
            'reference' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'date_creation' => 'nullable|date',
            'categorie' => 'nullable|string|max:255',
            'montant_total_ht' => 'nullable|numeric|min:0',
            'statut' => 'required|string|in:Payé,Impayé',
            'date_derniere_facture' => $hasFactures ? 'required|date' : 'nullable|date',
            'releve_pdf' => 'nullable|file|mimes:pdf|max:2048',
            'factures' => 'array',
            'factures.*.reference' => 'required_with:factures.*.montant_ht,factures.*.date_facture|string|max:255',
            'factures.*.date_facture' => 'required_with:factures.*.reference|date',
            'factures.*.montant_ht' => 'required_with:factures.*.reference|numeric|min:0',
            'factures.*.reste_a_payer' => 'nullable|numeric|min:0',
            'factures.*.categorie' => 'nullable|string|max:255',
            'factures.*.pdf_file' => 'nullable|file|mimes:pdf|max:2048',
        ];
    }

    public function mount()
    {
        $this->date_creation = now()->toDateString();
        $this->factures = [
            // Start with one empty line for UX
        ];
    }

    public function addFacture()
    {
        $this->factures[] = [
            'reference' => '',
            'date_facture' => '',
            'montant_ht' => '',
            'reste_a_payer' => '',
            'categorie' => '',
            'pdf_file' => null,
        ];
    }

    public function removeFacture($index)
    {
        unset($this->factures[$index]);
        $this->factures = array_values($this->factures);
        $this->updateMontantTotal();
    }

    public function updatedFactures()
    {
        $this->updateMontantTotal();
        $this->updateDateDerniereFacture();
    }

    private function updateMontantTotal()
    {
        $sum = 0;
        foreach ($this->factures as $f) {
            $sum += is_numeric($f['montant_ht'] ?? null) ? (float) $f['montant_ht'] : 0;
        }
        $this->montant_total_ht = $sum;
    }

    private function updateDateDerniereFacture()
    {
        $dates = [];
        foreach ($this->factures as $f) {
            if (!empty($f['date_facture'])) {
                $dates[] = $f['date_facture'];
            }
        }
        
        if (!empty($dates)) {
            $this->date_derniere_facture = max($dates);
        }
    }

    public function store()
    {
        // Validation conditionnelle pour date_derniere_facture
        $hasFactures = !empty(array_filter($this->factures, function($f) {
            return !empty($f['reference']) || !empty($f['montant_ht']) || !empty($f['date_facture']);
        }));

        if ($hasFactures && empty($this->date_derniere_facture)) {
            $this->updateDateDerniereFacture();
        }

        $data = $this->validate();

        // If there are factures, enforce montant_total_ht equals sum
        if (!empty($this->factures)) {
            $this->updateMontantTotal();
        }

        // Upload du PDF du relevé
        $relevePdfPath = null;
        if ($this->releve_pdf) {
            $relevePdfPath = $this->releve_pdf->store('public/releves');
            $relevePdfPath = str_replace('public/', '', $relevePdfPath);
        }

        $releve = Releve::create([
            'client_id' => $this->client_id,
            'reference' => $this->reference,
            'date_debut' => $this->date_debut,
            'date_fin' => $this->date_fin,
            'date_creation' => $this->date_creation ?: now()->toDateString(),
            'categorie' => $this->categorie,
            'montant_total_ht' => $this->montant_total_ht ?? 0,
            'statut' => $this->statut,
            'date_derniere_facture' => $this->date_derniere_facture,
            'releve_pdf' => $relevePdfPath,
        ]);

        foreach ($this->factures as $f) {
            if (empty($f['reference']) && empty($f['montant_ht']) && empty($f['date_facture'])) {
                continue;
            }

            // Upload du PDF de la facture
            $facturePdfPath = null;
            if (!empty($f['pdf_file'])) {
                $facturePdfPath = $f['pdf_file']->store('public/factures');
                $facturePdfPath = str_replace('public/', '', $facturePdfPath);
            }

            Facture::create([
                'client_id' => $this->client_id,
                'releve_id' => $releve->id,
                'reference' => $f['reference'],
                'date_facture' => $f['date_facture'],
                'montant_ht' => $f['montant_ht'],
                'date_depot' => null,
                'date_reglement' => null,
                'net_a_payer' => $f['reste_a_payer'] ?? $f['montant_ht'],
                'statut' => 'En attente',
                'pdf_path' => $facturePdfPath,
            ]);
        }

        session()->flash('message', 'Relevé créé avec succès.');
        return redirect()->route('releves.interets', ['releve' => $releve->id]);
    }

    public function render()
    {
        $clients = Client::orderBy('raison_sociale')->get();
        $statuts = ['Payé' => 'Payé', 'Impayé' => 'Impayé'];
        return view('livewire.releve-form', compact('clients', 'statuts'));
    }
}