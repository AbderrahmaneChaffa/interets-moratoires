# Configuration de l'envoi d'emails

## ⚠️ Erreur d'authentification SMTP résolue

L'erreur "SmtpClientAuthentication is disabled for the Tenant" indique que l'authentification SMTP est désactivée sur votre compte Microsoft/Outlook. Voici les solutions :

## Solution 1 : Activer l'authentification SMTP sur Microsoft 365 (Recommandée)

### Configuration Microsoft 365

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME=interet.moratoire@hts-hightechsystems.com
MAIL_PASSWORD=votre_mot_de_passe_application
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=interet.moratoire@hts-hightechsystems.com
MAIL_FROM_NAME="HTS High Tech Systems"
```

### Étapes pour activer l'authentification SMTP :

1. **Connectez-vous à Microsoft 365 Admin Center** : https://admin.microsoft.com/
2. **Allez dans Exchange > Protection**
3. **Cliquez sur "Authentification"**
4. **Activez "Authentification SMTP"** pour votre domaine
5. **Ou utilisez l'authentification moderne OAuth2** (voir solution 2)

### Alternative : Mot de passe d'application

Si l'authentification SMTP est désactivée, utilisez un mot de passe d'application :

1. **Allez sur https://account.microsoft.com/security**
2. **Activez l'authentification à 2 facteurs**
3. **Générez un mot de passe d'application**
4. **Utilisez ce mot de passe dans `MAIL_PASSWORD`**

## Solution 2 : Utiliser l'authentification moderne OAuth2

Si l'authentification SMTP traditionnelle ne fonctionne pas, utilisez OAuth2 :

### Configuration OAuth2 pour Microsoft 365

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME=interet.moratoire@hts-hightechsystems.com
MAIL_PASSWORD=votre_mot_de_passe_application
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=interet.moratoire@hts-hightechsystems.com
MAIL_FROM_NAME="HTS High Tech Systems"
```

### Étapes pour OAuth2 :

1. **Créez une application dans Azure AD**
2. **Configurez les permissions pour Microsoft Graph**
3. **Générez un client secret**
4. **Utilisez le client secret comme mot de passe**

## Solution 3 : Utiliser un service d'email tiers

### Mailgun (Gratuit jusqu'à 5,000 emails/mois)

```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=votre-domaine.mailgun.org
MAILGUN_SECRET=votre-clé-secrète
MAIL_FROM_ADDRESS=interet.moratoire@hts-hightechsystems.com
MAIL_FROM_NAME="HTS High Tech Systems"
```

### SendGrid (Gratuit jusqu'à 100 emails/jour)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=votre-clé-api-sendgrid
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=interet.moratoire@hts-hightechsystems.com
MAIL_FROM_NAME="HTS High Tech Systems"
```

## Test de la configuration

Après avoir configuré les paramètres, vous pouvez tester l'envoi d'emails en :

1. Ajoutant une adresse email à un client
2. Uploadant un PDF de facture
3. Cliquant sur le bouton "Envoyer" dans la liste des factures

## Fonctionnalités implémentées

- ✅ Classe Mailable `FactureEmail` créée
- ✅ Template d'email professionnel
- ✅ Contrôleur `EmailController` avec méthode d'envoi
- ✅ Route pour l'envoi d'emails
- ✅ Bouton d'envoi dans le tableau des factures
- ✅ Gestion des erreurs et validation
- ✅ Interface utilisateur avec indicateurs de statut

## Structure des emails

Les emails envoyés contiennent :
- En-tête avec le logo de l'entreprise
- Détails de la facture (numéro, montants, dates)
- Informations du client
- Pièce jointe PDF de la facture
- Pied de page avec les informations de contact

## Sécurité

- Vérification de l'existence du PDF avant envoi
- Validation de l'adresse email du client
- Protection CSRF sur les routes
- Gestion des erreurs avec messages appropriés
