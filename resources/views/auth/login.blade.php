@extends('layouts.app')
@section('title', 'Connexion - ArtisanConnect')
@section('content')

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">🔐</div>
            <h2>Bon retour !</h2>
            <p>Connectez-vous à votre espace ArtisanConnect</p>
        </div>

        <div id="alert-box"></div>

        <form id="loginForm">
            <div class="form-group">
                <label>Adresse email</label>
                <input type="email" id="email" placeholder="vous@exemple.com" required autocomplete="email">
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" id="password" placeholder="••••••••" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-full" id="submitBtn">Se connecter</button>
        </form>

        <p class="auth-footer-text" style="margin-top:1.25rem">
            Pas encore de compte ? <a href="{{ route('auth.register') }}">S'inscrire gratuitement</a>

        </p>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn      = document.getElementById('submitBtn');
    const alertBox = document.getElementById('alert-box');
    btn.disabled    = true;
    btn.textContent = 'Connexion...';
    alertBox.innerHTML = '';

    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
    const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');

    try {
        const res  = await fetch('/api/v1/auth/login', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            body: JSON.stringify({
                email    : document.getElementById('email').value.trim(),
                password : document.getElementById('password').value,
            }),
        });
        const json = await res.json();

        if (!res.ok) {
            const msg = json.errors ? Object.values(json.errors).flat().join(', ') : (json.message ?? 'Identifiants incorrects.');
            alertBox.innerHTML = `<div class="alert alert-error"><ul><li>${msg}</li></ul></div>`;
            return;
        }

        if (json.data?.token) {
            localStorage.setItem('token', json.data.token);
        }

        const role = json.data?.user?.role ?? json.data?.role;
        const dashLinks = {
            'artisan'       : 'dashboard/artisan',
            'acheteur'      : 'dashboard/acheteur',
            'administrateur': 'dashboard/admin',
            'admin'         : 'dashboard/admin'
        };
        window.location.href = '/' + (dashLinks[role] ?? '');

    } catch (e) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Erreur réseau. Réessayez.</li></ul></div>`;
        console.error(e);
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Se connecter';
    }
});
</script>
@endpush

@endsection
