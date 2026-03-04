# ArtisanConnect API Documentation v1.6

## Overview

L'API ArtisanConnect est une API RESTful complète pour la plateforme d'e-commerce d'artisanat africain. Elle permet aux utilisateurs de découvrir, acheter et vendre des œuvres d'artisants locaux.

**Base URL**: `http://127.0.0.1:8000/api`  
**Version**: v1.6  
**Authentication**: Bearer Token (Sanctum)

## Authentication

### Register
```http
POST /auth/register
Content-Type: application/json

{
  "nom": "John",
  "prenom": "Doe", 
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "telephone": "770000000",
  "role": "acheteur|artisan|administrateur",
  "date_naissance": "1990-01-01"
}
```

### Login
```http
POST /auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "token": "1|abcdef123456...",
    "user": {...}
  }
}
```

## Catalogue Public

### Categories
```http
GET /catalog/categories
```

### Œuvres
```http
GET /catalog/oeuvres?page=1&per_page=15&sort_by=recent&categorie_id=1&search=sculpture&prix_min=1000&prix_max=50000
```

**Query Parameters**:
- `page`: Page number (default: 1)
- `per_page`: Items per page (max: 50, default: 15)
- `sort_by`: recent|prix_asc|prix_desc|populaire
- `categorie_id`: Filter by category
- `search`: Full-text search
- `prix_min/prix_max`: Price range
- `region`: Filter by artisan region

### Œuvre Detail
```http
GET /catalog/oeuvres/{id}
```

### Statistics
```http
GET /catalog/stats
```

## Acheteur (Authenticated)

### Panier Management

#### Ajouter au panier
```http
POST /acheteur/panier/ajouter
Authorization: Bearer {token}
Content-Type: application/json

{
  "oeuvre_id": 1,
  "quantite": 1
}
```

#### Voir panier
```http
GET /acheteur/panier
Authorization: Bearer {token}
```

#### Mettre à jour quantité
```http
PUT /acheteur/panier/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "quantite": 2
}
```

#### Supprimer du panier
```http
DELETE /acheteur/panier/{id}
Authorization: Bearer {token}
```

### Commandes

#### Créer commande
```http
POST /acheteur/commandes
Authorization: Bearer {token}
Content-Type: application/json

{
  "adresse_livraison": "123 Rue Test, Abidjan",
  "telephone_livraison": "770000000", 
  "instructions_livraison": "Porte bleue",
  "methode_paiement": "orange_money|mtn_money|moov_money|wave"
}
```

#### Lister commandes
```http
GET /acheteur/commandes
Authorization: Bearer {token}
```

### Paiement Mobile Money

#### Initier paiement
```http
POST /api/acheteur/paiement/initier
Authorization: Bearer {token}
Content-Type: application/json

{
  "commande_id": 1,
  "methode": "orange_money",
  "telephone": "770000000"
}
```

#### Vérifier statut paiement
```http
GET /acheteur/paiement/{id}/statut
Authorization: Bearer {token}
```

#### Historique paiements
```http
GET /acheteur/paiement/historique
Authorization: Bearer {token}
```

### Avis & Notations

#### Créer avis (après achat)
```http
POST /acheteur/avis
Authorization: Bearer {token}
Content-Type: application/json

{
  "oeuvre_id": 1,
  "note": 5,
  "titre_avis": "Excellent travail",
  "commentaire": "Très belle œuvre, je recommande"
}
```

#### Voir mes avis
```http
GET /acheteur/mes-avis
Authorization: Bearer {token}
```

#### Mettre à jour avis
```http
PUT /acheteur/avis/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "note": 4,
  "commentaire": "Mise à jour du commentaire"
}
```

### Favoris

#### Ajouter favori
```http
POST /acheteur/favoris
Authorization: Bearer {token}
Content-Type: application/json

{
  "oeuvre_id": 1
}
```

#### Voir favoris
```http
GET /acheteur/favoris
Authorization: Bearer {token}
```

#### Vérifier si favori
```http
GET /acheteur/favoris/{oeuvreId}/verifier
Authorization: Bearer {token}
```

### Notifications

#### Lister notifications
```http
GET /acheteur/notifications
Authorization: Bearer {token}
```

#### Notifications non lues
```http
GET /acheteur/notifications/non-lues
Authorization: Bearer {token}
```

#### Marquer comme lue
```http
PUT /acheteur/notifications/{id}/lire
Authorization: Bearer {token}
```

## Artisan (Authenticated)

### Dashboard
```http
GET /artisan/dashboard
Authorization: Bearer {token}
```

### Gestion Œuvres

#### Créer œuvre
```http
POST /artisan/oeuvres
Authorization: Bearer {token}
Content-Type: application/json

{
  "categorie_id": 1,
  "titre": "Sculpture sur bois",
  "description": "Description détaillée de l'œuvre",
  "prix": 15000,
  "quantite_disponible": 1,
  "dimensions": {
    "longueur": 30,
    "largeur": 20,
    "hauteur": 15
  },
  "materiaux": ["Bois", "Peinture naturelle"],
  "images": [file1, file2]
}
```

#### Lister mes œuvres
```http
GET /artisan/oeuvres?statut=brouillon&categorie_id=1
Authorization: Bearer {token}
```

#### Mettre à jour œuvre
```http
PUT /artisan/oeuvres/{id}
Authorization: Bearer {token}
```

#### Supprimer œuvre
```http
DELETE /artisan/oeuvres/{id}
Authorization: Bearer {token}
```

#### Soumettre pour validation
```http
POST /artisan/oeuvres/{id}/soumettre
Authorization: Bearer {token}
```

### Gestion Images

#### Upload images
```http
POST /artisan/oeuvres/{id}/images
Authorization: Bearer {token}
Content-Type: multipart/form-data

images: [file1, file2, file3]
```

#### Définir image principale
```http
PUT /artisan/images/{imageId}/principale
Authorization: Bearer {token}
```

## Administrateur (Authenticated)

### Dashboard
```http
GET /admin/dashboard
Authorization: Bearer {token}
```

### Validation Œuvres

#### Œuvres en attente
```http
GET /admin/oeuvres/en-attente
Authorization: Bearer {token}
```

#### Valider œuvre
```http
PUT /admin/oeuvres/{id}/valider
Authorization: Bearer {token}
Content-Type: application/json

{
  "notes_validation": "Œuvre de grande qualité",
  "priorite": "normale"
}
```

#### Refuser œuvre
```http
PUT /admin/oeuvres/{id}/refuser
Authorization: Bearer {token}
Content-Type: application/json

{
  "motif_refus": "Qualité insuffisante",
  "motif_refus_code": "qualite",
  "notes_admin": "Améliorer la finition"
}
```

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Opération réussie",
  "data": {...},
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Erreur de validation",
  "errors": {
    "email": ["L'email est requis"],
    "password": ["Le mot de passe doit faire au moins 8 caractères"]
  }
}
```

## Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Rate Limiting

- **Public endpoints**: 100 requests/minute
- **Authenticated endpoints**: 500 requests/minute
- **Payment endpoints**: 10 requests/minute

## Error Handling

L'API retourne toujours un format JSON cohérent avec des messages d'erreur détaillés en français.

## Testing

Utilisez la collection Postman fournie (`postman_collection.json`) pour tester tous les endpoints.

## Support

Pour toute question technique, contactez l'équipe de développement.
