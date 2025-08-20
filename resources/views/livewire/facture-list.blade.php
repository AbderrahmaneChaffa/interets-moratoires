@section('title', 'Liste des factures')

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-file-invoice"></i> Liste des factures</h2>
                <div>
                    <button wire:click="exportExcel" class="btn btn-success me-2">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button wire:click="exportPdf" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filtres</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <label for="selectedClient" class="form-label">Client</label>
                    <select wire:model.live="selectedClient" class="form-select">
                        <option value="">Tous les clients</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->raison_sociale }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="selectedStatut" class="form-label">Statut</label>
                    <select wire:model.live="selectedStatut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="En attente">En attente</option>
                        <option value="Payée">Payée</option>
                        <option value="Retard de paiement">Retard de paiement</option>
                        <option value="Impayée">Impayée</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="dateFrom" class="form-label">Date début</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="dateTo" class="form-label">Date fin</label>
                    <input type="date" wire:model.live="dateTo" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="montantMin" class="form-label">Montant min (DA)</label>
                    <input type="number" wire:model.live="montantMin" class="form-control" placeholder="0">
                </div>
                <div class="col-md-2">
                    <label for="montantMax" class="form-label">Montant max (DA)</label>
                    <input type="number" wire:model.live="montantMax" class="form-control" placeholder="999999999">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i> Réinitialiser les filtres
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des factures -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Date Facture</th>
                            <th>Référence</th>
                            <th>Client</th>
                            <th>Montant HT</th>
                            <th>Montant TTC</th>
                            <th>Date Dépôt</th>
                            <th>Date Règlement</th>
                            <th>Statut</th>
                            <th>Intérêts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($factures as $facture)
                            <tr>
                                <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $facture->reference }}</strong>
                                    @if ($facture->prestation)
                                        <br><small class="text-muted">{{ $facture->prestation }}</small>
                                    @endif
                                </td>
                                <td>{{ $facture->client->raison_sociale ?? '-' }}</td>
                                <td class="text-end">{{ $facture->montant_ht_formatted }}</td>
                                <td class="text-end">{{ $facture->net_a_payer_formatted }}</td>
                                <td>{{ $facture->date_depot ? $facture->date_depot->format('d/m/Y') : '-' }}</td>
                                <td>{{ $facture->date_reglement ? $facture->date_reglement->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    <span
                                        class="badge 
                                        @if ($facture->statut === 'Payée') bg-success
                                        @elseif($facture->statut === 'Retard de paiement') bg-warning
                                        @elseif($facture->statut === 'Impayée') bg-danger
                                        @else bg-secondary @endif">
                                        {{ $facture->statut }}
                                    </span>
                                    @if ($facture->jours_retard > 0)
                                        <br><small class="text-muted">{{ $facture->jours_retard }} jours de
                                            retard</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ($facture->total_interets > 0)
                                        <span
                                            class="text-danger fw-bold">{{ $facture->total_interets_formatted }}</span>
                                    @else
                                        <span class="text-muted">0,00 DA</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button wire:click="showDetails({{ $facture->id }})"
                                            class="btn btn-sm btn-outline-primary" title="Détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button wire:click="openEdit({{ $facture->id }})"
                                            class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="confirmDelete({{ $facture->id }})"
                                            class="btn btn-sm btn-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @if ($facture->peutGenererInterets())
                                            <button wire:click="calculerInteret({{ $facture->id }})"
                                                class="btn btn-sm btn-info" title="Calculer intérêts">
                                                <i class="fas fa-calculator"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('factures.interets', $facture->id) }}"
                                            class="btn btn-sm btn-outline-info" title="Gérer intérêts">
                                            <i class="fas fa-cogs"></i>
                                        </a>
                                    </div>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">Aucune facture trouvée</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <p class="text-muted mb-0">
                        Affichage de {{ $factures->firstItem() ?? 0 }} à {{ $factures->lastItem() ?? 0 }}
                        sur {{ $factures->total() }} facture(s)
                    </p>
                </div>
                <div>
                    {{ $factures->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Détails -->
    @if ($showDetailsModal && $factureDetails)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-file-invoice"></i> Détails de la facture {{ $factureDetails->reference }}
                        </h5>
                        <button type="button" class="btn-close"
                            wire:click="$set('showDetailsModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Informations générales</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Client:</strong></td>
                                        <td>{{ $factureDetails->client->raison_sociale }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Référence:</strong></td>
                                        <td>{{ $factureDetails->reference }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Prestation:</strong></td>
                                        <td>{{ $factureDetails->prestation ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date facture:</strong></td>
                                        <td>{{ $factureDetails->date_facture->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Montant HT:</strong></td>
                                        <td>{{ $factureDetails->montant_ht_formatted }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Net à payer:</strong></td>
                                        <td>{{ $factureDetails->net_a_payer_formatted }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Informations de paiement</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Date dépôt:</strong></td>
                                        <td>{{ $factureDetails->date_depot ? $factureDetails->date_depot->format('d/m/Y') : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date règlement:</strong></td>
                                        <td>{{ $factureDetails->date_reglement ? $factureDetails->date_reglement->format('d/m/Y') : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Statut:</strong></td>
                                        <td>{{ $factureDetails->statut }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jours de retard:</strong></td>
                                        <td>{{ $factureDetails->jours_retard }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Mois de retard:</strong></td>
                                        <td>{{ $factureDetails->mois_retard }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total intérêts:</strong></td>
                                        <td>{{ $factureDetails->total_interets_formatted }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if ($factureDetails->interets->count() > 0)
                            <div class="mt-4">
                                <h6>Intérêts moratoires calculés</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Période</th>
                                                <th>Date début</th>
                                                <th>Date fin</th>
                                                <th>Jours retard</th>
                                                <th>Intérêt HT</th>
                                                <th>Intérêt TTC</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($factureDetails->interets as $interet)
                                                <tr>
                                                    <td>{{ $interet->date_debut_periode->format('m/Y') }}</td>
                                                    <td>{{ $interet->date_debut_periode->format('d/m/Y') }}</td>
                                                    <td>{{ $interet->date_fin_periode->format('d/m/Y') }}</td>
                                                    <td>{{ $interet->jours_retard }}</td>
                                                    <td class="text-end">{{ $interet->interet_ht_formatted }}</td>
                                                    <td class="text-end">{{ $interet->interet_ttc_formatted }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('showDetailsModal', false)">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Modal Édition -->
    @if ($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit"></i> Modifier la facture
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="reference" class="form-label">Référence *</label>
                                        <input type="text" wire:model="reference"
                                            class="form-control @error('reference') is-invalid @enderror">
                                        @error('reference')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_facture" class="form-label">Date facture *</label>
                                        <input type="date" wire:model="date_facture"
                                            class="form-control @error('date_facture') is-invalid @enderror">
                                        @error('date_facture')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="prestation" class="form-label">Prestation</label>
                                <input type="text" wire:model="prestation"
                                    class="form-control @error('prestation') is-invalid @enderror">
                                @error('prestation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="montant_ht" class="form-label">Montant HT *</label>
                                        <input type="number" step="0.01" wire:model="montant_ht"
                                            class="form-control @error('montant_ht') is-invalid @enderror">
                                        @error('montant_ht')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="net_a_payer" class="form-label">Net à payer *</label>
                                        <input type="number" step="0.01" wire:model="net_a_payer"
                                            class="form-control @error('net_a_payer') is-invalid @enderror">
                                        @error('net_a_payer')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_depot" class="form-label">Date dépôt *</label>
                                        <input type="date" wire:model="date_depot"
                                            class="form-control @error('date_depot') is-invalid @enderror">
                                        @error('date_depot')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_reglement" class="form-label">Date règlement</label>
                                        <input type="date" wire:model="date_reglement"
                                            class="form-control @error('date_reglement') is-invalid @enderror">
                                        @error('date_reglement')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="statut" class="form-label">Statut *</label>
                                        <select wire:model="statut"
                                            class="form-select @error('statut') is-invalid @enderror">
                                            <option value="">Sélectionner...</option>
                                            <option value="En attente">En attente</option>
                                            <option value="Payée">Payée</option>
                                            <option value="Retard de paiement">Retard de paiement</option>
                                            <option value="Impayée">Impayée</option>
                                        </select>
                                        @error('statut')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="delai_legal_jours" class="form-label">Délai légal (jours)
                                            *</label>
                                        <input type="number" wire:model="delai_legal_jours"
                                            class="form-control @error('delai_legal_jours') is-invalid @enderror">
                                        @error('delai_legal_jours')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                wire:click="$set('showModal', false)">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Modal Suppression -->
    @if ($showDeleteModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle text-danger"></i> Confirmer la suppression
                        </h5>
                        <button type="button" class="btn-close"
                            wire:click="$set('showDeleteModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer cette facture ?</p>
                        <p class="text-muted">Cette action est irréversible et supprimera également tous les intérêts
                            associés.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('showDeleteModal', false)">Annuler</button>
                        <button type="button" class="btn btn-danger" wire:click="delete">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

</div>
