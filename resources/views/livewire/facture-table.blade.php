<div class="container mt-4">
    <h2>Tableau des factures avec intérêts</h2>
    
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filtres</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label>Client</label>
                    <select wire:model="selectedClient" class="form-control">
                        <option value="">Tous les clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->raison_sociale }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Date de début</label>
                    <input type="date" wire:model="dateFrom" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Date de fin</label>
                    <input type="date" wire:model="dateTo" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <!-- Boutons d'export -->
    <div class="mb-3">
        <button wire:click="exportPdf" class="btn btn-danger">
            <i class="fas fa-file-pdf"></i> Export PDF
        </button>
        <button wire:click="exportExcel" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export Excel
        </button>
    </div>

    <!-- Tableau -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Résultats ({{ $factures->count() }} factures)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Référence</th>
                            <th>Date facture</th>
                            <th>Client</th>
                            <th>Montant HT</th>
                            <th>Jours de retard</th>
                            <th>Intérêt HT</th>
                            <th>Intérêt TTC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($factures as $facture)
                            <tr>
                                <td>{{ $facture->reference }}</td>
                                <td>{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</td>
                                <td>{{ $facture->client->raison_sociale ?? '-' }}</td>
                                <td>{{ number_format($facture->montant_ht, 2) }} DA</td>
                                <td>
                                    @if($facture->jours_retards > 0)
                                        <span class="badge bg-warning">{{ $facture->jours_retards }} jours</span>
                                    @else
                                        <span class="badge bg-success">0 jour</span>
                                    @endif
                                </td>
                                <td>{{ number_format($facture->interet_ht, 2) }} DA</td>
                                <td>{{ number_format($facture->interet_ttc, 2) }} DA</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucune facture trouvée</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
