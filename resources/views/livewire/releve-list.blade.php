@section('title', 'Liste des relevés')

<div class="container-fluid mt-4">
    <!-- Export buttons section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-list-alt text-primary"></i> Liste des relevés</h2>
                <div class="export-buttons">
                    <button onclick="exportToExcel()" class="btn btn-success export-btn">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button onclick="exportToPDF()" class="btn btn-danger export-btn">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button onclick="printTable()" class="btn btn-info export-btn">
                        <i class="fas fa-print"></i> Imprimer
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
                        <option value="Payé">Payé</option>
                        <option value="Impayé">Impayé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dateFrom" class="form-label fw-semibold">Date début période</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="dateTo" class="form-label fw-semibold">Date fin période</label>
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

    <!-- Tableau des relevés -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto; overflow-y: visible;">
                <table id="relevesTable" class="table table-hover mb-0" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3 py-3 sortable" onclick="sortTable(0, 'Référence')">
                                Référence <span class="sort-icon"></span>
                            </th>
                            <th class="px-3 py-3 sortable" onclick="sortTable(1, 'Client')">
                                Client <span class="sort-icon"></span>
                            </th>
                            <th class="px-3 py-3 text-center sortable" onclick="sortTable(2, 'Période')">
                                Période <span class="sort-icon"></span>
                            </th>
                            <th class="px-3 py-3 text-center sortable" onclick="sortTable(3, 'Date création')">
                                Date création <span class="sort-icon"></span>
                            </th>
                            <th class="px-3 py-3 text-end sortable" onclick="sortTable(4, 'Montant')">
                                Montant total HT <span class="sort-icon"></span>
                            </th>
                            <th class="px-3 py-3 text-center sortable" onclick="sortTable(5, 'Statut')">
                                Statut <span class="sort-icon"></span>
                            </th>
                            <th class="px-3 py-3 text-center sortable" onclick="sortTable(6, 'Factures')">
                                Factures <span class="sort-icon"></span>
                            </th>
                            <th class="px-3 py-3 text-center" style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($releves as $releve)
                            <tr class="border-bottom" style="position: relative;">
                                <td class="px-3 py-3">
                                    <div>
                                        <strong class="text-primary">{{ $releve->reference }}</strong>
                                        @if ($releve->categorie)
                                            <br><small class="text-muted">{{ Str::limit($releve->categorie, 30) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <div>
                                        <span class="fw-medium">{{ $releve->client->raison_sociale ?? '-' }}</span>
                                        @if ($releve->client->email)
                                            <br><small class="text-muted"><i class="fas fa-envelope"></i>
                                                {{ $releve->client->email }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center" data-order="{{ $releve->date_debut->format('Y-m-d') }}">
                                    <div>
                                        <span class="fw-medium">{{ $releve->date_debut->format('d/m/Y') }}</span>
                                        <br><small class="text-muted">au</small>
                                        <br><span class="fw-medium">{{ $releve->date_fin->format('d/m/Y') }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center"
                                    data-order="{{ $releve->date_creation->format('Y-m-d') }}">
                                    <span class="fw-bold">{{ $releve->date_creation->format('d/m/Y') }}</span>
                                    @if($releve->date_derniere_facture)
                                        <br><small class="text-info"><i class="fas fa-calendar"></i>
                                            Dernière facture: {{ $releve->date_derniere_facture->format('d/m/Y') }}</small>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-end" data-order="{{ $releve->montant_total_ht }}">
                                    <span
                                        class="fw-bold text-dark">{{ number_format($releve->montant_total_ht, 2, ',', ' ') }}
                                        DA</span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div>
                                        <span class="badge fs-6 px-2 py-1
                                                            @if ($releve->statut === 'Payé') bg-success
                                                            @elseif($releve->statut === 'Impayé') bg-danger
                                                            @else bg-secondary @endif">
                                            {{ $releve->statut }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="badge bg-info">{{ $releve->factures->count() }} facture(s)</span>
                                </td>
                                <td class="px-3 py-3">
                                    <!-- Actions compactes -->
                                    <div class="d-flex gap-1 justify-content-center flex-wrap">
                                        @if ($releve->releve_pdf)
                                            <a href="{{ route('releves.pdf', basename($releve->releve_pdf)) }}" target="_blank"
                                                class="btn btn-sm btn-outline-secondary" title="Voir PDF"
                                                data-bs-toggle="tooltip">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @endif

                                        <!-- Boutons d'actions horizontaux -->
                                        <button class="btn btn-sm btn-outline-info"
                                            wire:click="showDetails({{ $releve->id }})" title="Voir détails"
                                            data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <button class="btn btn-sm btn-outline-warning"
                                            wire:click="openEdit({{ $releve->id }})" title="Modifier"
                                            data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button class="btn btn-sm btn-outline-secondary"
                                            wire:click="calculerInterets({{ $releve->id }})" title="Calculer intérêts"
                                            data-bs-toggle="tooltip">
                                            <i class="fas fa-calculator"></i>
                                        </button>

                                        <a class="btn btn-sm btn-outline-primary"
                                            href="{{ route('releves.show', $releve->id) }}" title="Gérer intérêts"
                                            data-bs-toggle="tooltip">
                                            <i class="fas fa-cogs"></i>
                                        </a>


                                        <button class="btn btn-sm btn-outline-danger"
                                            wire:click="confirmDelete({{ $releve->id }})" title="Supprimer"
                                            data-bs-toggle="tooltip">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <h5>Aucun relevé trouvé</h5>
                                        <p>Aucun relevé ne correspond aux critères de recherche.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $releves->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Détails -->
    @if ($showDetailsModal && $releveDetails)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-list-alt"></i> Détails du relevé {{ $releveDetails->reference }}
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
                                        <td>{{ $releveDetails->client->raison_sociale }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Référence:</strong></td>
                                        <td>{{ $releveDetails->reference }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Période:</strong></td>
                                        <td>{{ $releveDetails->date_debut->format('d/m/Y') }} -
                                            {{ $releveDetails->date_fin->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date création:</strong></td>
                                        <td>{{ $releveDetails->date_creation->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Catégorie:</strong></td>
                                        <td>{{ $releveDetails->categorie ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Montant total HT:</strong></td>
                                        <td>{{ number_format($releveDetails->montant_total_ht, 2, ',', ' ') }} DA</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Informations complémentaires</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Statut:</strong></td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $releveDetails->statut === 'Payé' ? 'success' : ($releveDetails->statut === 'Impayé' ? 'danger' : 'secondary') }}">
                                                {{ $releveDetails->statut }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date dernière facture:</strong></td>
                                        <td>{{ $releveDetails->date_derniere_facture ? $releveDetails->date_derniere_facture->format('d/m/Y') : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nombre de factures:</strong></td>
                                        <td>{{ $releveDetails->factures->count() }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>PDF relevé:</strong></td>
                                        <td>
                                            @if($releveDetails->releve_pdf)
                                                <a href="{{ route('releves.pdf', basename($releveDetails->releve_pdf)) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file-pdf"></i> Voir PDF
                                                </a>
                                            @else
                                                <span class="text-muted">Aucun PDF</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if ($releveDetails->factures->count() > 0)
                            <div class="mt-4">
                                <h6>Factures du relevé</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Référence</th>
                                                <th>Date facture</th>
                                                <th>Montant HT</th>
                                                <th>Net à payer</th>
                                                <th>Statut</th>
                                                <th>PDF</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($releveDetails->factures as $facture)
                                                <tr>
                                                    <td>{{ $facture->reference }}</td>
                                                    <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
                                                    <td class="text-end">{{ number_format($facture->montant_ht, 2, ',', ' ') }} DA
                                                    </td>
                                                    <td class="text-end">{{ number_format($facture->net_a_payer, 2, ',', ' ') }} DA
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $facture->statut === 'Payée' ? 'success' : ($facture->statut === 'Impayée' ? 'danger' : 'warning') }}">
                                                            {{ $facture->statut }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($facture->pdf_path)
                                                            <a href="{{ route('factures.pdf', basename($facture->pdf_path)) }}"
                                                                target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-file-pdf"></i>
                                                            </a>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
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
                            <i class="fas fa-edit"></i> Modifier le relevé
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
                                        <label for="statut" class="form-label">Statut *</label>
                                        <select wire:model="statut"
                                            class="form-select @error('statut') is-invalid @enderror">
                                            <option value="">Sélectionner...</option>
                                            <option value="Payé">Payé</option>
                                            <option value="Impayé">Impayé</option>
                                        </select>
                                        @error('statut')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_derniere_facture" class="form-label">Date dernière facture</label>
                                        <input type="date" wire:model="date_derniere_facture"
                                            class="form-control @error('date_derniere_facture') is-invalid @enderror">
                                        @error('date_derniere_facture')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="montant_total_ht" class="form-label">Montant total HT</label>
                                        <input type="number" step="0.01" wire:model="montant_total_ht"
                                            class="form-control @error('montant_total_ht') is-invalid @enderror">
                                        @error('montant_total_ht')
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
                        <button type="button" class="btn-close" wire:click="$set('showDeleteModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer ce relevé ?</p>
                        <p class="text-muted">Cette action est irréversible et supprimera également toutes les factures et
                            intérêts associés.</p>
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

    <!-- Styles et scripts -->
    <style>
        .export-buttons {
            margin-bottom: 1rem;
        }

        .export-btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .sortable {
            cursor: pointer;
            user-select: none;
        }

        .sortable:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sort-icon {
            margin-left: 5px;
            opacity: 0.5;
        }

        .sort-asc .sort-icon:before {
            content: "▲";
            opacity: 1;
        }

        .sort-desc .sort-icon:before {
            content: "▼";
            opacity: 1;
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('[v0] Initializing export and sort functionality for Livewire v2...');

            // Export to Excel function
            window.exportToExcel = function () {
                try {
                    console.log('[v0] Starting Excel export...');
                    if (typeof XLSX === 'undefined') {
                        alert('XLSX library not loaded. Please refresh the page.');
                        return;
                    }

                    const table = document.getElementById('relevesTable');
                    if (!table) {
                        alert('Table not found');
                        return;
                    }

                    const wb = XLSX.utils.table_to_book(table, {
                        sheet: "Relevés"
                    });
                    const filename = 'releves_' + new Date().toISOString().slice(0, 10) + '.xlsx';
                    XLSX.writeFile(wb, filename);
                    console.log('[v0] Excel export completed successfully');
                } catch (error) {
                    console.error('[v0] Excel export error:', error);
                    alert('Erreur lors de l\'export Excel: ' + error.message);
                }
            };

            // Export to PDF function
            window.exportToPDF = function () {
                try {
                    console.log('[v0] Starting PDF export...');

                    if (typeof window.jspdf === 'undefined' || !window.jspdf.jsPDF) {
                        alert('jsPDF library not loaded properly. Please refresh the page.');
                        return;
                    }

                    const {
                        jsPDF
                    } = window.jspdf;
                    const doc = new jsPDF('landscape');

                    // Add header
                    doc.setFontSize(16);
                    doc.text('Liste des Relevés - HIGH TECH SYSTEMS', 14, 15);
                    doc.setFontSize(10);
                    doc.text('Généré le: ' + new Date().toLocaleDateString('fr-FR'), 14, 25);

                    const table = document.getElementById('relevesTable');
                    if (!table) {
                        alert('Table not found');
                        return;
                    }

                    // Prepare table data
                    const headers = [];
                    const headerCells = table.querySelectorAll('thead th');
                    for (let i = 0; i < headerCells.length - 1; i++) { // Skip actions column
                        headers.push(headerCells[i].textContent.trim().replace(/\s+/g, ' '));
                    }

                    const rows = [];
                    table.querySelectorAll('tbody tr').forEach(tr => {
                        const cells = tr.querySelectorAll('td');
                        if (cells.length > 0) {
                            const row = [];
                            for (let i = 0; i < cells.length - 1; i++) { // Skip actions column
                                let cellText = cells[i].textContent.trim().replace(/\s+/g, ' ');
                                // Clean up text for PDF
                                cellText = cellText.replace(/\n/g, ' ').substring(0, 50);
                                row.push(cellText);
                            }
                            if (row.length > 0) rows.push(row);
                        }
                    });

                    // Add table to PDF
                    doc.autoTable({
                        head: [headers],
                        body: rows,
                        startY: 35,
                        styles: {
                            fontSize: 8,
                            cellPadding: 2
                        },
                        headStyles: {
                            fillColor: [52, 58, 64],
                            textColor: 255
                        },
                        columnStyles: {
                            0: {
                                cellWidth: 25
                            }, // Reference
                            1: {
                                cellWidth: 40
                            }, // Client
                            2: {
                                cellWidth: 30
                            }, // Période
                            3: {
                                cellWidth: 25
                            }, // Date création
                            4: {
                                cellWidth: 30
                            }, // Montant
                            5: {
                                cellWidth: 20
                            }, // Statut
                            6: {
                                cellWidth: 20
                            } // Factures
                        }
                    });

                    const filename = 'releves_' + new Date().toISOString().slice(0, 10) + '.pdf';
                    doc.save(filename);
                    console.log('[v0] PDF export completed successfully');
                } catch (error) {
                    console.error('[v0] PDF export error:', error);
                    alert('Erreur lors de l\'export PDF: ' + error.message);
                }
            };

            // Print table function
            window.printTable = function () {
                try {
                    console.log('[v0] Starting print...');
                    const printWindow = window.open('', '_blank');
                    if (!printWindow) {
                        alert('Impossible d\'ouvrir la fenêtre d\'impression. Vérifiez les paramètres de votre navigateur.');
                        return;
                    }

                    const table = document.getElementById('relevesTable');
                    if (!table) {
                        alert('Table not found');
                        return;
                    }

                    const clonedTable = table.cloneNode(true);

                    // Remove actions column
                    clonedTable.querySelectorAll('th:last-child, td:last-child').forEach(el => el.remove());

                    printWindow.document.write(`
                <html>
                    <head>
                        <title>Liste des Relevés - HIGH TECH SYSTEMS</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            h1 { color: #333; text-align: center; margin-bottom: 10px; }
                            .subtitle { text-align: center; margin-bottom: 20px; color: #666; }
                            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; }
                            th { background-color: #343a40; color: white; font-weight: bold; }
                            .badge { padding: 2px 6px; border-radius: 3px; font-size: 10px; }
                            .bg-success { background-color: #28a745; color: white; }
                            .bg-danger { background-color: #dc3545; color: white; }
                            .bg-secondary { background-color: #6c757d; color: white; }
                            .bg-info { background-color: #17a2b8; color: white; }
                            @media print {
                                body { margin: 0; }
                                .no-print { display: none; }
                            }
                        </style>
                    </head>
                    <body>
                        <h1>Liste des Relevés</h1>
                        <div class="subtitle">HIGH TECH SYSTEMS - Généré le: ${new Date().toLocaleDateString('fr-FR')}</div>
                        ${clonedTable.outerHTML}
                    </body>
                </html>
            `);
                    printWindow.document.close();

                    // Wait for content to load then print
                    setTimeout(() => {
                        printWindow.print();
                        printWindow.close();
                    }, 500);

                    console.log('[v0] Print completed successfully');
                } catch (error) {
                    console.error('[v0] Print error:', error);
                    alert('Erreur lors de l\'impression: ' + error.message);
                }
            };

            // Sort functionality
            let sortDirection = {};

            window.sortTable = function (columnIndex, columnName) {
                try {
                    console.log(`[v0] Sorting table by column ${columnIndex} (${columnName})`);
                    const table = document.getElementById('relevesTable');
                    if (!table) return;

                    const tbody = table.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));

                    // Skip if no data rows or only "no data" row
                    if (rows.length === 0 || (rows.length === 1 && rows[0].cells.length === 1)) return;

                    // Toggle sort direction
                    sortDirection[columnIndex] = sortDirection[columnIndex] === 'asc' ? 'desc' : 'asc';

                    // Update sort icons
                    table.querySelectorAll('.sortable').forEach(th => th.classList.remove('sort-asc', 'sort-desc'));
                    const currentTh = table.querySelector(`th:nth-child(${columnIndex + 1})`);
                    if (currentTh) {
                        currentTh.classList.add('sort-' + sortDirection[columnIndex]);
                    }

                    // Sort rows
                    rows.sort((a, b) => {
                        if (!a.cells[columnIndex] || !b.cells[columnIndex]) return 0;

                        let aVal = a.cells[columnIndex].textContent.trim();
                        let bVal = b.cells[columnIndex].textContent.trim();

                        // Handle different data types based on column
                        if (columnIndex === 2 || columnIndex === 3) { // Date columns
                            aVal = a.cells[columnIndex].getAttribute('data-order') || aVal;
                            bVal = b.cells[columnIndex].getAttribute('data-order') || bVal;
                        } else if (columnIndex === 4) { // Amount columns
                            aVal = parseFloat(a.cells[columnIndex].getAttribute('data-order') || aVal.replace(/[^\d.-]/g, '')) || 0;
                            bVal = parseFloat(b.cells[columnIndex].getAttribute('data-order') || bVal.replace(/[^\d.-]/g, '')) || 0;
                        }

                        if (sortDirection[columnIndex] === 'asc') {
                            return aVal > bVal ? 1 : -1;
                        } else {
                            return aVal < bVal ? 1 : -1;
                        }
                    });

                    // Reorder rows in DOM
                    rows.forEach(row => tbody.appendChild(row));
                    console.log(`[v0] Table sorted by ${columnName} (${sortDirection[columnIndex]})`);
                } catch (error) {
                    console.error('[v0] Sort error:', error);
                }
            };

            // Check libraries after page load
            setTimeout(function () {
                console.log('[v0] Library status check:');
                console.log('[v0] XLSX available:', typeof XLSX !== 'undefined');
                console.log('[v0] jsPDF available:', typeof window.jspdf !== 'undefined');
                console.log('[v0] autoTable available:', typeof window.jspdf !== 'undefined' && window.jspdf.jsPDF && typeof window.jspdf.jsPDF.prototype.autoTable === 'function');
            }, 1000);
        });

        document.addEventListener('livewire:load', function () {
            console.log('[v0] Livewire v2 loaded - reinitializing functions');
        });

        document.addEventListener('livewire:update', function () {
            console.log('[v0] Livewire v2 updated - functions still available');
        });
    </script>
</div>