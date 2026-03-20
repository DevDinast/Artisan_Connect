@extends('layouts.app')

@section('title', $artisan->name . ' - ArtisanConnect')

@section('content')

<style>
.artisan-profil { max-width:800px; margin:0 auto; }
.artisan-hero { background:linear-gradient(135deg,#eef2ff,#e9ecef); border-radius:16px; padding:2rem; display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap; margin-bottom:2rem; }
.artisan-hero-avatar { width:80px; height:80px; border-radius:50%; background:#dbeafe; color:#1d4ed8; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1.5rem; flex-shrink:0; overflow:hidden; }
.artisan-hero-avatar img { width:100%; height:100%; object-fit:cover; }
.artisan-hero-name { font-size:1.6rem; font-weight:700; color:#1e293b; letter-spacing:-0.3px; }
.artisan-hero-spe { display:inline-block; background:#f0f9ff; color:#0369a1; font-size:0.78rem; font-weight:700; padding:0.25rem 0.65rem; border-radius:999px; margin:0.4rem 0; }
.artisan-hero-tel { color:#475569; font-size:0.9rem; margin-top:0.3rem; }
.info-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:1rem; }
.oeuvres-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:1.2rem; }
.oeuvre-card { background:white; border-radius:10px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.06); text-decoration:none; color:inherit; transition:transform 0.2s; display:block; }
.oeuvre-card:hover { transform:translateY(-3px); }
.oeuvre-card img { width:100%; height:160px; object-fit:cover; }
.oeuvre-card-body { padding:0.85rem; }
.oeuvre-card-titre { font-weight:700; font-size:0.9rem; color:#1e293b; margin-bottom:0.25rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.oeuvre-card-cat { color:#64748b; font-size:0.78rem; margin-bottom:0.4rem; }
.oeuvre-card-prix { color:#0d6efd; font-weight:700; font-size:0.95rem; }
</style>

<div class="artisan-profil">

    {{-- Hero --}}
    <div class="artisan-hero">
        <div class="artisan-hero-avatar">
            @if($artisan->avatar)
                <img src="{{ asset('storage/' . $artisan->avatar) }}" alt="{{ $artisan->name }}">
            @else
                {{ strtoupper(substr($artisan->name, 0, 2)) }}
            @endif
        </div>
        <div style="flex:1">
            <div class="artisan-hero-name">{{ $artisan->name }}</div>
            @if($artisan->artisan?->specialite)
                <span class="artisan-hero-spe">{{ $artisan->artisan->specialite }}</span>
            @endif
            @if($artisan->telephone)
                <div class="artisan-hero-tel">📞 {{ $artisan->telephone }}</div>
            @endif
        </div>
        @if($artisan->telephone)
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $artisan->telephone) }}"
           target="_blank" class="btn" style="background:#25D366;border-color:#25D366;margin-left:auto">
            💬 WhatsApp
        </a>
        @endif
    </div>

    {{-- Bio --}}
    @if($artisan->artisan?->biographie)
    <section class="section">
        <div class="section-header"><h2>À propos</h2></div>
        <p style="color:#475569;line-height:1.7;font-size:0.95rem">{{ $artisan->artisan->biographie }}</p>
    </section>
    @endif

    {{-- Infos --}}
    @if($artisan->artisan?->region || $artisan->artisan?->adresse_atelier)
    <section class="section">
        <div class="section-header"><h2>Informations</h2></div>
        <div class="info-grid">
            @if($artisan->artisan?->region)
            <div class="stat-card">
                <span class="stat-label">📍 Région</span>
                <span style="font-weight:600;color:#1e293b">{{ $artisan->artisan->region }}</span>
            </div>
            @endif
            @if($artisan->artisan?->adresse_atelier)
            <div class="stat-card">
                <span class="stat-label">🏠 Atelier</span>
                <span style="font-weight:600;color:#1e293b">{{ $artisan->artisan->adresse_atelier }}</span>
            </div>
            @endif
        </div>
    </section>
    @endif

    {{-- Œuvres --}}
    <section class="section">
        <div class="section-header">
            <h2>Ses œuvres</h2>
            <p class="section-sub">Les créations disponibles de cet artisan</p>
        </div>
        <div id="loading" style="text-align:center;padding:3rem 0;color:#94a3b8">Chargement des œuvres...</div>
        <div id="empty" style="display:none;text-align:center;padding:3rem 0;color:#94a3b8">Aucune œuvre disponible.</div>
        <div class="oeuvres-grid" id="oeuvres-grid"></div>
    </section>

    <div style="margin-top:2rem">
        <a href="/catalogue" class="card-link">← Retour au catalogue</a>
    </div>
</div>

<script>
async function loadOeuvres() {
    const loading = document.getElementById('loading');
    const empty   = document.getElementById('empty');
    const grid    = document.getElementById('oeuvres-grid');

    try {
        const res     = await fetch('/api/v1/catalog/artisans/{{ $artisan->id }}', { headers: { 'Accept': 'application/json' } });
        const json    = await res.json();
        const oeuvres = json.data?.oeuvres ?? [];

        loading.style.display = 'none';

        if (!oeuvres.length) { empty.style.display = 'block'; return; }

        grid.innerHTML = oeuvres.map(o => {
            const img = o.image ?? (o.images?.[0]?.chemin ? `/storage/${o.images[0].chemin}` : 'https://via.placeholder.com/400x300?text=Oeuvre');
            return `
            <a href="/catalogue/oeuvres/${o.id}" class="oeuvre-card">
                <img src="${img}" alt="${o.titre}" onerror="this.src='https://via.placeholder.com/400x300?text=Oeuvre'">
                <div class="oeuvre-card-body">
                    <div class="oeuvre-card-titre">${o.titre}</div>
                    <div class="oeuvre-card-cat">${o.categorie ?? ''}</div>
                    <div class="oeuvre-card-prix">${Number(o.prix).toLocaleString('fr-FR')} FCFA</div>
                </div>
            </a>`;
        }).join('');

    } catch (e) {
        loading.style.display = 'none';
        grid.innerHTML = '<p style="color:#ef4444">Erreur chargement des œuvres.</p>';
        console.error(e);
    }
}

loadOeuvres();
</script>

@endsection
