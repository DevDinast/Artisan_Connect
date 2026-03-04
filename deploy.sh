#!/bin/bash

# Script de déploiement ArtisanConnect - Production
# JOUR 7 - Déploiement Automatisé

set -e  # Arrêter le script en cas d'erreur

# Configuration
PROJECT_NAME="ArtisanConnect"
DEPLOY_PATH="/var/www/artisanconnect"
BACKUP_PATH="/var/backups/artisanconnect"
LOG_FILE="/var/log/artisanconnect/deploy.log"
BRANCH="main"
ENVIRONMENT="production"

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonctions de logging
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1" | tee -a $LOG_FILE
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1" | tee -a $LOG_FILE
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a $LOG_FILE
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a $LOG_FILE
}

# Vérification des prérequis
check_prerequisites() {
    log_info "Vérification des prérequis..."
    
    # Vérifier PHP
    if ! command -v php &> /dev/null; then
        log_error "PHP n'est pas installé"
        exit 1
    fi
    
    # Vérifier Composer
    if ! command -v composer &> /dev/null; then
        log_error "Composer n'est pas installé"
        exit 1
    fi
    
    # Vérifier Node.js (pour les assets)
    if ! command -v node &> /dev/null; then
        log_warning "Node.js n'est pas installé - les assets ne seront pas compilés"
    fi
    
    # Vérifier Git
    if ! command -v git &> /dev/null; then
        log_error "Git n'est pas installé"
        exit 1
    fi
    
    log_success "Prérequis vérifiés"
}

# Sauvegarde de l'application actuelle
backup_application() {
    log_info "Sauvegarde de l'application actuelle..."
    
    if [ -d "$DEPLOY_PATH" ]; then
        BACKUP_NAME="backup_$(date +%Y%m%d_%H%M%S)"
        mkdir -p "$BACKUP_PATH"
        cp -r "$DEPLOY_PATH" "$BACKUP_PATH/$BACKUP_NAME"
        log_success "Sauvegarde créée: $BACKUP_NAME"
    else
        log_warning "Aucune application existante à sauvegarder"
    fi
}

# Mise à jour du code
update_code() {
    log_info "Mise à jour du code depuis la branche $BRANCH..."
    
    if [ -d "$DEPLOY_PATH" ]; then
        cd "$DEPLOY_PATH"
        git fetch origin
        git reset --hard origin/$BRANCH
        git clean -fd
    else
        git clone https://github.com/DevDinast/Artisan_Connect.git "$DEPLOY_PATH"
        cd "$DEPLOY_PATH"
        git checkout $BRANCH
    fi
    
    log_success "Code mis à jour"
}

# Installation des dépendances
install_dependencies() {
    log_info "Installation des dépendances PHP..."
    
    cd "$DEPLOY_PATH"
    composer install --no-dev --optimize-autoloader --no-interaction
    
    log_success "Dépendances PHP installées"
    
    # Installation des dépendances Node.js si disponible
    if command -v node &> /dev/null && [ -f "package.json" ]; then
        log_info "Installation des dépendances Node.js..."
        npm install --production
        npm run build
        log_success "Dépendances Node.js installées et assets compilés"
    fi
}

# Configuration de l'environnement
configure_environment() {
    log_info "Configuration de l'environnement..."
    
    cd "$DEPLOY_PATH"
    
    # Copier le fichier d'environnement
    if [ ! -f ".env" ]; then
        if [ -f "env.production.example" ]; then
            cp env.production.example .env
            log_warning "Fichier .env créé à partir de env.production.example - Veuillez le configurer"
        else
            cp .env.example .env
            log_warning "Fichier .env créé à partir de .env.example - Veuillez le configurer"
        fi
    fi
    
    # Générer la clé d'application
    php artisan key:generate --force
    
    # Optimiser le chargement
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    log_success "Environnement configuré"
}

# Migration de la base de données
migrate_database() {
    log_info "Migration de la base de données..."
    
    cd "$DEPLOY_PATH"
    php artisan migrate --force --seed
    
    # Appliquer les optimisations d'indexes
    if [ -f "database/optimisation_indexes.sql" ]; then
        log_info "Application des optimisations d'indexes..."
        mysql artisan_connect_prod < database/optimisation_indexes.sql
        log_success "Indexes optimisés"
    fi
    
    log_success "Base de données migrée"
}

# Nettoyage du cache
clear_cache() {
    log_info "Nettoyage du cache..."
    
    cd "$DEPLOY_PATH"
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    log_success "Cache nettoyé"
}

# Optimisation de l'application
optimize_application() {
    log_info "Optimisation de l'application..."
    
    cd "$DEPLOY_PATH"
    
    # Optimiser Composer
    composer dump-autoload --optimize --classmap-authoritative
    
    # Optimiser les fichiers
    php artisan optimize
    
    # Préchauffer le cache
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    log_success "Application optimisée"
}

# Vérification de la santé
health_check() {
    log_info "Vérification de la santé de l'application..."
    
    cd "$DEPLOY_PATH"
    
    # Vérifier que l'application répond
    if php artisan route:list --quiet > /dev/null 2>&1; then
        log_success "Application en bonne santé"
    else
        log_error "Problème de santé détecté"
        return 1
    fi
    
    # Vérifier les permissions
    chown -R www-data:www-data "$DEPLOY_PATH"
    chmod -R 755 "$DEPLOY_PATH"
    chmod -R 777 "$DEPLOY_PATH/storage"
    
    log_success "Permissions configurées"
}

# Redémarrage des services
restart_services() {
    log_info "Redémarrage des services..."
    
    # Redémarrer PHP-FPM
    systemctl restart php8.2-fpm
    
    # Redémarrer Nginx
    systemctl restart nginx
    
    # Redémarrer Redis
    systemctl restart redis-server
    
    # Redémarrer le worker de queue
    systemctl restart artisanconnect-queue
    
    log_success "Services redémarrés"
}

# Nettoyage des anciennes sauvegardes
cleanup_backups() {
    log_info "Nettoyage des anciennes sauvegardes..."
    
    # Supprimer les sauvegardes de plus de 30 jours
    find "$BACKUP_PATH" -type d -name "backup_*" -mtime +30 -exec rm -rf {} \; 2>/dev/null || true
    
    log_success "Anciennes sauvegardes nettoyées"
}

# Notification de déploiement
notify_deployment() {
    log_info "Envoi de la notification de déploiement..."
    
    # Envoyer un email (optionnel)
    # echo "Déploiement de $PROJECT_NAME terminé avec succès" | mail -s "Déploiement $PROJECT_NAME" admin@artisanconnect.ci
    
    # Envoyer une notification Slack (optionnel)
    # curl -X POST -H 'Content-type: application/json' \
    #     --data '{"text":"✅ Déploiement de '$PROJECT_NAME' terminé avec succès"}' \
    #     YOUR_SLACK_WEBHOOK_URL
    
    log_success "Notification envoyée"
}

# Fonction principale de déploiement
deploy() {
    log_info "Début du déploiement de $PROJECT_NAME..."
    log_info "Branche: $BRANCH"
    log_info "Environnement: $ENVIRONMENT"
    
    # Créer les répertoires nécessaires
    mkdir -p "$DEPLOY_PATH"
    mkdir -p "$BACKUP_PATH"
    mkdir -p "$(dirname "$LOG_FILE")"
    
    # Exécuter les étapes de déploiement
    check_prerequisites
    backup_application
    update_code
    install_dependencies
    configure_environment
    migrate_database
    clear_cache
    optimize_application
    health_check
    restart_services
    cleanup_backups
    notify_deployment
    
    log_success "Déploiement terminé avec succès!"
    log_info "L'application est maintenant disponible sur: https://api.artisanconnect.ci"
}

# Rollback en cas d'erreur
rollback() {
    log_error "Rollback en cours..."
    
    if [ -d "$BACKUP_PATH" ]; then
        LATEST_BACKUP=$(ls -t "$BACKUP_PATH" | head -n 1)
        if [ -n "$LATEST_BACKUP" ]; then
            rm -rf "$DEPLOY_PATH"
            cp -r "$BACKUP_PATH/$LATEST_BACKUP" "$DEPLOY_PATH"
            restart_services
            log_success "Rollback effectué avec la sauvegarde: $LATEST_BACKUP"
        else
            log_error "Aucune sauvegarde disponible pour le rollback"
            exit 1
        fi
    else
        log_error "Aucun répertoire de sauvegarde trouvé"
        exit 1
    fi
}

# Gestion des erreurs
trap 'log_error "Une erreur est survenue pendant le déploiement"; rollback; exit 1' ERR

# Point d'entrée
case "${1:-deploy}" in
    deploy)
        deploy
        ;;
    rollback)
        rollback
        ;;
    health)
        health_check
        ;;
    *)
        echo "Usage: $0 {deploy|rollback|health}"
        exit 1
        ;;
esac
