# Déploiement Netlify + Render

ArtisanConnect conserve Laravel comme backend/API sur Render. Le build Netlify compile les vues Blade en HTML statique ; Netlify ne lance donc pas de serveur PHP pour les visiteurs.

## Configuration Netlify

1. Connecter le dépôt à Netlify avec la racine du dépôt comme base.
2. Utiliser `npm run build:netlify` comme commande de build et `netlify-dist` comme répertoire de publication (déjà définis dans `netlify.toml`).
3. Définir la variable de build `ARTISAN_API_BASE_URL` sur l’origine HTTPS du service Laravel Render, sans `/api` ni chemin final, par exemple `https://artisanconnect-api.onrender.com`.

Le build nécessite PHP et Composer pour compiler les vues Blade. Le site publié ne contient que les pages HTML, le CSS, le JavaScript et les images statiques.

## Configuration Render

Ajouter l’origine Netlify à `CORS_ALLOWED_ORIGINS` dans la configuration Render, en conservant les origines déjà autorisées et en les séparant par des virgules, par exemple `https://nom-du-site.netlify.app,https://artisanconnect.ci`. Cette configuration active le CORS de l’API sans autoriser les cookies inter-origines ; l’interface utilise les tokens Bearer de Sanctum.

Les liens de vérification d’adresse e-mail doivent continuer à pointer vers l’application Laravel sur Render : cette route vérifie une signature côté serveur et n’est pas une page statique Netlify.

## Pages avec identifiant dynamique

Netlify réécrit les chemins des œuvres, des profils d’artisan et de la modification d’œuvre vers des modèles HTML statiques. Le JavaScript de ces pages utilise l’identifiant présent dans l’URL pour appeler l’API Render.
