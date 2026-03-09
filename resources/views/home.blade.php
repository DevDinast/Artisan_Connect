@extends('layouts.app')

@section('title', 'Accueil - ArtisanConnect')

@section('content')

{{-- HERO --}}
<div class="hero">
    <span class="hero-badge">✦ Plateforme artisanale</span>
    <h1>Bienvenue sur <span class="highlight">ArtisanConnect</span></h1>
    <p>Découvrez des artisans talentueux et achetez leurs œuvres uniques, faites main avec passion.</p>
    <div class="hero-actions">
        <a href="{{ route('auth.register') }}" class="btn">Commencer gratuitement</a>
        <a href="#categories" class="btn-outline">Explorer les œuvres</a>
    </div>
    <div class="hero-stats">
        <div class="stat">
            <strong>+1 200</strong>
            <span>Artisans</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat">
            <strong>+8 500</strong>
            <span>Créations</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat">
            <strong>+30</strong>
            <span>Catégories</span>
        </div>
    </div>
</div>

{{-- CATEGORIES --}}
<section class="section" id="categories">
    <div class="section-header">
        <h2>Catégories populaires</h2>
        <p class="section-sub">Explorez l'artisanat par univers</p>
    </div>
    <div class="cards">
        <div class="card">
            <div class="card-icon">🎨</div>
            <h3>Peinture</h3>
            <p>Œuvres peintes par des artisans locaux.</p>
            <a href="#" class="card-link">Voir les œuvres →</a>
        </div>
        <div class="card">
            <div class="card-icon">💍</div>
            <h3>Bijoux</h3>
            <p>Créations uniques faites main.</p>
            <a href="#" class="card-link">Voir les créations →</a>
        </div>
        <div class="card">
            <div class="card-icon">🗿</div>
            <h3>Sculpture</h3>
            <p>Objets décoratifs et sculptures d'art.</p>
            <a href="#" class="card-link">Voir les sculptures →</a>
        </div>
    </div>
</section>

{{-- ARTISANS --}}
<section class="section">
    <div class="section-header">
        <h2>Artisans à la une</h2>
        <p class="section-sub">Des talents reconnus sur la plateforme</p>
    </div>
    <div class="cards">
        <div class="card card-artisan">
            <div class="artisan-avatar">MD</div>
            <div class="artisan-info">
                <h3>Marie Dupont</h3>
                <span class="artisan-tag">Peinture contemporaine</span>
                <p>Artiste parisienne reconnue pour ses toiles abstraites lumineuses.</p>
            </div>
            <a href="#" class="card-link">Voir le profil →</a>
        </div>
        <div class="card card-artisan">
            <div class="artisan-avatar">JK</div>
            <div class="artisan-info">
                <h3>Jean Kouassi</h3>
                <span class="artisan-tag">Bijoux traditionnels</span>
                <p>Maître joaillier alliant savoir-faire traditionnel et modernité.</p>
            </div>
            <a href="#" class="card-link">Voir le profil →</a>
        </div>
        <div class="card card-artisan">
            <div class="artisan-avatar">FD</div>
            <div class="artisan-info">
                <h3>Fatoumata Diop</h3>
                <span class="artisan-tag">Sculpture africaine</span>
                <p>Sculptrice contemporaine inspirée des cultures d'Afrique de l'Ouest.</p>
            </div>
            <a href="#" class="card-link">Voir le profil →</a>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="cta-section">
    <h2>Vous êtes artisan ?</h2>
    <p>Rejoignez notre communauté et vendez vos créations à des milliers d'acheteurs passionnés.</p>
    <a href="{{ route('auth.register') }}" class="btn">Créer mon espace artisan</a>
</section>

@endsection
