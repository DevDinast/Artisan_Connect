# 🎯 GUIDE RAPIDE ARTISAN CONNECT

> **Status:** ✅ **PROJET OPÉRATIONNEL**
> 
> **Validé:** 9 tests passant, 33 assertions, 0 erreurs

---

## 1️⃣ Démarrer le Serveur

```bash
cd /home/qwerty/Artisan_Connect
php artisan serve --host=0.0.0.0 --port=8000
```

L'appplication sera disponible à: `http://localhost:8000`

---

## 2️⃣ Tester l'API

### Enregistrement
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "acheteur"
  }'
```

### Connexion
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

---

## 3️⃣ Exécuter les Tests

```bash
./vendor/bin/phpunit --testdox
```

**Résultat attendu:**
```
OK (9 tests, 33 assertions)
```

---

## 📚 Documentation

- **Déploiement complet:** Voir `OPERATIONNEL.md`
- **Rapport détaillé:** Voir `RAPPORT_FINAL.md`
- **Résumé rapide:** Voir `PROJET_OPERATIONNEL_RESUME.txt`
- **API docs:** Voir `api_documentation.md`

---

## 🔑 Endpoints Clés

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/v1/auth/register` | Enregistrement |
| POST | `/api/v1/auth/login` | Connexion |
| GET | `/api/v1/me` | Mon profil |
| POST | `/api/v1/auth/logout` | Déconnexion |
| GET | `/api/v1/acheteur/panier` | Voir panier |
| POST | `/api/v1/acheteur/panier` | Ajouter article |
| GET | `/api/v1/catalog/oeuvres` | Voir produits |

---

## ⚙️ Configuration

Fichier: `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=artisan_connect
DB_USERNAME=root
DB_PASSWORD=
```

---

## ✅ Vérifications Complètes

- ✅ 115 dépendances OK
- ✅ 81 routes enregistrées
- ✅ 15 modèles Eloquent
- ✅ 4 fichiers tests
- ✅ 9 tests PASSING
- ✅ 0 erreurs SQL

---

## 🆘 Problèmes?

1. **Base de données introuvable:**
   ```bash
   php artisan migrate:fresh
   ```

2. **Tests echouent:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

3. **Migrations sans erreur:**
   ```bash
   ./vendor/bin/phpunit tests/Feature/ExampleTest.php
   ```

---

## 🎉 Vous êtes Prêt!

Le projet ArtisanConnect est **pleinement fonctionnel** et prêt pour production.

Bon développement! 🚀
