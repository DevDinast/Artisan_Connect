# ArtisanConnect - Guide d'Opérationnalisation

## État du Projet ✅

Tous les tests passent avec succès:
- **9 tests unitaires et d'intégration** : ✅ OK
- **33 assertions** validées
- **Base de données** : MySQL compatible
- **API REST** : Entièrement fonctionnelle
- **Authentification** : Sanctum token-based ✅

## Avant de Démarrer

### Prérequis  
- PHP 8.3+
- MySQL 8.0+
- Composer
- Node.js (optionnel, pour assets)

### Installation

```bash
# 1. Cloner le projet
git clone <repo-url>
cd Artisan_Connect

# 2. Installer les dépendances
composer install

# 3. Configurer l'environnement
cp .env.production.example .env

# 4. Générer la clé application
php artisan key:generate

# 5. Créer la base de données
mysql -u root -p < database/artisan_connect.sql

# 6. Exécuter les migrations
php artisan migrate --force

# 7. Seeder les données (optional)
php artisan db:seed
```

## Configuration MySQL

Modifier `.env` avec vos paramètres:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=artisan_connect
DB_USERNAME=root
DB_PASSWORD=votre_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre@gmail.com
MAIL_PASSWORD=votre_app_password
```

## Déploiement Production

### 1. Sur un VPS (Ubuntu 22.04)

```bash
# Install PHP & MySQL
sudo apt update
sudo apt install php8.3 php8.3-mysql php8.3-json mysql-server nginx

# Setup Nginx
sudo cp miscellaneous/nginx.conf /etc/nginx/sites-available/artisan-connect
sudo ln -s /etc/nginx/sites-available/artisan-connect /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# Setup Laravel
cd /var/www/artisan-connect
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the queue
php artisan queue:work &
```

### 2. Lancer le serveur

**Mode Développement:**
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**Mode Production (Nginx):**
- Nginx écoute sur le port 80/443
- Les requests sont routées vers le backend PHP-FPM

### 3. Vérifier la Santé du Projet

```bash
# Tests
./vendor/bin/phpunit --testdox

# Routes
php artisan route:list

# Migrations
php artisan migrate:status
```

## Points d'Accès API

### Authentification
- **POST** `/api/v1/auth/register` - Enregistrement
- **POST** `/api/v1/auth/login` - Connexion
- **POST** `/api/v1/auth/logout` - Déconnexion
- **GET** `/api/v1/me` - Profil utilisateur

### Panier & Commandes
- **GET** `/api/v1/acheteur/panier` - Voir le panier
- **POST** `/api/v1/acheteur/panier` - Ajouter article
- **PUT** `/api/v1/acheteur/panier/{id}` - Modifier quantité
- **DELETE** `/api/v1/acheteur/panier/{id}` - Supprimer article
- **POST** `/api/v1/acheteur/commandes` - Créer commande

### Catalogue
- **GET** `/api/v1/catalog/oeuvres` - Listing des œuvres
- **GET** `/api/v1/catalog/oeuvres/{id}` - Détail œuvre
- **GET** `/api/v1/catalog/artisans` - Listing des artisans
- **GET** `/api/v1/catalog/categories` - Catégories

## Modification de Schéma (Guide)

Les migrations are défensively written pour supporter:
- SQLite (tests)
- MySQL (production)

Pour ajouter une colonne:

```php
Schema::table('users', function (Blueprint $table) {
    if (!Schema::hasColumn('users', 'new_column')) {
        $table->string('new_column')->nullable();
    }
});
```

## Troubleshooting

### Erreur: "Base de données non trouvée"
```bash
php artisan migrate:fresh
php artisan db:seed
```

### Erreur: "Unauthorized"
Assurez-vous que le token Sanctum est valide:
```bash
# Dans le header de la requête:
Authorization: Bearer {token}
```

### Erreur: "SQLSTATE[23000]"
Vérifier les contraintes de clés étrangères:
```bash
php artisan migrate:refresh
```

## Produit Final

Le projet contient:
- ✅ **Users Model** consolidé avec polymorphismes (Artisan, Acheteur, Admin)
- ✅ **API complète** (Auth, Panier, Commandes, Catalog, Avis, Favoris)
- ✅ **Service Layer** pour logique métier
- ✅ **Tests** (Auth, Panier, Routes)
- ✅ **Notifications** système
- ✅ **Transactions** Mobile Money intégrées

## Support & Questions

Pour toute question, consultez:
- `api_documentation.md` - Documentation API détaillée
- `RAPPORT_TESTS.md` - Rapport de tests exhaustif
- `CHANGELOG.md` - Historique des modifications
