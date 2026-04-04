<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>@yield('title', 'ArtisanConnect')</title>
</head>
<body>

<header>
    <nav class="navbar">
        <a href="{{ url('/') }}" class="logo">✦ ArtisanConnect</a>

        <button class="nav-toggle" id="nav-toggle" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>

        <ul class="nav-links" id="nav-links">
            <li><a href="{{ route('catalogue.categories') }}">Catalogue</a></li>
            <li id="nav-register"><a href="{{ route('auth.register') }}">S'inscrire</a></li>
            <li id="nav-login"><a href="{{ route('auth.login') }}">Connexion</a></li>
            <li id="nav-panier" style="display:none">
                <a href="/panier" class="nav-panier">
                    🛒 Panier
                    <span class="panier-badge" id="panier-badge">0</span>
                </a>
            </li>
            <li id="nav-commandes" style="display:none">
                <a href="/commandes">Mes commandes</a>
            </li>
            <li id="nav-dashboard" style="display:none">
                <a href="#" id="nav-dashboard-link">Mon espace</a>
            </li>
            <li id="nav-user" style="display:none">
                <span class="nav-user">
                    <div class="nav-user-avatar" id="nav-avatar">?</div>
                    <span id="nav-username" style="font-size:0.82rem;max-width:80px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:rgba(255,255,255,0.9)"></span>
                </span>
            </li>
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
    <p>&copy; {{ date('Y') }} ArtisanConnect — Plateforme artisanale africaine</p>
</footer>

<script>
var token = localStorage.getItem('token');

// ── Menu hamburger ──────────────────────────────────────────────────────────
const navToggle = document.getElementById('nav-toggle');
const navLinks  = document.getElementById('nav-links');

navToggle.addEventListener('click', function() {
    this.classList.toggle('open');
    navLinks.classList.toggle('open');
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('header')) {
        navToggle.classList.remove('open');
        navLinks.classList.remove('open');
    }
});

// ── Fermer menu au clic sur un lien ────────────────────────────────────────
navLinks.querySelectorAll('a, button').forEach(el => {
    el.addEventListener('click', () => {
        navToggle.classList.remove('open');
        navLinks.classList.remove('open');
    });
});

// ── Navbar selon connexion ──────────────────────────────────────────────────
async function initNavbar() {
    if (!token) return;
    try {
        const res  = await fetch('/api/v1/me', {
            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
            credentials: 'include',
        });
        if (!res.ok) { localStorage.removeItem('token'); return; }

        const json = await res.json();
        const user = json.data?.user ?? json;
        const role = user.role;

        document.getElementById('nav-register').style.display = 'none';
        document.getElementById('nav-login').style.display    = 'none';
        document.getElementById('nav-logout').style.display   = 'list-item';
        document.getElementById('nav-user').style.display     = 'list-item';
        document.getElementById('nav-dashboard').style.display = 'list-item';

        const initiales = user.name?.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) ?? '?';
        document.getElementById('nav-avatar').textContent   = initiales;
        document.getElementById('nav-username').textContent = user.name ?? '';

        const dashLinks = {
            'artisan'        : '/dashboard/artisan',
            'acheteur'       : '/dashboard/acheteur',
            'administrateur' : '/dashboard/admin',
        };
        document.getElementById('nav-dashboard-link').href = dashLinks[role] ?? '/';

        if (role === 'acheteur') {
            document.getElementById('nav-panier').style.display    = 'list-item';
            document.getElementById('nav-commandes').style.display = 'list-item';
            loadPanierCount();
        }
    } catch (e) { console.error('Navbar error', e); }
}

async function loadPanierCount() {
    try {
        const res   = await fetch('/api/v1/acheteur/panier', {
            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
            credentials: 'include',
        });
        const json  = await res.json();
        const items = json.data?.items ?? json.data ?? [];
        const count = Array.isArray(items) ? items.length : 0;
        const badge = document.getElementById('panier-badge');
        if (count > 0) {
            badge.textContent   = count > 9 ? '9+' : count;
            badge.style.display = 'flex';
        }
    } catch (e) { console.error(e); }
}

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

// ── Animation scroll ────────────────────────────────────────────────────────
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add('visible');
    });
}, { threshold: 0.1 });
document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

initNavbar();
</script>

</body>
</html>
