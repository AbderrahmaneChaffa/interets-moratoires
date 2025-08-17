# Configuration Microsoft 365 pour l'envoi d'emails

## 🚨 Problème actuel
L'erreur `"SmtpClientAuthentication is disabled for the Tenant"` indique que l'authentification SMTP est désactivée sur votre compte Microsoft 365.

## ✅ Solutions

### Option 1 : Activer l'authentification SMTP (Plus simple)

1. **Connectez-vous à Microsoft 365 Admin Center**
   - Allez sur https://admin.microsoft.com/
   - Connectez-vous avec votre compte administrateur

2. **Naviguez vers Exchange**
   - Dans le menu de gauche, cliquez sur "Exchange"

3. **Allez dans Protection**
   - Cliquez sur "Protection" dans le menu Exchange

4. **Activez l'authentification SMTP**
   - Cherchez "Authentification" ou "SMTP"
   - Activez l'option "Authentification SMTP"
   - Sauvegardez les modifications

### Option 2 : Utiliser un mot de passe d'application

1. **Activez l'authentification à 2 facteurs**
   - Allez sur https://account.microsoft.com/security
   - Activez l'authentification à 2 facteurs

2. **Générez un mot de passe d'application**
   - Dans les paramètres de sécurité
   - Cliquez sur "Mots de passe d'application"
   - Générez un nouveau mot de passe pour "Laravel"

3. **Utilisez ce mot de passe dans votre .env**

### Option 3 : Configuration OAuth2 (Avancée)

Si les options précédentes ne fonctionnent pas :

1. **Créez une application Azure AD**
   - Allez sur https://portal.azure.com/
   - Azure Active Directory > Inscriptions d'applications
   - Créez une nouvelle inscription

2. **Configurez les permissions**
   - Ajoutez les permissions Microsoft Graph
   - Permissions : Mail.Send, SMTP.Send

3. **Générez un client secret**
   - Certificats et secrets > Nouveau secret client
   - Utilisez ce secret comme mot de passe

## 📧 Configuration finale

Une fois l'authentification configurée, utilisez cette configuration dans votre `.env` :

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

## 🧪 Test

Après configuration, testez avec :
```bash
php test_email_outlook.php
```

## 📞 Support

Si vous avez des difficultés :
1. Vérifiez que vous êtes administrateur Microsoft 365
2. Contactez votre administrateur IT
3. Consultez la documentation Microsoft officielle
