<?php

namespace App\Http\Livewire;

use App\Models\Client;
use Livewire\WithPagination;
use Livewire\Component;

class ClientCrud extends Component
{
    use WithPagination;

    public $client_id, $raison_sociale, $contrat_maintenance, $formule, $taux, $nif, $rc, $ai, $adresse, $email;
    public $isEdit = false;
    public $showModal = false;
    public $expandedId = null;
    protected $rules = [
        'raison_sociale' => 'required|string|max:255',
        'contrat_maintenance' => 'nullable|string|max:255',
        'formule' => 'nullable|string|max:1000',
        'taux' => 'nullable|numeric|min:0|max:1',
        'nif' => 'required|string|max:255',
        'rc' => 'required|string|max:255',
        'ai' => 'nullable|string|max:255',
        'adresse' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
    ];

    public function render()
    {
       
        return view('livewire.client-crud', [
            'clients' => Client::orderBy('id', 'desc')->paginate(10)
        ]);
    }

    public function resetFields()
    {
        $this->client_id = null;
        $this->raison_sociale = '';
        $this->contrat_maintenance = '';
        $this->formule = '';
        $this->taux = null;
        $this->nif = '';
        $this->rc = '';
        $this->ai = '';
        $this->adresse = '';
        $this->email = '';
        $this->isEdit = false;
        $this->showModal = false;
    }

    public function openCreateModal()
    {
        $this->resetFields();
        $this->showModal = true;
        $this->dispatchBrowserEvent('open-client-modal');
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);
        $this->client_id = $client->id;
        $this->raison_sociale = $client->raison_sociale;
        $this->contrat_maintenance = $client->contrat_maintenance;
        $this->formule = $client->formule;
        $this->taux = $client->taux;
        $this->nif = $client->nif;
        $this->rc = $client->rc;
        $this->ai = $client->ai;
        $this->adresse = $client->adresse;
        $this->email = $client->email;
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatchBrowserEvent('open-client-modal');
    }

    public function toggle($id)
    {
        $this->expandedId = ($this->expandedId === $id) ? null : $id;
    }

    public function save()
    {
        $this->validate();
        $data = [
            'raison_sociale' => $this->raison_sociale,
            'contrat_maintenance' => $this->contrat_maintenance,
            'formule' => $this->formule,
            'taux' => $this->taux,
            'nif' => $this->nif,
            'rc' => $this->rc,
            'ai' => $this->ai,
            'adresse' => $this->adresse,
            'email' => $this->email,
        ];

        if ($this->isEdit && $this->client_id) {
            $client = Client::findOrFail($this->client_id);
            $client->update($data);
            session()->flash('message', 'Client modifié avec succès.');
        } else {
            Client::create($data);
            session()->flash('message', 'Client ajouté avec succès.');
        }

        $this->dispatchBrowserEvent('close-client-modal');
        $this->resetFields();
    }

    public function destroy($id)
    {
        Client::destroy($id);
        session()->flash('message', 'Client supprimé avec succès.');
        $this->resetFields();
    }
}
