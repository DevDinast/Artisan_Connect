@extends('layouts.app')

@section('title', 'Détail de l\'œuvre - ArtisanConnect')

@section('content')

<div class="container" style="max-width: 900px; margin: auto">

    <div id="loading" class="text-center py-12 text-gray-400">Chargement...</div>
    <div id="content" style="display:none">

        <div style="display: flex; flex-wrap: wrap; gap: 2rem; margin-bottom: 2rem">

            {{-- Image principale --}}
            <div style="flex: 1; min-width: 280px">
                <img id="oeuvre-image" src="" alt=""
                     style="width:100%; border-radius: 0.75rem; object-fit: cover; max-height: 400px">
                <div id="images-thumbnails" style="display:flex; gap:0.5rem; margin-top:0.5rem; flex-wrap:wrap"></div>
            </div>

            {{-- Infos --}}
            <div style="flex: 1; min-width: 280px">
                <h1 id="oeuvre-titre" style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.5rem"></h1>
                <p id="oeuvre-categorie" class="artisan-tag" style="margin-bottom: 1rem"></p>
                <p id="oeuvre-prix" style="font-size: 1.5rem; font-weight: 700; color: #2563eb; margin-bottom: 1rem"></p>
                <p id="oeuvre-description" style="color: #555; line-height: 1.7; margin-bottom: 1.5rem"></p>

                {{-- Artisan --}}
                <div id="artisan-card" style="background: #f9fafb; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; display:flex; align-items:center; gap: 1rem">
                    <div id="artisan-avatar" class="dashboard-avatar" style="width:3rem;height:3rem;font-size:1rem;flex-shrink:0"></div>
                    <div>
                        <p id="artisan-name" style="font-weight: 600"></p>
                        <p id="artisan-specialite" style="color: #888; font-size: 0.85rem"></p>
                    </div>
                    <a id="artisan-link" href="#" class="card-link" style="margin-left:auto">Voir le profil →</a>
                </div>

                <button id="btn-panier" class="btn btn-full" style="margin-bottom: 0.75rem">
                    🛒 Ajouter au panier
                </button>
                <button id="btn-favori" class="btn-outline" style="width:100%; padding: 0.6rem; border-radius: 0.5rem; cursor:pointer">
                    ♡ Ajouter aux favoris
                </button>

                <div id="action-alert" style="margin-top: 0.75rem"></div>
            </div>
        </div>

        {{-- Avis --}}
        <section class="section">
            <div class="section-header">
                <h2>Avis clients</h2>
            </div>
            <div id="avis-container">
                <p class="text-gray-400">Chargement des avis...</p>
            </div>
        </section>

        {{-- Œuvres similaires --}}
        <section class="section">
            <div class="section-header">
                <h2>Œuvres similaires</h2>
            </div>
            <div id="similaires-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6"></div>
        </section>

    </div>

    <div style="margin-top: 2rem">
        <a href="{{ route('catalogue.categories') }}" class="card-link">← Retour au catalogue</a>
    </div>
</div>

<script>
const oeuvreId = {{ $id }};
const token    = localStorage.getItem('token');

const authHeaders = {
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`,
};

async function loadOeuvre() {
    try {
        const res  = await fetch(`/api/v1/catalog/oeuvres/${oeuvreId}`, {
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) throw new Error('Œuvre non trouvée');
        const json = await res.json();
        const o    = json.data ?? json;

        document.getElementById('loading').classList.add('hidden');
        document.getElementById('content').style.display = 'block';

        document.getElementById('oeuvre-titre').textContent      = o.titre;
        document.getElementById('oeuvre-categorie').textContent  = o.categorie?.nom ?? '';
        document.getElementById('oeuvre-prix').textContent       = Number(o.prix).toLocaleString('fr-FR') + ' FCFA';
        document.getElementById('oeuvre-description').textContent = o.description ?? '';

        // Images
        const images = o.images ?? [];
        if (images.length) {
            document.getElementById('oeuvre-image').src = images[0].url;
            if (images.length > 1) {
                document.getElementById('images-thumbnails').innerHTML = images.map((img, i) => `
                    <img src="${img.url}" onclick="document.getElementById('oeuvre-image').src='${img.url}'"
                         style="width:60px;height:60px;object-fit:cover;border-radius:0.25rem;cursor:pointer;border:2px solid ${i===0?'#2563eb':'#eee'}">
                `).join('');
            }
        } else {
            document.getElementById('oeuvre-image').src = 'https://via.placeholder.com/600x400?text=Oeuvre';
        }

        // Artisan
        if (o.artisan) {
            const initiales = o.artisan.name?.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) ?? 'A';
            document.getElementById('artisan-avatar').textContent = initiales;
            document.getElementById('artisan-name').textContent   = o.artisan.name;
            document.getElementById('artisan-specialite').textContent = o.artisan.specialite ?? '';
            document.getElementById('artisan-link').href = `/catalogue/artisans/${o.artisan.id}`;
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
        const res  = await fetch(`/api/v1/catalog/oeuvres/${oeuvreId}/avis`, {
            headers: { 'Accept': 'application/json' }
        });
        const json = await res.json();
        const avis = json.data ?? json;

        if (!Array.isArray(avis) || !avis.length) {
            container.innerHTML = '<p class="text-gray-400">Aucun avis pour cette œuvre.</p>';
            return;
        }

        container.innerHTML = avis.map(a => `
            <div style="border-bottom: 1px solid #eee; padding: 1rem 0">
                <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem">
                    <strong>${a.user?.name ?? 'Anonyme'}</strong>
                    <span style="color:#f59e0b">${'⭐'.repeat(a.note ?? 0)}</span>
                </div>
                <p style="color:#555">${a.commentaire ?? ''}</p>
            </div>
        `).join('');

    } catch (e) {
        container.innerHTML = '<p class="text-gray-400">Avis non disponibles.</p>';
    }
}

async function loadSimilaires() {
    const grid = document.getElementById('similaires-grid');
    try {
        const res     = await fetch(`/api/v1/catalog/oeuvres/${oeuvreId}/similar`, {
            headers: { 'Accept': 'application/json' }
        });
        const json    = await res.json();
        const oeuvres = json.data ?? json;

        if (!Array.isArray(oeuvres) || !oeuvres.length) {
            grid.innerHTML = '<p class="text-gray-400">Aucune œuvre similaire.</p>';
            return;
        }

        grid.innerHTML = oeuvres.map(o => `
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="${o.image ?? 'https://via.placeholder.com/400x300'}" class="w-full h-40 object-cover">
                <div class="p-3">
                    <h3 class="font-semibold mb-1">${o.titre}</h3>
                    <p class="text-gray-800 font-bold mb-2">${Number(o.prix).toLocaleString('fr-FR')} FCFA</p>
                    <a href="{{ route('catalogue.oeuvre', '') }}/${o.id}"
                       class="block text-center bg-blue-500 text-white py-1 rounded hover:bg-blue-600 transition text-sm">
                        Voir
                    </a>
                </div>
            </div>
        `).join('');

    } catch (e) {
        console.error(e);
    }
}

// Ajouter au panier
document.getElementById('btn-panier').addEventListener('click', async function() {
    const alertBox = document.getElementById('action-alert');
    if (!token) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Connectez-vous pour ajouter au panier.</li></ul></div>`;
        return;
    }
    try {
        await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
        const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');

        const res = await fetch('/api/v1/acheteur/panier', {
            method: 'POST',
            headers: { ...authHeaders, 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            body: JSON.stringify({ oeuvre_id: oeuvreId, quantite: 1 }),
        });
        const json = await res.json();
        alertBox.innerHTML = res.ok
            ? `<div class="alert alert-success"><ul><li>Ajouté au panier ✓</li></ul></div>`
            : `<div class="alert alert-error"><ul><li>${json.message ?? 'Erreur.'}</li></ul></div>`;
    } catch (e) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Erreur réseau.</li></ul></div>`;
    }
});

// Ajouter aux favoris
document.getElementById('btn-favori').addEventListener('click', async function() {
    const alertBox = document.getElementById('action-alert');
    if (!token) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Connectez-vous pour ajouter aux favoris.</li></ul></div>`;
        return;
    }
    try {
        await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
        const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');

        const res = await fetch('/api/v1/acheteur/favoris', {
            method: 'POST',
            headers: { ...authHeaders, 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            body: JSON.stringify({ oeuvre_id: oeuvreId }),
        });
        const json = await res.json();
        alertBox.innerHTML = res.ok
            ? `<div class="alert alert-success"><ul><li>Ajouté aux favoris ✓</li></ul></div>`
            : `<div class="alert alert-error"><ul><li>${json.message ?? 'Erreur.'}</li></ul></div>`;
    } catch (e) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Erreur réseau.</li></ul></div>`;
    }
});

loadOeuvre();
</script>

@endsection
