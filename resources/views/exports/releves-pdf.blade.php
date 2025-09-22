<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Liste des Relevés</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Liste des Relevés</h1>
        <p>HIGH TECH SYSTEMS - Généré le {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Client</th>
                <th>Période</th>
                <th>Date création</th>
                <th class="text-right">Montant total HT</th>
                <th class="text-center">Statut</th>
                <th class="text-center">Factures</th>
                <th>Date dernière facture</th>
            </tr>
        </thead>
        <tbody>
            @foreach($releves as $releve)
            <tr>
                <td><strong>{{ $releve->reference }}</strong>
                    @if($releve->categorie)
                    <br><small>{{ $releve->categorie }}</small>
                    @endif
                </td>
                <td>{{ $releve->client->raison_sociale ?? '-' }}</td>
                <td class="text-center">
                    {{ $releve->date_debut->format('d/m/Y') }}<br>
                    au {{ $releve->date_fin->format('d/m/Y') }}
                </td>
                <td class="text-center">{{ $releve->date_creation->format('d/m/Y') }}</td>
                <td class="text-right">{{ number_format($releve->montant_total_ht, 2, ',', ' ') }} DA</td>
                <td class="text-center">
                    <span class="badge badge-{{ $releve->statut === 'Payé' ? 'success' : ($releve->statut === 'Impayé' ? 'danger' : 'secondary') }}">
                        {{ $releve->statut }}
                    </span>
                </td>
                <td class="text-center">{{ $releve->factures->count() }} facture(s)</td>
                <td class="text-center">
                    @if($releve->date_derniere_facture)
                        {{ $releve->date_derniere_facture->format('d/m/Y') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Document généré automatiquement par le système de gestion des intérêts moratoires</p>
        <p>Total des relevés: {{ $releves->count() }} | Montant total: {{ number_format($releves->sum('montant_total_ht'), 2, ',', ' ') }} DA</p>
    </div>
</body>
</html>

