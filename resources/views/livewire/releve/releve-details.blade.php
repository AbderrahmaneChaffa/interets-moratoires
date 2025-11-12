<div>
    <x-app-layout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-2">
                {{-- Card Détails du Relevé --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0"><i class="fas fa-list"></i> Relevé d'intérêts moratoires
                                @if(!empty($releve->reference)) #{{ $releve->reference }} @endif
                            </h5>
                            <small
                                class="text-muted">{{ optional($releve->client)->raison_sociale ?? 'Client inconnu' }}</small>
                        </div>
                        <div class="d-flex align-items-center">
                            @if(optional($releve)->statut === 'Payé')
                                <span class="badge bg-success me-2">Payé</span>
                            @else
                                <span class="badge bg-danger me-2">Impayé</span>
                            @endif

                            @if(!empty($releve->releve_pdf))
                                <a href="{{ route('releves.pdf', basename($releve->releve_pdf)) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file-pdf"></i> Voir PDF
                                </a>
                            @else
                                <span class="text-muted small">Aucun PDF</span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Date création :</strong><br>
                                {{ optional($releve->created_at) ? $releve->created_at->format('d/m/Y') : '—' }}
                            </div>
                            <div class="col-md-4">
                                <strong>Dernière facture :</strong><br>
                                {{ optional($releve->date_derniere_facture) ? $releve->date_derniere_facture->format('d/m/Y') : '—' }}
                            </div>
                            <div class="col-md-4 text-end">
                                <strong>Montant total HT :</strong><br>
                                {{ number_format($releve->montant_total_ht ?? 0, 2, ',', ' ') }} DA
                            </div>
                        </div>

                        {{-- Factures list --}}
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Référence</th>
                                        <th>Date</th>
                                        <th class="text-end">Montant HT</th>
                                        <th class="text-end">Net à payer</th>
                                        <th>Statut</th>
                                        <th>PDF</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($releve->factures as $facture)
                                        <tr>
                                            <td>{{ $facture->reference }}</td>
                                            <td>{{ optional($facture->date_facture) ? $facture->date_facture->format('d/m/Y') : '—' }}
                                            </td>
                                            <td class="text-end">{{ number_format($facture->montant_ht ?? 0, 2, ',', ' ') }}
                                                DA</td>
                                            <td class="text-end">
                                                {{ number_format($facture->net_a_payer ?? 0, 2, ',', ' ') }} DA
                                            </td>
                                            <td>
                                                @php $statut = $facture->statut ?? 'Impayé'; @endphp
                                                @if($statut === 'Payé')
                                                    <span class="badge bg-success">Payé</span>
                                                @else
                                                    <span class="badge bg-danger">Impayé</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($facture->pdf_path))
                                                    <a href="{{ route('factures.pdf', basename($facture->pdf_path)) }}"
                                                        target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted small">Aucun PDF</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($statut !== 'Payé')
                                                    <button class="btn btn-sm btn-success"
                                                        wire:click="openFacturePayModal({{ $facture->id }})">
                                                        <i class="fas fa-check"></i> Marquer payé
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        wire:click="marquerFactureImpaye({{ $facture->id }})">
                                                        <i class="fas fa-times"></i> Marquer impayé
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Aucune facture trouvée.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <div>
                                <span class="small text-muted">Factures impayées : {{ $this->nonPayees }}</span>
                            </div>
                            <div>
                                <button class="btn btn-outline-primary" wire:click="openFacturesPayAllModal"
                                    @if($this->nonPayees == 0) disabled @endif>
                                    <i class="fas fa-check-double"></i> Marquer toutes payées
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card intérêts --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div><strong>Les intérêts du relevé</strong></div>
                        <div>
                            <button class="btn btn-primary" wire:click="calculerInteretsReleve">
                                <i class="fas fa-calculator"></i> Calculer les intérêts du relevé
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div wire:ignore>
                            @livewire('releve-interets', ['releveId' => $releve->id], key('releve-interets-' . $releve->id))
                        </div>
                    </div>
                </div>

                {{-- Modals gérés par ce composant --}}
                {{-- Modal: Confirmer paiement d'une facture --}}
                <div wire:ignore.self>
                    @if($showFacturePayModal)
                        <div class="modal fade show d-block" tabindex="-1" role="dialog"
                            style="background: rgba(0,0,0,0.5);">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirmer le paiement</h5>
                                        <button type="button" class="btn-close" aria-label="Close"
                                            wire:click="closeModals"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Voulez-vous vraiment marquer cette facture comme <strong>payée</strong> ?</p>
                                        <p class="text-muted small">La date de règlement sera définie à aujourd'hui si elle
                                            n'est pas déjà renseignée.</p>
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

                {{-- Modal: Confirmer toutes les factures payées --}}
                <div wire:ignore.self>
                    @if($showFacturesPayAllModal)
                        <div class="modal fade show d-block" tabindex="-1" role="dialog"
                            style="background: rgba(0,0,0,0.5);">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Marquer toutes les factures comme payées</h5>
                                        <button type="button" class="btn-close" aria-label="Close"
                                            wire:click="closeModals"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Confirmer l'opération : toutes les factures liées à ce relevé seront marquées
                                            comme <strong>payées</strong> et la date de règlement sera définie si elle est
                                            absente.</p>
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

                {{-- Si tu as d'autres modals Livewire séparés, tu peux les inclure ici --}}
                @livewire('releve.releve-pay-modal', ['releveId' => $releve->id], key('releve-pay-modal-' . $releve->id))
            </div>
        </div>
    </x-app-layout>
</div>