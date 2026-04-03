# Plateforme de petites annonces

Application web de vente entre particuliers (type *Le Bon Coin*).  
Développée en **PHP natif**, architecture **MVCR**, pattern **Strategy** pour le stockage, et forte attention à la **sécurité** (CSRF, validation, hashage).

---

## Fonctionnalités principales

### Visiteur anonyme
- Consultation des annonces par catégorie  
- Inscription / connexion  

### Utilisateur connecté
- Dépôt d’annonces (titre, description, prix, catégorie, modes de livraison, **jusqu’à 5 photos JPEG**)  
- Suppression de ses propres annonces (si non vendues)  
- Achat d’un bien (choix du mode de livraison, marquage comme vendu)  
- Confirmation de réception (supprime définitivement l’annonce)  
- Interface personnelle :  
  - annonces en vente  
  - ventes réalisées  
  - achats effectués  

### Administrateur (compte unique pré‑existant)
- Suppression d’utilisateurs et de leurs annonces  
- Gestion des catégories : ajout, renommage, suppression (uniquement si vide)

---

## Stack technique

- **Backend** : PHP 7.4+ (POO, sessions, PDO)  
- **Base de données** : MySQL / MariaDB  
- **Frontend** : HTML5, CSS3 (responsive, Flexbox/Grid), JavaScript (galerie d’images)  
- **Architecture** : MVCR + Strategy (Session / SQL interchangeables)  
- **Sécurité** :  
  - CSRF  
  - Validation des données  
  - Hashage bcrypt  
  - Requêtes préparées PDO  
  - Validation MIME des images  

---

## Installation rapide

### Prérequis
- Serveur web (Apache / Nginx)  
- PHP 7.4+  
- MySQL / MariaDB  
- Extensions : `pdo_mysql`, `gd`, `fileinfo`, `session`

---

### Étapes

#### 1. Cloner le dépôt
```bash
git clone https://github.com/votre-utilisateur/plateforme-annonces.git
cd plateforme-annonces
```

#### 2. Créer la base de données

Importer les fichiers SQL :

```bash
mysql -u root -p < src/config/sql/init.sql
mysql -u root -p < src/config/sql/data.sql
```

#### 3. Configurer l’accès à la base

Modifier `src/config/config.php` :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nom_base');
define('DB_USER', 'utilisateur');
define('DB_PASS', 'mot_de_passe');
```

#### 4. Configurer le serveur web

- Pointer la racine web vers le dossier contenant site.php.
- Activer la réécriture d’URL (optionnel, fonctionne aussi avec ?action=...).

#### 5. Permissions

Le dossier suivant doit être accessible en écriture :
```bash
uploads/annonces/
```

#### 6. Lancer l’application

Accéder à :
```bash
http://localhost/site.php
```
Compte administrateur par défaut :
admin@example.com / admin123

### Structure du projet
```bash 
.
├── site.php                 # Point d’entrée
├── install.sh               # Script d’installation (optionnel)
├── src/
│   ├── config/              # Configuration BDD + SQL
│   ├── control/             # Contrôleurs (Controller, UserController, AdminController)
│   ├── model/               # Entités, Builders, Interfaces + implémentations SQL/Session
│   ├── view/                # Vues (View, UserView, AdminView)
│   ├── css/                 # Styles responsive
│   ├── Router.php           # Routeur principal
│   └── TokenCSRF.php        # Gestion des tokens CSRF
└── uploads/annonces/        # Photos téléchargées
```

### Choix d’architecture

#### MVCR 

Séparation claire des responsabilités :

- Routeur : analyse l’URL

- Contrôleur : logique métier

- Vue : génération du HTML

#### Pattern Strategy

Interfaces : AnnonceStorage, UserStorage, etc.
Implémentations :

- StorageSql (MySQL)

- StorageSession (tests)

Un simple changement dans *site.php* permet de basculer de l’une à l’autre.

#### Builders

Validation centralisée des formulaires :

- AnnonceBuilder

- UserBuilder

- CategoryBuilder

#### Sécurité

- Token CSRF pour toutes les requêtes POST

- Validation stricte des uploads (MIME, taille, nombre)

- Mots de passe hashés (bcrypt)

- Requêtes préparées PDO

- Vérification des rôles (user/admin) pour les actions sensibles


### Auteur
Beny A.