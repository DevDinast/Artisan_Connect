@extends('layouts.app')

@section('title', 'Mon atelier - ArtisanConnect')

@section('content')

<div class="dashboard">

    <div class="dashboard-hero artisan">
        <div class="dashboard-avatar artisan-bg" id="artisan-avatar-hero">A</div>
        <div>
            <h1 id="artisan-greeting">Bonjour 🎨</h1>
            <p>Gérez votre atelier et suivez vos ventes.</p>
        </div>
        <a href="{{ route('artisan.oeuvres.create') }}" class="btn" style="margin-left:auto">+ Ajouter une œuvre</a>
    </div>

    {{-- Stats dynamiques --}}
    <div class="dashboard-stats">
        <div class="stat-card">
            <span class="stat-num" id="stat-oeuvres">—</span>
            <span class="stat-label">Œuvres publiées</span>
        </div>
        <div class="stat-card">
            <span class="stat-num" id="stat-attente">—</span>
            <span class="stat-label">En attente</span>
        </div>
        <div class="stat-card">
            <span class="stat-num" id="stat-commandes">—</span>
            <span class="stat-label">Commandes reçues</span>
        </div>
        <div class="stat-card">
            <span class="stat-num" id="stat-revenus">—</span>
            <span class="stat-label">Revenus totaux</span>
        </div>
    </div>

    {{-- Mes œuvres --}}
    <section class="section">
        <div class="section-header">
            <h2>Mes œuvres</h2>
            <p class="section-sub">Gérez votre catalogue de créations</p>
        </div>
        <div id="oeuvres-container">
            <p style="color:var(--text-light)">Chargement de vos œuvres...</p>
        </div>
    </section>

    {{-- Dernières commandes --}}
    <section class="section">
        <div class="section-header">
            <h2>Dernières commandes</h2>
            <p class="section-sub">Suivez les achats de vos clients</p>
        </div>
        <div id="commandes-container">
            <p style="color:var(--text-light)">Chargement des commandes...</p>
        </div>
    </section>

    <section class="cta-section" style="margin-top:3rem">
        <h2>Boostez votre visibilité</h2>
        <p>Complétez votre profil et ajoutez des photos de qualité pour attirer plus d'acheteurs.</p>
        <a href="{{ route('me.profil') }}" class="btn">Compléter mon profil</a>
    </section>

</div>

@endsection

@push('scripts')
<script>
// "token" est déclaré dans layouts/app.blade.php
const authHeaders = { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` };

// ── Charger le profil artisan (nom + avatar) ──────────────────────────────────
async function loadProfil() {
    if (!token) {
        window.location.href = '{{ route("auth.login") }}';
        return;
    }
    try {
        const res  = await fetch('/api/v1/me', { headers: authHeaders, credentials: 'include' });
        const json = await res.json();
        const user = json.data?.user ?? json.data;
        if (!user) return;

        // Salutation
        const prenom = user.name?.split(' ')[0] ?? user.name ?? 'Artisan';
        document.getElementById('artisan-greeting').textContent = `Bonjour, ${prenom} 🎨`;

        // Avatar : image si elle existe, sinon initiales
        const avatarEl = document.getElementById('artisan-avatar-hero');
        if (user.avatar) {
            // Le backend stocke le chemin relatif ex: "avatars/xxx.jpg"
            const avatarUrl = user.avatar.startsWith('http') ? user.avatar : `/storage/${user.avatar}`;
            avatarEl.innerHTML = `<img src="${avatarUrl}" alt="${user.name}"
                style="width:100%;height:100%;border-radius:50%;object-fit:cover"
                onerror="this.parentElement.textContent='${prenom.slice(0,2).toUpperCase()}'">`;
        } else {
            avatarEl.textContent = user.name?.slice(0, 2).toUpperCase() ?? 'A';
        }
    } catch (e) { console.error('Profil non chargé', e); }
}

// ── Badge couleur selon statut ────────────────────────────────────────────────
function badgeStatut(statut) {
    const badges = {
        'validee'    : 'background:#d1fae5;color:#065f46',
        'en_attente' : 'background:#fef3c7;color:#92400e',
        'brouillon'  : 'background:#f3f4f6;color:#374151',
        'refusee'    : 'background:#fee2e2;color:#991b1b',
    };
    const labels = {
        'validee'    : '✓ Validée',
        'en_attente' : '⏳ En attente',
        'brouillon'  : '📝 Brouillon',
        'refusee'    : '✗ Refusée',
    };
    const style = badges[statut] ?? 'background:#f3f4f6;color:#374151';
    const label = labels[statut] ?? statut;
    return `<span style="${style};padding:0.2rem 0.6rem;border-radius:1rem;font-size:0.75rem;font-weight:600">${label}</span>`;
}

// ── Image depuis chemin storage ───────────────────────────────────────────────
function imageUrl(images) {
    if (!images?.length) return 'https://placehold.co/400x300?text=Oeuvre';
    const img = images[0];
    return img.url ?? (img.chemin ? `/storage/${img.chemin}` : 'https://placehold.co/400x300?text=Oeuvre');
}

// ── Dashboard stats ───────────────────────────────────────────────────────────
async function loadDashboard() {
    try {
        const res  = await fetch('/api/v1/artisan/dashboard', { headers: authHeaders, credentials: 'include' });
        const json = await res.json();
        const d    = json.data?.stats ?? json.data ?? json;

        document.getElementById('stat-oeuvres').textContent   = d.nb_oeuvres_publiees  ?? d.total_oeuvres   ?? 0;
        document.getElementById('stat-attente').textContent   = d.nb_oeuvres_attente   ?? 0;
        document.getElementById('stat-commandes').textContent = d.nb_ventes            ?? d.total_commandes ?? 0;

        const revenus = Number(d.revenus_total ?? d.revenus_totaux ?? 0).toLocaleString('fr-FR');
        document.getElementById('stat-revenus').textContent   = revenus + ' FCFA';

    } catch (e) { console.error('Erreur dashboard', e); }
}

// ── Mes œuvres ────────────────────────────────────────────────────────────────
async function loadOeuvres() {
    const container = document.getElementById('oeuvres-container');
    try {
        const res  = await fetch('/api/v1/artisan/oeuvres', { headers: authHeaders, credentials: 'include' });
        const json = await res.json();
        const oeuvres = json.data?.oeuvres ?? json.data ?? json;

        if (!Array.isArray(oeuvres) || oeuvres.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <span class="empty-icon">🖼️</span>
                    <h3>Aucune œuvre pour le moment</h3>
                    <p>Commencez par ajouter votre première création.</p>
                    <a href="{{ route('artisan.oeuvres.create') }}" class="btn" style="margin-top:1rem">Ajouter une œuvre</a>
                </div>`;
            return;
        }

        container.innerHTML = `
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.2rem">
                ${oeuvres.map(o => {
                    const image     = imageUrl(o.images);
                    const statut    = o.statut ?? 'brouillon';
                    const prix      = Number(o.prix).toLocaleString('fr-FR') + ' FCFA';
                    const categorie = o.categorie?.name ?? o.categorie?.nom ?? 'Divers';
                    return `
                    <div style="background:white;border-radius:12px;overflow:hidden;box-shadow:var(--shadow-sm);border:1px solid var(--border)">
                        <img src="${image}" alt="${o.titre}"
                             style="width:100%;height:160px;object-fit:cover"
                             onerror="this.src='https://placehold.co/400x160?text=Image'">
                        <div style="padding:1rem">
                            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:0.5rem">
                                <h3 style="font-size:0.95rem;font-weight:700;color:var(--brun)">${o.titre}</h3>
                                ${badgeStatut(statut)}
                            </div>
                            <p style="color:var(--text-mid);font-size:0.82rem;margin-bottom:0.25rem">${categorie}</p>
                            <p style="color:var(--terra);font-weight:700;font-size:0.95rem;margin-bottom:0.75rem">${prix}</p>
                            / APRÈS :
                          ${o.statut === 'refusee' ? `
                                       <div style="background:#fee2e2;border:1px solid #fecaca;border-radius:8px;padding:0.6rem 0.8rem;margin-bottom:0.75rem">
                                      <p style="color:#991b1b;font-size:0.82rem;font-weight:700;margin-bottom:0.2rem">⚠️ Refusée par l'admin</p>
                                      <p style="color:#7f1d1d;font-size:0.78rem">Motif : ${o.motif_refus ?? 'Non précisé'}</p>
                                       <p style="color:#991b1b;font-size:0.75rem;margin-top:0.2rem">Modifiez et soumettez à nouveau.</p>
                              </div>` : ''}
                            <div style="display:flex;gap:0.5rem">
                                <a href="/artisan/oeuvres/${o.id}/edit"
                                   style="flex:1;text-align:center;border:1px solid var(--terra);color:var(--terra);padding:0.4rem;border-radius:6px;font-size:0.82rem;font-weight:600;text-decoration:none">
                                   Modifier
                                </a>
                                <button onclick="supprimerOeuvre(${o.id})"
                                    style="flex:1;border:1px solid #ef4444;color:#ef4444;padding:0.4rem;border-radius:6px;font-size:0.82rem;font-weight:600;background:none;cursor:pointer">
                                    Supprimer
                                </button>
                            </div>
                        </div>
                    </div>`;
                }).join('')}
            </div>`;

    } catch (e) {
        container.innerHTML = '<p style="color:#ef4444">Erreur chargement des œuvres.</p>';
        console.error(e);
    }
}

// ── Supprimer une œuvre ───────────────────────────────────────────────────────
async function supprimerOeuvre(id) {
    if (!confirm('Supprimer cette œuvre définitivement ?')) return;
    try {
        await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
        const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');
        const res  = await fetch(`/api/v1/artisan/oeuvres/${id}`, {
            method: 'DELETE',
            headers: { ...authHeaders, 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
        });
        if (res.ok) loadOeuvres();
        else alert('Erreur lors de la suppression.');
    } catch (e) { alert('Erreur réseau.'); }
}

// ── Dernières commandes ───────────────────────────────────────────────────────
async function loadCommandes() {
    const container = document.getElementById('commandes-container');
    try {
        const res      = await fetch('/api/v1/artisan/ventes', { headers: authHeaders, credentials: 'include' });
        const json     = await res.json();
        const commandes = json.data?.ventes ?? json.data ?? json;

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
            <div style="overflow-x:auto">
            <table style="width:100%;font-size:0.88rem;border-collapse:collapse">
                <thead>
                    <tr style="background:var(--sable-mid);text-align:left">
                        <th style="padding:0.75rem 1rem;border-bottom:1px solid var(--border)">Réf.</th>
                        <th style="padding:0.75rem 1rem;border-bottom:1px solid var(--border)">Œuvre</th>
                        <th style="padding:0.75rem 1rem;border-bottom:1px solid var(--border)">Montant</th>
                        <th style="padding:0.75rem 1rem;border-bottom:1px solid var(--border)">Statut</th>
                        <th style="padding:0.75rem 1rem;border-bottom:1px solid var(--border)">Date</th>
                    </tr>
                </thead>
                <tbody>
                    ${commandes.slice(0, 10).map(c => `
                    <tr style="border-bottom:1px solid var(--border)">
                        <td style="padding:0.75rem 1rem">#${c.id}</td>
                        <td style="padding:0.75rem 1rem">${c.oeuvre?.titre ?? '—'}</td>
                        <td style="padding:0.75rem 1rem;color:var(--terra);font-weight:700">${Number(c.montant_artisan ?? c.montant ?? 0).toLocaleString('fr-FR')} FCFA</td>
                        <td style="padding:0.75rem 1rem">${c.statut ?? '—'}</td>
                        <td style="padding:0.75rem 1rem;color:var(--text-mid)">${c.created_at ? new Date(c.created_at).toLocaleDateString('fr-FR') : '—'}</td>
                    </tr>`).join('')}
                </tbody>
            </table>
            </div>`;

    } catch (e) {
        container.innerHTML = '<p style="color:#ef4444">Erreur chargement des commandes.</p>';
        console.error(e);
    }
}

// ── Init ─────────────────────────────────────────────────────────────────────
loadProfil();
loadDashboard();
loadOeuvres();
loadCommandes();
</script>
@endpush
