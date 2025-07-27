
@section('title', 'Créer une facture')
@section('content')
<div class="container mt-4">
    <h2>Créer une facture</h2>
    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    <form wire:submit.prevent="store">
        <div class="mb-3 position-relative">
            <label>Client</label>
            <input type="text" wire:model="client_search" wire:input="updatedClientSearch" class="form-control" placeholder="Rechercher un client..." autocomplete="off">
            @if($showClientDropdown && strlen($client_search) > 0)
                <ul class="list-group position-absolute w-100" style="z-index:10;">
                    @forelse($clients as $client)
                        <li class="list-group-item list-group-item-action" wire:click="selectClient({{ $client->id }}, '{{ addslashes($client->raison_sociale) }}')">
                            {{ $client->raison_sociale }} ({{ $client->nif }})
                        </li>
                    @empty
                        <li class="list-group-item">Aucun client trouvé</li>
                    @endforelse
                </ul>
            @endif
            @error('client_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label>Référence</label>
            <input type="text" wire:model.defer="reference" class="form-control" required>
            @error('reference') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label>Date facture</label>
            <input type="date" wire:model.defer="date_facture" class="form-control" required>
            @error('date_facture') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label>Montant HT</label>
            <input type="number" step="0.01" wire:model.defer="montant_ht" class="form-control" required>
            @error('montant_ht') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label>Date dépôt</label>
            <input type="date" wire:model.defer="date_depot" class="form-control" required>
            @error('date_depot') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label>Date règlement</label>
            <input type="date" wire:model.defer="date_reglement" class="form-control">
            @error('date_reglement') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label>Net à payer</label>
            <input type="number" step="0.01" wire:model.defer="net_a_payer" class="form-control" required>
            @error('net_a_payer') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label>Statut paiement</label>
            <input type="text" wire:model.defer="statut_paiement" class="form-control" required>
            @error('statut_paiement') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="btn btn-success">Créer la facture</button>
    </form>
</div>
@endsection
