<div wire:poll.1000ms>
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div>
            <button class="btn btn-primary" wire:click="calculer">
                <i class="fas fa-calculator"></i> Calculer les intérêts du relevé
            </button>
        </div>
        <div class="d-flex gap-2">
            @if($totalImpayes > 0)
                <button class="btn btn-warning" wire:click="generateInvoiceUnpaid">
                    <i class="fas fa-file-invoice-dollar"></i> Générer facture (intérêts impayés)
                </button>
            @endif

            @if(!empty($lastInvoicePath))
                <a href="{{ Storage::url($lastInvoicePath) }}" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-download"></i> Télécharger dernière facture
                </a>
            @endif
        </div>
    </div>

    <!-- Résumé du relevé -->
    <div class="mb-3">
        <strong>Statut du relevé :</strong> <span
            class="badge {{ $releveStatus === 'Payé' ? 'bg-success' : 'bg-danger' }}">{{ $releveStatus }}</span>
        &nbsp; | &nbsp;
        <strong>Montant relevé :</strong> {{ number_format($releveAmount ?? 0, 2, ',', ' ') }} DA
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Mois</th>
                    <th>Date début</th>
                    <th>Date fin</th>
                    <th>Jours retard</th>
                    <th class="text-end">Intérêt HT</th>
                    <th class="text-end">Intérêt TTC</th>
                    <th>Référence</th>
                    <th>PDF</th>
                    <th>Statut</th>
                    <th>Validation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody wire:poll.1000ms>
                @forelse($periodes as $p)
                    <tr>
                        <td>{{ $p['mois'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($p['date_debut'])->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($p['date_fin'])->format('d/m/Y') }}</td>
                        <td>{{ $p['jours_retard'] }}</td>
                        <td class="text-end">{{ number_format($p['interet_ht'], 2, ',', ' ') }} DA</td>
                        <td class="text-end">{{ number_format($p['interet_ttc'], 2, ',', ' ') }} DA</td>
                        <td>{{ $p['reference'] ?? '-' }}</td>
                        <td>
                            @if(!empty($p['pdf_path']))
                                <a href="{{ Storage::url($p['pdf_path']) }}" target="_blank"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file-pdf"></i> Voir
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @php $statut = $p['statut'] ?? 'Impayé'; @endphp
                            @if($statut === 'Payé')
                                <span class="badge bg-success">Payé</span>
                            @else
                                <span class="badge bg-danger">Impayé</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($p['valide']))
                                <span class="badge bg-success">Validé</span>
                            @else
                                <span class="badge bg-warning">En attente</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @if(!$p['valide'])
                                    <button class="btn btn-outline-success" wire:click="validerInteret({{ $p['id'] }})"
                                        onclick="return confirm('Valider cet intérêt ?')" title="Valider">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                @if($statut !== 'Payé')
                                    <button class="btn btn-outline-primary" wire:click="openPayModal({{ $p['id'] }})"
                                        title="Marquer comme payé">
                                        <i class="fas fa-money-check-alt"></i>
                                    </button>

                                    <!-- Générer facture pour une seule période -->
                                    <button class="btn btn-outline-warning" wire:click="generateInvoice({{ $p['id'] }})"
                                        title="Générer facture (cette période)">
                                        <i class="fas fa-file-invoice"></i>
                                    </button>
                                @endif
                                <button class="btn btn-outline-danger" wire:click="supprimerInteret({{ $p['id'] }})"
                                    onclick="return confirm('Supprimer cet intérêt ?')" title="Supprimer"
                                    @if($statut === 'Payé') disabled @endif>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted">Aucun intérêt calculé pour ce relevé.</td>
                    </tr>
                @endforelse
            </tbody>

            <!-- Footer : total -->
            <tfoot>
                <tr class="table-secondary">
                    <th colspan="4" class="text-end">Total impayé :</th>
                    <th class="text-end">{{ number_format($totalImpayesHT ?? 0, 2, ',', ' ') }} DA</th>
                    <th class="text-end">{{ number_format($totalImpayesTTC ?? 0, 2, ',', ' ') }} DA</th>
                    <th colspan="5"></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- (les modals existants: showPayModal, showValidateAllModal, showRelevePayModal) -->
    <!-- Modal: Marquer intérêt comme payé -->
    @if($showPayModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmer le paiement</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <p>Voulez-vous vraiment marquer cet intérêt moratoire comme <strong>payé</strong> ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Annuler</button>
                        <button type="button" class="btn btn-success" wire:click="marquerInteretPaye">
                            <i class="fas fa-check"></i> Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Valider tous les intérêts -->
    @if($showValidateAllModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Valider tous les intérêts</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <p>Voulez-vous vraiment valider <strong>tous les intérêts non validés</strong> de ce relevé ?</p>
                        <p class="text-muted small">Cette action marquera tous les intérêts comme validés.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Annuler</button>
                        <button type="button" class="btn btn-success" wire:click="validerTousInterets">
                            <i class="fas fa-check-double"></i> Valider tous
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Marquer relevé comme payé avec option pour les intérêts -->
    @if($showRelevePayModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Marquer le relevé comme payé</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <p>Voulez-vous marquer ce relevé comme <strong>payé</strong> ?</p>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" wire:model="markInteretsAsPaid"
                                id="markInteretsAsPaid">
                            <label class="form-check-label" for="markInteretsAsPaid">
                                Marquer aussi tous les intérêts associés comme payés
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Annuler</button>
                        <button type="button" class="btn btn-success" wire:click="marquerRelevePaye">
                            <i class="fas fa-check"></i> Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Marquer facture comme payée -->
    @if($showFacturePayModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmer le paiement de la facture</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <p>Voulez-vous vraiment marquer cette facture comme <strong>payée</strong> ?</p>
                        <p class="text-muted small">La date de règlement sera définie à aujourd'hui si elle n'est pas déjà
                            renseignée.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Annuler</button>
                        <button type="button" class="btn btn-success" wire:click="marquerFacturePaye">
                            <i class="fas fa-check"></i> Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Marquer toutes les factures comme payées -->
    @if($showFacturesPayAllModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Marquer toutes les factures comme payées</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <p>Voulez-vous vraiment marquer <strong>toutes les factures non payées</strong> de ce relevé comme
                            payées ?</p>
                        <p class="text-muted small">La date de règlement sera définie à aujourd'hui pour toutes les factures
                            qui n'en ont pas encore.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Annuler</button>
                        <button type="button" class="btn btn-success" wire:click="marquerToutesFacturesPayees">
                            <i class="fas fa-check-double"></i> Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- Modal: Marquer facture comme payée (adapté pour invoice sélectionnée) -->
    @if($showInvoiceFacturePayModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmer le paiement de la facture</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <p>Voulez-vous vraiment marquer cette facture comme <strong>payée</strong> ?</p>
                        <p class="text-muted small">La date de règlement sera définie à aujourd'hui si elle n'est pas déjà
                            renseignée.</p>
                        <p><strong>Montant facture :</strong> {{ number_format($selectedInvoiceAmount ?? 0, 2, ',', ' ') }}
                            DA</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Annuler</button>
                        <button type="button" class="btn btn-success"
                            wire:click="marquerInvoiceFacturePaye({{ $selectedInvoiceId ?? 'null' }})">
                            <i class="fas fa-check"></i> Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>