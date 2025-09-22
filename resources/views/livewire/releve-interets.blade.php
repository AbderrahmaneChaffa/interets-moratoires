<div>
    <div class="mb-3">
        <button class="btn btn-primary" wire:click="calculer">
            <i class="fas fa-calculator"></i> Calculer les intérêts du relevé
        </button>
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
            <tbody>
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
                                <a href="{{ Storage::url($p['pdf_path']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file-pdf"></i> Voir
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            {{ $p['statut'] ?? 'En attente' }}
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
                                <button class="btn btn-outline-danger" wire:click="supprimerInteret({{ $p['id'] }})" 
                                    onclick="return confirm('Supprimer cet intérêt ?')" title="Supprimer">
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
        </table>
    </div>
</div>


