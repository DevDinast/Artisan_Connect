@extends('layouts.app')
@section('title', 'Accueil - ArtisanConnect')
@section('content')

<style>
@keyframes fadeInUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
@keyframes shimmer { 0% { background-position:200% center; } 100% { background-position:-200% center; } }
@keyframes float { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-8px); } }

.anim-1 { animation: fadeInUp 0.7s ease both; }
.anim-2 { animation: fadeInUp 0.7s 0.15s ease both; }
.anim-3 { animation: fadeInUp 0.7s 0.3s ease both; }
.anim-4 { animation: fadeInUp 0.7s 0.45s ease both; }
.anim-5 { animation: fadeInUp 0.7s 0.6s ease both; }

/* Hero */
.hero-home {
    position: relative; text-align: center; padding: 4.5rem 1.5rem 3.5rem;
    border-radius: 20px; margin-bottom: 2.5rem; overflow: hidden;
    min-height: 440px; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
}
.hero-bg {
    position: absolute; inset: 0;
    background-image: url('https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?w=1200&q=80');
    background-size: cover; background-position: center;
    filter: brightness(0.3) saturate(1.3);
}
.hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(150deg, rgba(192,84,42,0.6) 0%, rgba(107,58,42,0.75) 50%, rgba(58,107,74,0.45) 100%);
}
.hero-pattern {
    position: absolute; inset: 0; opacity: 0.08;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23FAF4E8' fill-rule='evenodd'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
}
.hero-home > * { position: relative; z-index: 3; }
.hero-home .hero-badge { background: rgba(255,255,255,0.18); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3); color: white; }
.hero-home h1 { font-size: clamp(2rem,6vw,3rem); font-weight: 700; color: white; text-shadow: 0 2px 20px rgba(0,0,0,0.4); margin-bottom: 1rem; letter-spacing: -0.8px; line-height: 1.2; }
.hero-highlight {
    background: linear-gradient(90deg, #E8A92A, #F5C842, #E8A92A);
    background-size: 200% auto;
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    animation: shimmer 3s linear infinite;
}
.hero-home > p { color: rgba(255,255,255,0.88); font-size: 1.05rem; margin-bottom: 2rem; max-width: 520px; text-shadow: 0 1px 8px rgba(0,0,0,0.3); }

.hero-actions { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 2.5rem; }
.hero-actions .btn { background: #E8A92A; color: #6B3A2A; font-weight: 700; box-shadow: 0 4px 20px rgba(232,169,42,0.45); }
.hero-actions .btn:hover { background: #F5C842; transform: translateY(-2px); box-shadow: 0 8px 28px rgba(232,169,42,0.55); }
.hero-actions .btn-outline { border-color: rgba(255,255,255,0.6); color: white; background: rgba(255,255,255,0.12); backdrop-filter: blur(8px); }
.hero-actions .btn-outline:hover { background: rgba(255,255,255,0.22); border-color: white; }

.hero-stats { display: flex; justify-content: center; align-items: center; gap: 2rem; flex-wrap: wrap; }
.hero-stat { display: flex; flex-direction: column; align-items: center; }
.hero-stat strong { font-size: 1.5rem; font-weight: 700; color: #E8A92A; }
.hero-stat span { font-size: 0.82rem; color: rgba(255,255,255,0.75); }
.hero-divider { width: 1px; height: 2.5rem; background: rgba(255,255,255,0.25); }

/* Float badges */
.float-badges { position: absolute; inset: 0; z-index: 2; pointer-events: none; }
.float-badge {
    position: absolute; background: rgba(255,255,255,0.12); backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,0.2); border-radius: 999px;
    color: white; font-size: 0.75rem; font-weight: 600; padding: 0.38rem 0.85rem;
    animation: float 4s ease-in-out infinite;
}
.float-badge:nth-child(1) { top:12%; left:5%;  animation-delay:0s; }
.float-badge:nth-child(2) { top:18%; right:6%; animation-delay:1.3s; }
.float-badge:nth-child(3) { bottom:20%; left:7%; animation-delay:2.2s; }
.float-badge:nth-child(4) { bottom:24%; right:5%; animation-delay:0.8s; }

/* Cat cards */
.cat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 1rem; }
.cat-card {
    background: white; border-radius: 14px; padding: 1.4rem 1.2rem;
    border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(107,58,42,0.07);
    text-decoration: none; color: inherit; display: flex; flex-direction: column; gap: 0.45rem;
    transition: all 0.3s; position: relative; overflow: hidden;
}
.cat-card::after { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg,var(--terra),var(--or)); transform:scaleX(0); transition:transform 0.3s; transform-origin:left; }
.cat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 28px rgba(107,58,42,0.14); }
.cat-card:hover::after { transform: scaleX(1); }
.cat-icon { font-size: 2.2rem; animation: float 5s ease-in-out infinite; }
.cat-card h3 { color: var(--brun); font-weight: 700; font-size: 1rem; }
.cat-card p { color: var(--text-mid); font-size: 0.84rem; flex: 1; }
.cat-link-text { color: var(--terra); font-size: 0.82rem; font-weight: 600; margin-top: auto; }

/* Artisan cards */
.artisan-home-card {
    background: white; border-radius: 14px; padding: 1.5rem;
    border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(107,58,42,0.07);
    transition: all 0.3s; position: relative; overflow: hidden;
    display: flex; flex-direction: column; gap: 0.4rem;
}
.artisan-home-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,var(--terra),var(--ocre),var(--or)); }
.artisan-home-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(107,58,42,0.15); }
.artisan-home-avatar { width:52px; height:52px; background:linear-gradient(135deg,var(--terra),var(--ocre)); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1rem; }

/* CTA image */
.cta-home {
    margin-top: 3rem; border-radius: 20px; padding: 3rem 1.5rem;
    text-align: center; position: relative; overflow: hidden;
    background-image: url('https://images.unsplash.com/photo-1561715276-a2d087060f1d?w=1200&q=80');
    background-size: cover; background-position: center;
}
.cta-home::before { content:''; position:absolute; inset:0; background:linear-gradient(135deg,rgba(107,58,42,0.9),rgba(192,84,42,0.85)); }
.cta-home > * { position: relative; z-index: 1; }
.cta-home h2 { font-size: clamp(1.3rem,4vw,1.7rem); font-weight: 700; color: white; margin-bottom: 0.65rem; }
.cta-home p { color: rgba(255,255,255,0.82); font-size: 0.95rem; margin-bottom: 1.4rem; max-width: 480px; margin-left: auto; margin-right: auto; }
.cta-home .btn { background: var(--or); color: var(--brun); font-weight: 700; box-shadow: 0 4px 16px rgba(232,169,42,0.4); }
.cta-home .btn:hover { background: #F5C842; transform: translateY(-2px); }

@media (max-width: 768px) {
    .hero-home { min-height: 380px; padding: 3rem 1rem 2.5rem; border-radius: 14px; }
    .float-badge { display: none; }
    .cat-grid { grid-template-columns: 1fr 1fr; }
    .cta-home { padding: 2rem 1rem; border-radius: 14px; }
    .hero-actions { flex-direction: column; align-items: center; }
    .hero-actions .btn, .hero-actions .btn-outline { width: 100%; max-width: 280px; }
    .hero-divider { display: none; }
}
@media (max-width: 420px) {
    .cat-grid { grid-template-columns: 1fr; }
}
</style>

{{-- HERO --}}
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
    <span class="hero-badge anim-1">✦ Plateforme artisanale</span>
    <h1 class="anim-2">Bienvenue sur <span class="hero-highlight">ArtisanConnect</span></h1>
    <p class="anim-3">Découvrez des artisans talentueux et achetez leurs œuvres uniques, faites main avec passion.</p>
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
    </div>
</div>

<div class="deco-band"><span></span><span></span><span></span><span></span><span></span></div>

{{-- CATEGORIES --}}
<section class="section">
    <div class="section-header">
        <h2>Catégories populaires</h2>
        <p class="section-sub">Explorez l'artisanat par univers</p>
    </div>
    <div class="cat-grid">
        <a href="{{ route('catalogue.categories') }}" class="cat-card fade-in">
            <div class="cat-icon">🎨</div>
            <h3>Peinture</h3>
            <p>Œuvres peintes par des artisans locaux.</p>
            <span class="cat-link-text">Voir les œuvres →</span>
        </a>
        <a href="{{ route('catalogue.categories') }}" class="cat-card fade-in">
            <div class="cat-icon">💍</div>
            <h3>Bijouterie</h3>
            <p>Créations uniques faites main.</p>
            <span class="cat-link-text">Voir les créations →</span>
        </a>
        <a href="{{ route('catalogue.categories') }}" class="cat-card fade-in">
            <div class="cat-icon">🗿</div>
            <h3>Sculpture</h3>
            <p>Objets décoratifs et sculptures d'art.</p>
            <span class="cat-link-text">Voir les sculptures →</span>
        </a>
        <a href="{{ route('catalogue.categories') }}" class="cat-card fade-in">
            <div class="cat-icon">🪘</div>
            <h3>Artisanat</h3>
            <p>Savoir-faire traditionnel africain.</p>
            <span class="cat-link-text">Voir les créations →</span>
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
        <div class="artisan-home-card fade-in">
            <div class="artisan-home-avatar">MD</div>
            <h3 style="color:var(--brun);font-weight:700;margin:0.4rem 0 0.2rem">Marie Dupont</h3>
            <span class="artisan-tag">Peinture contemporaine</span>
            <p style="color:var(--text-mid);font-size:0.88rem">Artiste reconnue pour ses toiles abstraites lumineuses.</p>
            <a href="#" class="card-link">Voir le profil →</a>
        </div>
        <div class="artisan-home-card fade-in">
            <div class="artisan-home-avatar">JK</div>
            <h3 style="color:var(--brun);font-weight:700;margin:0.4rem 0 0.2rem">Jean Kouassi</h3>
            <span class="artisan-tag">Bijoux traditionnels</span>
            <p style="color:var(--text-mid);font-size:0.88rem">Maître joaillier alliant tradition et modernité.</p>
            <a href="#" class="card-link">Voir le profil →</a>
        </div>
        <div class="artisan-home-card fade-in">
            <div class="artisan-home-avatar">FD</div>
            <h3 style="color:var(--brun);font-weight:700;margin:0.4rem 0 0.2rem">Fatoumata Diop</h3>
            <span class="artisan-tag">Sculpture africaine</span>
            <p style="color:var(--text-mid);font-size:0.88rem">Sculptrice inspirée des cultures d'Afrique de l'Ouest.</p>
            <a href="#" class="card-link">Voir le profil →</a>
        </div>
    </div>
</section>

{{-- CTA --}}
<div class="cta-home fade-in">
    <h2>Vous êtes artisan ?</h2>
    <p>Rejoignez notre communauté et vendez vos créations à des milliers d'acheteurs passionnés.</p>
    <a href="{{ route('auth.register') }}" class="btn">Créer mon espace artisan</a>
</div>

@endsection
