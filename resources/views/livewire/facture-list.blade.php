@section('title', 'Liste des factures')

<div class="container mt-4">
    <h2>Liste des factures</h2>
    @if (session()->has('interet'))
        <div class="alert alert-info">
            <strong>Intérêts moratoires pour la facture #{{ session('interet.facture_id') }} :</strong><br>
            Jours de retard : {{ session('interet.jours_retards') }}<br>
            Taux utilisé : {{ session('interet.taux_utilise') }}%<br>
            Intérêt HT : {{ session('interet.interet_ht') }} DA<br>
            Intérêt TTC : {{ session('interet.interet_ttc') }} DA
        </div>
    @endif
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
                <th>Statut paiement</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($factures as $facture)
                <tr>
                    <td>{{ $facture->id }}</td>
                    <td>{{ $facture->client->raison_sociale ?? '-' }}</td>
                    <td>{{ $facture->reference }}</td>
                    <td>{{ $facture->date_facture }}</td>
                    <td>{{ $facture->montant_ht }}</td>
                    <td>{{ $facture->date_depot }}</td>
                    <td>{{ $facture->date_reglement ?? '-' }}</td>
                    <td>{{ $facture->net_a_payer }}</td>
                    <td>{{ $facture->statut_paiement }}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <button wire:click="calculInteret({{ $facture->id }})" class="btn btn-sm btn-info">
                                <i class="fas fa-calculator"></i> Calcul intérêt
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
            @endforeach
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
