@extends('layouts.app')

@section('title', 'Dashboard Admin - ArtisanConnect')

@section('content')

<div class="dashboard">

    <div class="dashboard-hero">
        <div class="dashboard-avatar" style="background:#7c3aed">AD</div>
        <div>
            <h1>Dashboard Administrateur</h1>
            <p>Gérez les œuvres en attente de validation.</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="dashboard-stats">
        <div class="stat-card">
            <span class="stat-num" id="stat-attente">—</span>
            <span class="stat-label">En attente</span>
        </div>
        <div class="stat-card">
            <span class="stat-num" id="stat-validees">—</span>
            <span class="stat-label">Validées</span>
        </div>
        <div class="stat-card">
            <span class="stat-num" id="stat-artisans">—</span>
            <span class="stat-label">Artisans</span>
        </div>
        <div class="stat-card">
            <span class="stat-num" id="stat-ca">—</span>
            <span class="stat-label">CA total</span>
        </div>
    </div>

    {{-- Œuvres en attente --}}
    <section class="section">
        <div class="section-header">
            <h2>Œuvres en attente de validation</h2>
            <p class="section-sub">Validez ou refusez les créations soumises par les artisans</p>
        </div>
        <div id="loading" class="text-center py-8 text-gray-400">Chargement...</div>
        <div id="empty" class="text-center py-8 text-gray-400 hidden">✅ Aucune œuvre en attente.</div>
        <div id="oeuvres-container"></div>
    </section>

</div>

{{-- Modal refus --}}
<div id="modal-refus" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:100;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:0.75rem;padding:2rem;max-width:500px;width:90%">
        <h3 style="margin-bottom:1rem">Motif de refus <span style="color:red">*</span></h3>
        <p style="color:#888;font-size:0.85rem;margin-bottom:0.75rem">Minimum 10 caractères</p>
        <textarea id="motif-refus" rows="4" placeholder="Expliquez pourquoi cette œuvre est refusée..."
            style="width:100%;padding:0.6rem;border:1px solid #ddd;border-radius:0.5rem;resize:vertical;margin-bottom:1rem"></textarea>
        <div id="modal-alert" style="margin-bottom:0.75rem"></div>
        <div style="display:flex;gap:1rem">
            <button id="btn-confirmer-refus" class="btn" style="background:#ef4444;border-color:#ef4444;flex:1">Confirmer le refus</button>
            <button onclick="fermerModal()" class="btn" style="background:#6b7280;border-color:#6b7280;flex:1">Annuler</button>
        </div>
    </div>
</div>

<script>
const authToken = localStorage.getItem('token');
const authHeaders = { 'Accept': 'application/json', 'Authorization': `Bearer ${authToken}` };
let oeuvreARefuserID = null;

// ── Stats admin ───────────────────────────────────────────────────────────────
async function loadStats() {
    try {
        const res  = await fetch('/api/v1/admin/dashboard', { headers: authHeaders, credentials: 'include' });
        const json = await res.json();
        const d    = json.data?.stats ?? json.data ?? json;

        document.getElementById('stat-attente').textContent  = d.oeuvres_en_attente ?? 0;
        document.getElementById('stat-validees').textContent = d.oeuvres_validees   ?? 0;
        document.getElementById('stat-artisans').textContent = d.total_artisans     ?? 0;

        const ca = Number(d.ca_total ?? 0).toLocaleString('fr-FR');
        document.getElementById('stat-ca').textContent = ca + ' FCFA';

    } catch (e) { console.error(e); }
}

// ── Œuvres en attente ─────────────────────────────────────────────────────────
async function loadOeuvresEnAttente() {
    const loading   = document.getElementById('loading');
    const empty     = document.getElementById('empty');
    const container = document.getElementById('oeuvres-container');

    try {
        const res     = await fetch('/api/v1/admin/oeuvres/en-attente', { headers: authHeaders, credentials: 'include' });
        const json    = await res.json();
        const oeuvres = json.data?.oeuvres ?? json.data ?? [];

        loading.classList.add('hidden');

        if (!Array.isArray(oeuvres) || !oeuvres.length) {
            empty.classList.remove('hidden');
            return;
        }

        container.innerHTML = oeuvres.map(o => {
            const image     = o.images?.[0]?.url ?? 'https://via.placeholder.com/400x300?text=Oeuvre';
            const prix      = Number(o.prix).toLocaleString('fr-FR') + ' FCFA';
            // ✅ artisan peut être lié via artisan.user (relation imbriquée)
            const artisan   = o.artisan?.user?.name ?? o.artisan?.name ?? '—';
            const categorie = o.categorie?.name ?? '—';
            const date      = o.created_at ? new Date(o.created_at).toLocaleDateString('fr-FR') : '—';
            const desc      = o.description ? o.description.substring(0, 150) + (o.description.length > 150 ? '...' : '') : '';

            return `
            <div style="background:#fff;border-radius:0.75rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);display:flex;flex-wrap:wrap;margin-bottom:1rem;overflow:hidden">
                <img src="${image}" alt="${o.titre}" style="width:180px;height:140px;object-fit:cover;flex-shrink:0">
                <div style="flex:1;padding:1rem;min-width:200px">
                    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:0.5rem">
                        <h3 style="font-size:1.1rem;font-weight:600">${o.titre}</h3>
                        <span style="background:#fef3c7;color:#d97706;padding:0.2rem 0.6rem;border-radius:1rem;font-size:0.75rem">En attente</span>
                    </div>
                    <p style="color:#555;font-size:0.9rem;margin-bottom:0.2rem"><strong>Artisan :</strong> ${artisan}</p>
                    <p style="color:#555;font-size:0.9rem;margin-bottom:0.2rem"><strong>Catégorie :</strong> ${categorie}</p>
                    <p style="color:#555;font-size:0.9rem;margin-bottom:0.2rem"><strong>Prix :</strong> ${prix}</p>
                    <p style="color:#555;font-size:0.9rem;margin-bottom:0.5rem"><strong>Soumise le :</strong> ${date}</p>
                    ${desc ? `<p style="color:#666;font-size:0.85rem;margin-bottom:0.75rem">${desc}</p>` : ''}
                    <div style="display:flex;gap:0.75rem">
                        <button onclick="valider(${o.id})"
                            style="background:#10b981;color:#fff;border:none;padding:0.5rem 1.25rem;border-radius:0.5rem;cursor:pointer;font-weight:600">
                            ✓ Valider
                        </button>
                        <button onclick="ouvrirModalRefus(${o.id})"
                            style="background:#ef4444;color:#fff;border:none;padding:0.5rem 1.25rem;border-radius:0.5rem;cursor:pointer;font-weight:600">
                            ✗ Refuser
                        </button>
                    </div>
                </div>
            </div>`;
        }).join('');

    } catch (e) {
        loading.classList.add('hidden');
        container.innerHTML = '<p style="color:#ef4444">Erreur chargement des œuvres.</p>';
        console.error(e);
    }
}

// ── Valider ───────────────────────────────────────────────────────────────────
async function valider(id) {
    if (!confirm('Valider cette œuvre ?')) return;

    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
    const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');

    try {
        const res  = await fetch(`/api/v1/admin/oeuvres/${id}/valider`, {
            method: 'PUT',
            headers: { ...authHeaders, 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            // ✅ Envoyer double_validation_confirmee pour les œuvres > 500 000 FCFA
            body: JSON.stringify({ double_validation_confirmee: true }),
        });
        const json = await res.json();

        if (res.ok) {
            loadOeuvresEnAttente();
            loadStats();
        } else {
            alert(json.message ?? 'Erreur lors de la validation.');
        }
    } catch (e) { alert('Erreur réseau.'); }
}

// ── Refuser ───────────────────────────────────────────────────────────────────
function ouvrirModalRefus(id) {
    oeuvreARefuserID = id;
    document.getElementById('motif-refus').value = '';
    document.getElementById('modal-alert').innerHTML = '';
    document.getElementById('modal-refus').style.display = 'flex';
}

function fermerModal() {
    document.getElementById('modal-refus').style.display = 'none';
    oeuvreARefuserID = null;
}

document.getElementById('btn-confirmer-refus').addEventListener('click', async function() {
    if (!oeuvreARefuserID) return;

    // ✅ Le backend attend 'motif_refus' avec minimum 10 caractères
    const motif = document.getElementById('motif-refus').value.trim();
    const modalAlert = document.getElementById('modal-alert');

    if (motif.length < 10) {
        modalAlert.innerHTML = `<p style="color:#ef4444;font-size:0.85rem">Le motif doit contenir au moins 10 caractères.</p>`;
        return;
    }

    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
    const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');

    try {
        const res  = await fetch(`/api/v1/admin/oeuvres/${oeuvreARefuserID}/refuser`, {
            method: 'PUT',
            headers: { ...authHeaders, 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            // ✅ CORRECTION : 'motif_refus' correspond au champ validé dans le backend
            body: JSON.stringify({ motif_refus: motif }),
        });
        const json = await res.json();

        if (res.ok) {
            fermerModal();
            loadOeuvresEnAttente();
            loadStats();
        } else {
            modalAlert.innerHTML = `<p style="color:#ef4444;font-size:0.85rem">${json.message ?? 'Erreur.'}</p>`;
        }
    } catch (e) {
        modalAlert.innerHTML = `<p style="color:#ef4444;font-size:0.85rem">Erreur réseau.</p>`;
    }
});

// ── Init ──────────────────────────────────────────────────────────────────────
loadStats();
loadOeuvresEnAttente();
</script>

@endsection
