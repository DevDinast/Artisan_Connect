@extends('layouts.app')
@section('title', 'Détail de l\'œuvre - ArtisanConnect')
@section('content')

<div class="oeuvre-detail">
    <div id="loading" style="text-align:center;padding:4rem 0;color:var(--text-mid)">Chargement...</div>
    <div id="content" style="display:none">
        <div class="oeuvre-layout">
            <div class="oeuvre-images">
                <img id="main-img" class="oeuvre-main-img" src="" alt="">
                <div class="oeuvre-thumbs" id="thumbs"></div>
            </div>
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
                    <span style="margin-left:auto;color:var(--terra);font-size:0.82rem;font-weight:600">Voir le profil →</span>
                </a>
                <button id="btn-panier" class="btn btn-panier">🛒 Ajouter au panier</button>
                <button id="btn-favori" class="btn-favori">♡ Ajouter aux favoris</button>
                <div id="action-alert" style="margin-top:0.75rem"></div>
            </div>
        </div>
        <section class="section">
            <div class="section-header"><h2>Avis clients</h2></div>
            <div id="avis-container"><p style="color:var(--text-light)">Chargement des avis...</p></div>
        </section>
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
const oeuvreId  = {{ $id }};
const authToken = localStorage.getItem('token');
const authHeaders = { 'Accept': 'application/json', 'Authorization': `Bearer ${authToken}` };

function imgSrc(images) {
    if (!images?.length) return 'https://via.placeholder.com/600x400?text=Oeuvre';
    const i = images[0];
    return i.url ?? (i.chemin ? `/storage/${i.chemin}` : 'https://via.placeholder.com/600x400?text=Oeuvre');
}

function showAlert(msg, type = 'error') {
    document.getElementById('action-alert').innerHTML = `<div class="alert alert-${type}"><ul><li>${msg}</li></ul></div>`;
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
        document.getElementById('oeuvre-cat').textContent   = o.categorie?.nom ?? o.categorie?.name ?? '';
        document.getElementById('oeuvre-prix').textContent  = Number(o.prix).toLocaleString('fr-FR') + ' FCFA';
        document.getElementById('oeuvre-desc').textContent  = o.description ?? '';

        const images  = o.images ?? [];
        const mainImg = document.getElementById('main-img');
        mainImg.src   = imgSrc(images);
        mainImg.alt   = o.titre;

        if (images.length > 1) {
            document.getElementById('thumbs').innerHTML = images.map((img, i) => {
                const src = img.url ?? (img.chemin ? `/storage/${img.chemin}` : '');
                return `<img src="${src}" class="oeuvre-thumb ${i===0?'active':''}" onclick="document.getElementById('main-img').src='${src}';document.querySelectorAll('.oeuvre-thumb').forEach(t=>t.classList.remove('active'));this.classList.add('active')">`;
            }).join('');
        }

        if (o.artisan) {
            const initiales = o.artisan.name?.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) ?? 'A';
            document.getElementById('artisan-avatar').textContent = initiales;
            document.getElementById('artisan-name').textContent   = o.artisan.name ?? '';
            document.getElementById('artisan-spe').textContent    = o.artisan.specialite ?? '';
            document.getElementById('artisan-link').href          = `/catalogue/artisans/${o.artisan.id}`;
        }
        loadAvis(); loadSimilaires();
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
        if (!Array.isArray(avis) || !avis.length) { container.innerHTML = '<p style="color:var(--text-light)">Aucun avis pour cette œuvre.</p>'; return; }
        container.innerHTML = avis.map(a => `
            <div class="avis-item">
                <div class="avis-header">
                    <span class="avis-auteur">${a.acheteur?.utilisateur?.name ?? 'Anonyme'}</span>
                    <span class="avis-note">${'⭐'.repeat(a.note ?? 0)}</span>
                </div>
                <p class="avis-comment">${a.commentaire ?? ''}</p>
            </div>`).join('');
    } catch (e) { container.innerHTML = '<p style="color:var(--text-light)">Avis non disponibles.</p>'; }
}

async function loadSimilaires() {
    const grid = document.getElementById('similaires-grid');
    try {
        const res     = await fetch(`/api/v1/catalog/oeuvres/${oeuvreId}/similar`, { headers: { 'Accept': 'application/json' } });
        const json    = await res.json();
        const oeuvres = json.data ?? [];
        if (!Array.isArray(oeuvres) || !oeuvres.length) { grid.innerHTML = '<p style="color:var(--text-light)">Aucune œuvre similaire.</p>'; return; }
        grid.innerHTML = oeuvres.map(o => `
            <a href="/catalogue/oeuvres/${o.id}" class="sim-card">
                <img src="${o.image ?? 'https://via.placeholder.com/400x300'}" alt="${o.titre}" loading="lazy" onerror="this.src='https://via.placeholder.com/400x300'">
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

document.getElementById('btn-panier').addEventListener('click', async function() {
    if (!authToken) { showAlert('Connectez-vous pour ajouter au panier. <a href="/auth/login" style="font-weight:700">Se connecter →</a>'); return; }
    this.disabled = true; this.textContent = 'Ajout en cours...';
    const xsrf = await getXsrf();
    try {
        const res  = await fetch('/api/v1/acheteur/panier', { method:'POST', headers:{...authHeaders,'Content-Type':'application/json','X-XSRF-TOKEN':xsrf}, credentials:'include', body:JSON.stringify({oeuvre_id:oeuvreId,quantite:1}) });
        const json = await res.json();
        if (res.ok) { showAlert('Ajouté au panier ✓ — <a href="/panier" style="font-weight:700;color:var(--vert)">Voir mon panier →</a>', 'success'); this.textContent = '✓ Dans le panier'; }
        else { showAlert(json.message ?? 'Erreur.'); this.disabled=false; this.textContent='🛒 Ajouter au panier'; }
    } catch (e) { showAlert('Erreur réseau.'); this.disabled=false; this.textContent='🛒 Ajouter au panier'; }
});

document.getElementById('btn-favori').addEventListener('click', async function() {
    if (!authToken) { showAlert('Connectez-vous pour ajouter aux favoris. <a href="/auth/login" style="font-weight:700">Se connecter →</a>'); return; }
    this.disabled = true; this.textContent = 'Ajout en cours...';
    const xsrf = await getXsrf();
    try {
        const res  = await fetch('/api/v1/acheteur/favoris', { method:'POST', headers:{...authHeaders,'Content-Type':'application/json','X-XSRF-TOKEN':xsrf}, credentials:'include', body:JSON.stringify({oeuvre_id:oeuvreId}) });
        const json = await res.json();
        if (res.ok) { showAlert('Ajouté aux favoris ✓', 'success'); this.textContent = '♥ Dans vos favoris'; }
        else { showAlert(json.message ?? 'Erreur.'); this.disabled=false; this.textContent='♡ Ajouter aux favoris'; }
    } catch (e) { showAlert('Erreur réseau.'); this.disabled=false; this.textContent='♡ Ajouter aux favoris'; }
});

loadOeuvre();
</script>

@endsection
