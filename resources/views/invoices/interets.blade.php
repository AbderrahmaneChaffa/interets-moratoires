<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Facture Intérêts - Relevé {{ $releve->id ?? '' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .header {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>

<body>
    <div class="header">
        <div>
            <h3>Facture - Intérêts moratoires</h3>
            <p>Relevé : {{ $releve->id ?? '' }}</p>
            <p>Date : {{ $date }}</p>
        </div>
        <div>
            <strong>Client</strong>
            <p>{{ $releve->client_name ?? 'N/A' }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Mois</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Jours</th>
                <th class="text-right">Intérêt HT</th>
                <th class="text-right">Intérêt TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($periodes as $p)
                <tr>
                    <td>{{ $p['mois'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($p['date_debut'])->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($p['date_fin'])->format('d/m/Y') }}</td>
                    <td>{{ $p['jours_retard'] }}</td>
                    <td class="text-right">{{ number_format($p['interet_ht'], 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($p['interet_ttc'], 2, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Total</strong></td>
                <td class="text-right"><strong>{{ number_format($totalHT, 2, ',', ' ') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totalTTC, 2, ',', ' ') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <p style="margin-top:30px; font-size:11px">Merci. Ceci est une facture générée automatiquement.</p>
</body>

</html>