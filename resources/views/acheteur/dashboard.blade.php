@extends('layouts.app')
@section('title', 'Mon espace - ArtisanConnect')
@section('content')

<style>
.dash-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:1.1rem; }
.cat-card { background:white; border-radius:12px; padding:1.3rem; box-shadow:0 2px 10px rgba(107,58,42,0.07); border:1px solid var(--border); transition:transform 0.2s,box-shadow 0.2s; text-decoration:none; color:inherit; display:flex; flex-direction:column; gap:0.4rem; position:relative; overflow:hidden; }
.cat-card::after { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg,var(--terra),var(--or)); transform:scaleX(0); transition:transform 0.3s; transform-origin:left; }
.cat-card:hover { transform:translateY(-4px); box-shadow:0 10px 22px rgba(107,58,42,0.14); }
.cat-card:hover::after { transform:scaleX(1); }
.cat-icon { font-size:2rem; }
.cat-name { font-weight:700; color:var(--brun); font-size:0.97rem; }
.cat-desc { color:var(--text-mid); font-size:0.83rem; flex:1; }
.cat-link { color:var(--terra); font-size:0.82rem; font-weight:600; margin-top:auto; }

.artisan-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:1.1rem; }
.artisan-card { background:white; border-radius:12px; padding:1.3rem; box-shadow:0 2px 10px rgba(107,58,42,0.07); border:1px solid var(--border); display:flex; flex-direction:column; gap:0.4rem; transition:transform 0.2s; text-decoration:none; color:inherit; position:relative; overflow:hidden; }
.artisan-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,var(--terra),var(--ocre),var(--or)); }
.artisan-card:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(107,58,42,0.13); }
.artisan-initiales { width:50px; height:50px; border-radius:50%; background:linear-gradient(135deg,var(--terra),var(--ocre)); color:white; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.95rem; }
.artisan-name { font-weight:700; color:var(--brun); font-size:0.97rem; }
.artisan-spe { display:inline-block; background:var(--vert-light); color:var(--vert); font-size:0.7rem; font-weight:600; padding:0.18rem 0.5rem; border-radius:999px; }
.artisan-bio { color:var(--text-mid); font-size:0.83rem; }
.artisan-link { color:var(--terra); font-size:0.82rem; font-weight:600; margin-top:0.4rem; }
</style>

<div class="dashboard">
    <div class="dashboard-hero fade-in">
        <div class="dashboard-avatar">{{ auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'U' }}</div>
        <div>
            <h1>Bonjour, {{ auth()->user()?->name ?? 'Utilisateur' }} 👋</h1>
            <p>Découvrez les dernières créations de nos artisans.</p>
        </div>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card fade-in"><span class="stat-num" id="stat-commandes">—</span><span class="stat-label">Commandes</span></div>
        <div class="stat-card fade-in"><span class="stat-num" id="stat-favoris">—</span><span class="stat-label">Favoris</span></div>
        <div class="stat-card fade-in"><span class="stat-num" id="stat-panier">—</span><span class="stat-label">Articles au panier</span></div>
    </div>

    <section class="section">
        <div class="section-header"><h2>Parcourir par catégorie</h2><p class="section-sub">Trouvez l'œuvre qui vous parle</p></div>
        <div class="dash-grid" id="categories-grid"><p style="color:var(--text-light)">Chargement...</p></div>
    </section>

    <section class="section">
        <div class="section-header"><h2>Artisans à découvrir</h2><p class="section-sub">Des talents sélectionnés pour vous</p></div>
        <div class="artisan-grid" id="artisans-grid"><p style="color:var(--text-light)">Chargement...</p></div>
    </section>
</div>

<script>
const token = localStorage.getItem('token');
const authHeaders = { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` };

function iconeCategorie(nom) {
    const k = nom?.toLowerCase() ?? '';
    if (k.includes('peinture')) return '🎨';
    if (k.includes('bijou'))    return '💍';
    if (k.includes('sculpture'))return '🗿';
    if (k.includes('textile'))  return '🧵';
    if (k.includes('décor') || k.includes('decor')) return '🏺';
    if (k.includes('artisanat')|| k.includes('trad')) return '🤝';
    if (k.includes('poterie') || k.includes('argile')) return '🏛️';
    if (k.includes('bois'))     return '🪵';
    return '🎁';
}

async function loadStats() {
    try {
        const [commandes, favoris, panier] = await Promise.all([
            fetch('/api/v1/acheteur/commandes', {headers:authHeaders,credentials:'include'}).then(r=>r.json()).catch(()=>({})),
            fetch('/api/v1/acheteur/favoris',   {headers:authHeaders,credentials:'include'}).then(r=>r.json()).catch(()=>({})),
            fetch('/api/v1/acheteur/panier',    {headers:authHeaders,credentials:'include'}).then(r=>r.json()).catch(()=>({})),
        ]);
        document.getElementById('stat-commandes').textContent = commandes.data?.commandes?.length ?? commandes.data?.length ?? 0;
        document.getElementById('stat-favoris').textContent   = favoris.data?.favoris?.length ?? favoris.data?.length ?? 0;
        document.getElementById('stat-panier').textContent    = panier.data?.items?.length ?? panier.data?.length ?? 0;
    } catch(e) { ['stat-commandes','stat-favoris','stat-panier'].forEach(id=>{ document.getElementById(id).textContent='0'; }); }
}

async function loadCategories() {
    const grid = document.getElementById('categories-grid');
    try {
        const res  = await fetch('/api/v1/catalog/categories', { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        const cats = Array.isArray(json.data) ? json.data : [];
        if (!cats.length) { grid.innerHTML = '<p style="color:var(--text-light)">Aucune catégorie.</p>'; return; }
        grid.innerHTML = cats.map(cat => {
            const nom  = cat.name ?? cat.nom ?? '';
            const icon = iconeCategorie(nom);
            const url  = "{{ route('catalogue.categories') }}?categorie=" + encodeURIComponent(cat.id);
            return `<a href="${url}" class="cat-card fade-in">
                <div class="cat-icon">${icon}</div>
                <div class="cat-name">${nom}</div>
                <div class="cat-desc">${cat.description ?? ''}</div>
                <span class="cat-link">Explorer →</span>
            </a>`;
        }).join('');
        document.querySelectorAll('.fade-in').forEach(el => {
            if (window.observer) window.observer.observe(el);
        });
    } catch(e) { grid.innerHTML = '<p style="color:#ef4444">Erreur chargement.</p>'; }
}

async function loadArtisans() {
    const grid = document.getElementById('artisans-grid');
    try {
        const res      = await fetch('/api/v1/catalog/artisans?per_page=3', { headers: { 'Accept': 'application/json' } });
        const json     = await res.json();
        const artisans = Array.isArray(json.data) ? json.data : [];
        if (!artisans.length) { grid.innerHTML = '<p style="color:var(--text-light)">Aucun artisan.</p>'; return; }
        grid.innerHTML = artisans.map(a => {
            const initiales = a.name?.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) ?? 'A';
            const avatar    = a.avatar ? `<img src="${a.avatar}" style="width:50px;height:50px;border-radius:50%;object-fit:cover">` : `<div class="artisan-initiales">${initiales}</div>`;
            const note = a.note_moyenne > 0 ? `<span style="color:var(--or);font-size:0.78rem">⭐ ${a.note_moyenne}</span>` : '';
            return `<a href="/catalogue/artisans/${a.id}" class="artisan-card fade-in">
                ${avatar}
                <div class="artisan-name">${a.name}</div>
                ${a.specialite ? `<span class="artisan-spe">${a.specialite}</span>` : ''}
                ${note}
                ${a.bio ? `<p class="artisan-bio">${a.bio.substring(0,80)}...</p>` : ''}
                <span class="artisan-link">Voir le profil →</span>
            </a>`;
        }).join('');
    } catch(e) { grid.innerHTML = '<p style="color:var(--text-light)">Artisans non disponibles.</p>'; }
}

loadStats(); loadCategories(); loadArtisans();
</script>

@endsection
