@extends('layouts.app')

@section('title', 'Catalogue des œuvres - ArtisanConnect')

@section('content')

{{-- En-tête --}}
<div style="text-align:center; margin-bottom:2rem">
    <span class="hero-badge">✦ Collection artisanale</span>
    <h1 style="font-size:2rem;font-weight:700;letter-spacing:-0.5px;margin-bottom:0.5rem">
        Catalogue des œuvres
    </h1>
    <p style="color:#64748b;font-size:1rem;max-width:500px;margin:0 auto">
        Explorez des créations uniques faites main par nos artisans.
    </p>
</div>

{{-- Barre de filtres --}}
<div style="background:white;border-radius:12px;padding:1.25rem 1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);margin-bottom:2rem;display:flex;flex-wrap:wrap;gap:1rem;align-items:center">

    <input
        id="search"
        type="text"
        placeholder="🔍 Rechercher une œuvre..."
        style="flex:1;min-width:200px;padding:0.65rem 1rem;border:2px solid #e2e8f0;border-radius:8px;font-family:inherit;font-size:0.95rem;outline:none;transition:border-color 0.2s"
        onfocus="this.style.borderColor='#0d6efd'"
        onblur="this.style.borderColor='#e2e8f0'"
    >

    <select id="categorie"
        style="padding:0.65rem 1rem;border:2px solid #e2e8f0;border-radius:8px;font-family:inherit;font-size:0.9rem;color:#374151;outline:none;cursor:pointer;min-width:160px">
        <option value="">Toutes les catégories</option>
    </select>

    <select id="tri"
        style="padding:0.65rem 1rem;border:2px solid #e2e8f0;border-radius:8px;font-family:inherit;font-size:0.9rem;color:#374151;outline:none;cursor:pointer;min-width:160px">
        <option value="">Trier par défaut</option>
        <option value="recent">Plus récentes</option>
        <option value="prix_asc">Prix croissant</option>
        <option value="prix_desc">Prix décroissant</option>
    </select>

</div>

{{-- États --}}
<div id="loading" style="text-align:center;padding:4rem 0;color:#64748b">
    <div style="font-size:2rem;margin-bottom:0.5rem">⏳</div>
    Chargement des œuvres...
</div>

<div id="empty" style="display:none;text-align:center;padding:4rem 0;color:#64748b">
    <div style="font-size:3rem;margin-bottom:1rem">🎨</div>
    <h3 style="font-weight:700;color:#1e293b;margin-bottom:0.5rem">Aucune œuvre trouvée</h3>
    <p>Essayez de modifier vos filtres de recherche.</p>
</div>

{{-- Compteur --}}
<div id="compteur" style="display:none;margin-bottom:1rem;color:#64748b;font-size:0.9rem"></div>

{{-- Grille catalogue --}}
<div id="catalogue-grid" style="
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1.5rem;
"></div>

{{-- Pagination --}}
<div id="pagination" style="display:flex;justify-content:center;gap:0.5rem;margin-top:2.5rem"></div>

<div style="margin-top:2rem;text-align:center">
    <a href="{{ route('dashboard.acheteur') }}" class="btn-outline">Mon espace acheteur →</a>
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

// ── Charger les catégories ────────────────────────────────────────────────────
async function loadCategories() {
    try {
        const res  = await fetch('/api/v1/catalog/categories', { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        const cats = Array.isArray(json.data) ? json.data : (Array.isArray(json) ? json : []);
        cats.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nom ?? c.name;
            categorieSelect.appendChild(opt);
        });
    } catch (e) { console.error(e); }
}

// ── Charger les œuvres ────────────────────────────────────────────────────────
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
        const res  = await fetch(`/api/v1/catalog/oeuvres?${params}`, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        const oeuvres = json.data ?? [];
        const meta    = json.meta ?? {};

        loading.style.display = 'none';

        if (!Array.isArray(oeuvres) || !oeuvres.length) {
            empty.style.display = 'block';
            return;
        }

        // Compteur
        if (meta.total) {
            compteur.style.display = 'block';
            compteur.textContent   = `${meta.total} œuvre${meta.total > 1 ? 's' : ''} trouvée${meta.total > 1 ? 's' : ''}`;
        }

        // Cartes
        grid.innerHTML = oeuvres.map(o => {
            const image     = o.image ?? (o.images?.[0]?.chemin ? `/storage/${o.images[0].chemin}` : null);
            const imgSrc    = image ?? 'https://via.placeholder.com/400x300?text=Oeuvre';
            const prix      = Number(o.prix).toLocaleString('fr-FR');
            const categorie = o.categorie?.nom ?? o.categorie?.name ?? '';

            return `
            <a href="{{ route('catalogue.oeuvre', '') }}/${o.id}"
               style="text-decoration:none;color:inherit;display:block;background:white;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden;transition:transform 0.2s,box-shadow 0.2s"
               onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)'"
               onmouseout="this.style.transform='';this.style.boxShadow='0 2px 8px rgba(0,0,0,0.06)'">

                {{-- Image --}}
                <div style="position:relative;overflow:hidden;height:200px;background:#f1f5f9">
                    <img src="${imgSrc}" alt="${o.titre}"
                         style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s"
                         onmouseover="this.style.transform='scale(1.05)'"
                         onmouseout="this.style.transform=''"
                         onerror="this.src='https://via.placeholder.com/400x300?text=Oeuvre'">
                    ${categorie ? `
                    <span style="position:absolute;top:0.75rem;left:0.75rem;background:rgba(255,255,255,0.92);color:#0369a1;font-size:0.72rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:999px;backdrop-filter:blur(4px)">
                        ${categorie}
                    </span>` : ''}
                </div>

                {{-- Infos --}}
                <div style="padding:1rem 1.1rem 1.2rem">
                    <h3 style="font-size:0.98rem;font-weight:700;color:#1e293b;margin-bottom:0.3rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        ${o.titre}
                    </h3>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.5rem">
                        <span style="font-size:1.05rem;font-weight:700;color:#0d6efd">${prix} FCFA</span>
                        <span style="font-size:0.78rem;color:#64748b;background:#f1f5f9;padding:0.2rem 0.5rem;border-radius:6px">
                            ${o.artisan?.name ?? ''}
                        </span>
                    </div>
                    <div style="margin-top:0.9rem;text-align:center;background:#eff6ff;color:#0d6efd;padding:0.5rem;border-radius:7px;font-size:0.85rem;font-weight:600">
                        Voir les détails →
                    </div>
                </div>
            </a>`;
        }).join('');

        // Pagination
        if (meta.last_page > 1) {
            for (let i = 1; i <= meta.last_page; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.style.cssText = `
                    padding: 0.5rem 0.9rem;
                    border-radius: 8px;
                    border: 2px solid ${i === page ? '#0d6efd' : '#e2e8f0'};
                    background: ${i === page ? '#0d6efd' : 'white'};
                    color: ${i === page ? 'white' : '#374151'};
                    font-family: inherit;
                    font-size: 0.9rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s;
                `;
                btn.addEventListener('click', () => { currentPage = i; loadOeuvres(i); window.scrollTo(0, 0); });
                pagination.appendChild(btn);
            }
        }

    } catch (e) {
        loading.style.display = 'none';
        grid.innerHTML = '<p style="color:#ef4444;grid-column:1/-1;text-align:center">Erreur lors du chargement des œuvres.</p>';
        console.error(e);
    }
}

// ── Listeners ────────────────────────────────────────────────────────────────
searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => { currentPage = 1; loadOeuvres(1); }, 400);
});
categorieSelect.addEventListener('change', () => { currentPage = 1; loadOeuvres(1); });
triSelect.addEventListener('change',       () => { currentPage = 1; loadOeuvres(1); });

// ── Init ──────────────────────────────────────────────────────────────────────
loadCategories();
loadOeuvres(1);
</script>

@endsection
