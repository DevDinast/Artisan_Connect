@extends('layouts.app')

@section('title', 'Mes commandes - ArtisanConnect')

@section('content')

<style>
.commande-card {
    background:white; border-radius:12px; padding:1.5rem;
    box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:1.25rem;
    transition:box-shadow 0.2s;
}
.commande-card:hover { box-shadow:0 6px 16px rgba(0,0,0,0.1); }
.commande-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem; margin-bottom:1rem; padding-bottom:0.75rem; border-bottom:1px solid #f1f5f9; }
.commande-ref { font-weight:700; color:#1e293b; font-size:1rem; }
.commande-date { color:#64748b; font-size:0.85rem; }
.commande-body { display:flex; gap:1rem; align-items:center; flex-wrap:wrap; }
.commande-img { width:70px; height:70px; object-fit:cover; border-radius:8px; background:#f1f5f9; flex-shrink:0; }
.commande-info { flex:1; min-width:200px; }
.commande-titre { font-weight:700; color:#1e293b; font-size:0.95rem; margin-bottom:0.25rem; }
.commande-artisan { color:#64748b; font-size:0.82rem; margin-bottom:0.4rem; }
.commande-montant { color:#0d6efd; font-weight:700; font-size:1rem; }
.commande-footer { display:flex; justify-content:space-between; align-items:center; margin-top:1rem; padding-top:0.75rem; border-top:1px solid #f1f5f9; flex-wrap:wrap; gap:0.5rem; }

.badge-statut { padding:0.25rem 0.75rem; border-radius:999px; font-size:0.75rem; font-weight:700; }
.badge-en_attente { background:#fef3c7; color:#92400e; }
.badge-payee { background:#d1fae5; color:#065f46; }
.badge-annulee { background:#fee2e2; color:#991b1b; }
.badge-livree { background:#dbeafe; color:#1d4ed8; }
</style>

<div style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem">
    <div>
        <h1 style="font-size:1.6rem;font-weight:700;color:#1e293b;letter-spacing:-0.3px">📦 Mes commandes</h1>
        <p style="color:#64748b;font-size:0.95rem">Suivez l'état de vos achats</p>
    </div>
    <a href="/catalogue" class="btn-outline">Explorer le catalogue</a>
</div>

<div id="loading" style="text-align:center;padding:3rem 0;color:#64748b">Chargement des commandes...</div>
<div id="empty" style="display:none" class="empty-state">
    <span class="empty-icon">📦</span>
    <h3>Aucune commande pour le moment</h3>
    <p>Vos commandes apparaîtront ici après vos achats.</p>
    <a href="/catalogue" class="btn" style="margin-top:1rem">Découvrir les œuvres</a>
</div>

<div id="commandes-list"></div>

<script>
const token = localStorage.getItem('token');
const authHeaders = { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` };

function badgeStatut(statut) {
    const labels = { 'en_attente': 'En attente', 'payee': 'Payée', 'annulee': 'Annulée', 'livree': 'Livrée' };
    return `<span class="badge-statut badge-${statut}">${labels[statut] ?? statut}</span>`;
}

async function loadCommandes() {
    if (!token) {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('empty').style.display = 'block';
        return;
    }

    try {
        const res      = await fetch('/api/v1/acheteur/commandes', { headers: authHeaders, credentials: 'include' });
        const json     = await res.json();
        const commandes = json.data?.commandes ?? json.data ?? [];

        document.getElementById('loading').style.display = 'none';

        if (!Array.isArray(commandes) || !commandes.length) {
            document.getElementById('empty').style.display = 'block';
            return;
        }

        const list = document.getElementById('commandes-list');
        list.innerHTML = commandes.map(c => {
            const oeuvre   = c.oeuvre ?? {};
            const images   = oeuvre.images ?? [];
            const img      = images[0]?.url ?? (images[0]?.chemin ? `/storage/${images[0].chemin}` : 'https://via.placeholder.com/70x70?text=Oeuvre');
            const artisan  = oeuvre.artisan?.user?.name ?? oeuvre.artisan?.name ?? '—';
            const montant  = Number(c.montant_total ?? c.montant ?? 0).toLocaleString('fr-FR');
            const date     = c.created_at ? new Date(c.created_at).toLocaleDateString('fr-FR') : '—';

            return `
            <div class="commande-card">
                <div class="commande-header">
                    <span class="commande-ref">Commande #${c.id}</span>
                    <div style="display:flex;align-items:center;gap:0.75rem">
                        ${badgeStatut(c.statut ?? 'en_attente')}
                        <span class="commande-date">${date}</span>
                    </div>
                </div>
                <div class="commande-body">
                    <img src="${img}" class="commande-img" onerror="this.src='https://via.placeholder.com/70x70'">
                    <div class="commande-info">
                        <div class="commande-titre">${oeuvre.titre ?? '—'}</div>
                        <div class="commande-artisan">par ${artisan}</div>
                        <div class="commande-montant">${montant} FCFA</div>
                    </div>
                </div>
                <div class="commande-footer">
                    <span style="color:#64748b;font-size:0.85rem">Réf. paiement : ${c.reference_paiement ?? '—'}</span>
                    ${c.statut === 'en_attente' ? `
                    <button onclick="payerCommande(${c.id})"
                        class="btn" style="padding:0.5rem 1.2rem;font-size:0.88rem;background:#10b981;border-color:#10b981">
                        💳 Payer maintenant
                    </button>` : ''}
                </div>
            </div>`;
        }).join('');

    } catch (e) {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('commandes-list').innerHTML = '<p style="color:#ef4444">Erreur chargement des commandes.</p>';
        console.error(e);
    }
}

// ── Paiement simulation ───────────────────────────────────────────────────────
async function payerCommande(commandeId) {
    if (!confirm('Simuler le paiement de cette commande ?')) return;

    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
    const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');

    try {
        // Initier le paiement
        const initRes  = await fetch('/api/v1/acheteur/paiement/initier', {
            method: 'POST',
            headers: { ...authHeaders, 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            body: JSON.stringify({ commande_id: commandeId, mode_paiement: 'mobile_money' }),
        });
        const initJson = await initRes.json();

        if (!initRes.ok) {
            alert(initJson.message ?? 'Erreur lors de l\'initiation du paiement.');
            return;
        }

        // Mock confirmation
        const mockRes  = await fetch('/api/v1/acheteur/paiement/mock-confirmer', {
            method: 'POST',
            headers: { ...authHeaders, 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            body: JSON.stringify({ commande_id: commandeId }),
        });
        const mockJson = await mockRes.json();

        if (mockRes.ok) {
            alert('✅ Paiement effectué avec succès !');
            loadCommandes();
        } else {
            alert(mockJson.message ?? 'Erreur lors du paiement.');
        }

    } catch (e) {
        alert('Erreur réseau.');
        console.error(e);
    }
}

loadCommandes();
</script>

@endsection
