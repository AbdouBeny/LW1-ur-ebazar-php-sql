NOM: benyoucef
PRENOM: abderezak

-----------------------------------

Déploiement du projet

Avant toute chose : Déplacez le dossier "annonces" dans le répertoire du serveur web 

1. MÉTHODE AUTOMATIQUE (via le script install.sh)
étapes :
* Ouvrez un terminal à la racine du projet.
* Rendez le script exécutable (si nécessaire) :
   chmod +x install.sh
* Lancer le script :
   ./install.sh

le script peut être lancé :

- En tant que root (préférable) :
  Le script configurera les permissions de manière sécurisée pour l'utilisateur du serveur web (www-data).

- En tant qu'utilisateur normal :
  Le script appliquera un fallback avec des permissions 777 pour faciliter l'installation.

le script va :
- Mettre à jour le fichier src/config/config.php avec vos informations de base de données.
- Initialiser la base de données avec init.sql et data.sql.
- Configurer les permissions sur le dossier uploads.


2. MÉTHODE MANUELLE (si le script ne fonctionne pas)
étapes :

* Modifier le fichier de configuration :
   - Ouvrez src/config/config.php
   - Remplissez vos informations MySQL :
     DB_HOST, DB_NAME, DB_USER, DB_PASS

* Créer la base de données et importer les scripts SQL :
   - Importez le script d'initialisation :
     mysql -u <user> -p<DB_PASS> <DB_NAME> < src/config/sql/init.sql
   - Importez le script des données :
     mysql -u <user> -p<DB_PASS> <DB_NAME> < src/config/sql/data.sql


--------------------------
 
 Accédez à l'application : 
    http://votre-adresse/annonces/site.php
