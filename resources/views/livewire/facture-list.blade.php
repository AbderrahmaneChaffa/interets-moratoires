
@section('title', 'Liste des factures')
@section('content')
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
            @foreach($factures as $facture)
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
                        <button wire:click="calculInteret({{ $facture->id }})" class="btn btn-sm btn-info">Calcul intérêt</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $factures->links() }}
</div>
@endsection
