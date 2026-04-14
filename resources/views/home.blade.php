@extends('layouts.app')
@section('title', 'Accueil - ArtisanConnect')
@section('content')

<style>
@keyframes fadeInUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
@keyframes shimmer  { 0% { background-position:200% center; } 100% { background-position:-200% center; } }
@keyframes float    { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-8px); } }
@keyframes drift    { 0%,100% { transform:translateY(0) rotate(0deg); } 50% { transform:translateY(-5px) rotate(2deg); } }

.anim-1 { animation: fadeInUp 0.7s ease both; }
.anim-2 { animation: fadeInUp 0.7s 0.12s ease both; }
.anim-3 { animation: fadeInUp 0.7s 0.25s ease both; }
.anim-4 { animation: fadeInUp 0.7s 0.38s ease both; }
.anim-5 { animation: fadeInUp 0.7s 0.5s ease both; }

/* ── Hero ─────────────────────────────────────────────────────────────
   FIX : gradient solide en fallback — l'image Unsplash ne charge pas
   en localhost. Le gradient garantit un fond sombre en toutes conditions.
   ──────────────────────────────────────────────────────────────────── */
.hero-home {
    position: relative;
    text-align: center;
    padding: 5rem 1.5rem 4rem;
    border-radius: 20px;
    margin-bottom: 2.5rem;
    overflow: hidden;
    min-height: 480px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    /* FIX : gradient de secours visible sans image externe */
    background: linear-gradient(150deg, #3D1A08 0%, #7A2E10 30%, #C0542A 65%, #6B3A2A 100%);
}

/* Couche image (optionnelle, écrasée par le gradient si elle ne charge pas) */
.hero-bg {
    position: absolute; inset: 0;
    background-image: url('https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?w=1400&q=80');
    background-size: cover;
    background-position: center;
    filter: brightness(0.28) saturate(1.4);
    /* FIX : si l'image ne charge pas, la div est invisible — le gradient du parent s'affiche */
}

.hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(150deg,
        rgba(192,84,42,0.55) 0%,
        rgba(107,58,42,0.70) 50%,
        rgba(58,107,74,0.30) 100%
    );
}

.hero-pattern {
    position: absolute; inset: 0; opacity: 0.06;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23FAF4E8' fill-rule='evenodd'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
}

/* Tout le contenu du hero au-dessus des couches */
.hero-home > *:not(.hero-bg):not(.hero-overlay):not(.hero-pattern):not(.float-badges) {
    position: relative;
    z-index: 3;
}

/* FIX badge : fond + texte bien visibles sur fond sombre */
.hero-home .hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: rgba(232,169,42,0.25);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(232,169,42,0.55);
    color: #F5C842;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.3rem 1rem;
    border-radius: 999px;
    margin-bottom: 1.25rem;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    position: relative;
    z-index: 3;
}

.hero-home h1 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(2.2rem, 6.5vw, 3.4rem);
    font-weight: 800;
    color: #FFFFFF;
    text-shadow: 0 2px 30px rgba(0,0,0,0.6);
    margin-bottom: 1rem;
    line-height: 1.15;
    position: relative;
    z-index: 3;
}

.hero-highlight {
    background: linear-gradient(90deg, #E8A92A, #F5C842, #E8A92A);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: shimmer 3s linear infinite;
    font-style: italic;
}

/* FIX : paragraphe lisible — couleur explicitement blanche */
.hero-home > p {
    color: rgba(255,255,255,0.90);
    font-size: 1.05rem;
    margin-bottom: 2rem;
    max-width: 500px;
    text-shadow: 0 1px 10px rgba(0,0,0,0.4);
    line-height: 1.75;
    position: relative;
    z-index: 3;
}

/* FIX : boutons toujours affichés côte à côte, centré */
.hero-actions {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 2.5rem;
    position: relative;
    z-index: 3;
    width: 100%;
}

.hero-actions .btn {
    background: #E8A92A;
    color: #4A2518;
    font-weight: 800;
    box-shadow: 0 4px 20px rgba(232,169,42,0.45);
    white-space: nowrap;
}
.hero-actions .btn:hover {
    background: #F5C842;
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(232,169,42,0.55);
    color: #4A2518;
}

/* FIX : btn-outline visible sur fond sombre */
.hero-actions .btn-outline {
    border: 2px solid rgba(255,255,255,0.65);
    color: white;
    background: rgba(255,255,255,0.10);
    backdrop-filter: blur(8px);
    white-space: nowrap;
}
.hero-actions .btn-outline:hover {
    background: rgba(255,255,255,0.20);
    border-color: white;
    color: white;
    transform: translateY(-2px);
}

.hero-stats {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
    position: relative;
    z-index: 3;
}
.hero-stat  { display: flex; flex-direction: column; align-items: center; gap: 0.1rem; }
.hero-stat strong {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    font-weight: 700;
    color: #E8A92A;
}
.hero-stat span   { font-size: 0.8rem; color: rgba(255,255,255,0.75); letter-spacing: 0.3px; }
.hero-divider { width: 1px; height: 2.5rem; background: rgba(255,255,255,0.22); }

/* Badges flottants */
.float-badges { position: absolute; inset: 0; z-index: 2; pointer-events: none; }
.float-badge {
    position: absolute;
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 999px;
    color: white;
    font-size: 0.76rem;
    font-weight: 600;
    padding: 0.4rem 0.9rem;
    animation: drift 5s ease-in-out infinite;
}
.float-badge:nth-child(1) { top:12%; left:5%;  animation-delay:0s; }
.float-badge:nth-child(2) { top:18%; right:6%; animation-delay:1.3s; }
.float-badge:nth-child(3) { bottom:22%; left:7%; animation-delay:2.2s; }
.float-badge:nth-child(4) { bottom:26%; right:5%; animation-delay:0.8s; }

/* ── Catégories ──────────────────────────────────────────────────────── */
.cat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }

.cat-card {
    background: var(--blanc); border-radius: 16px; padding: 1.5rem 1.2rem;
    border: 1px solid var(--border); box-shadow: var(--shadow-sm);
    text-decoration: none; color: inherit; display: flex;
    flex-direction: column; gap: 0.45rem; transition: all 0.3s;
    position: relative; overflow: hidden;
}
.cat-card::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, var(--terra-pale), transparent);
    opacity: 0; transition: opacity 0.3s;
}
.cat-card::after {
    content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--terra), var(--or));
    transform: scaleX(0); transition: transform 0.3s; transform-origin: left;
}
.cat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); text-decoration: none; }
.cat-card:hover::before { opacity: 1; }
.cat-card:hover::after  { transform: scaleX(1); }

.cat-icon { font-size: 2.4rem; animation: float 5s ease-in-out infinite; position: relative; z-index: 1; }
.cat-card h3 { font-family: 'Playfair Display', serif; color: var(--brun); font-weight: 700; font-size: 1.05rem; position: relative; z-index: 1; }
.cat-card p  { color: var(--text-mid); font-size: 0.84rem; flex: 1; line-height: 1.55; position: relative; z-index: 1; }
.cat-link-text { color: var(--terra); font-size: 0.82rem; font-weight: 700; margin-top: auto; position: relative; z-index: 1; }

/* ── Artisans ────────────────────────────────────────────────────────── */
.artisan-home-card {
    background: var(--blanc); border-radius: 16px; padding: 1.6rem;
    border: 1px solid var(--border); box-shadow: var(--shadow-sm);
    transition: all 0.3s; position: relative; overflow: hidden;
    display: flex; flex-direction: column; gap: 0.4rem;
}
.artisan-home-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(90deg, var(--terra), var(--ocre), var(--or), var(--vert));
}
.artisan-home-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }

.artisan-home-avatar {
    width: 54px; height: 54px;
    background: linear-gradient(135deg, var(--terra), var(--ocre));
    color: white; border-radius: 50%; display: flex;
    align-items: center; justify-content: center;
    font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1rem;
    box-shadow: 0 3px 10px rgba(192,84,42,0.3);
}
.artisan-home-name {
    font-family: 'Playfair Display', serif;
    color: var(--brun); font-weight: 700; font-size: 1.05rem; margin-top: 0.4rem;
}

/* ── Valeurs ─────────────────────────────────────────────────────────── */
.valeurs-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.valeur-card {
    background: linear-gradient(135deg, var(--sable-mid), var(--blanc));
    border-radius: 14px; padding: 1.4rem 1.2rem;
    border: 1px solid var(--border); text-align: center;
    transition: transform 0.2s;
}
.valeur-card:hover { transform: translateY(-3px); }
.valeur-icon { font-size: 2rem; margin-bottom: 0.6rem; display: block; }
.valeur-card h4 { font-family: 'Playfair Display', serif; color: var(--brun); font-size: 1rem; margin-bottom: 0.35rem; }
.valeur-card p  { color: var(--text-mid); font-size: 0.83rem; line-height: 1.55; }

/* ── CTA artisan ─────────────────────────────────────────────────────── */
.cta-home {
    margin-top: 3rem; border-radius: 20px; padding: 3.5rem 1.5rem;
    text-align: center; position: relative; overflow: hidden;
    /* FIX : gradient solide en fallback — image non chargée en local */
    background: linear-gradient(135deg, #4A2518 0%, #C0542A 100%);
}
.cta-home-bg {
    position: absolute; inset: 0;
    background-image: url('https://images.unsplash.com/photo-1561715276-a2d087060f1d?w=1400&q=80');
    background-size: cover; background-position: center;
    filter: brightness(0.25);
}
.cta-home-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(74,37,24,0.88), rgba(192,84,42,0.82));
}
.cta-pattern {
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23FAF4E8' fill-opacity='0.03' fill-rule='evenodd'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4z'/%3E%3C/g%3E%3C/svg%3E");
}
.cta-home > *:not(.cta-home-bg):not(.cta-home-overlay):not(.cta-pattern) { position: relative; z-index: 1; }
.cta-home h2 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.5rem, 4vw, 2rem); font-weight: 700;
    color: white; margin-bottom: 0.7rem;
}
.cta-home p  {
    color: rgba(255,255,255,0.88); font-size: 0.97rem;
    margin-bottom: 1.6rem; max-width: 460px;
    margin-left: auto; margin-right: auto; line-height: 1.7;
}
.cta-home .btn {
    background: var(--or); color: var(--brun-dark); font-weight: 800;
    box-shadow: 0 4px 18px rgba(232,169,42,0.45);
}
.cta-home .btn:hover {
    background: #F5C842; transform: translateY(-2px);
    box-shadow: 0 8px 26px rgba(232,169,42,0.55);
    color: var(--brun-dark);
}

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .hero-home { min-height: 400px; padding: 3.5rem 1rem 3rem; border-radius: 14px; }
    .float-badge { display: none; }
    .cat-grid { grid-template-columns: 1fr 1fr; }
    .cta-home { padding: 2.5rem 1rem; border-radius: 14px; }
    .hero-actions { flex-direction: column; align-items: center; }
    .hero-actions .btn, .hero-actions .btn-outline { width: 100%; max-width: 280px; text-align: center; }
    .hero-divider { display: none; }
    .valeurs-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 420px) {
    .cat-grid { grid-template-columns: 1fr; }
    .valeurs-grid { grid-template-columns: 1fr; }
    .hero-home h1 { font-size: 1.9rem; }
}
</style>

{{-- ═══════════════════════════════ HERO ═══════════════════════════════ --}}
<div class="hero-home">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-pattern"></div>
    <div class="float-badges">
        <span class="float-badge">🎨 Peinture</span>
        <span class="float-badge">🪘 Artisanat</span>
        <span class="float-badge">💍 Bijoux</span>
        <span class="float-badge">🗿 Sculpture</span>
    </div>

    <span class="hero-badge anim-1">✦ Plateforme artisanale africaine</span>

    <h1 class="anim-2">
        L'art africain à<br>
        <span class="hero-highlight">portée de main</span>
    </h1>

    <p class="anim-3">
        Découvrez des artisans talentueux et achetez leurs œuvres uniques,<br>
        façonnées avec passion et héritage culturel.
    </p>

    <div class="hero-actions anim-4">
        <a href="{{ route('auth.register') }}" class="btn">Commencer gratuitement</a>
        <a href="{{ route('catalogue.categories') }}" class="btn-outline">Explorer les œuvres</a>
    </div>

    <div class="hero-stats anim-5">
        <div class="hero-stat"><strong>+1 200</strong><span>Artisans</span></div>
        <div class="hero-divider"></div>
        <div class="hero-stat"><strong>+8 500</strong><span>Créations</span></div>
        <div class="hero-divider"></div>
        <div class="hero-stat"><strong>+30</strong><span>Catégories</span></div>
        <div class="hero-divider"></div>
        <div class="hero-stat"><strong>15+</strong><span>Pays</span></div>
    </div>
</div>

<div class="deco-band"><span></span><span></span><span></span><span></span><span></span></div>

{{-- ═══════════════════════════ CATÉGORIES ═════════════════════════════ --}}
<section class="section">
    <div class="section-header">
        <div>
            <h2>Catégories populaires</h2>
            <p class="section-sub">Explorez l'artisanat africain par univers</p>
        </div>
        <a href="{{ route('catalogue.categories') }}" class="section-link">Tout voir →</a>
    </div>
    <div class="cat-grid">
        <a href="{{ route('catalogue.categories') }}" class="cat-card card-animate">
            <div class="cat-icon">🎨</div>
            <h3>Peinture</h3>
            <p>Toiles vibrantes et œuvres peintes par des artisans locaux.</p>
            <span class="cat-link-text">Voir les œuvres →</span>
        </a>
        <a href="{{ route('catalogue.categories') }}" class="cat-card card-animate">
            <div class="cat-icon">💍</div>
            <h3>Bijouterie</h3>
            <p>Créations uniques en or, argent et pierres naturelles.</p>
            <span class="cat-link-text">Voir les créations →</span>
        </a>
        <a href="{{ route('catalogue.categories') }}" class="cat-card card-animate">
            <div class="cat-icon">🗿</div>
            <h3>Sculpture</h3>
            <p>Objets décoratifs et sculptures taillées avec savoir-faire.</p>
            <span class="cat-link-text">Voir les sculptures →</span>
        </a>
        <a href="{{ route('catalogue.categories') }}" class="cat-card card-animate">
            <div class="cat-icon">🪘</div>
            <h3>Artisanat</h3>
            <p>Savoir-faire traditionnel africain transmis de génération en génération.</p>
            <span class="cat-link-text">Voir les créations →</span>
        </a>
    </div>
</section>

<div class="deco-band"><span></span><span></span><span></span><span></span><span></span></div>

{{-- ═══════════════════════════ ARTISANS ════════════════════════════════ --}}
<section class="section">
    <div class="section-header">
        <div>
            <h2>Artisans à la une</h2>
            <p class="section-sub">Des talents reconnus sur notre plateforme</p>
        </div>
    </div>
    <div class="cards">
        @foreach($artisans as $artisan)
<div class="artisan-home-card card-animate">
    <div class="artisan-home-avatar">
        {{ strtoupper(substr($artisan->name, 0, 1)) }}
    </div>
    <div class="artisan-home-name">{{ $artisan->name }}</div>
    @if($artisan->artisan?->specialite)
        <span class="artisan-tag">{{ $artisan->artisan->specialite }}</span>
    @endif
    <p style="color:var(--text-mid);font-size:0.88rem;line-height:1.6">
        {{ Str::limit($artisan->artisan?->bio ?? 'Artisan talentueux sur ArtisanConnect.', 100) }}
    </p>
    <a href="{{ route('catalogue.artisan', $artisan->id) }}" class="card-link">Voir le profil →</a>
</div>
@endforeach
    </div>
</section>

<div class="deco-band"><span></span><span></span><span></span><span></span><span></span></div>

{{-- ══════════════════════════ VALEURS ═════════════════════════════════ --}}
<section class="section">
    <div class="section-header">
        <div>
            <h2>Pourquoi choisir ArtisanConnect ?</h2>
            <p class="section-sub">Une plateforme conçue pour valoriser l'artisanat africain</p>
        </div>
    </div>
    <div class="valeurs-grid">
        <div class="valeur-card card-animate">
            <span class="valeur-icon">🤝</span>
            <h4>Commerce équitable</h4>
            <p>Les artisans reçoivent une juste rémunération pour leur travail et leur talent.</p>
        </div>
        <div class="valeur-card card-animate">
            <span class="valeur-icon">🔒</span>
            <h4>Paiement sécurisé</h4>
            <p>Transactions protégées avec Mobile Money et autres moyens de paiement locaux.</p>
        </div>
        <div class="valeur-card card-animate">
            <span class="valeur-icon">🌍</span>
            <h4>Portée mondiale</h4>
            <p>Acheteurs du monde entier, artisans africains — une rencontre authentique.</p>
        </div>
        <div class="valeur-card card-animate">
            <span class="valeur-icon">✅</span>
            <h4>Œuvres vérifiées</h4>
            <p>Chaque création est validée par notre équipe avant d'être publiée.</p>
        </div>
    </div>
</section>

{{-- ══════════════════════════ CTA ARTISAN ═════════════════════════════ --}}
<div class="cta-home card-animate">
    <div class="cta-home-bg"></div>
    <div class="cta-home-overlay"></div>
    <div class="cta-pattern"></div>
    <h2>Vous êtes artisan ?</h2>
    <p>Rejoignez notre communauté et vendez vos créations à des milliers d'acheteurs passionnés du monde entier.</p>
    <a href="{{ route('auth.register') }}" class="btn">Créer mon espace artisan</a>
</div>

@endsection
