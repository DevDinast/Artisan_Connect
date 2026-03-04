-- Optimisation des indexes pour ArtisanConnect API
-- JOUR 7 - Performance Optimization

-- Indexes pour les tables principales
CREATE INDEX idx_oeuvres_statut ON oeuvres(statut);
CREATE INDEX idx_oeuvres_categorie_id ON oeuvres(categorie_id);
CREATE INDEX idx_oeuvres_artisan_id ON oeuvres(artisan_id);
CREATE INDEX idx_oeuvres_prix ON oeuvres(prix);
CREATE INDEX idx_oeuvres_created_at ON oeuvres(created_at);
CREATE INDEX idx_oeuvres_statut_prix ON oeuvres(statut, prix);
CREATE INDEX idx_oeuvres_categorie_statut ON oeuvres(categorie_id, statut);

-- Indexes pour la recherche full-text
CREATE FULLTEXT INDEX idx_oeuvres_search ON oeuvres(titre, description, materiaux);

-- Indexes pour les transactions
CREATE INDEX idx_transactions_acheteur_id ON transactions(acheteur_id);
CREATE INDEX idx_transactions_artisan_id ON transactions(artisan_id);
CREATE INDEX idx_transactions_oeuvre_id ON transactions(oeuvre_id);
CREATE INDEX idx_transactions_statut ON transactions(statut);
CREATE INDEX idx_transactions_created_at ON transactions(created_at);

-- Indexes pour les commandes
CREATE INDEX idx_commandes_acheteur_id ON commandes(acheteur_id);
CREATE INDEX idx_commandes_statut ON commandes(statut);
CREATE INDEX idx_commandes_created_at ON commandes(created_at);

-- Indexes pour les paiements
CREATE INDEX idx_paiements_commande_id ON paiements(commande_id);
CREATE INDEX idx_paiements_acheteur_id ON paiements(acheteur_id);
CREATE INDEX idx_paiements_statut ON paiements(statut);
CREATE INDEX idx_paiements_created_at ON paiements(created_at);
CREATE INDEX idx_paiements_reference ON paiements(reference);

-- Indexes pour les avis
CREATE INDEX idx_avis_oeuvre_id ON avis(oeuvre_id);
CREATE INDEX idx_avis_acheteur_id ON avis(acheteur_id);
CREATE INDEX idx_avis_artisan_id ON avis(artisan_id);
CREATE INDEX idx_avis_statut ON avis(statut);
CREATE INDEX idx_avis_created_at ON avis(created_at);
CREATE INDEX idx_avis_note ON avis(note);

-- Indexes pour les notifications
CREATE INDEX idx_notifications_utilisateur_id ON notifications(utilisateur_id);
CREATE INDEX idx_notifications_type ON notifications(type);
CREATE INDEX idx_notifications_lue ON notifications(lue);
CREATE INDEX idx_notifications_created_at ON notifications(created_at);
CREATE INDEX idx_notifications_user_lue ON notifications(utilisateur_id, lue);

-- Indexes pour les favoris
CREATE INDEX idx_favoris_acheteur_id ON favoris(acheteur_id);
CREATE INDEX idx_favoris_oeuvre_id ON favoris(oeuvre_id);
CREATE INDEX idx_favoris_type ON favoris(type);
CREATE INDEX idx_favoris_created_at ON favoris(created_at);
CREATE INDEX idx_favoris_acheteur_type ON favoris(acheteur_id, type);

-- Indexes pour les images
CREATE INDEX idx_images_oeuvre_id ON images(oeuvre_id);
CREATE INDEX idx_images_type ON images(type);
CREATE INDEX idx_images_ordre ON images(ordre);

-- Indexes pour les catégories
CREATE INDEX idx_categories_parent_id ON categories(parent_id);
CREATE INDEX idx_categories_slug ON categories(slug);

-- Indexes pour les utilisateurs
CREATE INDEX idx_utilisateurs_email ON utilisateurs(email);
CREATE INDEX idx_utilisateurs_telephone ON utilisateurs(telephone);
CREATE INDEX idx_utilisateurs_created_at ON utilisateurs(created_at);

-- Indexes composites pour les requêtes fréquentes
CREATE INDEX idx_oeuvres_categorie_statut_prix ON oeuvres(categorie_id, statut, prix);
CREATE INDEX idx_transactions_artisan_statut_date ON transactions(artisan_id, statut, created_at);
CREATE INDEX idx_commandes_acheteur_statut_date ON commandes(acheteur_id, statut, created_at);
CREATE INDEX idx_avis_oeuvre_statut_note ON avis(oeuvre_id, statut, note);

-- Indexes pour les jointures optimisées
CREATE INDEX idx_oeuvres_artisan_categorie ON oeuvres(artisan_id, categorie_id, statut);
CREATE INDEX idx_transactions_commande_oeuvre ON transactions(commande_id, oeuvre_id);
CREATE INDEX idx_favoris_acheteur_oeuvre ON favoris(acheteur_id, oeuvre_id, type);

-- Analyse des performances après création des indexes
ANALYZE TABLE oeuvres;
ANALYZE TABLE transactions;
ANALYZE TABLE commandes;
ANALYZE TABLE paiements;
ANALYZE TABLE avis;
ANALYZE TABLE notifications;
ANALYZE TABLE favoris;
ANALYZE TABLE images;
ANALYZE TABLE categories;
ANALYZE TABLE utilisateurs;

-- Statistiques des indexes
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    CARDINALITY,
    INDEX_TYPE
FROM 
    information_schema.STATISTICS 
WHERE 
    TABLE_SCHEMA = 'ArtisanConnect'
ORDER BY 
    TABLE_NAME, 
    INDEX_NAME;
