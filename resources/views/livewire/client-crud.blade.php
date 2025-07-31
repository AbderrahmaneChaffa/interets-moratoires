
@section('title', 'Gestion des clients')

<div class="container mt-4">
    <h2>Gestion des clients</h2>
    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="mb-4" enctype="multipart/form-data">
        <div class="row">
            <div class="col">
                <input type="text" wire:model.defer="raison_sociale" class="form-control" placeholder="Raison sociale" required>
            </div>
            <div class="col">
                <input type="text" wire:model.defer="nif" class="form-control" placeholder="NIF" required>
            </div>
            <div class="col">
                <input type="text" wire:model.defer="rc" class="form-control" placeholder="RC" required>
            </div>
            <div class="col">
                <input type="text" wire:model.defer="adresse" class="form-control" placeholder="Adresse" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Modifier' : 'Ajouter' }}</button>
                @if($isEdit)
                    <button type="button" wire:click="resetFields" class="btn btn-secondary">Annuler</button>
                @endif
            </div>
        </div>
        @error('raison_sociale') <span class="text-danger">{{ $message }}</span> @enderror
        @error('nif') <span class="text-danger">{{ $message }}</span> @enderror
        @error('rc') <span class="text-danger">{{ $message }}</span> @enderror
        @error('adresse') <span class="text-danger">{{ $message }}</span> @enderror
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Raison sociale</th>
                <th>NIF</th>
                <th>RC</th>
                <th>Adresse</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
                <tr>
                    <td>{{ $client->id }}</td>
                    <td>{{ $client->raison_sociale }}</td>
                    <td>{{ $client->nif }}</td>
                    <td>{{ $client->rc }}</td>
                    <td>{{ $client->adresse }}</td>
                    <td>
                        <button wire:click="edit({{ $client->id }})" class="btn btn-sm btn-warning">Modifier</button>
                        <button wire:click="destroy({{ $client->id }})" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce client ?')">Supprimer</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $clients->links() }}
</div>

