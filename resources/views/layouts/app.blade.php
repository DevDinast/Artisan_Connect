<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/app.css">
    <title>@yield('title', 'ArtisanConnect')</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .nav-panier {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .panier-badge {
            position: absolute;
            top: -8px;
            right: -10px;
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            display: none;
        }
        .nav-user {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255,255,255,0.15);
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            font-size: 0.88rem;
        }
        .nav-user-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: white;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.7rem;
        }
        .btn-logout {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 0.3rem 0.75rem;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.2s;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.25); }
    </style>
</head>
<body>

    <header>
        <nav class="navbar">
            <a href="{{ url('/') }}" class="logo">✦ ArtisanConnect</a>

            <ul class="nav-links" id="nav-links">

                {{-- Liens publics --}}
                <li><a href="{{ route('catalogue.categories') }}">Catalogue</a></li>

                {{-- Liens visiteur (non connecté) --}}
                <li id="nav-register"><a href="{{ route('auth.register') }}">S'inscrire</a></li>
                <li id="nav-login"><a href="{{ route('auth.login') }}">Connexion</a></li>

                {{-- Liens acheteur --}}
                <li id="nav-panier" style="display:none">
                    <a href="/panier" class="nav-panier">
                        🛒 Panier
                        <span class="panier-badge" id="panier-badge">0</span>
                    </a>
                </li>
                <li id="nav-commandes" style="display:none">
                    <a href="/commandes">Mes commandes</a>
                </li>

                {{-- Lien dashboard (tous rôles connectés) --}}
                <li id="nav-dashboard" style="display:none">
                    <a href="#" id="nav-dashboard-link">Mon espace</a>
                </li>

                {{-- User connecté --}}
                <li id="nav-user" style="display:none">
                    <span class="nav-user">
                        <div class="nav-user-avatar" id="nav-avatar">?</div>
                        <span id="nav-username" style="font-size:0.88rem;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></span>
                    </span>
                </li>

                {{-- Déconnexion --}}
                <li id="nav-logout" style="display:none">
                    <button class="btn-logout" onclick="logout()">Déconnexion</button>
                </li>

            </ul>
        </nav>
    </header>

    <main class="container">
        @yield('content')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} ArtisanConnect. Tous droits réservés.</p>
    </footer>

    <script>
    var token = localStorage.getItem('token');

    // ── Initialiser la navbar selon l'état de connexion ───────────────────────
    async function initNavbar() {
        if (!token) {
            // Visiteur non connecté — afficher inscription/connexion
            return;
        }

        try {
            const res  = await fetch('/api/v1/me', {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
                credentials: 'include',
            });

            if (!res.ok) {
                // Token invalide — nettoyer
                localStorage.removeItem('token');
                return;
            }

            const json = await res.json();
            const user = json.data?.user ?? json;
            const role = user.role;

            // Masquer les liens visiteur
            document.getElementById('nav-register').style.display = 'none';
            document.getElementById('nav-login').style.display    = 'none';

            // Afficher les liens connectés
            document.getElementById('nav-logout').style.display   = 'list-item';
            document.getElementById('nav-user').style.display     = 'list-item';
            document.getElementById('nav-dashboard').style.display = 'list-item';

            // Avatar et nom
            const initiales = user.name?.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) ?? '?';
            document.getElementById('nav-avatar').textContent   = initiales;
            document.getElementById('nav-username').textContent = user.name ?? '';

            // Dashboard selon le rôle
            const dashLinks = {
                'artisan'        : '/dashboard/artisan',
                'acheteur'       : '/dashboard/acheteur',
                'administrateur' : '/dashboard/admin',
            };
            document.getElementById('nav-dashboard-link').href = dashLinks[role] ?? '/';

            // Liens spécifiques acheteur
            if (role === 'acheteur') {
                document.getElementById('nav-panier').style.display   = 'list-item';
                document.getElementById('nav-commandes').style.display = 'list-item';
                loadPanierCount();
            }

        } catch (e) {
            console.error('Erreur navbar', e);
        }
    }

    // ── Compteur panier ───────────────────────────────────────────────────────
    async function loadPanierCount() {
        try {
            const res  = await fetch('/api/v1/acheteur/panier', {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
                credentials: 'include',
            });
            const json = await res.json();
            const items = json.data?.items ?? json.data ?? [];
            const count = Array.isArray(items) ? items.length : 0;

            const badge = document.getElementById('panier-badge');
            if (count > 0) {
                badge.textContent    = count > 9 ? '9+' : count;
                badge.style.display  = 'flex';
            }
        } catch (e) { console.error(e); }
    }

    // ── Déconnexion ───────────────────────────────────────────────────────────
    async function logout() {
        try {
            await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
            const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');
            await fetch('/api/v1/auth/logout', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}`, 'X-XSRF-TOKEN': xsrf },
                credentials: 'include',
            });
        } catch (e) { console.error(e); }
        finally {
            localStorage.removeItem('token');
            window.location.href = '/';
        }
    }

    initNavbar();
    </script>

</body>
</html>
