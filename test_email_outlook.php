<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use App\Mail\FactureEmail;
use App\Models\Facture;
use App\Models\Client;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test d'envoi d'email avec Microsoft 365 ===\n";

try {
    // Créer des données de test
    $client = new Client();
    $client->raison_sociale = "Client Test";
    $client->nif = "123456789";
    $client->rc = "RC123456";
    $client->adresse = "Adresse de test";
    $client->email = "ab.chaffa31@gmail.com";

    $facture = new Facture();
    $facture->id = 999;
    $facture->numero = "FACT-2024-001";
    $facture->reference = "REF-001";
    $facture->date_facture = "2024-01-15";
    $facture->montant_ht = 1000.00;
    $facture->montant_ttc = 1190.00;
    $facture->statut = "En attente";

    echo "✅ Données de test créées\n";
    echo "📧 Email de destination: ab.chaffa31@gmail.com\n";
    echo "📄 Facture: FACT-2024-001\n";
    echo "📤 Expéditeur: interet.moratoire@hts-hightechsystems.com\n";

    // Envoyer l'email
    echo "\n🔄 Envoi de l'email en cours...\n";
    
    Mail::to("ab.chaffa31@gmail.com")->send(new FactureEmail($facture, $client));
    
    echo "✅ Email envoyé avec succès !\n";
    echo "📧 Vérifiez votre boîte de réception (et les spams)\n";

} catch (\Exception $e) {
    echo "❌ Erreur lors de l'envoi: " . $e->getMessage() . "\n";
    echo "\n🔧 Solutions pour Microsoft 365:\n";
    echo "1. Activez l'authentification SMTP dans Microsoft 365 Admin Center\n";
    echo "2. Ou utilisez l'authentification moderne OAuth2\n";
    echo "3. Vérifiez que le mot de passe d'application est correct\n";
    echo "4. Assurez-vous que l'authentification à 2 facteurs est activée\n";
}
