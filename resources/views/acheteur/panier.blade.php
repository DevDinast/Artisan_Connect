@extends('layouts.app')

@section('title', 'Mon panier - ArtisanConnect')

@section('content')

<style>
.panier-layout { display:flex; flex-wrap:wrap; gap:2rem; align-items:flex-start; }
.panier-items { flex:2; min-width:300px; }
.panier-resume { flex:1; min-width:260px; position:sticky; top:2rem; }

.panier-item {
    background:white; border-radius:12px; padding:1.25rem;
    box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:1rem;
    display:flex; gap:1rem; align-items:center;
}
.panier-item-img { width:90px; height:90px; object-fit:cover; border-radius:8px; flex-shrink:0; background:#f1f5f9; }
.panier-item-info { flex:1; }
.panier-item-titre { font-weight:700; color:#1e293b; font-size:0.97rem; margin-bottom:0.25rem; }
.panier-item-artisan { color:#64748b; font-size:0.82rem; margin-bottom:0.5rem; }
.panier-item-prix { color:#0d6efd; font-weight:700; font-size:1rem; }
.panier-item-actions { display:flex; align-items:center; gap:0.75rem; margin-top:0.75rem; flex-wrap:wrap; }
.qty-btn { width:28px; height:28px; border-radius:6px; border:2px solid #e2e8f0; background:white; font-size:1rem; cursor:pointer; display:flex; align-items:center; justify-content:center; font-weight:700; color:#374151; transition:all 0.2s; }
.qty-btn:hover { border-color:#0d6efd; color:#0d6efd; }
.qty-val { font-weight:700; color:#1e293b; min-width:24px; text-align:center; }
.btn-retirer { background:none; border:none; color:#ef4444; font-size:0.82rem; font-weight:600; cursor:pointer; padding:0.25rem 0.5rem; border-radius:6px; transition:background 0.2s; }
.btn-retirer:hover { background:#fef2f2; }

.resume-card { background:white; border-radius:12px; padding:1.5rem; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
.resume-card h3 { font-size:1.1rem; font-weight:700; color:#1e293b; margin-bottom:1.25rem; padding-bottom:0.75rem; border-bottom:1px solid #f1f5f9; }
.resume-ligne { display:flex; justify-content:space-between; margin-bottom:0.75rem; font-size:0.92rem; color:#475569; }
.resume-total { display:flex; justify-content:space-between; font-size:1.1rem; font-weight:700; color:#1e293b; padding-top:0.75rem; border-top:2px solid #f1f5f9; margin-top:0.5rem; }
.resume-total span:last-child { color:#0d6efd; }
</style>

<div style="margin-bottom:1.5rem">
    <h1 style="font-size:1.6rem;font-weight:700;color:#1e293b;letter-spacing:-0.3px">🛒 Mon panier</h1>
    <p style="color:#64748b;font-size:0.95rem">Vérifiez vos articles avant de commander</p>
</div>

<div id="loading" style="text-align:center;padding:3rem 0;color:#64748b">Chargement du panier...</div>
<div id="empty" style="display:none" class="empty-state">
    <span class="empty-icon">🛒</span>
    <h3>Votre panier est vide</h3>
    <p>Découvrez nos œuvres et ajoutez-en à votre panier.</p>
    <a href="/catalogue" class="btn" style="margin-top:1rem">Explorer le catalogue</a>
</div>
<div id="alert-box"></div>

<div id="panier-content" style="display:none">
    <div class="panier-layout">
        <div class="panier-items" id="panier-items"></div>
        <div class="panier-resume">
            <div class="resume-card">
                <h3>Récapitulatif</h3>
                <div id="resume-lignes"></div>
                <div class="resume-total">
                    <span>Total</span>
                    <span id="resume-total">0 FCFA</span>
                </div>
                <button id="btn-commander" class="btn btn-full" style="margin-top:1.5rem;font-size:1rem">
                    Passer la commande →
                </button>
                <a href="/catalogue" style="display:block;text-align:center;margin-top:0.75rem;color:#64748b;font-size:0.85rem">
                    ← Continuer mes achats
                </a>
            </div>
        </div>
    </div>
</div>

<script>
const token = localStorage.getItem('token');
const authHeaders = { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` };
let panierItems = [];

async function getXsrf() {
    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
    return decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');
}

function showAlert(msg, type='error') {
    document.getElementById('alert-box').innerHTML = `<div class="alert alert-${type}"><ul><li>${msg}</li></ul></div>`;
}

// ── Charger le panier ─────────────────────────────────────────────────────────
async function loadPanier() {
    if (!token) {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('empty').style.display = 'block';
        return;
    }

    try {
        const res  = await fetch('/api/v1/acheteur/panier', { headers: authHeaders, credentials: 'include' });
        const json = await res.json();
        panierItems = json.data?.items ?? json.data ?? [];

        document.getElementById('loading').style.display = 'none';

        if (!Array.isArray(panierItems) || !panierItems.length) {
            document.getElementById('empty').style.display = 'block';
            return;
        }

        document.getElementById('panier-content').style.display = 'block';
        renderPanier();

    } catch (e) {
        document.getElementById('loading').style.display = 'none';
        showAlert('Erreur chargement du panier.');
        console.error(e);
    }
}

// ── Afficher les articles ─────────────────────────────────────────────────────
function renderPanier() {
    const container = document.getElementById('panier-items');
    const lignes    = document.getElementById('resume-lignes');
    let total = 0;

    container.innerHTML = panierItems.map(item => {
        const oeuvre  = item.oeuvre ?? {};
        const images  = oeuvre.images ?? [];
        const img     = images[0]?.url ?? (images[0]?.chemin ? `/storage/${images[0].chemin}` : 'https://via.placeholder.com/90x90?text=Oeuvre');
        const prix    = Number(oeuvre.prix ?? 0);
        const sousTotal = prix * (item.quantite ?? 1);
        total += sousTotal;

        return `
        <div class="panier-item" id="item-${item.id}">
            <img src="${img}" class="panier-item-img" onerror="this.src='https://via.placeholder.com/90x90'">
            <div class="panier-item-info">
                <div class="panier-item-titre">${oeuvre.titre ?? '—'}</div>
                <div class="panier-item-artisan">${oeuvre.artisan?.user?.name ?? oeuvre.artisan?.name ?? ''}</div>
                <div class="panier-item-prix">${prix.toLocaleString('fr-FR')} FCFA / unité</div>
                <div class="panier-item-actions">
                    <button class="qty-btn" onclick="modifierQty(${item.id}, ${(item.quantite ?? 1) - 1})">−</button>
                    <span class="qty-val">${item.quantite ?? 1}</span>
                    <button class="qty-btn" onclick="modifierQty(${item.id}, ${(item.quantite ?? 1) + 1})">+</button>
                    <span style="color:#64748b;font-size:0.85rem">= ${sousTotal.toLocaleString('fr-FR')} FCFA</span>
                    <button class="btn-retirer" onclick="retirerItem(${item.id})">🗑 Retirer</button>
                </div>
            </div>
        </div>`;
    }).join('');

    // Récapitulatif
    lignes.innerHTML = panierItems.map(item => {
        const prix = Number(item.oeuvre?.prix ?? 0);
        return `<div class="resume-ligne"><span>${item.oeuvre?.titre ?? '—'} ×${item.quantite ?? 1}</span><span>${(prix * (item.quantite ?? 1)).toLocaleString('fr-FR')} FCFA</span></div>`;
    }).join('');

    document.getElementById('resume-total').textContent = total.toLocaleString('fr-FR') + ' FCFA';
}

// ── Modifier quantité ─────────────────────────────────────────────────────────
async function modifierQty(id, nouvelleQty) {
    if (nouvelleQty < 1) { retirerItem(id); return; }
    const xsrf = await getXsrf();
    try {
        const res = await fetch(`/api/v1/acheteur/panier/${id}`, {
            method: 'PUT',
            headers: { ...authHeaders, 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            body: JSON.stringify({ quantite: nouvelleQty }),
        });
        if (res.ok) {
            const item = panierItems.find(i => i.id === id);
            if (item) item.quantite = nouvelleQty;
            renderPanier();
        } else {
            const json = await res.json();
            showAlert(json.message ?? 'Erreur modification quantité.');
        }
    } catch (e) { showAlert('Erreur réseau.'); }
}

// ── Retirer un article ────────────────────────────────────────────────────────
async function retirerItem(id) {
    if (!confirm('Retirer cet article du panier ?')) return;
    const xsrf = await getXsrf();
    try {
        const res = await fetch(`/api/v1/acheteur/panier/${id}`, {
            method: 'DELETE',
            headers: { ...authHeaders, 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
        });
        if (res.ok) {
            panierItems = panierItems.filter(i => i.id !== id);
            if (!panierItems.length) {
                document.getElementById('panier-content').style.display = 'none';
                document.getElementById('empty').style.display = 'block';
            } else {
                renderPanier();
            }
        }
    } catch (e) { showAlert('Erreur réseau.'); }
}

// ── Passer commande ───────────────────────────────────────────────────────────
document.getElementById('btn-commander').addEventListener('click', async function() {
    this.disabled = true;
    this.textContent = 'Création de la commande...';

    const xsrf = await getXsrf();
    try {
        // Créer une commande par œuvre
        const promises = panierItems.map(item =>
            fetch('/api/v1/acheteur/commandes', {
                method: 'POST',
                headers: { ...authHeaders, 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
                credentials: 'include',
                body: JSON.stringify({ oeuvre_id: item.oeuvre_id ?? item.oeuvre?.id, quantite: item.quantite ?? 1 }),
            }).then(r => r.json())
        );

        const results = await Promise.all(promises);
        const errors  = results.filter(r => !r.success);

        if (errors.length) {
            showAlert('Certaines commandes ont échoué : ' + errors.map(e => e.message).join(', '));
            this.disabled = false;
            this.textContent = 'Passer la commande →';
            return;
        }

        // Vider le panier localement
        const deletes = panierItems.map(item =>
            fetch(`/api/v1/acheteur/panier/${item.id}`, {
                method: 'DELETE',
                headers: { ...authHeaders, 'X-XSRF-TOKEN': xsrf },
                credentials: 'include',
            })
        );
        await Promise.all(deletes);

        showAlert('Commande(s) créée(s) avec succès ! Redirection...', 'success');
        setTimeout(() => { window.location.href = '/commandes'; }, 1500);

    } catch (e) {
        showAlert('Erreur réseau. Réessayez.');
        this.disabled = false;
        this.textContent = 'Passer la commande →';
        console.error(e);
    }
});

loadPanier();
</script>

@endsection
