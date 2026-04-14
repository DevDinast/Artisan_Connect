<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#C0542A">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/app.css">
    <title>@yield('title', 'ArtisanConnect')</title>
    @stack('styles')
</head>
<body>

    <header>
        <nav class="navbar">
            <a href="{{ url('/') }}" class="logo">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                    <polygon points="14,2 26,8 26,20 14,26 2,20 2,8" fill="none" stroke="#E8A92A" stroke-width="2"/>
                    <polygon points="14,7 21,11 21,18 14,22 7,18 7,11" fill="#E8A92A" opacity="0.3"/>
                    <circle cx="14" cy="14" r="3" fill="#E8A92A"/>
                </svg>
                <span>ArtisanConnect</span>
            </a>

            <button class="nav-toggle" id="nav-toggle" aria-label="Ouvrir le menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>

            <ul class="nav-links" id="nav-links" role="navigation">
                <li><a href="{{ route('catalogue.categories') }}">Catalogue</a></li>
                <li id="nav-register"><a href="{{ route('auth.register') }}">S'inscrire</a></li>
                 <li id="nav-login"><a href="{{ route('auth.login') }}">Connexion</a></li>
                <li id="nav-panier" style="display:none">
                    <a href="/panier" class="nav-panier">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 01-8 0"/>
                        </svg>
                        Panier
                        <span class="panier-badge" id="panier-badge">0</span>
                    </a>
                </li>
                <li id="nav-commandes" style="display:none"><a href="/commandes">Mes commandes</a></li>
                <li id="nav-dashboard" style="display:none"><a href="/dashboard/acheteur" id="nav-dashboard-link">Mon espace</a></li>
                <li id="nav-user" style="display:none">
                    <span class="nav-user">
                        <div class="nav-user-avatar" id="nav-avatar">?</div>
                        <span id="nav-username"></span>
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
        <div class="footer-kente"></div>
        <div class="footer-inner">
            <div class="footer-brand">
                <div class="footer-logo">
                    <svg width="22" height="22" viewBox="0 0 28 28" fill="none">
                        <polygon points="14,2 26,8 26,20 14,26 2,20 2,8" fill="none" stroke="#E8A92A" stroke-width="2"/>
                        <circle cx="14" cy="14" r="3" fill="#E8A92A"/>
                    </svg>
                    ArtisanConnect
                </div>
                <p>La plateforme qui connecte les artisans africains talentueux avec des acheteurs passionnés du monde entier.</p>
                <div class="footer-social">
                    <a href="#" class="social-dot" title="Instagram">📸</a>
                    <a href="#" class="social-dot" title="Facebook">📘</a>
                    <a href="#" class="social-dot" title="WhatsApp">💬</a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Explorer</h4>
                <ul>
                    <li><a href="{{ route('catalogue.categories') }}">Catalogue</a></li>
                    <li><a href="#">Artisans</a></li>
                    <li><a href="#">Catégories</a></li>
                    <li><a href="#">Nouveautés</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Compte</h4>
                <ul>
                    <li><a href="{{ route('auth.register') }}">S'inscrire</a></li>
                    <li><a href="{{ route('auth.login') }}">Se connecter</a></li>
                    <li><a href="#">Mon profil</a></li>
                    <li><a href="#">Mes commandes</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Informations</h4>
                <ul>
                    <li><a href="#">À propos</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Mentions légales</a></li>
                    <li><a href="#">Confidentialité</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} ArtisanConnect — Fait avec ❤️ pour les artisans africains</p>
        </div>
    </footer>

    <script>
    var token = localStorage.getItem('token');

    // ── Menu mobile ────────────────────────────────────────────────────
    var navToggle = document.getElementById('nav-toggle');
    var navLinks  = document.getElementById('nav-links');

    navToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        var isOpen = navLinks.classList.toggle('open');
        this.classList.toggle('active', isOpen);
        this.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.navbar')) {
            navLinks.classList.remove('open');
            navToggle.classList.remove('active');
            navToggle.setAttribute('aria-expanded', 'false');
        }
    });

    // ── Navbar utilisateur ─────────────────────────────────────────────
async function initNavbar() {
    try {
        const res = await fetch('/api/v1/me', {
            headers: { 'Accept': 'application/json' },
            credentials: 'include'
        });

        if (res.ok) {
            // CONNECTÉ
            document.getElementById('nav-register').style.display  = 'none';
            document.getElementById('nav-login').style.display     = 'none';
            document.getElementById('nav-logout').style.display    = 'list-item';
            document.getElementById('nav-user').style.display      = 'list-item';
            document.getElementById('nav-dashboard').style.display = 'list-item';
        } else {
            // NON CONNECTÉ
            document.getElementById('nav-register').style.display = 'list-item';
            document.getElementById('nav-login').style.display    = 'list-item';
            document.getElementById('nav-logout').style.display   = 'none';
        }

    } catch (e) {
        console.error(e);
    }
}

    async function loadPanierCount() {
        try {
            const res   = await fetch('/api/v1/acheteur/panier', {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
                credentials: 'include'
            });
            const json  = await res.json();
            const items = json.data?.items ?? json.data ?? [];
            const count = Array.isArray(items) ? items.length : 0;
            const badge = document.getElementById('panier-badge');
            if (count > 0) {
                badge.textContent    = count > 9 ? '9+' : count;
                badge.style.display  = 'flex';
            }
        } catch (e) { console.error(e); }
    }

async function logout() {
    try {
        await fetch('/sanctum/csrf-cookie', {
            method: 'GET',
            credentials: 'include'
        });

        const xsrf = decodeURIComponent(
            document.cookie.split('; ')
                .find(r => r.startsWith('XSRF-TOKEN='))
                ?.split('=')[1] ?? ''
        );

        await fetch('/api/v1/auth/logout', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-XSRF-TOKEN': xsrf
            },
            credentials: 'include'
        });

    } catch (e) {
        console.error(e);
    } finally {
        window.location.href = '/';
    }
}
    // ── Intersection observer pour les animations de carte ─────────────
    if ('IntersectionObserver' in window) {
        window.observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    setTimeout(() => entry.target.classList.add('visible'), i * 80);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08 });
        document.querySelectorAll('.card-animate').forEach(el => observer.observe(el));
    } else {
        document.querySelectorAll('.card-animate').forEach(el => el.classList.add('visible'));
    }

    initNavbar();
    </script>

    {{-- Les scripts des vues enfants s'exécutent ICI, après que "token" soit défini --}}
    @stack('scripts')

</body>
</html>
