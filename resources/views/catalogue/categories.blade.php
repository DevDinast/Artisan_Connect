@extends('layouts.app')

@section('title', 'Catalogue des œuvres - ArtisanConnect')

@section('content')

<style>
.catalogue-header { text-align:center; margin-bottom:2rem; }
.catalogue-header h1 { font-size:2rem; font-weight:700; color:#1e293b; letter-spacing:-0.5px; margin-bottom:0.4rem; }
.catalogue-header p { color:#64748b; font-size:1rem; }

.filtres-bar {
    background:white; border-radius:12px; padding:1.25rem 1.5rem;
    box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:2rem;
    display:flex; flex-wrap:wrap; gap:1rem; align-items:center;
}
.filtres-bar input,
.filtres-bar select {
    padding:0.65rem 1rem; border:2px solid #e2e8f0; border-radius:8px;
    font-family:inherit; font-size:0.9rem; color:#374151; outline:none;
    transition:border-color 0.2s; background:white;
}
.filtres-bar input { flex:1; min-width:200px; }
.filtres-bar input:focus, .filtres-bar select:focus { border-color:#0d6efd; }
.filtres-bar select { min-width:160px; cursor:pointer; }

.catalogue-compteur { color:#64748b; font-size:0.88rem; margin-bottom:1rem; }

.oeuvres-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(230px, 1fr));
    gap:1.5rem;
}

.oeuvre-card {
    background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06);
    overflow:hidden; transition:transform 0.2s,box-shadow 0.2s;
    text-decoration:none; color:inherit; display:block;
}
.oeuvre-card:hover { transform:translateY(-5px); box-shadow:0 12px 28px rgba(0,0,0,0.12); }
.oeuvre-card-img { position:relative; height:210px; overflow:hidden; background:#f1f5f9; }
.oeuvre-card-img img { width:100%; height:100%; object-fit:cover; transition:transform 0.4s; }
.oeuvre-card:hover .oeuvre-card-img img { transform:scale(1.07); }
.oeuvre-card-badge {
    position:absolute; top:0.65rem; left:0.65rem;
    background:rgba(255,255,255,0.92); color:#0369a1;
    font-size:0.7rem; font-weight:700; padding:0.2rem 0.55rem;
    border-radius:999px; letter-spacing:0.3px; text-transform:uppercase;
}
.oeuvre-card-body { padding:1rem 1.1rem 1.2rem; }
.oeuvre-card-titre { font-size:0.97rem; font-weight:700; color:#1e293b; margin-bottom:0.3rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.oeuvre-card-artisan { font-size:0.8rem; color:#64748b; margin-bottom:0.6rem; }
.oeuvre-card-footer { display:flex; justify-content:space-between; align-items:center; margin-top:0.75rem; padding-top:0.75rem; border-top:1px solid #f1f5f9; }
.oeuvre-card-prix { font-size:1rem; font-weight:700; color:#0d6efd; }
.oeuvre-card-btn { font-size:0.78rem; font-weight:600; color:#0d6efd; background:#eff6ff; padding:0.3rem 0.7rem; border-radius:6px; }

.catalogue-loading { text-align:center; padding:4rem 0; color:#64748b; }
.catalogue-empty { text-align:center; padding:4rem 2rem; background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); }

.catalogue-pagination { display:flex; justify-content:center; gap:0.5rem; margin-top:2.5rem; flex-wrap:wrap; }
.page-btn { padding:0.5rem 0.9rem; border-radius:8px; border:2px solid #e2e8f0; background:white; color:#374151; font-family:inherit; font-size:0.88rem; font-weight:600; cursor:pointer; transition:all 0.2s; }
.page-btn:hover { border-color:#0d6efd; color:#0d6efd; }
.page-btn.active { background:#0d6efd; border-color:#0d6efd; color:white; }
</style>

<div class="catalogue-header">
    <span class="hero-badge">✦ Collection artisanale</span>
    <h1>Catalogue des œuvres</h1>
    <p>Explorez des créations uniques faites main par nos artisans.</p>
</div>

<div class="filtres-bar">
    <input id="search" type="text" placeholder="🔍 Rechercher une œuvre...">
    <select id="categorie">
        <option value="">Toutes les catégories</option>
    </select>
    <select id="tri">
        <option value="">Trier par défaut</option>
        <option value="recent">Plus récentes</option>
        <option value="prix_asc">Prix croissant</option>
        <option value="prix_desc">Prix décroissant</option>
    </select>
</div>

<p class="catalogue-compteur" id="compteur" style="display:none"></p>

<div class="catalogue-loading" id="loading">Chargement des œuvres...</div>

<div class="catalogue-empty" id="empty" style="display:none">
    <span style="font-size:3rem;display:block;margin-bottom:1rem">🎨</span>
    <h3 style="font-weight:700;color:#1e293b;margin-bottom:0.4rem">Aucune œuvre trouvée</h3>
    <p style="color:#64748b">Essayez de modifier vos critères de recherche.</p>
</div>

<div class="oeuvres-grid" id="catalogue-grid"></div>
<div class="catalogue-pagination" id="pagination"></div>

<div style="margin-top:2.5rem;text-align:center">
    <a href="/dashboard/acheteur" class="btn-outline">Mon espace acheteur →</a>
</div>

<script>
const searchInput     = document.getElementById('search');
const categorieSelect = document.getElementById('categorie');
const triSelect       = document.getElementById('tri');
const grid            = document.getElementById('catalogue-grid');
const loading         = document.getElementById('loading');
const empty           = document.getElementById('empty');
const compteur        = document.getElementById('compteur');
const pagination      = document.getElementById('pagination');
let debounceTimer = null;
let currentPage   = 1;

async function loadCategories() {
    try {
        const res  = await fetch('/api/v1/catalog/categories', { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        const cats = Array.isArray(json.data) ? json.data : [];
        cats.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name ?? c.nom;
            categorieSelect.appendChild(opt);
        });
    } catch (e) { console.error(e); }
}

function imageUrl(o) {
    if (o.image) return o.image;
    if (o.images?.[0]?.url) return o.images[0].url;
    if (o.images?.[0]?.chemin) return `/storage/${o.images[0].chemin}`;
    return 'https://via.placeholder.com/400x280?text=Oeuvre';
}

async function loadOeuvres(page = 1) {
    loading.style.display = 'block';
    empty.style.display   = 'none';
    compteur.style.display = 'none';
    grid.innerHTML        = '';
    pagination.innerHTML  = '';

    const params = new URLSearchParams();
    if (searchInput.value.trim())  params.set('search',    searchInput.value.trim());
    if (categorieSelect.value)     params.set('categorie', categorieSelect.value);
    if (triSelect.value)           params.set('tri',       triSelect.value);
    params.set('per_page', '12');
    params.set('page', page);

    try {
        const res     = await fetch(`/api/v1/catalog/oeuvres?${params}`, { headers: { 'Accept': 'application/json' } });
        const json    = await res.json();
        const oeuvres = json.data ?? [];
        const meta    = json.meta ?? {};

        loading.style.display = 'none';

        if (!Array.isArray(oeuvres) || !oeuvres.length) {
            empty.style.display = 'block';
            return;
        }

        if (meta.total) {
            compteur.style.display = 'block';
            compteur.textContent   = `${meta.total} œuvre${meta.total > 1 ? 's' : ''} trouvée${meta.total > 1 ? 's' : ''}`;
        }

        grid.innerHTML = oeuvres.map(o => {
            const img       = imageUrl(o);
            const prix      = Number(o.prix).toLocaleString('fr-FR');
            const categorie = o.categorie?.nom ?? o.categorie?.name ?? '';
            const artisan   = o.artisan?.name ?? '';

            return `
            <a href="/catalogue/oeuvres/${o.id}" class="oeuvre-card">
                <div class="oeuvre-card-img">
                    <img src="${img}" alt="${o.titre}" onerror="this.src='https://via.placeholder.com/400x280?text=Oeuvre'">
                    ${categorie ? `<span class="oeuvre-card-badge">${categorie}</span>` : ''}
                </div>
                <div class="oeuvre-card-body">
                    <div class="oeuvre-card-titre">${o.titre}</div>
                    ${artisan ? `<div class="oeuvre-card-artisan">par ${artisan}</div>` : ''}
                    <div class="oeuvre-card-footer">
                        <span class="oeuvre-card-prix">${prix} FCFA</span>
                        <span class="oeuvre-card-btn">Voir →</span>
                    </div>
                </div>
            </a>`;
        }).join('');

        if (meta.last_page > 1) {
            for (let i = 1; i <= meta.last_page; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.className   = 'page-btn' + (i === page ? ' active' : '');
                btn.addEventListener('click', () => { currentPage = i; loadOeuvres(i); window.scrollTo({top:0,behavior:'smooth'}); });
                pagination.appendChild(btn);
            }
        }

    } catch (e) {
        loading.style.display = 'none';
        grid.innerHTML = '<p style="color:#ef4444;grid-column:1/-1;text-align:center">Erreur de chargement.</p>';
        console.error(e);
    }
}

searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => { currentPage = 1; loadOeuvres(1); }, 400);
});
categorieSelect.addEventListener('change', () => { currentPage = 1; loadOeuvres(1); });
triSelect.addEventListener('change',       () => { currentPage = 1; loadOeuvres(1); });

loadCategories();
loadOeuvres(1);
</script>

@endsection
