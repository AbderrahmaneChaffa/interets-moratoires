# Améliorations de la gestion des factures et intérêts moratoires

## Vue d'ensemble

Ce document décrit les améliorations apportées au système de gestion des factures et des intérêts moratoires selon les spécifications demandées.

## Nouvelles fonctionnalités

### 1. Table `interets` (nouvelle structure)

La table `interets` a été recréée avec la structure suivante :

```sql
CREATE TABLE interets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    facture_id BIGINT NOT NULL,
    date_debut_periode DATE NOT NULL,
    date_fin_periode DATE NOT NULL,
    jours_retard INT NOT NULL,
    interet_ht DECIMAL(15,2) NOT NULL,
    interet_ttc DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (facture_id) REFERENCES factures(id) ON DELETE CASCADE,
    UNIQUE KEY unique_facture_periode (facture_id, date_debut_periode, date_fin_periode)
);
```

**Contraintes :**
- La paire `(facture_id, date_debut_periode, date_fin_periode)` est unique
- Suppression en cascade des intérêts lors de la suppression d'une facture

### 2. Suppression des colonnes inutiles

Les colonnes suivantes ont été supprimées de la table `factures` :
- `interets`
- `interets_ht`
- `interets_ttc`

### 3. Règles de calcul des intérêts

#### Délai de grâce
- Chaque facture a un délai de grâce (par défaut 30 jours) après `date_depot`
- Après ce délai, commence le calcul des intérêts moratoires

#### Périodes de calcul
- Chaque période correspond à un mois de retard
- `date_debut_periode = date_depot + délai + (n mois)`
- `date_fin_periode = date_debut_periode + 1 mois`

#### Formules de calcul
Le calcul suit la formule du client stockée en BDD :
- **Formule par jours** : `(Montant × Jours × Taux) / 360`
- **Formule par mois** : `(Montant × Taux × 1 mois)`
- **Formule par défaut** : `(Montant × Jours × Taux) / 360`

### 4. Relations Eloquent

```php
// Facture.php
public function interets()
{
    return $this->hasMany(Interet::class);
}

// Interet.php
public function facture()
{
    return $this->belongsTo(Facture::class);
}
```

### 5. Interface utilisateur améliorée

#### Liste des factures (DataTable)
- **Filtres avancés** : client, statut, date, montant
- **Colonnes** : Date Facture, Référence, Client, Montant HT, Montant TTC, Date Dépôt, Date Règlement, Statut, Intérêts, Actions
- **Pagination** : 15 factures par page
- **Formatage** : Tous les montants affichés en DA avec formatage correct

#### Actions disponibles
- **Détails** : Modal avec toutes les infos de la facture et les intérêts calculés
- **Modifier** : Modal pour mettre à jour date_règlement et statut
- **Supprimer** : Modal de confirmation avant suppression
- **Calculer intérêts** : Calcul automatique de tous les intérêts pour une facture
- **Gérer intérêts** : Page dédiée à la gestion fine des intérêts par période

### 6. Service InteretService

Le service centralise toute la logique de calcul :

```php
// Méthodes principales
InteretService::calculerJoursRetard(Facture $facture)
InteretService::calculerMoisRetard(Facture $facture)
InteretService::genererPeriodesInterets(Facture $facture)
InteretService::calculerEtSauvegarderInterets(Facture $facture, Carbon $dateDebut, Carbon $dateFin)
InteretService::calculerEtSauvegarderTousInterets(Facture $facture)
InteretService::getInteretsCalcules(Facture $facture)
```

### 7. Composant Livewire GestionInterets

Interface dédiée pour :
- Visualiser les périodes d'intérêts calculables
- Calculer les intérêts par période individuelle
- Supprimer des intérêts existants
- Voir les totaux des intérêts

## Optimisations

### 1. Formatage des montants
- Tous les montants affichés en DA avec formatage correct
- Utilisation d'accesseurs Eloquent pour le formatage

### 2. Logique centralisée
- Service `InteretService` pour tous les calculs
- Modèles avec méthodes utilitaires

### 3. Interface dynamique
- Livewire pour actions dynamiques (modals, calcul, sauvegarde)
- Validation en temps réel
- Messages de feedback utilisateur

### 4. Performance
- Requêtes optimisées avec eager loading
- Pagination pour les grandes listes
- Index sur les clés étrangères

## Utilisation

### 1. Accès à la liste des factures
```
GET /factures
```

### 2. Gestion des intérêts d'une facture
```
GET /factures/{facture}/interets
```

### 3. Calcul automatique des intérêts
- Cliquer sur "Calculer intérêts" dans la liste des factures
- Ou utiliser le bouton "Calculer tous les intérêts" dans la page de gestion

### 4. Calcul par période
- Dans la page de gestion des intérêts
- Cliquer sur le bouton calculer pour chaque période

## Migration des données

Les migrations suivantes ont été créées :
1. `2025_08_19_214919_create_interets_table_new.php` - Nouvelle table interets
2. `2025_08_19_215023_remove_interet_columns_from_factures_table.php` - Suppression des anciennes colonnes

## Tests recommandés

1. **Créer une facture** avec date_depot et vérifier le calcul des périodes
2. **Tester les filtres** de la liste des factures
3. **Calculer des intérêts** pour une facture en retard
4. **Vérifier l'unicité** des périodes d'intérêts
5. **Tester la suppression** d'intérêts existants

## Notes importantes

- Les intérêts sont calculés uniquement pour les factures en retard
- Un seul intérêt peut être enregistré par facture et par période
- La suppression d'une facture supprime automatiquement tous ses intérêts
- Les montants sont formatés en DA avec séparateurs de milliers
