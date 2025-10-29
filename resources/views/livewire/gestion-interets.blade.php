<div>
    @if ($facture)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calculator"></i> Gestion des intérêts moratoires
                    <span class="badge bg-primary ms-2">{{ $facture->reference }}</span>
                </h5>
            </div>
            <div class="card-body">
                <!-- Informations de la facture -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Informations de la facture</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Client:</strong></td>
                                <td>{{ $facture->client->raison_sociale }}</td>
                            </tr>
                            <tr>
                                <td><strong>Montant HT:</strong></td>
                                <td>{{ $facture->montant_ht_formatted }}</td>
                            </tr>
                            <tr>
                                <td><strong>Date dépôt:</strong></td>
                                <td>{{ $facture->date_depot ? $facture->date_depot->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Délai légal:</strong></td>
                                <td>{{ $facture->delai_legal_jours ?? 30 }} jours</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Calcul des intérêts</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Jours de retard:</strong></td>
                                <td>{{ $facture->jours_retard }}</td>
                            </tr>
                            <tr>
                                <td><strong>Mois de retard:</strong></td>
                                <td>{{ $facture->mois_retard }}</td>
                            </tr>
                            <tr>
                                <td><strong>Taux client:</strong></td>
                                <td>{{ $facture->client->taux ?? 0 }}%</td>
                            </tr>
                            <tr>
                                <td><strong>Formule:</strong></td>
                                <td>{{ $facture->client->formule ?? 'Standard' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Bouton calculer tous les intérêts -->
                @if ($facture->peutGenererInterets())
                    <div class="mb-3">
                        <button wire:click="calculerInteret({{ $facture->id }})" class="btn btn-primary">
                            <i class="fas fa-calculator"></i> Calculer tous les intérêts
                        </button>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Cette facture ne peut pas générer d'intérêts moratoires.
                    </div>
                @endif

                <!-- Périodes d'intérêts -->
                @if (count($periodesInterets) > 0)
                    @if ($facture->interets->count() > 0)
                        <div class="mb-3 d-flex gap-2">
                            <button onclick="exportInteretsToExcel()" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <!-- <button onclick="exportInteretsToPDF()" class="btn btn-danger btn-sm">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button> -->
                            <button onclick="printInteretsTable()" class="btn btn-info btn-sm">
                                <i class="fas fa-print"></i> Imprimer
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="interetsTable" class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mois</th>
                                    <th>Période</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Jours retard</th>
                                    <th>Intérêt HT</th>
                                    <th>Intérêt TTC</th>
                                    <th>Référence</th>
                                    <th>PDF</th>
                                    <th>Statut</th>
                                    <th>Validation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($periodesInterets as $periode)
                                    @php
                                        $interet = $periode['interet_existant'];
                                        $dateDebut = is_string($periode['date_debut_periode']) ?
                                            \Carbon\Carbon::parse($periode['date_debut_periode']) : $periode['date_debut_periode'];
                                        $dateFin = is_string($periode['date_fin_periode']) ?
                                            \Carbon\Carbon::parse($periode['date_fin_periode']) : $periode['date_fin_periode'];
                                    @endphp
                                    <tr>
                                        <td>{{ $periode['mois'] }}</td>
                                        <td>{{ $dateDebut->format('m/Y') }}</td>
                                        <td>{{ $dateDebut->format('d/m/Y') }}</td>
                                        <td>{{ $dateFin->format('d/m/Y') }}</td>
                                        <!-- Fixed array access for jours_retard instead of object property -->
                                        <td>{{ $periode['interet_existant'] ? ($periode['interet_existant']['jours_retard'] ?? $periode['interet_existant']->jours_retard ?? '-') : '-' }}
                                        </td>
                                        <td class="text-end">
                                            @if ($periode['interet_existant'])
                                                <!-- Fixed array access for interet_ht_formatted -->
                                                {{ $periode['interet_existant']['interet_ht_formatted'] ?? $periode['interet_existant']->interet_ht_formatted ?? '-' }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($periode['interet_existant'])
                                                <!-- Fixed array access for interet_ttc_formatted -->
                                                {{ $periode['interet_existant']['interet_ttc_formatted'] ?? $periode['interet_existant']->interet_ttc_formatted ?? '-' }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        {{-- Référence --}}
                                        <td>
                                            @if ($interet)
                                                @php
                                                    $interetId = is_array($interet) ? $interet['id'] : $interet->id;
                                                @endphp
                                                <div class="input-group input-group-sm">
                                                    <!-- Fixed array access for interet id in wire:model -->
                                                    <input type="text" class="form-control" wire:model="references.{{ $interetId }}"
                                                        wire:blur="updateReference({{ $interetId }})" placeholder="Référence...">
                                                    <button class="btn btn-outline-secondary" type="button"
                                                        wire:click="updateReference({{ $interetId }})" title="Sauvegarder">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        {{-- PDF --}}
                                        <td>
                                            @if ($interet)
                                                @php
                                                    $interetId = is_array($interet) ? $interet['id'] : $interet->id;
                                                    $pdfPath = is_array($interet) ? ($interet['pdf_path'] ?? null) : $interet->pdf_path;
                                                @endphp
                                                <div class="d-flex flex-column gap-1">
                                                    @if ($pdfPath)
                                                        <a href="{{ Storage::url($pdfPath) }}" target="_blank"
                                                            class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-file-pdf"></i> Voir PDF
                                                        </a>
                                                    @endif

                                                    <div class="input-group input-group-sm">
                                                        <!-- Fixed array access for interet id in wire:model and wire:target -->
                                                        <input type="file" wire:model="pdfUploads.{{ $interetId }}" class="form-control"
                                                            accept=".pdf">
                                                        <button class="btn btn-outline-success" type="button"
                                                            wire:click="uploadPdf({{ $interetId }})" wire:loading.attr="disabled"
                                                            wire:target="pdfUploads.{{ $interetId }}" title="Uploader PDF">
                                                            <span wire:loading.remove wire:target="pdfUploads.{{ $interetId }}">
                                                                <i class="fas fa-upload"></i>
                                                            </span>
                                                            <span wire:loading wire:target="pdfUploads.{{ $interetId }}">
                                                                <i class="fas fa-spinner fa-spin"></i>
                                                            </span>
                                                        </button>
                                                    </div>

                                                    <!-- Fixed array access for interet id in error directive -->
                                                    @error("pdfUploads.{$interetId}")
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        {{-- Statut --}}
                                        <td>
                                            @if ($interet)
                                                                    @php
                                                                        $statut = is_array($interet) ? ($interet['statut'] ?? 'En attente') : ($interet->statut
                                                                            ?? 'En attente');
                                                                    @endphp
                                                                    <span class="badge 
                                                @if($statut === 'Validé') bg-success 
                                                @elseif($statut === 'Calculé') bg-info 
                                                @else bg-secondary @endif">
                                                                        {{ $statut }}
                                                                    </span>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        {{-- Validation --}}
                                        <td>
                                            @if ($interet)
                                                @php
                                                    $interetValide = is_array($interet) ? ($interet['valide'] ?? false) : $interet->valide;
                                                @endphp
                                                @if($interetValide)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle"></i> Validé
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock"></i> En attente
                                                    </span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($periode['peut_calculer'])
                                                <button
                                                    wire:click="calculerInteretPeriode('{{ $dateDebut->format('Y-m-d') }}', '{{ $dateFin->format('Y-m-d') }}')"
                                                    class="btn btn-sm btn-success" title="Calculer">
                                                    <i class="fas fa-calculator"></i>
                                                </button>
                                            @else
                                                @php
                                                    $interetExistantId = is_array($periode['interet_existant'])
                                                        ? $periode['interet_existant']['id']
                                                        : $periode['interet_existant']->id;

                                                    $interetId = is_array($interet) ? $interet['id'] : $interet->id;
                                                    $interetValide = is_array($interet) ? ($interet['valide'] ?? false) : $interet->valide;
                                                    $interetStatut = is_array($interet) ? ($interet['statut'] ?? null) : ($interet->statut
                                                        ?? null);
                                                @endphp

                                                <div class="btn-group" role="group">

                                                    {{-- Bouton Valider (uniquement si pas encore validé) --}}
                                                    <button wire:click="openValidateModal({{ $interetId }})"
                                                        class="btn btn-sm btn-primary" title="Valider le calcul" @if($interetValide)
                                                        disabled @endif>
                                                        <i class="fas fa-check-circle"></i>
                                                        @if($interetValide) Validé @else Valider @endif
                                                    </button>

                                                    {{-- Bouton Payer (uniquement si validé) --}}
                                                    @if($interetValide)
                                                        <button wire:click="openPayModal({{ $interetId }})"
                                                            class="btn btn-sm {{ $interetStatut === 'Payée' ? 'btn-success' : 'btn-warning' }}"
                                                            title="{{ $interetStatut === 'Payée' ? 'Déjà payé' : 'Marquer comme payé' }}"
                                                            @if($interetStatut === 'Payée') disabled @endif>
                                                            <i class="fas fa-money-check-alt"></i>
                                                            {{ $interetStatut === 'Payée' ? 'Payé' : 'Payer' }}
                                                        </button>
                                                    @endif

                                                    {{-- Bouton Supprimer (désactivé si payé) --}}
                                                    <button wire:click="supprimerInteret({{ $interetExistantId }})"
                                                        class="btn btn-sm btn-danger" title="Supprimer" @if($interetStatut === 'Payée')
                                                        disabled @endif
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet intérêt ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>

                                                </div>


                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Total des intérêts -->
                    @if ($facture->interets->count() > 0)
                        <div class="row mt-3">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-sm table-bordered">
                                    <tr class="table-warning">
                                        <td><strong>Total intérêts HT:</strong></td>
                                        <td class="text-end">
                                            <strong>{{ \App\Services\InteretService::formaterMontant($facture->interets->sum('interet_ht')) }}
                                            </strong>
                                        </td>
                                    </tr>
                                    <tr class="table-danger">
                                        <td><strong>Total intérêts TTC:</strong></td>
                                        <td class="text-end">
                                            <strong>{{ \App\Services\InteretService::formaterMontant($facture->interets->sum('interet_ttc')) }}
                                            </strong>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calculator fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Aucune période d'intérêts à calculer pour cette facture.</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Aucune facture sélectionnée.
        </div>
    @endif

    @if($showPayModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmation</h5>
                        <button type="button" class="btn-close" wire:click="$set('showPayModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p>Voulez-vous vraiment marquer cet intérêt moratoire comme
                            <strong>payé</strong> ?
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showPayModal', false)">
                            Annuler
                        </button>
                        <button type="button" class="btn btn-success" wire:click="confirmRendrePaye"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="confirmRendrePaye">
                                <i class="fas fa-check"></i> Confirmer
                            </span>
                            <span wire:loading wire:target="confirmRendrePaye">
                                <i class="fas fa-spinner fa-spin"></i> Traitement...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if($showValidateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Validation du calcul</h5>
                        <button type="button" class="btn-close" wire:click="$set('showValidateModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p>Confirmez-vous que ce calcul d’intérêt moratoire est <strong>correct</strong> ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showValidateModal', false)">
                            Annuler
                        </button>
                        <button type="button" class="btn btn-success" wire:click="confirmValiderInteret"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="confirmValiderInteret">
                                <i class="fas fa-check-circle"></i> Valider
                            </span>
                            <span wire:loading wire:target="confirmValiderInteret">
                                <i class="fas fa-spinner fa-spin"></i> Validation...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initializeInteretsExport();
        });

        document.addEventListener('livewire:load', function () {
            initializeInteretsExport();
        });

        document.addEventListener('livewire:update', function () {
            setTimeout(function () {
                initializeInteretsExport();
            }, 100);
        });

        function initializeInteretsExport() {
            console.log('[v0] Initializing interests export functions');

            // Ensure functions are globally available
            window.exportInteretsToExcel = exportInteretsToExcel;
            window.exportInteretsToPDF = exportInteretsToPDF;
            window.printInteretsTable = printInteretsTable;
        }

        function exportInteretsToExcel() {
            try {
                if (typeof XLSX === 'undefined') {
                    alert('Erreur: Bibliothèque Excel non chargée');
                    return;
                }

                const table = document.getElementById('interetsTable');
                if (!table) {
                    alert('Erreur: Tableau non trouvé');
                    return;
                }

                // Get facture reference for filename
                const factureRef = document.querySelector('.badge.bg-primary')?.textContent || 'interets';

                // Create workbook
                const wb = XLSX.utils.book_new();

                // Convert table to worksheet
                const ws = XLSX.utils.table_to_sheet(table);
                const wsData = XLSX.utils.sheet_to_json(ws, {
                    header: 1
                });

                const totalsSection = document.querySelector('.table-bordered');
                if (totalsSection) {
                    wsData.push([]); // Empty row
                    wsData.push(['TOTAUX']);

                    const totalHT = totalsSection.querySelector('.table-warning td:last-child strong')?.textContent
                        ?.trim() || '0';
                    const totalTTC = totalsSection.querySelector('.table-danger td:last-child strong')?.textContent
                        ?.trim() || '0';

                    wsData.push(['Total intérêts HT:', totalHT]);
                    wsData.push(['Total intérêts TTC:', totalTTC]);
                }

                const newWs = XLSX.utils.aoa_to_sheet(wsData);
                XLSX.utils.book_append_sheet(wb, newWs, 'Intérêts');

                // Export file
                XLSX.writeFile(wb, `interets_${factureRef}_${new Date().toISOString().split('T')[0]}.xlsx`);

            } catch (error) {
                console.error('[v0] Excel export error:', error);
                alert('Erreur lors de l\'export Excel: ' + error.message);
            }
        }

        function exportInteretsToPDF() {
            try {
                if (typeof window.jspdf === 'undefined' || !window.jspdf.jsPDF) {
                    alert('jsPDF library not loaded properly. Please refresh the page.');
                    return;
                }

                const {
                    jsPDF
                } = window.jsPDF;
                const doc = new jsPDF();

                // Get facture reference
                const factureRef = document.querySelector('.badge.bg-primary')?.textContent || 'N/A';

                // Add title
                doc.setFontSize(16);
                doc.text('Intérêts Moratoires', 20, 20);
                doc.setFontSize(12);
                doc.text(`Facture: ${factureRef}`, 20, 30);
                doc.text(`Date: ${new Date().toLocaleDateString('fr-FR')}`, 20, 40);

                // Get table data
                const table = document.getElementById('interetsTable');
                if (!table) {
                    alert('Erreur: Tableau non trouvé');
                    return;
                }

                let yPosition = 60;

                // Add table headers
                const headers = table.querySelectorAll('thead th');
                let xPosition = 20;
                headers.forEach((header, index) => {
                    if (index < 7) { // Only first 7 columns to fit on page
                        doc.text(header.textContent.trim(), xPosition, yPosition);
                        xPosition += 25;
                    }
                });

                yPosition += 10;

                // Add table rows
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    xPosition = 20;
                    cells.forEach((cell, index) => {
                        if (index < 7) { // Only first 7 columns to fit on page
                            doc.text(cell.textContent.trim().substring(0, 15), xPosition, yPosition);
                            xPosition += 25;
                        }
                    });
                    yPosition += 8;

                    if (yPosition > 250) { // New page if needed
                        doc.addPage();
                        yPosition = 20;
                    }
                });

                const totalsSection = document.querySelector('.table-bordered');
                if (totalsSection) {
                    yPosition += 15;
                    doc.setFontSize(14);
                    doc.text('TOTAUX:', 20, yPosition);
                    yPosition += 10;

                    doc.setFontSize(12);
                    const totalHT = totalsSection.querySelector('.table-warning td:last-child strong')?.textContent
                        ?.trim() || '0';
                    const totalTTC = totalsSection.querySelector('.table-danger td:last-child strong')?.textContent
                        ?.trim() || '0';

                    doc.text(`Total intérêts HT: ${totalHT}`, 20, yPosition);
                    yPosition += 8;
                    doc.text(`Total intérêts TTC: ${totalTTC}`, 20, yPosition);
                }

                // Save PDF
                doc.save(`interets_${factureRef}_${new Date().toISOString().split('T')[0]}.pdf`);

            } catch (error) {
                console.error('[v0] PDF export error:', error);
                alert('Erreur lors de l\'export PDF: ' + error.message);
            }
        }

        function printInteretsTable() {
            try {
                const factureRef = document.querySelector('.badge.bg-primary')?.textContent || 'N/A';
                const table = document.getElementById('interetsTable');

                if (!table) {
                    alert('Erreur: Tableau non trouvé');
                    return;
                }

                const totalsSection = document.querySelector('.table-bordered');
                let totalsHTML = '';

                if (totalsSection) {
                    const totalHT = totalsSection.querySelector('.table-warning td:last-child strong')?.textContent
                        ?.trim() || '0';
                    const totalTTC = totalsSection.querySelector('.table-danger td:last-child strong')?.textContent
                        ?.trim() || '0';

                    totalsHTML = `
                <div class="totals">
                    <h3>TOTAUX</h3>
                    <table style="width: 50%; margin-left: auto;">
                        <tr style="background-color: #fff3cd;">
                            <td><strong>Total intérêts HT:</strong></td>
                            <td style="text-align: right;"><strong>${totalHT}</strong></td>
                        </tr>
                        <tr style="background-color: #f8d7da;">
                            <td><strong>Total intérêts TTC:</strong></td>
                            <td style="text-align: right;"><strong>${totalTTC}</strong></td>
                        </tr>
                    </table>
                </div>
            `;
                }

                // Create print content
                let printContent = `
            <html>
            <head>
                <title>Intérêts Moratoires - ${factureRef}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    h1 { color: #333; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .totals { margin-top: 30px; }
                    .totals h3 { margin-bottom: 15px; }
                </style>
            </head>
            <body>
                <h1>Intérêts Moratoires</h1>
                <p><strong>Facture:</strong> ${factureRef}</p>
                <p><strong>Date:</strong> ${new Date().toLocaleDateString('fr-FR')}</p>
                ${table.outerHTML}
                ${totalsHTML}
            </body></html>
        `;

                // Open print window
                const printWindow = window.open('', '_blank');
                printWindow.document.write(printContent);
                printWindow.document.close();
                printWindow.print();

            } catch (error) {
                console.error('[v0] Print error:', error);
                alert('Erreur lors de l\'impression: ' + error.message);
            }
        }
    </script>
</div>