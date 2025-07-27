<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Facture;
use App\Models\Client;

class FactureForm extends Component
{
    public $client_id, $client_search = '', $reference, $date_facture, $montant_ht, $date_depot, $date_reglement, $net_a_payer, $statut_paiement;
    public $clients = [];
    public $showClientDropdown = false;

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'reference' => 'required|string|max:255',
        'date_facture' => 'required|date',
        'montant_ht' => 'required|numeric',
        'date_depot' => 'required|date',
        'date_reglement' => 'nullable|date',
        'net_a_payer' => 'required|numeric',
        'statut_paiement' => 'required|string|max:255',
    ];

    public function updatedClientSearch()
    {
        $this->clients = Client::where('raison_sociale', 'like', '%'.$this->client_search.'%')->get();
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
        $this->validate();
        Facture::create([
            'client_id' => $this->client_id,
            'reference' => $this->reference,
            'date_facture' => $this->date_facture,
            'montant_ht' => $this->montant_ht,
            'date_depot' => $this->date_depot,
            'date_reglement' => $this->date_reglement,
            'net_a_payer' => $this->net_a_payer,
            'statut_paiement' => $this->statut_paiement,
        ]);
        session()->flash('message', 'Facture créée avec succès.');
        $this->resetFields();
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
        $this->statut_paiement = '';
        $this->clients = [];
        $this->showClientDropdown = false;
    }

    public function render()
    {
        return view('livewire.facture-form');
    }
}
