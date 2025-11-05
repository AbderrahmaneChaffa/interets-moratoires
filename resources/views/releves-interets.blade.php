<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-list"></i> Relevé d'intérêts moratoires
            </h2>
            <a href="{{ route('factures') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Détails du Relevé</h5>
                    <span class="badge bg-light text-dark">Réf: {{ $releve->reference ?? 'N/A' }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong><i class="fas fa-user"></i> Client :</strong><br>
                            {{ $releve->client->raison_sociale }}
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fas fa-calendar-alt"></i> Période :</strong><br>
                            {{ $releve->date_debut->format('d/m/Y') }} - {{ $releve->date_fin->format('d/m/Y') }}
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fas fa-info-circle"></i> Statut :</strong><br>
                            @if($releve->statut === 'Payé')
                            <span class="badge bg-success">Payé</span>
                            @else
                            <span class="badge bg-danger">Impayé</span>
                            @endif
                        </div>
                        <div class="col-md-3 text-end">
                            @if($releve->releve_pdf)
                            <a href="{{ route('releves.pdf', basename($releve->releve_pdf)) }}" target="_blank"
                                class="btn btn-sm btn-outline-light">
                                <i class="fas fa-file-pdf"></i> Voir PDF
                            </a>
                            @else
                            <span class="text-muted">Aucun PDF</span>
                            @endif
                        </div>
                    </div>

                    @if($releve->date_derniere_facture)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="fas fa-calendar-check"></i> Dernière Facture :</strong><br>
                            {{ $releve->date_derniere_facture->format('d/m/Y') }}
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-money-bill"></i> Montant Total HT :</strong><br>
                            {{ number_format($releve->montant_total_ht, 2, ',', ' ') }} DA
                        </div>
                    </div>
                    @endif
                </div>
            </div>



            <!-- Section Factures du relevé -->
            @if($releve->factures->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Factures du relevé</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Date facture</th>
                                    <th class="text-end">Montant HT</th>
                                    <th class="text-end">Net à payer</th>
                                    <th>Statut</th>
                                    <th>PDF</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($releve->factures as $facture)
                                <tr>
                                    <td>{{ $facture->reference }}</td>
                                    <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
                                    <td class="text-end">{{ number_format($facture->montant_ht, 2, ',', ' ') }} DA</td>
                                    <td class="text-end">{{ number_format($facture->net_a_payer, 2, ',', ' ') }} DA</td>
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
                                            <i class="fas fa-file-pdf"></i> Voir PDF
                                        </a>
                                        @else
                                        <span class="text-muted">Aucun PDF</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Actions</strong>
                    </div>
                    <div class="btn-group" role="group">
                        <button class="btn btn-primary" onclick="window.livewire.emit('calculerInteretsReleve')">
                            <i class="fas fa-calculator"></i> Calculer les intérêts du relevé
                        </button>
                        @if($releve->statut !== 'Payé')
                        <button class="btn btn-success" onclick="Livewire.emit('openRelevePayModal')">
                            <i class="fas fa-check"></i> Marquer comme payé
                        </button>
                        @else
                        <button class="btn btn-warning" wire:click="marquerReleveImpaye"
                            onclick="return confirm('Marquer ce relevé comme impayé ?')">
                            <i class="fas fa-times"></i> Marquer comme impayé
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @livewire('releve-interets', ['releveId' => $releve->id], key('releve-'.$releve->id))
                </div>
            </div>
        </div>
    </div>
</x-app-layout>