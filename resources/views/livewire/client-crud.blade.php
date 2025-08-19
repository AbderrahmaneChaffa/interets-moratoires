
@section('title', 'Gestion des clients')

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Gestion des clients</h2>
        <button class="btn btn-primary" wire:click="openCreateModal">Nouveau client</button>
    </div>
    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <!-- Modal -->
    <div class="modal fade @if($showModal) show d-block @endif" tabindex="-1" role="dialog" @if($showModal) style="background: rgba(0,0,0,.5);" @endif>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? 'Modifier le client' : 'Nouveau client' }}</h5>
                    <button type="button" class="btn-close" aria-label="Close" wire:click="resetFields"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Raison sociale</label>
                                <input type="text" wire:model.defer="raison_sociale" class="form-control" required>
                                @error('raison_sociale') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contrat de Maintenance</label>
                                <input type="text" wire:model.defer="contrat_maintenance" class="form-control">
                                @error('contrat_maintenance') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Formule calcul intérêts</label>
                                <input type="text" wire:model.defer="formule" class="form-control">
                                @error('formule') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Taux (ex: 0.09)</label>
                                <input type="number" step="0.01" min="0" max="1" wire:model.defer="taux" class="form-control">
                                @error('taux') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">NIF</label>
                                <input type="text" wire:model.defer="nif" class="form-control" required>
                                @error('nif') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RC</label>
                                <input type="text" wire:model.defer="rc" class="form-control" required>
                                @error('rc') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">AI</label>
                                <input type="text" wire:model.defer="ai" class="form-control">
                                @error('ai') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Adresse</label>
                                <input type="text" wire:model.defer="adresse" class="form-control" required>
                                @error('adresse') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" wire:model.defer="email" class="form-control">
                                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="resetFields">Annuler</button>
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Enregistrer' : 'Ajouter' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                        <div class="btn-group">
                            <button wire:click="toggle({{ $client->id }})" class="btn btn-sm btn-info">
                                @if($expandedId === $client->id) Masquer @else Voir @endif
                            </button>
                            <button wire:click="edit({{ $client->id }})" class="btn btn-sm btn-warning">Modifier</button>
                            <button wire:click="destroy({{ $client->id }})" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce client ?')">Supprimer</button>
                        </div>
                    </td>
                </tr>
                @if($expandedId === $client->id)
                    <tr>
                        <td colspan="6">
                            <div class="p-3 border rounded bg-light">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <strong>Contrat:</strong> {{ $client->contrat_maintenance ?? '-' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Taux:</strong> {{ $client->taux !== null ? ($client->taux*100).' %' : '-' }}
                                    </div>
                                    <div class="col-md-12">
                                        <strong>Formule:</strong> <small>{{ $client->formule ?? '-' }}</small>
                                    </div>
                                    <div class="col-md-3"><strong>NIF:</strong> {{ $client->nif }}</div>
                                    <div class="col-md-3"><strong>RC:</strong> {{ $client->rc }}</div>
                                    <div class="col-md-3"><strong>AI:</strong> {{ $client->ai ?? '-' }}</div>
                                    <div class="col-md-12"><strong>Adresse:</strong> {{ $client->adresse }}</div>
                                    <div class="col-md-6"><strong>Email:</strong> {{ $client->email ?? '-' }}</div>
                                    <div class="col-md-6 text-end">
                                        <small class="text-muted">Maj: {{ optional($client->updated_at)->format('d-m-Y H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    {{ $clients->links() }}
</div>

<!-- (Bloc modal "Détails du client" supprimé; remplacé par ligne repliable dans le tableau) -->
