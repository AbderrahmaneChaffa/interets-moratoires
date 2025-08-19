<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Facture;
use App\Models\Client;
use Livewire\WithFileUploads;

class FactureForm extends Component
{
    use WithFileUploads;
    public $client_id, $client_search = '', $reference, $date_facture, $montant_ht, $date_depot, $date_reglement, $net_a_payer;
    public $statut, $delai_legal_jours = 30;
    public $clients = [];
    public $showClientDropdown = false;
    public $facture_pdf;
    public $calculer_statut_auto = true;

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'reference' => 'required|string|max:255',
        'date_facture' => 'required|date',
        'montant_ht' => 'required|numeric',
        'date_depot' => 'required|date',
        'date_reglement' => 'nullable|date',
        'net_a_payer' => 'required|numeric',
        'statut' => 'required|string|in:En attente,Payée,Retard de paiement,Impayée',
        'delai_legal_jours' => 'required|integer|min:1|max:365',
        'facture_pdf' => 'nullable|file|mimes:pdf|max:20480', // max 20MB
    ];

    public function updatedClientSearch()
    {
        $this->clients = Client::where('raison_sociale', 'like', '%' . $this->client_search . '%')->get();
        $this->showClientDropdown = true;
    }

    public function selectClient($id, $name)
    {
        $this->client_id = $id;
        $this->client_search = $name;
        $this->showClientDropdown = false;
    }

    public function store()
    {
        logger('Tentative de création de facture', $this->all());

        $this->validate();

        try {
            if ($this->facture_pdf) {
                $pdfPath = $this->facture_pdf->store('factures','public');;
            }

            // Créer la facture
            $facture = Facture::create([
                'client_id' => $this->client_id,
                'reference' => $this->reference,
                'date_facture' => $this->date_facture,
                'montant_ht' => $this->montant_ht,
                'date_depot' => $this->date_depot,
                'date_reglement' => $this->date_reglement ?: null,
                'net_a_payer' => $this->net_a_payer,
                'pdf_path' => $pdfPath ?? null,
                'statut' => $this->statut,
                'delai_legal_jours' => $this->delai_legal_jours,
            ]);

            // Calculer automatiquement le statut et les intérêts si demandé
            if ($this->calculer_statut_auto && ($this->date_depot && $this->date_reglement)) {
                $resultat = $facture->mettreAJourStatutEtInterets();
                session()->flash('message', 'Facture créée avec succès. Statut: ' . $resultat['statut'] . ', Intérêts: ' . number_format($resultat['interets'], 2) . ' DA');
            } else {
                // Calculer les intérêts même si le statut est manuel
                $facture->calculerInterets();
                $facture->save();
                session()->flash('message', 'Facture créée avec succès.');
            }

            $this->resetFields();
        } catch (\Exception $e) {
            logger('Erreur facture : ' . $e->getMessage());
            session()->flash('message', 'Erreur: ' . $e->getMessage());
        }
    }


    public function resetFields()
    {
        $this->client_id = null;
        $this->client_search = '';
        $this->reference = '';
        $this->date_facture = '';
        $this->montant_ht = '';
        $this->date_depot = '';
        $this->date_reglement = '';
        $this->net_a_payer = '';
        $this->statut = 'En attente';
        $this->delai_legal_jours = 30;
        $this->calculer_statut_auto = true;
        $this->clients = [];
        $this->showClientDropdown = false;
    }

    public function render()
    {
        $statuts = Facture::getStatutsDisponibles();
        return view('livewire.facture-form', compact('statuts'));
    }
}
