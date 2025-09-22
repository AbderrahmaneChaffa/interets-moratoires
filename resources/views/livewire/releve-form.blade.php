@section('title', 'Créer Un Releve')
<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Créer Relevé</h5>
            <a href="{{ route('factures') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="store">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Client</label>
                        <select class="form-select" wire:model="client_id" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->raison_sociale }}</option>
                            @endforeach
                        </select>
                        @error('client_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Référence du relevé</label>
                        <input type="text" class="form-control" wire:model="reference" required>
                        @error('reference')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Date de début</label>
                        <input type="date" class="form-control" wire:model="date_debut" required>
                        @error('date_debut')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de fin</label>
                        <input type="date" class="form-control" wire:model="date_fin" required>
                        @error('date_fin')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de création</label>
                        <input type="date" class="form-control" wire:model="date_creation">
                        @error('date_creation')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Catégorie</label>
                        <input type="text" class="form-control" wire:model="categorie"
                            placeholder="Contrat de location GAB">
                        @error('categorie')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Montant total HT</label>
                        <input type="number" step="0.01" class="form-control" wire:model="montant_total_ht">
                        @error('montant_total_ht')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Statut</label>
                        <select class="form-select" wire:model="statut" required>
                            @foreach($statuts as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('statut')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">Date de dernière facture</label>
                        <input type="date" class="form-control" wire:model="date_derniere_facture">
                        @error('date_derniere_facture')<div class="text-danger small">{{ $message }}</div>@enderror
                        <small class="text-muted">Sera automatiquement calculée si des factures sont ajoutées</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">PDF du relevé (optionnel)</label>
                        <input type="file" class="form-control" wire:model="releve_pdf" accept=".pdf">
                        @error('releve_pdf')<div class="text-danger small">{{ $message }}</div>@enderror
                        <small class="text-muted">Fichier PDF uniquement, max 2MB</small>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Factures du Relevé (optionnel)</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addFacture">
                        <i class="fas fa-plus"></i> Ajouter une facture
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>N° de Facture</th>
                                <th>Date de Facture</th>
                                <th class="text-end">Montant HT</th>
                                <th class="text-end">Reste à payer</th>
                                <th>Catégorie</th>
                                <th>PDF</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($factures as $index => $f)
                            <tr>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="factures.{{ $index }}.reference">
                                    @error('factures.'.$index.'.reference')<div class="text-danger small">{{ $message }}
                                    </div>@enderror
                                </td>
                                <td>
                                    <input type="date" class="form-control form-control-sm"
                                        wire:model="factures.{{ $index }}.date_facture">
                                    @error('factures.'.$index.'.date_facture')<div class="text-danger small">
                                        {{ $message }}
                                    </div>@enderror
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end"
                                        wire:model="factures.{{ $index }}.montant_ht">
                                    @error('factures.'.$index.'.montant_ht')<div class="text-danger small">
                                        {{ $message }}
                                    </div>@enderror
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end"
                                        wire:model="factures.{{ $index }}.reste_a_payer">
                                    @error('factures.'.$index.'.reste_a_payer')<div class="text-danger small">
                                        {{ $message }}
                                    </div>@enderror
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="factures.{{ $index }}.categorie">
                                </td>
                                <td>
                                    <input type="file" class="form-control form-control-sm"
                                        wire:model="factures.{{ $index }}.pdf_file" accept=".pdf">
                                    @error('factures.'.$index.'.pdf_file')<div class="text-danger small">{{ $message }}</div>@enderror
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        wire:click="removeFacture({{ $index }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Aucune facture ajoutée.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Enregistrer le relevé
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>