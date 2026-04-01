@extends('layouts.app')

@section('title', 'Accueil - ArtisanConnect')

@section('content')

<style>
/* ── Animations ── */
@keyframes fadeInUp {
    from { opacity:0; transform:translateY(30px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes fadeInLeft {
    from { opacity:0; transform:translateX(-30px); }
    to   { opacity:1; transform:translateX(0); }
}
@keyframes float {
    0%,100% { transform:translateY(0px); }
    50%      { transform:translateY(-8px); }
}
@keyframes shimmer {
    0%   { background-position:200% center; }
    100% { background-position:-200% center; }
}
@keyframes pulse-ring {
    0%   { transform:scale(1); opacity:0.6; }
    100% { transform:scale(1.4); opacity:0; }
}
@keyframes draw-pattern {
    from { stroke-dashoffset: 1000; }
    to   { stroke-dashoffset: 0; }
}

.animate-fadeup  { animation: fadeInUp 0.7s ease both; }
.animate-delay-1 { animation-delay: 0.1s; }
.animate-delay-2 { animation-delay: 0.25s; }
.animate-delay-3 { animation-delay: 0.4s; }
.animate-delay-4 { animation-delay: 0.55s; }
.animate-delay-5 { animation-delay: 0.7s; }

/* ── HERO avec image de fond ── */
.hero-home {
    position: relative;
    text-align: center;
    padding: 4rem 1.5rem 3rem;
    border-radius: 20px;
    margin-bottom: 2.5rem;
    overflow: hidden;
    min-height: 420px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* Fond: image Unsplash artisanat africain */
.hero-home::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url('https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?w=1200&q=80');
    background-size: cover;
    background-position: center;
    filter: brightness(0.35) saturate(1.2);
    z-index: 0;
}

/* Overlay dégradé africain par-dessus */
.hero-home::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
        160deg,
        rgba(192,84,42,0.55) 0%,
        rgba(107,58,42,0.7) 50%,
        rgba(58,107,74,0.4) 100%
    );
    z-index: 1;
}

/* Motif géométrique africain SVG */
.hero-pattern {
    position: absolute;
    inset: 0;
    z-index: 2;
    opacity: 0.12;
    background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23FAF4E8' fill-rule='evenodd'%3E%3Cpath d='M0 0h40v40H0V0zm40 40h40v40H40V40zm0-40h2l-2 2V0zm0 4l4-4h2l-6 6V4zm0 4l8-8h2L40 10V8zm0 4L52 0h2L40 14v-2zm0 4L56 0h2L40 18v-2zm0 4L60 0h2L40 22v-2zm0 4L64 0h2L40 26v-2zm0 4L68 0h2L40 30v-2zm0 4L72 0h2L40 34v-2zm0 4L76 0h2L40 38v-2zm0 4L80 0v2L42 40h-2zm4 0L80 4v2L46 40h-2zm4 0L80 8v2L50 40h-2zm4 0L80 12v2L54 40h-2zm4 0L80 16v2L58 40h-2zm4 0L80 20v2L62 40h-2zm4 0L80 24v2L66 40h-2zm4 0L80 28v2L70 40h-2zm4 0L80 32v2L74 40h-2zm4 0L80 36v2L78 40h-2zm4 0L80 40h-2'/%3E%3C/g%3E%3C/svg%3E");
}

.hero-home > * { position: relative; z-index: 3; }

.hero-home .hero-badge {
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    letter-spacing: 2px;
}

.hero-home h1 {
    color: white;
    text-shadow: 0 2px 20px rgba(0,0,0,0.4);
    font-size: clamp(2rem, 6vw, 3rem);
}

.hero-home h1 .highlight {
    color: var(--or);
    background: linear-gradient(90deg, var(--or), #F5C842, var(--or));
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: shimmer 3s linear infinite;
}

.hero-home > p {
    color: rgba(255,255,255,0.88);
    font-size: 1.05rem;
    text-shadow: 0 1px 8px rgba(0,0,0,0.3);
}

.hero-home .hero-actions .btn {
    background: var(--or);
    color: var(--brun);
    font-weight: 700;
    box-shadow: 0 4px 20px rgba(232,169,42,0.4);
}
.hero-home .hero-actions .btn:hover {
    background: #F5C842;
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(232,169,42,0.5);
}
.hero-home .hero-actions .btn-outline {
    border-color: rgba(255,255,255,0.6);
    color: white;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(8px);
}
.hero-home .hero-actions .btn-outline:hover {
    background: rgba(255,255,255,0.2);
    border-color: white;
}

.hero-home .stat strong { color: var(--or); }
.hero-home .stat span  { color: rgba(255,255,255,0.75); }
.hero-home .stat-divider { background: rgba(255,255,255,0.3); }

/* ── Badges flottants décoratifs ── */
.hero-floats {
    position: absolute;
    inset: 0;
    z-index: 2;
    pointer-events: none;
}
.float-badge {
    position: absolute;
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 999px;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.4rem 0.9rem;
    animation: float 4s ease-in-out infinite;
}
.float-badge:nth-child(1) { top:12%; left:4%;  animation-delay:0s; }
.float-badge:nth-child(2) { top:20%; right:5%; animation-delay:1.2s; }
.float-badge:nth-child(3) { bottom:18%; left:6%; animation-delay:2.1s; }
.float-badge:nth-child(4) { bottom:22%; right:4%; animation-delay:0.7s; }

/* ── Cards animées au scroll ── */
.card-animate {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.6s ease, transform 0.6s ease;
}
.card-animate.visible {
    opacity: 1;
    transform: translateY(0);
}

/* ── Section catégories ── */
.cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}
.cat-card {
    background: white;
    border-radius: 14px;
    padding: 1.5rem 1.2rem;
    border: 1px solid var(--border);
    box-shadow: 0 2px 8px rgba(107,58,42,0.07);
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}
.cat-card::before {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--terracotta), var(--or));
    transform: scaleX(0);
    transition: transform 0.3s;
    transform-origin: left;
}
.cat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 28px rgba(107,58,42,0.15); }
.cat-card:hover::before { transform: scaleX(1); }
.cat-icon { font-size: 2.2rem; animation: float 5s ease-in-out infinite; }
.cat-card h3 { color: var(--brun); font-weight: 700; font-size: 1rem; }
.cat-card p { color: var(--text-mid); font-size: 0.85rem; }
.cat-link { color: var(--terracotta); font-size: 0.82rem; font-weight: 600; margin-top: auto; }

/* ── Artisans ── */
.artisan-card {
    background: white;
    border-radius: 14px;
    padding: 1.5rem;
    border: 1px solid var(--border);
    box-shadow: 0 2px 8px rgba(107,58,42,0.07);
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}
.artisan-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--terracotta), var(--ocre), var(--or));
}
.artisan-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(107,58,42,0.15); }

.artisan-card .artisan-avatar {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, var(--terracotta), var(--ocre));
    color: white;
    font-size: 1rem;
    border: none;
    position: relative;
}
.artisan-card .artisan-avatar::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 2px solid var(--terracotta);
    animation: pulse-ring 2s ease-out infinite;
}

/* ── CTA améliorée ── */
.cta-home {
    margin-top: 3rem;
    border-radius: 20px;
    padding: 3rem 1.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
    background-image: url('https://images.unsplash.com/photo-1561715276-a2d087060f1d?w=1200&q=80');
    background-size: cover;
    background-position: center;
}
.cta-home::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(107,58,42,0.88), rgba(192,84,42,0.82));
}
.cta-home > * { position: relative; z-index: 1; }
.cta-home h2 { font-size: clamp(1.4rem, 4vw, 1.8rem); font-weight: 700; color: white; margin-bottom: 0.75rem; }
.cta-home p { color: rgba(255,255,255,0.82); font-size: 0.97rem; margin-bottom: 1.5rem; max-width: 480px; margin-left: auto; margin-right: auto; }
.cta-home .btn { background: var(--or); color: var(--brun); font-weight: 700; box-shadow: 0 4px 16px rgba(232,169,42,0.4); }
.cta-home .btn:hover { background: #F5C842; transform: translateY(-2px); }

/* ── Bandeau décoratif ── */
.deco-band {
    display: flex;
    gap: 0;
    height: 6px;
    border-radius: 999px;
    overflow: hidden;
    margin: 2rem 0;
}
.deco-band span { flex: 1; }
.deco-band span:nth-child(1) { background: var(--terracotta); }
.deco-band span:nth-child(2) { background: var(--ocre); }
.deco-band span:nth-child(3) { background: var(--or); }
.deco-band span:nth-child(4) { background: var(--vert); }
.deco-band span:nth-child(5) { background: var(--brun); }

@media (max-width: 768px) {
    .hero-home { min-height: 360px; padding: 3rem 1rem 2rem; }
    .float-badge { display: none; }
    .cat-grid { grid-template-columns: 1fr 1fr; }
    .cta-home { padding: 2rem 1rem; border-radius: 14px; }
}
@media (max-width: 400px) {
    .cat-grid { grid-template-columns: 1fr; }
}
</style>

{{-- HERO --}}
<div class="hero-home animate-fadeup">
    <div class="hero-pattern"></div>
    <div class="hero-floats">
        <span class="float-badge">🎨 Artisanat</span>
        <span class="float-badge">🪘 Musique</span>
        <span class="float-badge">💍 Bijoux</span>
        <span class="float-badge">🗿 Sculptures</span>
    </div>
    <span class="hero-badge animate-fadeup animate-delay-1">✦ Plateforme artisanale</span>
    <h1 class="animate-fadeup animate-delay-2">Bienvenue sur <span class="highlight">ArtisanConnect</span></h1>
    <p class="animate-fadeup animate-delay-3">Découvrez des artisans talentueux et achetez leurs œuvres uniques, faites main avec passion.</p>
    <div class="hero-actions animate-fadeup animate-delay-4">
        <a href="{{ route('auth.register') }}" class="btn">Commencer gratuitement</a>
        <a href="{{ route('catalogue.categories') }}" class="btn-outline">Explorer les œuvres</a>
    </div>
    <div class="hero-stats animate-fadeup animate-delay-5">
        <div class="stat"><strong>+1 200</strong><span>Artisans</span></div>
        <div class="stat-divider"></div>
        <div class="stat"><strong>+8 500</strong><span>Créations</span></div>
        <div class="stat-divider"></div>
        <div class="stat"><strong>+30</strong><span>Catégories</span></div>
    </div>
</div>

<div class="deco-band"><span></span><span></span><span></span><span></span><span></span></div>

{{-- CATEGORIES --}}
<section class="section" id="categories">
    <div class="section-header">
        <h2>Catégories populaires</h2>
        <p class="section-sub">Explorez l'artisanat par univers</p>
    </div>
    <div class="cat-grid">
        <a href="{{ route('catalogue.categories') }}" class="cat-card card-animate">
            <div class="cat-icon">🎨</div>
            <h3>Peinture</h3>
            <p>Œuvres peintes par des artisans locaux.</p>
            <span class="cat-link">Voir les œuvres →</span>
        </a>
        <a href="{{ route('catalogue.categories') }}" class="cat-card card-animate">
            <div class="cat-icon">💍</div>
            <h3>Bijouterie</h3>
            <p>Créations uniques faites main.</p>
            <span class="cat-link">Voir les créations →</span>
        </a>
        <a href="{{ route('catalogue.categories') }}" class="cat-card card-animate">
            <div class="cat-icon">🗿</div>
            <h3>Sculpture</h3>
            <p>Objets décoratifs et sculptures d'art.</p>
            <span class="cat-link">Voir les sculptures →</span>
        </a>
        <a href="{{ route('catalogue.categories') }}" class="cat-card card-animate">
            <div class="cat-icon">🪘</div>
            <h3>Artisanat</h3>
            <p>Savoir-faire traditionnel africain.</p>
            <span class="cat-link">Voir les créations →</span>
        </a>
    </div>
</section>

<div class="deco-band"><span></span><span></span><span></span><span></span><span></span></div>

{{-- ARTISANS --}}
<section class="section">
    <div class="section-header">
        <h2>Artisans à la une</h2>
        <p class="section-sub">Des talents reconnus sur la plateforme</p>
    </div>
    <div class="cards">
        <div class="artisan-card card-animate">
            <div class="artisan-avatar">MD</div>
            <h3 style="color:var(--brun);font-weight:700;margin:0.5rem 0 0.2rem">Marie Dupont</h3>
            <span class="artisan-tag">Peinture contemporaine</span>
            <p style="color:var(--text-mid);font-size:0.9rem">Artiste parisienne reconnue pour ses toiles abstraites lumineuses.</p>
            <a href="#" class="card-link">Voir le profil →</a>
        </div>
        <div class="artisan-card card-animate">
            <div class="artisan-avatar">JK</div>
            <h3 style="color:var(--brun);font-weight:700;margin:0.5rem 0 0.2rem">Jean Kouassi</h3>
            <span class="artisan-tag">Bijoux traditionnels</span>
            <p style="color:var(--text-mid);font-size:0.9rem">Maître joaillier alliant savoir-faire traditionnel et modernité.</p>
            <a href="#" class="card-link">Voir le profil →</a>
        </div>
        <div class="artisan-card card-animate">
            <div class="artisan-avatar">FD</div>
            <h3 style="color:var(--brun);font-weight:700;margin:0.5rem 0 0.2rem">Fatoumata Diop</h3>
            <span class="artisan-tag">Sculpture africaine</span>
            <p style="color:var(--text-mid);font-size:0.9rem">Sculptrice contemporaine inspirée des cultures d'Afrique de l'Ouest.</p>
            <a href="#" class="card-link">Voir le profil →</a>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="cta-home card-animate">
    <h2>Vous êtes artisan ?</h2>
    <p>Rejoignez notre communauté et vendez vos créations à des milliers d'acheteurs passionnés.</p>
    <a href="{{ route('auth.register') }}" class="btn">Créer mon espace artisan</a>
</section>

<script>
// Animation au scroll
const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
        if (entry.isIntersecting) {
            setTimeout(() => entry.target.classList.add('visible'), i * 100);
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.card-animate').forEach(el => observer.observe(el));
</script>

@endsection
