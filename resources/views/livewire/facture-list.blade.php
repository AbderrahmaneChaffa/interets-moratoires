@section('title', 'Liste des factures')

<div class="container mt-4">
    <h2>Liste des factures</h2>
    @if (session()->has('interet'))
        <div class="alert alert-info">
            <strong>{{ session('interet.message') }}</strong>
        </div>
    @endif
    
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
    
    <div class="mb-3">
        <button wire:click="mettreAJourToutesFactures" class="btn btn-warning">
            <i class="fas fa-sync-alt"></i> Mettre à jour toutes les factures
        </button>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Référence</th>
                <th>Date facture</th>
                <th>Montant HT</th>
                <th>Date dépôt</th>
                <th>Date règlement</th>
                <th>Net à payer</th>
                <th>Statut</th>
                <th>Intérêts moratoires</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($factures as $facture)
                <tr>
                    <td>{{ $facture->id }}</td>
                    <td>{{ $facture->client->raison_sociale ?? '-' }}</td>
                    <td>{{ $facture->reference }}</td>
                    <td>{{ $facture->date_facture }}</td>
                    <td>{{ $facture->montant_ht }}</td>
                    <td>{{ $facture->date_depot }}</td>
                    <td>{{ $facture->date_reglement ?? '-' }}</td>
                    <td>{{ $facture->net_a_payer }}</td>
                    <td>
                        <span class="badge 
                            @if($facture->statut === 'Payée') bg-success
                            @elseif($facture->statut === 'Retard de paiement') bg-warning
                            @elseif($facture->statut === 'Impayée') bg-danger
                            @else bg-secondary
                            @endif">
                            {{ $facture->statut }}
                        </span>
                    </td>
                    <td>
                        @if($facture->interets > 0)
                            <span class="text-danger fw-bold">{{ number_format($facture->interets, 2) }} DA</span>
                        @else
                            <span class="text-muted">0,00 DA</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button wire:click="toggle({{ $facture->id }})" class="btn btn-sm btn-outline-secondary">@if($expandedId === $facture->id) Masquer @else Détails @endif</button>
                            <button wire:click="openEdit({{ $facture->id }})" class="btn btn-sm btn-warning">Modifier</button>
                            <button wire:click="deleteParent({{ $facture->id }})" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette facture et ses sous-factures ?')">Supprimer</button>
                            <button wire:click="calculInteret({{ $facture->id }})" class="btn btn-sm btn-info">
                                <i class="fas fa-calculator"></i> Recalcul
                            </button>
                            
                            @if ($facture->pdf_path)
                                <a href="{{ asset('storage/' . $facture->pdf_path) }}" target="_blank"
                                    class="btn btn-sm btn-secondary">
                                    <i class="fas fa-file-pdf"></i> Voir PDF
                                </a>
                                
                                @if ($facture->client->email)
                                    <button onclick="sendFactureEmail({{ $facture->id }})" class="btn btn-sm btn-success">
                                        <i class="fas fa-envelope"></i> Envoyer
                                    </button>
                                @else
                                    <span class="btn btn-sm btn-warning" title="Client sans email">
                                        <i class="fas fa-exclamation-triangle"></i> Pas d'email
                                    </span>
                                @endif
                            @else
                                <form action="{{ route('factures.upload_pdf', $facture->id) }}" method="POST"
                                    enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                                    @csrf
                                    <input type="file" name="facture_pdf" accept="application/pdf" required
                                        class="form-control form-control-sm">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-upload"></i> Uploader
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @if($expandedId === $facture->id)
                    <tr>
                        <td colspan="11">
                            <div class="p-3 bg-light border rounded">
                                <h6 class="mb-2">Sous-factures d'intérêts</h6>
                                @if($facture->sousFactures->isEmpty())
                                    <div class="text-muted">Aucune sous-facture</div>
                                @else
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Référence</th>
                                                <th>Date</th>
                                                <th>Intérêts HT</th>
                                                <th>Intérêts TTC</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($facture->sousFactures as $sf)
                                                <tr>
                                                    <td>{{ $sf->reference }}</td>
                                                    <td>{{ optional($sf->date_facture)->format('d-m-Y') }}</td>
                                                    <td>DA {{ number_format($sf->interets_ht ?? 0, 2, ',', ' ') }}</td>
                                                    <td>DA {{ number_format($sf->interets_ttc ?? 0, 2, ',', ' ') }}</td>
                                                    <td>{{ $sf->statut }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="11" class="text-center">Aucune facture trouvée</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $factures->links() }}
</div>

<script>
function sendFactureEmail(factureId) {
    if (confirm('Êtes-vous sûr de vouloir envoyer cette facture par email ?')) {
        // Afficher un indicateur de chargement
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
        button.disabled = true;
        
        fetch(`/factures/${factureId}/send-email`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('❌ Erreur lors de l\'envoi de l\'email');
        })
        .finally(() => {
            // Restaurer le bouton
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}
</script>
