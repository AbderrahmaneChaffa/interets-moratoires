@section('title', 'Liste des factures')

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-file-invoice text-primary"></i> Liste des factures</h2>
                <div>
                    <!-- <button wire:click="exportExcel" class="btn btn-success me-2">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button> -->
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

    <!-- Filtres améliorés -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter text-primary"></i> Filtres de recherche</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="selectedClient" class="form-label fw-semibold">Client</label>
                    <select wire:model.live="selectedClient" class="form-select">
                        <option value="">Tous les clients</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->raison_sociale }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="selectedStatut" class="form-label fw-semibold">Statut</label>
                    <select wire:model.live="selectedStatut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="En attente">En attente</option>
                        <option value="Payée">Payée</option>
                        <option value="Retard de paiement">Retard de paiement</option>
                        <option value="Impayée">Impayée</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dateFrom" class="form-label fw-semibold">Date début</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="dateTo" class="form-label fw-semibold">Date fin</label>
                    <input type="date" wire:model.live="dateTo" class="form-control">
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <label for="montantMin" class="form-label fw-semibold">Montant min (DA)</label>
                    <input type="number" wire:model.live="montantMin" class="form-control" placeholder="0">
                </div>
                <div class="col-md-3">
                    <label for="montantMax" class="form-label fw-semibold">Montant max (DA)</label>
                    <input type="number" wire:model.live="montantMax" class="form-control" placeholder="999999999">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i> Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des factures amélioré -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <!-- Ajout de overflow-visible pour permettre l'affichage complet des dropdowns -->
            <div class="table-responsive" style="overflow-x: auto; overflow-y: visible;">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3 py-3">Référence</th>
                            <th class="px-3 py-3">Client</th>
                            <th class="px-3 py-3 text-center">Date</th>
                            <th class="px-3 py-3 text-end">Montant TTC</th>
                            <th class="px-3 py-3 text-center">Statut</th>
                            <th class="px-3 py-3 text-end">Intérêts</th>
                            <th class="px-3 py-3 text-center" style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($factures as $facture)
                            <!-- Ajout de position relative pour le contexte de positionnement du dropdown -->
                            <tr class="border-bottom" style="position: relative;">
                                <td class="px-3 py-3">
                                    <div>
                                        <strong class="text-primary">{{ $facture->reference }}</strong>
                                        @if ($facture->prestation)
                                            <br><small class="text-muted">{{ Str::limit($facture->prestation, 30) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <div>
                                        <span class="fw-medium">{{ $facture->client->raison_sociale ?? '-' }}</span>
                                        @if ($facture->client->email)
                                            <br><small class="text-muted"><i class="fas fa-envelope"></i>
                                                {{ $facture->client->email }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div>
                                        <span class="fw-medium">{{ $facture->date_facture->format('d/m/Y') }}</span>
                                        @if ($facture->date_reglement)
                                            <br><small class="text-success"><i class="fas fa-check"></i>
                                                {{ $facture->date_reglement->format('d/m/Y') }}</small>
                                        @elseif ($facture->date_depot)
                                            <br><small class="text-info"><i class="fas fa-clock"></i>
                                                {{ $facture->date_depot->format('d/m/Y') }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-end">
                                    <span class="fw-bold text-dark">{{ $facture->net_a_payer_formatted }}</span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div>
                                        <span class="badge fs-6 px-2 py-1
                                                @if ($facture->statut === 'Payée') bg-success
                                                @elseif($facture->statut === 'Retard de paiement') bg-warning text-dark
                                                @elseif($facture->statut === 'Impayée') bg-danger
                                                @else bg-secondary @endif">
                                            {{ $facture->statut }}
                                        </span>
                                        @if ($facture->jours_retard > 0)
                                            <br><small class="text-danger fw-medium">{{ $facture->jours_retard }} jours</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-end">
                                    @if ($facture->total_interets > 0)
                                        <span class="text-danger fw-bold">{{ $facture->total_interets_formatted }}</span>
                                    @else
                                        <span class="text-muted">0,00 DA</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <!-- Actions compactes avec dropdown pour éviter le scroll horizontal -->
                                    <div class="d-flex gap-1 justify-content-center flex-wrap">
                                        @if ($facture->pdf_path)
                                            <a href="{{ asset('storage/' . $facture->pdf_path) }}" target="_blank"
                                                class="btn btn-sm btn-outline-secondary" title="Voir PDF"
                                                data-bs-toggle="tooltip">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>

                                            <!-- Bouton pour ouvrir le modal d'envoi d'email -->
                                            <button wire:click="openEmailModal({{ $facture->id }})"
                                                class="btn btn-sm btn-outline-success" title="Envoyer par email"
                                                data-bs-toggle="tooltip">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        @else
                                            <form action="{{ route('factures.upload_pdf', $facture->id) }}" method="POST"
                                                enctype="multipart/form-data" class="d-inline">
                                                @csrf
                                                <input type="file" name="facture_pdf" accept="application/pdf" required
                                                    class="d-none" id="file-{{ $facture->id }}" onchange="this.form.submit()">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="document.getElementById('file-{{ $facture->id }}').click()"
                                                    title="Uploader PDF" data-bs-toggle="tooltip">
                                                    <i class="fas fa-upload"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Boutons d'actions horizontaux -->
                                        <button class="btn btn-sm btn-outline-info"
                                            wire:click="showDetails({{ $facture->id }})" title="Voir détails"
                                            data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <button class="btn btn-sm btn-outline-warning"
                                            wire:click="openEdit({{ $facture->id }})" title="Modifier"
                                            data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        @if ($facture->peutGenererInterets())
                                            <button class="btn btn-sm btn-outline-secondary"
                                                wire:click="calculerInteret({{ $facture->id }})" title="Calculer intérêts"
                                                data-bs-toggle="tooltip">
                                                <i class="fas fa-calculator"></i>
                                            </button>
                                        @endif

                                        <a class="btn btn-sm btn-outline-dark"
                                            href="{{ route('factures.interets', $facture->id) }}" title="Gérer intérêts"
                                            data-bs-toggle="tooltip">
                                            <i class="fas fa-cogs"></i>
                                        </a>

                                        <button class="btn btn-sm btn-outline-danger"
                                            wire:click="confirmDelete({{ $facture->id }})" title="Supprimer"
                                            data-bs-toggle="tooltip">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <h5>Aucune facture trouvée</h5>
                                        <p>Aucune facture ne correspond aux critères de recherche.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination améliorée -->
            @if($factures->hasPages())
                <div class="d-flex justify-content-between align-items-center p-3 bg-light border-top">
                    <div>
                        <p class="text-muted mb-0 small">
                            Affichage de {{ $factures->firstItem() ?? 0 }} à {{ $factures->lastItem() ?? 0 }}
                            sur {{ $factures->total() }} facture(s)
                        </p>
                    </div>
                    <div>
                        {{ $factures->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Nouveau Modal d'envoi d'email -->
    @if ($showEmailModal && $selectedFacture)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-envelope"></i> Envoyer la facture par email
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEmailModal"></button>
                    </div>
                    <form wire:submit.prevent="sendEmail">
                        <div class="modal-body">

                            {{-- ✅ Badge retour envoi email --}}
                            @if (session()->has('message'))
                                <div class="alert alert-success d-flex align-items-center">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <span>{{ session('message') }}</span>
                                </div>
                            @elseif (session()->has('error'))
                                <div class="alert alert-danger d-flex align-items-center">
                                    <i class="fas fa-times-circle me-2"></i>
                                    <span>{{ session('error') }}</span>
                                </div>
                            @endif

                            <!-- Informations de la facture -->
                            <div class="alert alert-info">
                                <h6><i class="fas fa-file-invoice"></i> Facture: {{ $selectedFacture->reference }}</h6>
                                <p class="mb-0">
                                    Client: {{ $selectedFacture->client->raison_sociale }} |
                                    Montant: {{ $selectedFacture->net_a_payer_formatted }}
                                </p>
                            </div>

                            <!-- Email destinataire -->
                            <div class="mb-3">
                                <label for="emailDestinataire" class="form-label fw-semibold">
                                    <i class="fas fa-at"></i> Email destinataire *
                                </label>
                                <input type="email" wire:model="emailDestinataire"
                                    class="form-control @error('emailDestinataire') is-invalid @enderror"
                                    placeholder="exemple@email.com">
                                @if($selectedFacture->client->email)
                                    <div class="form-text">
                                        <button type="button" class="btn btn-sm btn-link p-0"
                                            wire:click="$set('emailDestinataire', '{{ $selectedFacture->client->email }}')">
                                            <i class="fas fa-user"></i> Utiliser l'email du client:
                                            {{ $selectedFacture->client->email }}
                                        </button>
                                    </div>
                                @endif
                                @error('emailDestinataire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Objet -->
                            <div class="mb-3">
                                <label for="emailObjet" class="form-label fw-semibold">
                                    <i class="fas fa-tag"></i> Objet *
                                </label>
                                <input type="text" wire:model="emailObjet"
                                    class="form-control @error('emailObjet') is-invalid @enderror"
                                    placeholder="Facture {{ $selectedFacture->reference }}">
                                @error('emailObjet')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Message -->
                            <div class="mb-3">
                                <label for="emailMessage" class="form-label fw-semibold">
                                    <i class="fas fa-comment"></i> Message
                                </label>
                                <textarea wire:model="emailMessage"
                                    class="form-control @error('emailMessage') is-invalid @enderror" rows="4"
                                    placeholder="Bonjour,&#10;&#10;Veuillez trouver ci-joint la facture {{ $selectedFacture->reference }}.&#10;&#10;Cordialement,"></textarea>
                                @error('emailMessage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fichiers joints -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-paperclip"></i> Fichiers joints
                                </label>
                                <div class="border rounded p-3 bg-light">
                                    <!-- @if($selectedFacture->pdf_path)
                                        <div class="d-flex align-items-center mb-2">
                                            <input type="checkbox" wire:model="attachFacturePdf" class="form-check-input me-2"
                                                id="attachFacture">
                                            <label for="attachFacture" class="form-check-label">
                                                <i class="fas fa-file-pdf text-danger"></i>
                                                Facture {{ $selectedFacture->reference }}.pdf
                                            </label>
                                        </div>
                                    @endif -->

                                    <!-- @if($selectedFacture->interets->where('pdf_path', '!=', null)->count() > 0)
                                        @foreach($selectedFacture->interets->where('pdf_path', '!=', null) as $interet)
                                            <div class="d-flex align-items-center mb-2">
                                                <input type="checkbox" wire:model="attachInteretsPdf" value="{{ $interet->id }}"
                                                    class="form-check-input me-2" id="attachInteret{{ $interet->id }}">
                                                <label for="attachInteret{{ $interet->id }}" class="form-check-label">
                                                    <i class="fas fa-file-pdf text-warning"></i>
                                                    Intérêts {{ $interet->date_debut_periode->format('m/Y') }}.pdf
                                                </label>
                                            </div>
                                        @endforeach
                                    @endif -->

                                    @if(!$selectedFacture->pdf_path && $selectedFacture->interets->where('pdf_path', '!=', null)->count() == 0)
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-info-circle"></i> Aucun fichier disponible pour cette facture
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeEmailModal">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Envoyer l'email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif


    <!-- Modal Détails -->
    @if ($showDetailsModal && $factureDetails)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-file-invoice"></i> Détails de la facture {{ $factureDetails->reference }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showDetailsModal', false)"></button>
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
                                            class="form-control @error('reference') is-invalid @enderror" disabled>
                                        @error('reference')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_facture" class="form-label">Date facture *</label>
                                        <input type="date" wire:model="date_facture"
                                            class="form-control @error('date_facture') is-invalid @enderror" disabled>
                                        @error('date_facture')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="mb-3">
                                <label for="prestation" class="form-label">Prestation</label>
                                <input type="text" wire:model="prestation"
                                    class="form-control @error('prestation') is-invalid @enderror" disabled>
                                @error('prestation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> -->

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="montant_ht" class="form-label">Montant HT *</label>
                                        <input type="number" step="0.01" wire:model="montant_ht"
                                            class="form-control @error('montant_ht') is-invalid @enderror" disabled>
                                        @error('montant_ht')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="net_a_payer" class="form-label">Net à payer *</label>
                                        <input type="number" step="0.01" wire:model="net_a_payer"
                                            class="form-control @error('net_a_payer') is-invalid @enderror" disabled>
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
                                            class="form-control @error('date_depot') is-invalid @enderror" disabled>
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
                                <!-- <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="delai_legal_jours" class="form-label">Délai légal (jours)
                                            *</label>
                                        <input type="number" wire:model="delai_legal_jours"
                                            class="form-control @error('delai_legal_jours') is-invalid @enderror">
                                        @error('delai_legal_jours')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div> -->
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
                        <button type="button" class="btn-close" wire:click="$set('showDeleteModal', false)"></button>
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