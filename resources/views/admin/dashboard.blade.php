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

    <section class="section">
        <div class="section-header">
            <h2>Œuvres en attente de validation</h2>
            <p class="section-sub">Validez ou refusez les créations soumises par les artisans</p>
        </div>
        <div id="loading" style="text-align:center;padding:2rem;color:var(--text-mid)">Chargement...</div>
        <div id="empty" style="display:none;text-align:center;padding:2rem;color:var(--text-mid)">✅ Aucune œuvre en attente.</div>
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

@endsection

@push('scripts')
<script>
(function () {
    function authHeaders(extra) {
        var h = { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token };
        if (extra) Object.assign(h, extra);
        return h;
    }

    async function getXsrf() {
        await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
        return decodeURIComponent(
            (document.cookie.split('; ').find(function(r) { return r.startsWith('XSRF-TOKEN='); }) || '').split('=')[1] || ''
        );
    }

    var oeuvreARefuserID = null;

    if (!token) {
        window.location.href = '/auth/login';
        return;
    }

    async function loadStats() {
        try {
            var res  = await fetch('/api/v1/admin/dashboard', { headers: authHeaders(), credentials: 'include' });
            var json = await res.json();

            if (res.status === 401 || res.status === 403) {
                document.getElementById('loading').textContent = 'Accès refusé (' + res.status + ') — vérifiez votre rôle.';
                return;
            }

            var d = (json.data && json.data.stats) ? json.data.stats : (json.data || json);
            document.getElementById('stat-attente').textContent  = d.oeuvres_en_attente ?? 0;
            document.getElementById('stat-validees').textContent = d.oeuvres_validees ?? 0;
            document.getElementById('stat-artisans').textContent = d.total_artisans ?? 0;
            document.getElementById('stat-ca').textContent       = Number(d.ca_total ?? 0).toLocaleString('fr-FR') + ' FCFA';
        } catch (e) { console.error(e); }
    }

    async function loadOeuvresEnAttente() {
        var loading   = document.getElementById('loading');
        var empty     = document.getElementById('empty');
        var container = document.getElementById('oeuvres-container');

        try {
            var res     = await fetch('/api/v1/admin/oeuvres/en-attente', { headers: authHeaders(), credentials: 'include' });
            var json    = await res.json();
            var oeuvres = (json.data && json.data.oeuvres) ? json.data.oeuvres : (json.data || []);

            loading.style.display = 'none';

            if (!Array.isArray(oeuvres) || !oeuvres.length) {
                empty.style.display = 'block';
                return;
            }

            empty.style.display = 'none';

            container.innerHTML = oeuvres.map(function(o) {
                var image     = (o.images && o.images[0] && o.images[0].url) ? o.images[0].url
                              : ((o.images && o.images[0] && o.images[0].chemin) ? '/storage/' + o.images[0].chemin
                              : 'https://placehold.co/400x300?text=Oeuvre');
                var prix      = Number(o.prix).toLocaleString('fr-FR') + ' FCFA';
                var artisan   = (o.artisan && o.artisan.name) ? o.artisan.name : '—';
                var categorie = (o.categorie && o.categorie.name) ? o.categorie.name : '—';
                var date      = o.created_at ? new Date(o.created_at).toLocaleDateString('fr-FR') : '—';
                var desc      = o.description ? o.description.substring(0, 150) + (o.description.length > 150 ? '...' : '') : '';

                return '<div style="background:#fff;border-radius:0.75rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);display:flex;flex-wrap:wrap;margin-bottom:1rem;overflow:hidden;border:1px solid var(--border)">'
                    + '<img src="' + image + '" alt="' + o.titre + '" style="width:180px;height:140px;object-fit:cover;flex-shrink:0" onerror="this.src=\'https://placehold.co/180x140?text=Image\'">'
                    + '<div style="flex:1;padding:1rem;min-width:200px">'
                    + '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:0.5rem">'
                    + '<h3 style="font-size:1.05rem;font-weight:700;color:var(--brun)">' + o.titre + '</h3>'
                    + '<span style="background:#fef3c7;color:#d97706;padding:0.2rem 0.6rem;border-radius:1rem;font-size:0.75rem;font-weight:600">⏳ En attente</span>'
                    + '</div>'
                    + '<p style="color:var(--text-mid);font-size:0.88rem;margin-bottom:0.2rem"><strong>Artisan :</strong> ' + artisan + '</p>'
                    + '<p style="color:var(--text-mid);font-size:0.88rem;margin-bottom:0.2rem"><strong>Catégorie :</strong> ' + categorie + '</p>'
                    + '<p style="color:var(--text-mid);font-size:0.88rem;margin-bottom:0.2rem"><strong>Prix :</strong> ' + prix + '</p>'
                    + '<p style="color:var(--text-mid);font-size:0.88rem;margin-bottom:0.5rem"><strong>Soumise le :</strong> ' + date + '</p>'
                    + (desc ? '<p style="color:var(--text-light);font-size:0.83rem;margin-bottom:0.75rem">' + desc + '</p>' : '')
                    + '<div style="display:flex;gap:0.75rem">'
                    + '<button onclick="validerOeuvre(' + o.id + ')" style="background:#10b981;color:#fff;border:none;padding:0.5rem 1.25rem;border-radius:0.5rem;cursor:pointer;font-weight:600;font-family:inherit">✓ Valider</button>'
                    + '<button onclick="ouvrirModalRefus(' + o.id + ')" style="background:#ef4444;color:#fff;border:none;padding:0.5rem 1.25rem;border-radius:0.5rem;cursor:pointer;font-weight:600;font-family:inherit">✗ Refuser</button>'
                    + '</div></div></div>';
            }).join('');

        } catch (e) {
            loading.style.display = 'none';
            if (container) container.innerHTML = '<p style="color:#ef4444">Erreur chargement des œuvres.</p>';
            console.error(e);
        }
    }

    window.validerOeuvre = async function(id) {
    if (!confirm('Valider cette œuvre ?')) return;
    var xsrf = await getXsrf();
    try {
        var res  = await fetch('/api/v1/admin/oeuvres/' + id + '/valider', {
            method      : 'PUT',
            headers     : authHeaders({ 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf }),
            credentials : 'include',
            body        : JSON.stringify({ double_validation_confirmee: true }),
        });
        var json = await res.json();
        if (res.ok) {
            // Vider immédiatement le container avant de recharger
            document.getElementById('oeuvres-container').innerHTML = '';
            document.getElementById('loading').style.display = 'block';
            document.getElementById('empty').style.display   = 'none';
            await loadOeuvresEnAttente();
            await loadStats();
        } else {
            alert(json.message || 'Erreur lors de la validation.');
        }
    } catch (e) { alert('Erreur réseau.'); }
};

    window.ouvrirModalRefus = function(id) {
        oeuvreARefuserID = id;
        document.getElementById('motif-refus').value = '';
        document.getElementById('modal-alert').innerHTML = '';
        document.getElementById('modal-refus').style.display = 'flex';
    };

    window.fermerModal = function() {
        document.getElementById('modal-refus').style.display = 'none';
        oeuvreARefuserID = null;
    };

    document.getElementById('btn-confirmer-refus').addEventListener('click', async function() {
        if (!oeuvreARefuserID) return;
        var motif      = document.getElementById('motif-refus').value.trim();
        var modalAlert = document.getElementById('modal-alert');

        if (motif.length < 10) {
            modalAlert.innerHTML = '<p style="color:#ef4444;font-size:0.85rem">Le motif doit contenir au moins 10 caractères.</p>';
            return;
        }
        if (res.ok) {
    fermerModal();
    // Vider immédiatement le container avant de recharger
    document.getElementById('oeuvres-container').innerHTML = '';
    document.getElementById('loading').style.display = 'block';
    document.getElementById('empty').style.display   = 'none';
    await loadOeuvresEnAttente();
    await loadStats();
}

        var xsrf = await getXsrf();
        try {
            var res  = await fetch('/api/v1/admin/oeuvres/' + oeuvreARefuserID + '/refuser', {
                method      : 'PUT',
                headers     : authHeaders({ 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf }),
                credentials : 'include',
                body        : JSON.stringify({ motif_refus: motif }),
            });
            var json = await res.json();
            if (res.ok) { fermerModal(); loadOeuvresEnAttente(); loadStats(); }
            else modalAlert.innerHTML = '<p style="color:#ef4444;font-size:0.85rem">' + (json.message || 'Erreur.') + '</p>';
        } catch (e) {
            modalAlert.innerHTML = '<p style="color:#ef4444;font-size:0.85rem">Erreur réseau.</p>';
        }
    });

    loadStats();
    loadOeuvresEnAttente();
})();
</script>
@endpush
