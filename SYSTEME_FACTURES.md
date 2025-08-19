# Système de Gestion des Factures avec Intérêts Moratoires

## 🎯 Fonctionnalités implémentées

### 1. **Modèle Facture enrichi**
- ✅ Champ `statut` avec valeurs prédéfinies
- ✅ Champ `interets` pour stocker les intérêts moratoires
- ✅ Champ `delai_legal_jours` configurable (défaut: 30 jours)
- ✅ Méthodes de calcul automatique

### 2. **Statuts de facture**
- **"En attente"** : Facture déposée mais pas encore réglée et hors délai de règlement inconnu
- **"Payée"** : Date de règlement existe et ≤ délai légal
- **"Retard de paiement"** : Date de règlement existe mais > délai légal
- **"Impayée"** : Aucune date de règlement et date de dépôt dépasse le délai légal

### 3. **Méthodes du modèle Facture**

#### `calculerStatut()`
- Compare `date_depot` avec `date_reglement` (si présente)
- Calcule automatiquement le statut approprié
- Prend en compte le délai légal configurable

#### `calculerInterets()`
- Calcule les intérêts moratoires si le règlement dépasse le délai légal
- Taux par défaut : 6% annuel + 19% TVA
- Stocke le montant dans le champ `interets`

#### `mettreAJourStatutEtInterets()`
- Met à jour automatiquement le statut et les intérêts
- Sauvegarde les modifications en base

### 4. **Interface utilisateur**

#### Formulaire de création (FactureForm)
- ✅ Select pour choisir le statut manuellement
- ✅ Champ pour configurer le délai légal
- ✅ Option pour calcul automatique du statut
- ✅ Calcul automatique des intérêts lors de la création

#### Liste des factures (FactureList)
- ✅ Colonne "Statut" avec badges colorés
- ✅ Colonne "Intérêts moratoires" avec montants
- ✅ Bouton pour mettre à jour toutes les factures
- ✅ Bouton pour recalculer une facture spécifique

### 5. **Logique de calcul**

#### Calcul du statut
```php
if (!$date_depot) {
    return 'En attente';
}

$date_limite = $date_depot + $delai_legal_jours;

if ($date_reglement) {
    if ($date_reglement <= $date_limite) {
        return 'Payée';
    } else {
        return 'Retard de paiement';
    }
} else {
    if (now() > $date_limite) {
        return 'Impayée';
    } else {
        return 'En attente';
    }
}
```

#### Calcul des intérêts
```php
if ($statut !== 'Retard de paiement' && $statut !== 'Impayée') {
    return 0.00;
}

$jours_retards = $date_reglement ? 
    $date_reglement->diffInDays($date_limite) : 
    now()->diffInDays($date_limite);

$interet_ht = ($montant_ht * 0.06 * $jours_retards) / 360;
$interet_ttc = $interet_ht * 1.19;
```

## 🧪 Tests effectués

### Scénarios testés
1. **Facture en retard de paiement** : Règlement après le délai légal
2. **Facture payée à temps** : Règlement dans les délais
3. **Facture impayée** : Aucun règlement et dépassement du délai

### Résultats
- ✅ Calculs de statut corrects
- ✅ Calculs d'intérêts précis
- ✅ Interface utilisateur fonctionnelle
- ✅ Base de données mise à jour

## 📊 Données de test

Le seeder `FactureSeeder` crée 4 factures de test :
- FACT-2024-001 : Payée à temps
- FACT-2024-002 : Retard de paiement
- FACT-2024-003 : Impayée
- FACT-2024-004 : En attente

## 🔧 Configuration

### Variables configurables
- `delai_legal_jours` : Délai légal en jours (défaut: 30)
- `taux_annuel` : Taux d'intérêt annuel (défaut: 6%)
- `tva` : Taux de TVA (défaut: 19%)

### Migration
```bash
php artisan migrate
```

### Seeder
```bash
php artisan db:seed --class=FactureSeeder
```

## 🎨 Interface utilisateur

### Badges de statut
- 🟢 **Payée** : Badge vert
- 🟡 **Retard de paiement** : Badge jaune
- 🔴 **Impayée** : Badge rouge
- ⚪ **En attente** : Badge gris

### Affichage des intérêts
- Montants > 0 : Affichés en rouge et gras
- Montants = 0 : Affichés en gris

## 🚀 Utilisation

1. **Créer une facture** : Remplir le formulaire avec les dates et montants
2. **Statut automatique** : Cocher l'option pour un calcul automatique
3. **Recalculer** : Utiliser les boutons pour mettre à jour les calculs
4. **Visualiser** : Consulter la liste avec statuts et intérêts

## 📈 Évolutions possibles

- Ajout de taux d'intérêt personnalisés par client
- Historique des modifications de statut
- Notifications automatiques pour les factures en retard
- Export des factures avec intérêts
- Tableau de bord avec statistiques
