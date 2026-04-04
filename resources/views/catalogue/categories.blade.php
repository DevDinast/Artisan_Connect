@extends('layouts.app')
@section('title', 'Catalogue des œuvres - ArtisanConnect')
@section('content')

<style>
.filtres-bar input:focus, .filtres-bar select:focus { border-color: var(--terra); }
.tri-option { cursor: pointer; }
</style>

<div class="catalogue-header fade-in">
    <span class="hero-badge">✦ Collection artisanale</span>
    <h1>Catalogue des œuvres</h1>
    <p>Explorez des créations uniques faites main par nos artisans.</p>
</div>

<div class="filtres-bar fade-in">
    <input id="search" type="text" placeholder="🔍 Rechercher une œuvre...">
    <select id="categorie">
        <option value="">Toutes les catégories</option>
    </select>
    <select id="tri">
        <option value="">Trier par défaut</option>
        <option value="">Plus récentes</option>
        <option value="prix_asc">Prix croissant</option>
        <option value="prix_desc">Prix décroissant</option>
    </select>
</div>

<p class="catalogue-compteur" id="compteur" style="display:none"></p>
<div class="catalogue-loading" id="loading">Chargement des œuvres...</div>
<div class="catalogue-empty" id="empty" style="display:none">
    <span style="font-size:3rem;display:block;margin-bottom:1rem">🎨</span>
    <h3 style="font-weight:700;color:var(--brun);margin-bottom:0.4rem">Aucune œuvre trouvée</h3>
    <p style="color:var(--text-mid)">Essayez de modifier vos critères de recherche.</p>
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
        // Pré-sélection depuis URL
        const urlCat = new URLSearchParams(window.location.search).get('categorie');
        if (urlCat) categorieSelect.value = urlCat;
    } catch (e) { console.error(e); }
}

function imageUrl(o) {
    if (o.image) return o.image;
    if (o.images?.[0]?.url) return o.images[0].url;
    if (o.images?.[0]?.chemin) return `/storage/${o.images[0].chemin}`;
    return 'https://via.placeholder.com/400x280?text=Oeuvre';
}

async function loadOeuvres(page = 1) {
    loading.style.display  = 'block';
    empty.style.display    = 'none';
    compteur.style.display = 'none';
    grid.innerHTML         = '';
    pagination.innerHTML   = '';

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
            empty.style.display = 'block'; return;
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
                    <img src="${img}" alt="${o.titre}" loading="lazy" onerror="this.src='https://via.placeholder.com/400x280?text=Oeuvre'">
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
