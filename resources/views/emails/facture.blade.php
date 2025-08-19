<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $facture->numero }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .facture-details {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .facture-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .amount {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>HTS High Tech Systems</h1>
        <p>Gestion des Intérêts de Moratoire</p>
    </div>

    <div class="company-info">
        <h2>Bonjour,</h2>
        <p>Veuillez trouver ci-joint la facture N° <strong>{{ $facture->numero }}</strong> pour votre entreprise <strong>{{ $client->raison_sociale }}</strong>.</p>
    </div>

    <div class="facture-details">
        <div class="facture-number">Facture N° {{ $facture->numero }}</div>
        <p><strong>Date de facturation :</strong> {{ $facture->date_facturation ? $facture->date_facturation->format('d/m/Y') : 'Non définie' }}</p>
        <p><strong>Échéance :</strong> {{ $facture->echeance ? $facture->echeance->format('d/m/Y') : 'Non définie' }}</p>
        <p><strong>Montant HT :</strong> <span class="amount">{{ number_format($facture->montant_ht, 2, ',', ' ') }} DA</span></p>
        <p><strong>Net à payer :</strong> <span class="amount">{{ number_format($facture->net_a_payer ?? 0, 2, ',', ' ') }} DA</span></p>
        
        @if($facture->statut)
            <p><strong>Statut :</strong> {{ ucfirst($facture->statut) }}</p>
        @endif
    </div>

    <div class="company-info">
        <h3>Informations du client :</h3>
        <p><strong>Raison sociale :</strong> {{ $client->raison_sociale }}</p>
        <p><strong>NIF :</strong> {{ $client->nif }}</p>
        <p><strong>RC :</strong> {{ $client->rc }}</p>
        <p><strong>Adresse :</strong> {{ $client->adresse }}</p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement par le système de gestion des intérêts de moratoire.</p>
        <p>Pour toute question, veuillez contacter notre équipe.</p>
        <p><strong>HTS High Tech Systems</strong></p>
    </div>
</body>
</html>
