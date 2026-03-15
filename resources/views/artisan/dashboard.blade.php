@extends('layouts.app')

@section('title', 'Mon atelier - ArtisanConnect')

@section('content')

<div class="dashboard">

    <div class="dashboard-hero artisan">
        <div class="dashboard-avatar artisan-bg">{{ auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'A' }}</div>
        <div>
            <h1>Bonjour, {{ auth()->user()?->name ?? 'Artisan' }} 🎨</h1>
            <p>Gérez votre atelier et suivez vos ventes.</p>
        </div>
        <a href="#" id="btn-ajouter-oeuvre" class="btn" style="margin-left:auto">+ Ajouter une œuvre</a>
    </div>

    {{-- Stats dynamiques --}}
    <div class="dashboard-stats">
        <div class="stat-card">
            <span class="stat-num" id="stat-oeuvres">—</span>
            <span class="stat-label">Œuvres publiées</span>
        </div>
        <div class="stat-card">
            <span class="stat-num" id="stat-commandes">—</span>
            <span class="stat-label">Commandes reçues</span>
        </div>
        <div class="stat-card">
            <span class="stat-num" id="stat-revenus">—</span>
            <span class="stat-label">Revenus totaux</span>
        </div>
        <div class="stat-card">
            <span class="stat-num" id="stat-avis">—</span>
            <span class="stat-label">Avis clients</span>
        </div>
    </div>

    {{-- Mes œuvres --}}
    <section class="section">
        <div class="section-header">
            <h2>Mes œuvres</h2>
            <p class="section-sub">Gérez votre catalogue de créations</p>
        </div>
        <div id="oeuvres-container">
            <p class="text-gray-400">Chargement de vos œuvres...</p>
        </div>
    </section>

    {{-- Dernières commandes --}}
    <section class="section">
        <div class="section-header">
            <h2>Dernières commandes</h2>
            <p class="section-sub">Suivez les achats de vos clients</p>
        </div>
        <div id="commandes-container">
            <p class="text-gray-400">Chargement des commandes...</p>
        </div>
    </section>

    <section class="cta-section" style="margin-top:3rem">
        <h2>Boostez votre visibilité</h2>
        <p>Complétez votre profil et ajoutez des photos de qualité pour attirer plus d'acheteurs.</p>

         <a href="#" class="btn">Compléter mon profil</a>
    </section>

</div>

<script>
const token = localStorage.getItem('token');

const authHeaders = {
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`,
};

// ── Dashboard stats ───────────────────────────────────────────────────────────
async function loadDashboard() {
    try {
        const res  = await fetch('/api/v1/artisan/dashboard', { headers: authHeaders, credentials: 'include' });
        const json = await res.json();
        const d    = json.data ?? json;

        document.getElementById('stat-oeuvres').textContent   = d.total_oeuvres   ?? 0;
        document.getElementById('stat-commandes').textContent = d.total_commandes  ?? 0;
        document.getElementById('stat-avis').textContent      = d.total_avis       ?? 0;

        const revenus = Number(d.revenus_totaux ?? 0).toLocaleString('fr-FR');
        document.getElementById('stat-revenus').textContent   = revenus + ' FCFA';

    } catch (e) {
        console.error('Erreur dashboard', e);
    }
}

// ── Mes œuvres ────────────────────────────────────────────────────────────────
async function loadOeuvres() {
    const container = document.getElementById('oeuvres-container');
    try {
        const res     = await fetch('/api/v1/artisan/oeuvres', { headers: authHeaders, credentials: 'include' });
        const json    = await res.json();
        const oeuvres = json.data ?? json;

        if (!Array.isArray(oeuvres) || oeuvres.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <span class="empty-icon">🖼️</span>
                    <h3>Aucune œuvre publiée</h3>
                    <p>Commencez par ajouter votre première création.</p>
                    <a href="#" id="btn-ajouter-empty" class="btn" style="margin-top:1rem">Ajouter une œuvre</a>
                </div>`;
            return;
        }

        container.innerHTML = `
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                ${oeuvres.map(o => {
                    const image  = o.images?.[0]?.url ?? o.image ?? 'https://via.placeholder.com/400x300?text=Oeuvre';
                    const statut = o.statut ?? 'brouillon';
                    const prix   = Number(o.prix).toLocaleString('fr-FR') + ' FCFA';
                    const badge  = {
                        'publie'     : 'bg-green-100 text-green-700',
                        'en_attente' : 'bg-yellow-100 text-yellow-700',
                        'brouillon'  : 'bg-gray-100 text-gray-600',
                        'refuse'     : 'bg-red-100 text-red-700',
                    }[statut] ?? 'bg-gray-100 text-gray-600';

                    return `
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <img src="${image}" alt="${o.titre}" class="w-full h-40 object-cover">
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-semibold">${o.titre}</h3>
                                <span class="text-xs px-2 py-1 rounded-full ${badge}">${statut.replace('_', ' ')}</span>
                            </div>
                            <p class="text-gray-800 font-bold mb-3">${prix}</p>
                            <div class="flex gap-2">
                                <a href="/artisan/oeuvres/${o.id}/edit" class="flex-1 text-center border border-blue-500 text-blue-500 py-1 rounded hover:bg-blue-50 transition text-sm">Modifier</a>
                                <button onclick="supprimerOeuvre(${o.id})" class="flex-1 text-center border border-red-400 text-red-400 py-1 rounded hover:bg-red-50 transition text-sm">Supprimer</button>
                            </div>
                        </div>
                    </div>`;
                }).join('')}
            </div>`;

    } catch (e) {
        container.innerHTML = '<p class="text-red-400">Erreur chargement des œuvres.</p>';
        console.error(e);
    }
}

// ── Supprimer une œuvre ───────────────────────────────────────────────────────
async function supprimerOeuvre(id) {
    if (!confirm('Supprimer cette œuvre définitivement ?')) return;
    try {
        const xsrfToken = decodeURIComponent(
            document.cookie.split('; ').find(r => r.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''
        );
        const res = await fetch(`/api/v1/artisan/oeuvres/${id}`, {
            method: 'DELETE',
            headers: { ...authHeaders, 'X-XSRF-TOKEN': xsrfToken },
            credentials: 'include',
        });
        if (res.ok) loadOeuvres();
        else alert('Erreur lors de la suppression.');
    } catch (e) {
        alert('Erreur réseau.');
    }
}

// ── Dernières commandes ───────────────────────────────────────────────────────
async function loadCommandes() {
    const container = document.getElementById('commandes-container');
    try {
        const res      = await fetch('/api/v1/artisan/ventes', { headers: authHeaders, credentials: 'include' });
        const json     = await res.json();
        const commandes = json.data ?? json;

        if (!Array.isArray(commandes) || commandes.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <span class="empty-icon">📦</span>
                    <h3>Aucune commande pour le moment</h3>
                    <p>Vos commandes apparaîtront ici dès qu'un client achètera une de vos œuvres.</p>
                </div>`;
            return;
        }

        container.innerHTML = `
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="p-3 border-b">Référence</th>
                        <th class="p-3 border-b">Œuvre</th>
                        <th class="p-3 border-b">Montant</th>
                        <th class="p-3 border-b">Statut</th>
                        <th class="p-3 border-b">Date</th>
                    </tr>
                </thead>
                <tbody>
                    ${commandes.slice(0, 10).map(c => `
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 border-b">#${c.id}</td>
                        <td class="p-3 border-b">${c.oeuvre?.titre ?? '—'}</td>
                        <td class="p-3 border-b">${Number(c.montant ?? 0).toLocaleString('fr-FR')} FCFA</td>
                        <td class="p-3 border-b">${c.statut ?? '—'}</td>
                        <td class="p-3 border-b">${c.created_at ? new Date(c.created_at).toLocaleDateString('fr-FR') : '—'}</td>
                    </tr>`).join('')}
                </tbody>
            </table>`;

    } catch (e) {
        container.innerHTML = '<p class="text-red-400">Erreur chargement des commandes.</p>';
        console.error(e);
    }
}

// ── Init ─────────────────────────────────────────────────────────────────────
loadDashboard();
loadOeuvres();
loadCommandes();
</script>

@endsection
