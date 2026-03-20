@extends('layouts.app')

@section('title', 'Détail de l\'œuvre - ArtisanConnect')

@section('content')

<style>
.oeuvre-detail { max-width:900px; margin:0 auto; }
.oeuvre-layout { display:flex; flex-wrap:wrap; gap:2rem; margin-bottom:2.5rem; }
.oeuvre-images { flex:1; min-width:280px; }
.oeuvre-main-img { width:100%; height:380px; object-fit:cover; border-radius:12px; background:#f1f5f9; }
.oeuvre-thumbs { display:flex; gap:0.5rem; margin-top:0.75rem; flex-wrap:wrap; }
.oeuvre-thumb { width:65px; height:65px; object-fit:cover; border-radius:8px; cursor:pointer; border:2px solid #e2e8f0; transition:border-color 0.2s; }
.oeuvre-thumb.active, .oeuvre-thumb:hover { border-color:#0d6efd; }
.oeuvre-info { flex:1; min-width:280px; }
.oeuvre-categorie { display:inline-block; background:#f0f9ff; color:#0369a1; font-size:0.78rem; font-weight:700; padding:0.25rem 0.65rem; border-radius:999px; margin-bottom:0.75rem; }
.oeuvre-titre { font-size:1.7rem; font-weight:700; color:#1e293b; letter-spacing:-0.4px; margin-bottom:0.5rem; }
.oeuvre-prix { font-size:1.6rem; font-weight:700; color:#0d6efd; margin-bottom:1.2rem; }
.oeuvre-desc { color:#475569; line-height:1.7; margin-bottom:1.5rem; font-size:0.95rem; }
.artisan-mini { background:#f8fafc; border-radius:10px; padding:1rem; display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem; text-decoration:none; color:inherit; transition:background 0.2s; }
.artisan-mini:hover { background:#eff6ff; }
.artisan-mini-avatar { width:44px; height:44px; border-radius:50%; background:#dbeafe; color:#1d4ed8; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.9rem; flex-shrink:0; }
.artisan-mini-name { font-weight:700; color:#1e293b; font-size:0.95rem; }
.artisan-mini-spe { color:#64748b; font-size:0.82rem; }
.btn-panier { width:100%; margin-bottom:0.75rem; }
.btn-favori { width:100%; background:white; color:#0d6efd; border:2px solid #0d6efd; padding:0.75rem; border-radius:8px; font-family:inherit; font-size:0.95rem; font-weight:600; cursor:pointer; transition:all 0.2s; }
.btn-favori:hover { background:#eff6ff; }
.avis-item { border-bottom:1px solid #f1f5f9; padding:1rem 0; }
.avis-header { display:flex; justify-content:space-between; margin-bottom:0.3rem; }
.avis-auteur { font-weight:700; color:#1e293b; font-size:0.9rem; }
.avis-note { color:#f59e0b; }
.avis-comment { color:#475569; font-size:0.9rem; }
.similaires-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:1rem; }
.sim-card { background:white; border-radius:10px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.06); text-decoration:none; color:inherit; transition:transform 0.2s; display:block; }
.sim-card:hover { transform:translateY(-3px); }
.sim-card img { width:100%; height:130px; object-fit:cover; }
.sim-card-body { padding:0.75rem; }
.sim-card-titre { font-weight:700; font-size:0.88rem; color:#1e293b; margin-bottom:0.25rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sim-card-prix { color:#0d6efd; font-weight:700; font-size:0.88rem; }
</style>

<div class="oeuvre-detail">

    <div id="loading" style="text-align:center;padding:4rem 0;color:#64748b">Chargement...</div>
    <div id="content" style="display:none">

        <div class="oeuvre-layout">

            {{-- Images --}}
            <div class="oeuvre-images">
                <img id="main-img" class="oeuvre-main-img" src="" alt="">
                <div class="oeuvre-thumbs" id="thumbs"></div>
            </div>

            {{-- Infos --}}
            <div class="oeuvre-info">
                <span id="oeuvre-cat" class="oeuvre-categorie"></span>
                <h1 id="oeuvre-titre" class="oeuvre-titre"></h1>
                <div id="oeuvre-prix" class="oeuvre-prix"></div>
                <p id="oeuvre-desc" class="oeuvre-desc"></p>

                <a id="artisan-link" href="#" class="artisan-mini">
                    <div class="artisan-mini-avatar" id="artisan-avatar"></div>
                    <div>
                        <div class="artisan-mini-name" id="artisan-name"></div>
                        <div class="artisan-mini-spe" id="artisan-spe"></div>
                    </div>
                    <span style="margin-left:auto;color:#0d6efd;font-size:0.85rem;font-weight:600">Voir le profil →</span>
                </a>

                <button id="btn-panier" class="btn btn-panier">🛒 Ajouter au panier</button>
                <button id="btn-favori" class="btn-favori">♡ Ajouter aux favoris</button>
                <div id="action-alert" style="margin-top:0.75rem"></div>
            </div>
        </div>

        {{-- Avis --}}
        <section class="section">
            <div class="section-header"><h2>Avis clients</h2></div>
            <div id="avis-container"><p style="color:#94a3b8">Chargement des avis...</p></div>
        </section>

        {{-- Similaires --}}
        <section class="section">
            <div class="section-header"><h2>Œuvres similaires</h2></div>
            <div class="similaires-grid" id="similaires-grid"></div>
        </section>

    </div>

    <div style="margin-top:2rem">
        <a href="/catalogue" class="card-link">← Retour au catalogue</a>
    </div>
</div>

<script>
const oeuvreId = {{ $id }};
const authToken    = localStorage.getItem('token');
const authHeaders = { 'Accept': 'application/json', 'Authorization': `Bearer ${authToken}` };

function imgSrc(images) {
    if (!images?.length) return 'https://via.placeholder.com/600x400?text=Oeuvre';
    const i = images[0];
    return i.url ?? (i.chemin ? `/storage/${i.chemin}` : 'https://via.placeholder.com/600x400?text=Oeuvre');
}

function showAlert(msg, type = 'error') {
    document.getElementById('action-alert').innerHTML =
        `<div class="alert alert-${type}"><ul><li>${msg}</li></ul></div>`;
}

async function loadOeuvre() {
    try {
        const res  = await fetch(`/api/v1/catalog/oeuvres/${oeuvreId}`, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('Œuvre non trouvée');
        const json = await res.json();
        const o    = json.data ?? json;

        document.getElementById('loading').style.display = 'none';
        document.getElementById('content').style.display = 'block';

        document.getElementById('oeuvre-titre').textContent = o.titre;
        document.getElementById('oeuvre-cat').textContent   = o.categorie?.nom ?? '';
        document.getElementById('oeuvre-prix').textContent  = Number(o.prix).toLocaleString('fr-FR') + ' FCFA';
        document.getElementById('oeuvre-desc').textContent  = o.description ?? '';

        const images  = o.images ?? [];
        const mainImg = document.getElementById('main-img');
        mainImg.src   = imgSrc(images);
        mainImg.alt   = o.titre;

        if (images.length > 1) {
            document.getElementById('thumbs').innerHTML = images.map((img, i) => {
                const src = img.url ?? (img.chemin ? `/storage/${img.chemin}` : '');
                return `<img src="${src}" class="oeuvre-thumb ${i===0?'active':''}"
                    onclick="document.getElementById('main-img').src='${src}';document.querySelectorAll('.oeuvre-thumb').forEach(t=>t.classList.remove('active'));this.classList.add('active')">`;
            }).join('');
        }

        if (o.artisan) {
            const initiales = o.artisan.name?.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) ?? 'A';
            document.getElementById('artisan-avatar').textContent = initiales;
            document.getElementById('artisan-name').textContent   = o.artisan.name ?? '';
            document.getElementById('artisan-spe').textContent    = o.artisan.specialite ?? '';
            document.getElementById('artisan-link').href          = `/catalogue/artisans/${o.artisan.id}`;
        }

        loadAvis();
        loadSimilaires();

    } catch (e) {
        document.getElementById('loading').textContent = 'Œuvre introuvable.';
        console.error(e);
    }
}

async function loadAvis() {
    const container = document.getElementById('avis-container');
    try {
        const res  = await fetch(`/api/v1/catalog/oeuvres/${oeuvreId}/avis`, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        const avis = json.data?.avis ?? json.data ?? json;

        if (!Array.isArray(avis) || !avis.length) {
            container.innerHTML = '<p style="color:#94a3b8">Aucun avis pour cette œuvre.</p>';
            return;
        }
        container.innerHTML = avis.map(a => `
            <div class="avis-item">
                <div class="avis-header">
                    <span class="avis-auteur">${a.acheteur?.utilisateur?.name ?? 'Anonyme'}</span>
                    <span class="avis-note">${'⭐'.repeat(a.note ?? 0)}</span>
                </div>
                <p class="avis-comment">${a.commentaire ?? ''}</p>
            </div>`).join('');
    } catch (e) {
        container.innerHTML = '<p style="color:#94a3b8">Avis non disponibles.</p>';
    }
}

async function loadSimilaires() {
    const grid = document.getElementById('similaires-grid');
    try {
        const res     = await fetch(`/api/v1/catalog/oeuvres/${oeuvreId}/similar`, { headers: { 'Accept': 'application/json' } });
        const json    = await res.json();
        const oeuvres = json.data ?? [];

        if (!Array.isArray(oeuvres) || !oeuvres.length) {
            grid.innerHTML = '<p style="color:#94a3b8">Aucune œuvre similaire.</p>';
            return;
        }
        grid.innerHTML = oeuvres.map(o => `
            <a href="/catalogue/oeuvres/${o.id}" class="sim-card">
                <img src="${o.image ?? 'https://via.placeholder.com/400x300?text=Oeuvre'}" alt="${o.titre}" onerror="this.src='https://via.placeholder.com/400x300'">
                <div class="sim-card-body">
                    <div class="sim-card-titre">${o.titre}</div>
                    <div class="sim-card-prix">${Number(o.prix).toLocaleString('fr-FR')} FCFA</div>
                </div>
            </a>`).join('');
    } catch (e) { console.error(e); }
}

async function getXsrf() {
    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
    return decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');
}

// ── Ajouter au panier ─────────────────────────────────────────────────────────
document.getElementById('btn-panier').addEventListener('click', async function() {
    if (!authToken) {
        showAlert('Connectez-vous pour ajouter au panier. <a href="/auth/login" style="font-weight:700">Se connecter →</a>');
        return;
    }
    this.disabled    = true;
    this.textContent = 'Ajout en cours...';
    const xsrf = await getXsrf();
    try {
        const res  = await fetch('/api/v1/acheteur/panier', {
            method: 'POST',
            headers: { ...authHeaders, 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            body: JSON.stringify({ oeuvre_id: oeuvreId, quantite: 1 }),
        });
        const json = await res.json();
        if (res.ok) {
            // ✅ Lien vers le panier après ajout
            showAlert(
                'Ajouté au panier ✓ &nbsp;—&nbsp; <a href="/panier" style="font-weight:700;color:#15803d">Voir mon panier →</a>',
                'success'
            );
            this.textContent = '✓ Dans le panier';
        } else {
            showAlert(json.message ?? 'Erreur.');
            this.disabled    = false;
            this.textContent = '🛒 Ajouter au panier';
        }
    } catch (e) {
        showAlert('Erreur réseau.');
        this.disabled    = false;
        this.textContent = '🛒 Ajouter au panier';
    }
});

// ── Ajouter aux favoris ───────────────────────────────────────────────────────
document.getElementById('btn-favori').addEventListener('click', async function() {
    if (!authToken) {
        showAlert('Connectez-vous pour ajouter aux favoris. <a href="/auth/login" style="font-weight:700">Se connecter →</a>');
        return;
    }
    this.disabled    = true;
    this.textContent = 'Ajout en cours...';
    const xsrf = await getXsrf();
    try {
        const res  = await fetch('/api/v1/acheteur/favoris', {
            method: 'POST',
            headers: { ...authHeaders, 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            body: JSON.stringify({ oeuvre_id: oeuvreId }),
        });
        const json = await res.json();
        if (res.ok) {
            // ✅ Confirmation favoris
            showAlert('Ajouté aux favoris ✓', 'success');
            this.textContent = '♥ Dans vos favoris';
        } else {
            showAlert(json.message ?? 'Erreur.');
            this.disabled    = false;
            this.textContent = '♡ Ajouter aux favoris';
        }
    } catch (e) {
        showAlert('Erreur réseau.');
        this.disabled    = false;
        this.textContent = '♡ Ajouter aux favoris';
    }
});

loadOeuvre();
</script>

@endsection
