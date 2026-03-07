@extends('layouts.app')

@section('title', 'Connexion - ArtisanConnect')

@section('content')

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-header">
            <div class="auth-icon">✦</div>
            <h2>Bon retour !</h2>
            <p>Connectez-vous à votre espace ArtisanConnect</p>
        </div>

        <div id="alert-box"></div>

        <form id="loginForm">

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="jean@exemple.com" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-full" id="submitBtn">Se connecter</button>

            <p class="auth-footer-text">
                Pas encore de compte ? <a href="{{ route('auth.register') }}">S'inscrire</a>
            </p>
        </form>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('submitBtn');
    const alertBox = document.getElementById('alert-box');
    btn.disabled = true;
    btn.textContent = 'Connexion...';
    alertBox.innerHTML = '';

    try {
        // Étape 1 : récupérer le cookie CSRF de Sanctum
        await fetch('/sanctum/csrf-cookie', {
            method: 'GET',
            credentials: 'include',
        });

        // Étape 2 : lire le token XSRF dans les cookies
        const xsrfToken = decodeURIComponent(
            document.cookie.split('; ').find(r => r.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''
        );

        // Étape 3 : envoyer le formulaire
        const response = await fetch('/api/v1/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': xsrfToken,
            },
            credentials: 'include',
            body: JSON.stringify({
                email: this.email.value,
                password: this.password.value,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            let messages = '';
            if (data.errors) {
                messages = Object.values(data.errors).flat().map(e => `<li>${e}</li>`).join('');
            } else {
                messages = `<li>${data.detail || data.message || data.error || 'Une erreur est survenue.'}</li>`;
            }
            alertBox.innerHTML = `<div class="alert alert-error"><ul>${messages}</ul></div>`;
            return;
        }

        localStorage.setItem('token', data.token);

        const role = data.user?.role;
        window.location.href = role === 'artisan' ? '/dashboard/artisan' : '/dashboard/acheteur';

    } catch (err) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Erreur réseau. Réessayez.</li></ul></div>`;
    } finally {
        btn.disabled = false;
        btn.textContent = 'Se connecter';
    }
});
</script>

@endsection
