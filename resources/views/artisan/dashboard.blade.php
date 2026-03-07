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
        <a href="#" class="btn" style="margin-left:auto">+ Ajouter une œuvre</a>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <span class="stat-num">0</span>
            <span class="stat-label">Œuvres publiées</span>
        </div>
        <div class="stat-card">
            <span class="stat-num">0</span>
            <span class="stat-label">Commandes reçues</span>
        </div>
        <div class="stat-card">
            <span class="stat-num">0 FCFA</span>
            <span class="stat-label">Revenus totaux</span>
        </div>
        <div class="stat-card">
            <span class="stat-num">0</span>
            <span class="stat-label">Avis clients</span>
        </div>
    </div>

    <section class="section">
        <div class="section-header">
            <h2>Mes œuvres</h2>
            <p class="section-sub">Gérez votre catalogue de créations</p>
        </div>
        <div class="empty-state">
            <span class="empty-icon">🖼️</span>
            <h3>Aucune œuvre publiée</h3>
            <p>Commencez par ajouter votre première création.</p>
            <a href="#" class="btn" style="margin-top:1rem">Ajouter une œuvre</a>
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <h2>Dernières commandes</h2>
            <p class="section-sub">Suivez les achats de vos clients</p>
        </div>
        <div class="empty-state">
            <span class="empty-icon">📦</span>
            <h3>Aucune commande pour le moment</h3>
            <p>Vos commandes apparaîtront ici dès qu'un client achètera une de vos œuvres.</p>
        </div>
    </section>

    <section class="cta-section" style="margin-top:3rem">
        <h2>Boostez votre visibilité</h2>
        <p>Complétez votre profil et ajoutez des photos de qualité pour attirer plus d'acheteurs.</p>
        <a href="#" class="btn">Compléter mon profil</a>
    </section>

</div>

@endsection
