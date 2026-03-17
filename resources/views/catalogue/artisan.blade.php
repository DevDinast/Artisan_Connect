@extends('layouts.app')

@section('title', $artisan->name . ' - ArtisanConnect')

@section('content')

<div class="container" style="max-width: 800px; margin: auto">

    <div class="dashboard-hero" style="margin-bottom: 2rem">
        <div class="dashboard-avatar" style="font-size:1.5rem;width:5rem;height:5rem;flex-shrink:0">
            @if($artisan->avatar)
                <img src="{{ asset('storage/' . $artisan->avatar) }}" alt="{{ $artisan->name }}"
                     style="width:5rem;height:5rem;border-radius:50%;object-fit:cover">
            @else
                {{ strtoupper(substr($artisan->name, 0, 2)) }}
            @endif
        </div>
        <div>
            <h1>{{ $artisan->name }}</h1>
            @if($artisan->artisan?->specialite)
                <span class="artisan-tag">{{ $artisan->artisan->specialite }}</span>
            @endif
            @if($artisan->telephone)
                <p style="margin-top:0.5rem;color:#555">📞 {{ $artisan->telephone }}</p>
            @endif
        </div>
        @if($artisan->telephone)
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $artisan->telephone) }}"
           target="_blank" class="btn" style="margin-left:auto;background:#25D366;border-color:#25D366">
            💬 WhatsApp
        </a>
        @endif
    </div>

    @if($artisan->artisan?->bio)
    <section class="section">
        <div class="section-header"><h2>À propos</h2></div>
        <p style="color:#555;line-height:1.7">{{ $artisan->artisan->bio }}</p>
    </section>
    @endif

    @if($artisan->artisan?->region || $artisan->artisan?->atelier_adresse)
    <section class="section">
        <div class="section-header"><h2>Informations</h2></div>
        <div style="display:flex;flex-wrap:wrap;gap:1rem">
            @if($artisan->artisan?->region)
            <div class="stat-card">
                <span class="stat-label">📍 Région</span>
                <span class="stat-num" style="font-size:1rem">{{ $artisan->artisan->region }}</span>
            </div>
            @endif
            @if($artisan->artisan?->atelier_adresse)
            <div class="stat-card">
                <span class="stat-label">🏠 Atelier</span>
                <span class="stat-num" style="font-size:1rem">{{ $artisan->artisan->atelier_adresse }}</span>
            </div>
            @endif
        </div>
    </section>
    @endif

    <section class="section">
        <div class="section-header">
            <h2>Ses œuvres</h2>
            <p class="section-sub">Les créations disponibles de cet artisan</p>
        </div>
        <div id="loading" class="text-center py-8 text-gray-400">Chargement des œuvres...</div>
        <div id="empty" class="text-center py-8 text-gray-400 hidden">Aucune œuvre disponible.</div>
        <div id="oeuvres-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6"></div>
    </section>

    <div style="margin-top:2rem">
        <a href="{{ route('catalogue.categories') }}" class="card-link">← Retour au catalogue</a>
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
        loading.classList.add('hidden');
        if (!oeuvres.length) { empty.classList.remove('hidden'); return; }
        grid.innerHTML = oeuvres.map(o => `
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="${o.image ?? 'https://via.placeholder.com/400x300?text=Oeuvre'}" alt="${o.titre}" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="text-lg font-semibold mb-2">${o.titre}</h3>
                    <p class="text-gray-600 mb-2">${o.categorie ?? ''}</p>
                    <p class="text-gray-800 font-bold mb-4">${Number(o.prix).toLocaleString('fr-FR')} FCFA</p>
                    <a href="{{ route('catalogue.oeuvre', '') }}/${o.id}" class="block text-center bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Voir les détails</a>
                </div>
            </div>`).join('');
    } catch (e) {
        loading.classList.add('hidden');
        grid.innerHTML = '<p class="text-red-400">Erreur chargement des œuvres.</p>';
    }
}
loadOeuvres();
</script>

@endsection
