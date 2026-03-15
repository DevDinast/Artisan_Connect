@extends('layouts.app')

@section('title', 'Mon espace - ArtisanConnect')

@section('content')

<div class="dashboard">

    <div class="dashboard-hero">
        <div class="dashboard-avatar">{{ auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'U' }}</div>
        <div>
            <h1>Bonjour, {{ auth()->user()?->name ?? 'Utilisateur' }} 👋</h1>
            <p>Découvrez les dernières créations de nos artisans.</p>
        </div>
    </div>

    {{-- Stats dynamiques --}}
    <div class="dashboard-stats">
        <div class="stat-card">
            <span class="stat-num" id="stat-commandes">—</span>
            <span class="stat-label">Commandes</span>
        </div>
        <div class="stat-card">
            <span class="stat-num" id="stat-favoris">—</span>
            <span class="stat-label">Favoris</span>
        </div>
        <div class="stat-card">
            <span class="stat-num" id="stat-panier">—</span>
            <span class="stat-label">Articles au panier</span>
        </div>
    </div>

    {{-- Catégories dynamiques --}}
    <section class="section">
        <div class="section-header">
            <h2>Parcourir par catégorie</h2>
            <p class="section-sub">Trouvez l'œuvre qui vous parle</p>
        </div>
        <div class="cards" id="categories-grid">
            <p class="text-gray-400">Chargement des catégories...</p>
        </div>
    </section>

    {{-- Artisans dynamiques --}}
    <section class="section">
        <div class="section-header">
            <h2>Artisans à découvrir</h2>
            <p class="section-sub">Des talents sélectionnés pour vous</p>
        </div>
        <div class="cards" id="artisans-grid">
            <p class="text-gray-400">Chargement des artisans...</p>
        </div>
    </section>

</div>

<script>
const token = localStorage.getItem('token');

const authHeaders = {
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`,
};

const icones = {
    'peinture'  : '🎨',
    'bijoux'    : '💍',
    'bijouterie': '💍',
    'sculpture' : '🗿',
    'textile'   : '🧵',
};

function iconeCategorie(nom) {
    return icones[nom?.toLowerCase()] ?? '🎁';
}

// ── Stats ─────────────────────────────────────────────────────────────────────
async function loadStats() {
    try {
        const [commandes, favoris, panier] = await Promise.all([
            fetch('/api/v1/acheteur/commandes', { headers: authHeaders, credentials: 'include' }).then(r => r.json()),
            fetch('/api/v1/acheteur/favoris',   { headers: authHeaders, credentials: 'include' }).then(r => r.json()),
            fetch('/api/v1/acheteur/panier',    { headers: authHeaders, credentials: 'include' }).then(r => r.json()),
        ]);

        document.getElementById('stat-commandes').textContent = (commandes.data ?? commandes)?.length ?? 0;
        document.getElementById('stat-favoris').textContent   = (favoris.data   ?? favoris)?.length   ?? 0;
        document.getElementById('stat-panier').textContent    = (panier.data    ?? panier)?.length    ?? 0;

    } catch (e) {
        ['stat-commandes','stat-favoris','stat-panier'].forEach(id => {
            document.getElementById(id).textContent = '—';
        });
        console.error('Erreur stats', e);
    }
}

// ── Catégories ────────────────────────────────────────────────────────────────
async function loadCategories() {
    const grid = document.getElementById('categories-grid');
    try {
        const res  = await fetch('/api/v1/catalog/categories', { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        const cats = json.data ?? json;

        if (!Array.isArray(cats) || !cats.length) {
            grid.innerHTML = '<p class="text-gray-400">Aucune catégorie disponible.</p>';
            return;
        }

        grid.innerHTML = cats.map(cat => `
            <div class="card">
                <div class="card-icon">${iconeCategorie(cat.nom)}</div>
                <h3>${cat.nom}</h3>
                <p>${cat.description ?? ''}</p>
                <a href="{{ route('catalogue.categories') }}?categorie=${encodeURIComponent(cat.id ?? cat.slug ?? cat.nom)}" class="card-link">Explorer →</a>
            </div>
        `).join('');

    } catch (e) {
        grid.innerHTML = '<p class="text-red-400">Erreur chargement des catégories.</p>';
        console.error(e);
    }
}

// ── Artisans — GET /api/v1/catalog/artisans ───────────────────────────────────
async function loadArtisans() {
    const grid = document.getElementById('artisans-grid');
    try {
        const res      = await fetch('/api/v1/catalog/artisans?per_page=3', {
            headers: { 'Accept': 'application/json' }
        });
        const json     = await res.json();
        const artisans = json.data ?? json;

        if (!Array.isArray(artisans) || !artisans.length) {
            grid.innerHTML = '<p class="text-gray-400">Aucun artisan à afficher pour le moment.</p>';
            return;
        }

        grid.innerHTML = artisans.map(a => {
            const initiales = a.name?.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2) ?? 'A';
            const avatar    = a.avatar
                ? `<img src="${a.avatar}" alt="${a.name}" class="artisan-avatar-img">`
                : `<div class="artisan-avatar">${initiales}</div>`;
            const note      = a.note_moyenne > 0 ? `⭐ ${a.note_moyenne} (${a.total_avis} avis)` : '';

            return `
            <div class="card">
                ${avatar}
                <h3>${a.name}</h3>
                <span class="artisan-tag">${a.specialite ?? ''}</span>
                ${note ? `<span class="text-sm text-yellow-600">${note}</span>` : ''}
                <p>${a.bio ?? ''}</p>
                <a href="/catalogue/artisans/${a.id}" class="card-link">Voir le profil →</a>
            </div>`;
        }).join('');

    } catch (e) {
        grid.innerHTML = '<p class="text-gray-400">Artisans non disponibles pour le moment.</p>';
        console.error(e);
    }
}

// ── Init ──────────────────────────────────────────────────────────────────────
loadStats();
loadCategories();
loadArtisans();
</script>

@endsection
