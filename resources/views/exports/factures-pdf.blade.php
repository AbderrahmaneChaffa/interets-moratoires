<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport des factures avec intérêts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
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
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
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
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        .badge-success {
            background-color: #28a745;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Rapport des factures avec intérêts moratoires</div>
        <div class="subtitle">Généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Date facture</th>
                <th>Client</th>
                <th class="text-right">Montant HT</th>
                <th class="text-center">Jours de retard</th>
                <th class="text-right">Intérêt HT</th>
                <th class="text-right">Intérêt TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factures as $facture)
                @php
                    $jours_retards = 0;
                    if ($facture->date_depot && is_null($facture->date_reglement)) {
                        $jours_retards = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($facture->date_depot));
                    }
                    $result = $facture->calculerInteretsMoratoires($jours_retards);
                @endphp
                <tr>
                    <td>{{ $facture->reference }}</td>
                    <td>{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</td>
                    <td>{{ $facture->client->raison_sociale ?? '-' }}</td>
                    <td class="text-right">{{ number_format($facture->montant_ht, 2) }} DA</td>
                    <td class="text-center">
                        @if($jours_retards > 0)
                            <span class="badge badge-warning">{{ $jours_retards }} jours</span>
                        @else
                            <span class="badge badge-success">0 jour</span>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($result['interet_ht'], 2) }} DA</td>
                    <td class="text-right">{{ number_format($result['interet_ttc'], 2) }} DA</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; text-align: center; font-size: 10px; color: #666;">
        Total des factures : {{ $factures->count() }} | 
        Total intérêts HT : {{ number_format($factures->sum(function($f) { 
            $jours = 0;
            if ($f->date_depot && is_null($f->date_reglement)) {
                $jours = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($f->date_depot));
            }
            return $f->calculerInteretsMoratoires($jours)['interet_ht'];
        }), 2) }} DA
    </div>
</body>
</html> 