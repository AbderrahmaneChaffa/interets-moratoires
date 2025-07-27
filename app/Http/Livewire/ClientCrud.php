<?php

namespace App\Http\Livewire;

use App\Models\Client;
use Livewire\WithPagination;
use Livewire\Component;

class ClientCrud extends Component
{
    use WithPagination;

    public $client_id, $raison_sociale, $nif, $rc, $adresse;
    public $isEdit = false;
    protected $rules = [
        'raison_sociale' => 'required|string|max:255',
        'nif' => 'required|string|max:255',
        'rc' => 'required|string|max:255',
        'adresse' => 'required|string|max:255',
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
        $this->nif = '';
        $this->rc = '';
        $this->adresse = '';
        $this->isEdit = false;
    }

    public function store()
    {
        $this->validate();
        Client::create([
            'raison_sociale' => $this->raison_sociale,
            'nif' => $this->nif,
            'rc' => $this->rc,
            'adresse' => $this->adresse,
        ]);
        session()->flash('message', 'Client ajouté avec succès.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);
        $this->client_id = $client->id;
        $this->raison_sociale = $client->raison_sociale;
        $this->nif = $client->nif;
        $this->rc = $client->rc;
        $this->adresse = $client->adresse;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate();
        $client = Client::findOrFail($this->client_id);
        $client->update([
            'raison_sociale' => $this->raison_sociale,
            'nif' => $this->nif,
            'rc' => $this->rc,
            'adresse' => $this->adresse,
        ]);
        session()->flash('message', 'Client modifié avec succès.');
        $this->resetFields();
    }

    public function destroy($id)
    {
        Client::destroy($id);
        session()->flash('message', 'Client supprimé avec succès.');
        $this->resetFields();
    }
}
