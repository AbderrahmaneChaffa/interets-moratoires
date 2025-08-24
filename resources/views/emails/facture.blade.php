<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->reference }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #2c3e50; margin-bottom: 15px;">Facture {{ $facture->reference }}</h2>
        
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Client:</strong> {{ $facture->client->raison_sociale }}</p>
            <p style="margin: 5px 0;"><strong>Montant:</strong> {{ $facture->net_a_payer_formatted }}</p>
            <p style="margin: 5px 0;"><strong>Date:</strong> {{ $facture->date_facture->format('d/m/Y') }}</p>
        </div>
        
        <div style="white-space: pre-line; margin: 20px 0; color: #444;">
            {{ $emailMessage }}
        </div>
        
        <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #666; text-align: center;">
            Cet email a été envoyé automatiquement depuis notre système de gestion des factures.<br>
            Merci de ne pas y répondre directement.
        </p>
    </div>
</body>
</html>
