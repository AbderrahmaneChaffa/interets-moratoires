<div>
    <x-app-layout>
        <!-- <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="fas fa-list"></i> Relevé d'intérêts moratoires 12345678
                </h2>
                <a href="{{ route('factures') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </x-slot> -->

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-2">
                {{-- Card Détails du Relevé --}}
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Détails du Relevé</h5>
                        </div>

                        <div class="d-flex align-items-center">
                            <span class="badge bg-light text-dark me-3">Réf: {{ $releve->reference ?? 'N/A' }}</span>

                            {{-- Bouton pour ouvrir modal "marquer payé" (composant dédié) --}}
                            @if($releve->statut !== 'Payé')
                                <button class="btn btn-success btn-sm me-2"
                                    wire:click="$emitTo('releve.releve-pay-modal', 'openRelevePayModal', {{ $releve->id }})">
                                    <i class="fas fa-check"></i> Marquer comme payé
                                </button>
                            @else
                                <button class="btn btn-warning btn-sm me-2" wire:click="marquerReleveImpaye"
                                    onclick="return confirm('Marquer ce relevé comme impayé ?')">
                                    <i class="fas fa-times"></i> Marquer comme impayé
                                </button>
                            @endif

                            <a href="{{ route('releves') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong><i class="fas fa-user"></i> Client :</strong><br>
                                {{ $releve->client->raison_sociale ?? '-' }}
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fas fa-calendar-alt"></i> Période :</strong><br>
                                {{ optional($releve->date_debut)->format('d/m/Y') ?? '-' }} -
                                {{ optional($releve->date_fin)->format('d/m/Y') ?? '-' }}
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fas fa-info-circle"></i> Statut :</strong><br>
                                @if(optional($releve)->statut === 'Payé')
                                    <span class="badge bg-success">Payé</span>
                                @else
                                    <span class="badge bg-danger">Impayé</span>
                                @endif
                            </div>
                            <div class="col-md-3 text-end">
                                @if(optional($releve)->releve_pdf)
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

                {{-- Section Factures du relevé --}}

                <!-- Section Factures du relevé -->
                @if($releve->factures->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Factures du relevé </h5>
                            @php
                                $nonPayees = $releve->factures->where('statut', '!=', 'Payé')->count();
                            @endphp
                            @if($nonPayees > 0)
                                <button class="btn btn-success btn-sm" wire:click="Livewire.emit('openFacturesPayAllModal')">
                                    <i class="fas fa-check-double"></i> Marquer toutes comme payées ({{ $nonPayees }})
                                </button>
                            @endif
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
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($releve->factures as $facture)
                                            <tr>
                                                <td>{{ $facture->reference }}</td>
                                                <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
                                                <td class="text-end">{{ number_format($facture->montant_ht, 2, ',', ' ') }} DA
                                                </td>
                                                <td class="text-end">{{ number_format($facture->net_a_payer, 2, ',', ' ') }} DA
                                                </td>
                                                <td>
                                                    @php
                                                        $statut = $facture->statut ?? 'Impayé';
                                                    @endphp
                                                    @if($statut === 'Payé')
                                                        <span class="badge bg-success">Payé</span>
                                                    @else
                                                        <span class="badge bg-danger">Impayé</span>
                                                    @endif
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
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if($statut !== 'Payé')
                                                            <button class="btn btn-outline-primary"
                                                                wire:click="openFacturePayModal({{ $facture->id }})"
                                                                title="Marquer comme payée">
                                                                <i class="fas fa-money-check-alt"></i>
                                                            </button>
                                                        @else
                                                            <button class="btn btn-outline-warning"
                                                                onclick="if(confirm('Marquer cette facture comme impayée ?')) { Livewire.emit('marquerFactureImpaye', {{ $facture->id }}); }"
                                                                title="Marquer comme impayée">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Modal: Marquer facture comme payée -->
                    <div wire:ignore.self>
                        @if($showFacturePayModal)
                            <div class="modal fade show d-block" tabindex="-1" role="dialog"
                                style="background: rgba(0,0,0,0.5);">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirmer le paiement de la facture</h5>
                                            <button type="button" class="btn-close" wire:click="closeModals"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Voulez-vous vraiment marquer cette facture comme <strong>payée</strong> ?</p>
                                            <p class="text-muted small">La date de règlement sera définie à aujourd'hui si elle
                                                n'est pas déjà
                                                renseignée.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                wire:click="closeModals">Annuler</button>
                                            <button type="button" class="btn btn-success" wire:click="marquerFacturePaye">
                                                <i class="fas fa-check"></i> Confirmer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <!-- Modal: Marquer toutes les factures comme payées -->
                    <div wire:ignore.self>
                        @if($showFacturesPayAllModal)
                            <div class="modal fade show d-block" tabindex="-1" role="dialog"
                                style="background: rgba(0,0,0,0.5);">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Marquer toutes les factures comme payées</h5>
                                            <button type="button" class="btn-close" wire:click="closeModals"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Voulez-vous vraiment marquer <strong>toutes les factures non payées</strong> de
                                                ce
                                                relevé comme
                                                payées ?</p>
                                            <p class="text-muted small">La date de règlement sera définie à aujourd'hui pour
                                                toutes
                                                les factures
                                                qui n'en ont pas encore.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                wire:click="closeModals">Annuler</button>
                                            <button type="button" class="btn btn-success"
                                                wire:click="marquerToutesFacturesPayees">
                                                <i class="fas fa-check-double"></i> Confirmer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Card intérêts --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div><strong>Les intérêts du relevé</strong></div>
                        <div class="btn-group" role="group">
                            <button class="btn btn-primary" wire:click="calculerInteretsReleve">
                                <i class="fas fa-calculator"></i> Calculer les intérêts du relevé
                            </button>
                            {{-- Le bouton pay/impayé est maintenant dans l'entête du détail --}}
                        </div>
                    </div>

                    <div class="card-body">
                        <div wire:ignore.self>
                            @livewire('releve-interets', ['releveId' => $releve->id], key('releve-' . $releve->id))
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Composants modals livewire (séparés et uniques) --}}
        @livewire('releve.releve-pay-modal', ['releveId' => $releve->id], key('releve-pay-modal-' . $releve->id))

        {{-- si tu as d'autres modals : facture-pay-modal, factures-pay-all-modal, etc. les inclure de la même façon
        --}}
    </x-app-layout>

</div>