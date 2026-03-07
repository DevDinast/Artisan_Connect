@extends('layouts.app')

@section('title', 'Mon espace - ArtisanConnect')

@section('content')

<div class="dashboard">

    <div class="dashboard-hero">
        <div class="dashboard-avatar">{{ auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'U' }}</div>
        <div>
            <h1>Bonjour, {{ auth()->user()?->name ?? 'Utilisateur' }} 👋</h1>
            <p>Découvrez les dernières créations de nos artisans.</p>
        </div>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <span class="stat-num">0</span>
            <span class="stat-label">Commandes</span>
        </div>
        <div class="stat-card">
            <span class="stat-num">0</span>
            <span class="stat-label">Favoris</span>
        </div>
        <div class="stat-card">
            <span class="stat-num">0</span>
            <span class="stat-label">Articles au panier</span>
        </div>
    </div>

    <section class="section">
        <div class="section-header">
            <h2>Parcourir par catégorie</h2>
            <p class="section-sub">Trouvez l'œuvre qui vous parle</p>
        </div>
        <div class="cards">
            <div class="card">
                <div class="card-icon">🎨</div>
                <h3>Peinture</h3>
                <p>Toiles et œuvres peintes par des artisans locaux.</p>
                <a href="#" class="card-link">Explorer →</a>
            </div>
            <div class="card">
                <div class="card-icon">💍</div>
                <h3>Bijoux</h3>
                <p>Créations uniques faites main avec passion.</p>
                <a href="#" class="card-link">Explorer →</a>
            </div>
            <div class="card">
                <div class="card-icon">🗿</div>
                <h3>Sculpture</h3>
                <p>Objets décoratifs et sculptures d'art originaux.</p>
                <a href="#" class="card-link">Explorer →</a>
            </div>
            <div class="card">
                <div class="card-icon">🧵</div>
                <h3>Textile</h3>
                <p>Tissus, broderies et créations textiles artisanales.</p>
                <a href="#" class="card-link">Explorer →</a>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <h2>Artisans à découvrir</h2>
            <p class="section-sub">Des talents sélectionnés pour vous</p>
        </div>
        <div class="cards">
            <div class="card">
                <div class="artisan-avatar">MD</div>
                <h3>Marie Dupont</h3>
                <span class="artisan-tag">Peinture contemporaine</span>
                <p>Artiste reconnue pour ses toiles abstraites lumineuses.</p>
                <a href="#" class="card-link">Voir le profil →</a>
            </div>
            <div class="card">
                <div class="artisan-avatar">JK</div>
                <h3>Jean Kouassi</h3>
                <span class="artisan-tag">Bijoux traditionnels</span>
                <p>Maître joaillier alliant tradition et modernité.</p>
                <a href="#" class="card-link">Voir le profil →</a>
            </div>
            <div class="card">
                <div class="artisan-avatar">FD</div>
                <h3>Fatoumata Diop</h3>
                <span class="artisan-tag">Sculpture africaine</span>
                <p>Sculptrice inspirée des cultures d'Afrique de l'Ouest.</p>
                <a href="#" class="card-link">Voir le profil →</a>
            </div>
        </div>
    </section>

</div>

@endsection
