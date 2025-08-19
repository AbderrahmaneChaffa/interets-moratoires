<div class="container mt-4">
    <h3>Récapitulatif Intérêts moratoires - {{ $client->raison_sociale }}</h3>

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date Facture</th>
                    <th>Référence</th>
                    <th>Prestation</th>
                    <th>Net à payer</th>
                    <th>Date Dépôt</th>
                    <th>Date Facturation Intérêt</th>
                    <th>Délai (Jrs)</th>
                    <th>Jours Retards</th>
                    <th>Intérêts HT</th>
                    <th>Intérêts TTC</th>
                </tr>
            </thead>
            <tbody>
                @forelse($factures as $facture)
                    <tr>
                        <td>{{ optional($facture->date_facture)->format('d-m-Y') }}</td>
                        <td>{{ $facture->reference }}</td>
                        <td>{{ $facture->prestation ?? '-' }}</td>
                        <td>DA {{ number_format($facture->net_a_payer ?? 0, 2, ',', ' ') }}</td>
                        <td>{{ optional($facture->date_depot)->format('d-m-Y') }}</td>
                        <td>{{ optional($facture->date_reglement ?? $facture->date_depot)->format('d-m-Y') }}</td>
                        <td>{{ $facture->delai_legal_jours ?? 30 }}</td>
                        <td>{{ $facture->jours_retards ?? 0 }}</td>
                        <td>DA {{ number_format($facture->interets_ht ?? 0, 2, ',', ' ') }}</td>
                        <td>DA {{ number_format($facture->interets_ttc ?? 0, 2, ',', ' ') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">Aucune facture</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @php
        $totalImpayes = $totaux['impayes'] ?? 0;
        $totalInterets = $totaux['interets_ttc'] ?? 0;
        $totalAPayer = $totalImpayes + $totalInterets;
    @endphp

    <div class="mt-3">
        <p><strong>Montant factures impayées :</strong> DA {{ number_format($totalImpayes, 2, ',', ' ') }}</p>
        <p><strong>Montant intérêts moratoires :</strong> DA {{ number_format($totalInterets, 2, ',', ' ') }}</p>
        <p><strong>Total à payer par {{ $client->raison_sociale }} :</strong> DA {{ number_format($totalAPayer, 2, ',', ' ') }}</p>
    </div>
</div>
