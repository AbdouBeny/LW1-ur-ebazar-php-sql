#!/bin/bash

echo "--------------"
echo "configuration"
echo "---------------"

if [ ! -f "site.php" ]; then
    echo "ERREUR: Veuillez exécuter ce script depuis la racine du projet"
    exit 1
fi

echo ""
echo "=== CONFIGURATION DE LA BASE DE DONNÉES ==="

#valeurs par défaut
DB_HOST_DEFAULT="localhost"
DB_NAME_DEFAULT="projet"
DB_USER_DEFAULT="projet"
DB_PASS_DEFAULT="tejorp"

read -p "Hôte MySQL [$DB_HOST_DEFAULT]: " DB_HOST
DB_HOST=${DB_HOST:-$DB_HOST_DEFAULT}

read -p "Nom de la base de données [$DB_NAME_DEFAULT]: " DB_NAME
DB_NAME=${DB_NAME:-$DB_NAME_DEFAULT}

read -p "Nom d'utilisateur MySQL [$DB_USER_DEFAULT]: " DB_USER
DB_USER=${DB_USER:-$DB_USER_DEFAULT}

read -s -p "Mot de passe MySQL [$DB_PASS_DEFAULT]: " DB_PASS
DB_PASS=${DB_PASS:-$DB_PASS_DEFAULT}
echo ""

#mettre à jour le fichier config.php 
echo ""
echo "Mise à jour du fichier config.php..."
if [ -f "src/config/config.php" ]; then
    sed -i "s/define('DB_HOST', '.*');/define('DB_HOST', '$DB_HOST');/g" src/config/config.php
    sed -i "s/define('DB_NAME', '.*');/define('DB_NAME', '$DB_NAME');/g" src/config/config.php
    sed -i "s/define('DB_USER', '.*');/define('DB_USER', '$DB_USER');/g" src/config/config.php
    sed -i "s/define('DB_PASS', '.*');/define('DB_PASS', '$DB_PASS');/g" src/config/config.php
    echo "✓ Fichier config.php mis à jour"
else
    echo "ERREUR: Fichier src/config/config.php non trouvé"
    exit 1
fi

# initialisation de la base de données
echo ""
echo "Initialisation de la base de données..."

# vérifier la connexion MySQL avec les identifiants fournis
echo "Vérification de la connexion MySQL..."
if ! mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1" &> /dev/null; then
    echo "ERREUR: Impossible de se connecter à MySQL avec les identifiants fournis"
    echo "Vérifiez que :"
    echo "1. La base de données '$DB_NAME' existe"
    echo "2. L'utilisateur '$DB_USER' a les droits sur cette base"
    exit 1
fi

# exécuter les scripts SQL
echo "Exécution des scripts SQL..."

if [ -f "src/config/sql/init.sql" ]; then
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < src/config/sql/init.sql
    if [ $? -eq 0 ]; then
        echo "Script init.sql exécuté"
    else
        echo "ERREUR: Échec de l'exécution de init.sql"
        exit 1
    fi
else
    echo "ERREUR: Fichier init.sql non trouvé"
    exit 1
fi

if [ -f "src/config/sql/data.sql" ]; then
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < src/config/sql/data.sql
    if [ $? -eq 0 ]; then
        echo "Script data.sql exécuté"
    else
        echo "ERREUR: Échec de l'exécution de data.sql"
        exit 1
    fi
else
    echo "ERREUR: Fichier data.sql non trouvé"
    exit 1
fi

# configurer les permissions
echo ""
echo "Configuration des permissions..."

if [ "$(id -u)" = "0" ]; then
    echo "Script exécuté en root : configuration sécurisée"
    if id "www-data" &>/dev/null; then
        chown -R www-data:www-data uploads
        chmod -R 755 uploads
        echo "Permissions attribuées à www-data"
    else
        echo "Utilisateur www-data introuvable"
        chmod -R 777 uploads
        echo "Permissions 777 appliquées"
    fi
else
    echo "Script exécuté sans privilèges root"
    chmod -R 777 uploads
    echo "Permissions 777 appliquées"
fi


echo "Permissions configurées"

echo ""
echo "------------------------------------"
echo "INSTALLATION TERMINÉE AVEC SUCCÈS!"
echo "--------------------------------------"
echo ""
echo "Résumé de la configuration:"
echo "  Base de données: $DB_NAME"
echo "  Utilisateur BD: $DB_USER"
echo "  Hôte BD: $DB_HOST"
echo ""
echo "Accès à l'application:"
echo "  URL: http://votre-adresse/$(basename $(pwd))/site.php"
echo ""
echo "Identifiants de test:"
echo "  Admin: admin@example.com / admin123"
echo "  Vendeur: vendeur@example.com / vendeur123"
echo ""
