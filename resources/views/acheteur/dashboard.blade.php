@extends('layouts.app')

@section('title', 'Mon espace - ArtisanConnect')

@section('content')

<style>
.dash-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:1.2rem; }
.cat-card {
    background:white; border-radius:12px; padding:1.4rem;
    box-shadow:0 2px 8px rgba(0,0,0,0.06);
    transition:transform 0.2s,box-shadow 0.2s;
    text-decoration:none; color:inherit; display:block;
}
.cat-card:hover { transform:translateY(-4px); box-shadow:0 8px 20px rgba(0,0,0,0.1); }
.cat-icon { font-size:2rem; margin-bottom:0.5rem; }
.cat-name { font-weight:700; color:#1e293b; font-size:1rem; margin-bottom:0.3rem; }
.cat-desc { color:#64748b; font-size:0.85rem; flex:1; }
.cat-link { display:inline-block; margin-top:0.75rem; color:#0d6efd; font-size:0.85rem; font-weight:600; }

.artisan-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:1.2rem; }
.artisan-card {
    background:white; border-radius:12px; padding:1.4rem;
    box-shadow:0 2px 8px rgba(0,0,0,0.06);
    display:flex; flex-direction:column; gap:0.4rem;
    transition:transform 0.2s; text-decoration:none; color:inherit;
}
.artisan-card:hover { transform:translateY(-3px); }
.artisan-initiales {
    width:52px; height:52px; border-radius:50%;
    background:#dbeafe; color:#1d4ed8;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:1rem; margin-bottom:0.4rem;
}
.artisan-name { font-weight:700; color:#1e293b; font-size:1rem; }
.artisan-spe { display:inline-block; background:#f0f9ff; color:#0369a1; font-size:0.72rem; font-weight:600; padding:0.2rem 0.55rem; border-radius:999px; }
.artisan-bio { color:#64748b; font-size:0.85rem; }
.artisan-link { color:#0d6efd; font-size:0.85rem; font-weight:600; margin-top:0.5rem; }
</style>

<div class="dashboard">

    <div class="dashboard-hero">
        <div class="dashboard-avatar">{{ auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'U' }}</div>
        <div>
            <h1>Bonjour, {{ auth()->user()?->name ?? 'Utilisateur' }} 👋</h1>
            <p>Découvrez les dernières créations de nos artisans.</p>
        </div>
    </div>

    {{-- Stats --}}
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

    {{-- Catégories --}}
    <section class="section">
        <div class="section-header">
            <h2>Parcourir par catégorie</h2>
            <p class="section-sub">Trouvez l'œuvre qui vous parle</p>
        </div>
        <div class="dash-grid" id="categories-grid">
            <p style="color:#94a3b8">Chargement des catégories...</p>
        </div>
    </section>

    {{-- Artisans --}}
    <section class="section">
        <div class="section-header">
            <h2>Artisans à découvrir</h2>
            <p class="section-sub">Des talents sélectionnés pour vous</p>
        </div>
        <div class="artisan-grid" id="artisans-grid">
            <p style="color:#94a3b8">Chargement des artisans...</p>
        </div>
    </section>

</div>

<script>
const token = localStorage.getItem('token');
const authHeaders = { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` };

// ── Icônes catégories ─────────────────────────────────────────────────────────
function iconeCategorie(nom) {
    const k = nom?.toLowerCase() ?? '';
    if (k.includes('peinture'))                          return '🎨';
    if (k.includes('bijou'))                             return '💍';
    if (k.includes('sculpture'))                         return '🗿';
    if (k.includes('textile'))                           return '🧵';
    if (k.includes('décor') || k.includes('decor'))     return '🏺';
    if (k.includes('artisanat') || k.includes('trad'))  return '🤝';
    if (k.includes('poterie') || k.includes('argile'))  return '🏛️';
    if (k.includes('bois'))                              return '🪵';
    return '🎁';
}

// ── Stats ─────────────────────────────────────────────────────────────────────
async function loadStats() {
    try {
        const [commandes, favoris, panier] = await Promise.all([
            fetch('/api/v1/acheteur/commandes',  { headers: authHeaders, credentials: 'include' }).then(r => r.json()).catch(() => ({})),
            fetch('/api/v1/acheteur/favoris',    { headers: authHeaders, credentials: 'include' }).then(r => r.json()).catch(() => ({})),
            fetch('/api/v1/acheteur/panier',     { headers: authHeaders, credentials: 'include' }).then(r => r.json()).catch(() => ({})),
        ]);

        const nbCommandes = commandes.data?.commandes?.length ?? commandes.data?.length ?? 0;
        const nbFavoris   = favoris.data?.favoris?.length     ?? favoris.data?.length   ?? 0;
        const nbPanier    = panier.data?.items?.length        ?? panier.data?.length    ?? 0;

        document.getElementById('stat-commandes').textContent = nbCommandes;
        document.getElementById('stat-favoris').textContent   = nbFavoris;
        document.getElementById('stat-panier').textContent    = nbPanier;

    } catch (e) {
        ['stat-commandes','stat-favoris','stat-panier'].forEach(id => {
            document.getElementById(id).textContent = '0';
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
        const cats = Array.isArray(json.data) ? json.data : [];

        if (!cats.length) {
            grid.innerHTML = '<p style="color:#94a3b8">Aucune catégorie disponible.</p>';
            return;
        }

        grid.innerHTML = cats.map(cat => {
            // ✅ CORRECTION : name au lieu de nom
            const nom  = cat.name ?? cat.nom ?? '';
            const desc = cat.description ?? '';
            const icon = iconeCategorie(nom);
            const url  = "{{ route('catalogue.categories') }}?categorie=" + encodeURIComponent(cat.id);

            return `
            <a href="${url}" class="cat-card">
                <div class="cat-icon">${icon}</div>
                <div class="cat-name">${nom}</div>
                <div class="cat-desc">${desc}</div>
                <span class="cat-link">Explorer →</span>
            </a>`;
        }).join('');

    } catch (e) {
        grid.innerHTML = '<p style="color:#ef4444">Erreur chargement des catégories.</p>';
        console.error(e);
    }
}

// ── Artisans ──────────────────────────────────────────────────────────────────
async function loadArtisans() {
    const grid = document.getElementById('artisans-grid');
    try {
        const res      = await fetch('/api/v1/catalog/artisans?per_page=3', { headers: { 'Accept': 'application/json' } });
        const json     = await res.json();
        const artisans = Array.isArray(json.data) ? json.data : [];

        if (!artisans.length) {
            grid.innerHTML = '<p style="color:#94a3b8">Aucun artisan à afficher pour le moment.</p>';
            return;
        }

        grid.innerHTML = artisans.map(a => {
            const initiales = a.name?.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2) ?? 'A';
            const avatar    = a.avatar
                ? `<img src="${a.avatar}" style="width:52px;height:52px;border-radius:50%;object-fit:cover;margin-bottom:0.4rem">`
                : `<div class="artisan-initiales">${initiales}</div>`;
            const note = a.note_moyenne > 0 ? `⭐ ${a.note_moyenne} (${a.total_avis} avis)` : '';
            const bio  = a.bio ? a.bio.substring(0, 80) + (a.bio.length > 80 ? '...' : '') : '';

            return `
            <a href="/catalogue/artisans/${a.id}" class="artisan-card">
                ${avatar}
                <div class="artisan-name">${a.name}</div>
                ${a.specialite ? `<span class="artisan-spe">${a.specialite}</span>` : ''}
                ${note ? `<span style="color:#f59e0b;font-size:0.8rem">${note}</span>` : ''}
                ${bio  ? `<p class="artisan-bio">${bio}</p>` : ''}
                <span class="artisan-link">Voir le profil →</span>
            </a>`;
        }).join('');

    } catch (e) {
        grid.innerHTML = '<p style="color:#94a3b8">Artisans non disponibles.</p>';
        console.error(e);
    }
}

// ── Init ──────────────────────────────────────────────────────────────────────
loadStats();
loadCategories();
loadArtisans();
</script>

@endsection
