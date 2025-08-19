<div>
    @if($facture)
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
                            <tr><td><strong>Client:</strong></td><td>{{ $facture->client->raison_sociale }}</td></tr>
                            <tr><td><strong>Montant HT:</strong></td><td>{{ $facture->montant_ht_formatted }}</td></tr>
                            <tr><td><strong>Date dépôt:</strong></td><td>{{ $facture->date_depot ? $facture->date_depot->format('d/m/Y') : '-' }}</td></tr>
                            <tr><td><strong>Délai légal:</strong></td><td>{{ $facture->delai_legal_jours ?? 30 }} jours</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Calcul des intérêts</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Jours de retard:</strong></td><td>{{ $facture->jours_retard }}</td></tr>
                            <tr><td><strong>Mois de retard:</strong></td><td>{{ $facture->mois_retard }}</td></tr>
                            <tr><td><strong>Taux client:</strong></td><td>{{ $facture->client->taux ?? 0 }}%</td></tr>
                            <tr><td><strong>Formule:</strong></td><td>{{ $facture->client->formule ?? 'Standard' }}</td></tr>
                        </table>
                    </div>
                </div>

                <!-- Bouton calculer tous les intérêts -->
                @if($facture->peutGenererInterets())
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
                @if(count($periodesInterets) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mois</th>
                                    <th>Période</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Jours retard</th>
                                    <th>Intérêt HT</th>
                                    <th>Intérêt TTC</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($periodesInterets as $periode)
                                    <tr>
                                        <td>{{ $periode['mois'] }}</td>
                                        <td>{{ $periode['date_debut_periode']->format('m/Y') }}</td>
                                        <td>{{ $periode['date_debut_periode']->format('d/m/Y') }}</td>
                                        <td>{{ $periode['date_fin_periode']->format('d/m/Y') }}</td>
                                        <td>{{ $periode['interet_existant'] ? $periode['interet_existant']->jours_retard : '-' }}</td>
                                        <td class="text-end">
                                            @if($periode['interet_existant'])
                                                {{ $periode['interet_existant']->interet_ht_formatted }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($periode['interet_existant'])
                                                {{ $periode['interet_existant']->interet_ttc_formatted }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($periode['peut_calculer'])
                                                <button wire:click="calculerInteretPeriode('{{ $periode['date_debut_periode']->format('Y-m-d') }}', '{{ $periode['date_fin_periode']->format('Y-m-d') }}')" 
                                                        class="btn btn-sm btn-success" title="Calculer">
                                                    <i class="fas fa-calculator"></i>
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-secondary" disabled title="Intérêt déjà calculé">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button wire:click="supprimerInteret({{ $periode['interet_existant']->id }})" 
                                                        class="btn btn-sm btn-danger" title="Supprimer"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet intérêt ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Total des intérêts -->
                    @if($facture->interets->count() > 0)
                        <div class="row mt-3">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-sm table-bordered">
                                    <tr class="table-warning">
                                        <td><strong>Total intérêts HT:</strong></td>
                                        <td class="text-end"><strong>{{ InteretService::formaterMontant($facture->interets->sum('interet_ht')) }}</strong></td>
                                    </tr>
                                    <tr class="table-danger">
                                        <td><strong>Total intérêts TTC:</strong></td>
                                        <td class="text-end"><strong>{{ InteretService::formaterMontant($facture->interets->sum('interet_ttc')) }}</strong></td>
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
</div>
