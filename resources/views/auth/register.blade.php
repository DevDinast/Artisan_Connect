@extends('layouts.app')

@section('title', 'Inscription - ArtisanConnect')

@section('content')

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-header">
            <div class="auth-icon">✦</div>
            <h2>Créer un compte</h2>
            <p>Rejoignez la communauté ArtisanConnect</p>
        </div>

        <div id="alert-box"></div>

        <form id="registerForm" enctype="multipart/form-data">

            <div class="role-selector">
                <label class="role-option">
                    <input type="radio" name="role" value="acheteur">
                    <div class="role-card">
                        <span class="role-emoji">🛍️</span>
                        <strong>Acheteur</strong>
                        <small>Découvrez et achetez des œuvres</small>
                    </div>
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="artisan">
                    <div class="role-card">
                        <span class="role-emoji">🎨</span>
                        <strong>Artisan</strong>
                        <small>Vendez vos créations</small>
                    </div>
                </label>
            </div>

            <div class="form-group">
                <label>Nom complet</label>
                <input type="text" name="name" placeholder="Jean Dupont" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="jean@exemple.com" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <div class="form-group">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" placeholder="••••••••" required>
            </div>
            <div class="form-group">
                <label>Photo de profil <span class="optional">(optionnel)</span></label>
                <input type="file" name="avatar" accept="image/*" class="input-file">
            </div>

            <button type="submit" class="btn btn-full" id="submitBtn">S'inscrire</button>

            <p class="auth-footer-text">
                Déjà un compte ? <a href="{{ route('auth.login') }}">Se connecter</a>
            </p>
        </form>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn      = document.getElementById('submitBtn');
    const alertBox = document.getElementById('alert-box');
    btn.disabled   = true;
    btn.textContent = 'Inscription...';
    alertBox.innerHTML = '';

    // Validation du rôle côté JS
    const role = this.querySelector('input[name="role"]:checked')?.value;
    if (!role) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Veuillez choisir un rôle (Acheteur ou Artisan).</li></ul></div>`;
        btn.disabled = false;
        btn.textContent = "S'inscrire";
        return;
    }

    try {
        await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });

        const xsrfToken = decodeURIComponent(
            document.cookie.split('; ').find(r => r.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''
        );

        const formData = new FormData(this);

        const response = await fetch('/api/v1/auth/register', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-XSRF-TOKEN': xsrfToken,
            },
            credentials: 'include',
            body: formData,
        });

        const data = await response.json();

        if (!response.ok) {
            let messages = '';
            if (data.errors) {
                messages = Object.values(data.errors).flat().map(e => `<li>${e}</li>`).join('');
            } else {
                messages = `<li>${data.message || 'Une erreur est survenue.'}</li>`;
            }
            alertBox.innerHTML = `<div class="alert alert-error"><ul>${messages}</ul></div>`;
            return;
        }

        // ✅ Le contrôleur retourne { success, data: { user, token }, message }
        const token    = data.data?.token ?? data.token;
        const userRole = data.data?.user?.role ?? data.user?.role;

        if (token) localStorage.setItem('token', token);

        // ✅ Redirection selon le rôle
        if (userRole === 'artisan') {
            window.location.href = '/dashboard/artisan';
        } else if (userRole === 'administrateur') {
            window.location.href = '/dashboard/admin';
        } else {
            window.location.href = '/dashboard/acheteur';
        }

    } catch (err) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Erreur réseau. Réessayez.</li></ul></div>`;
        console.error(err);
    } finally {
        btn.disabled = false;
        btn.textContent = "S'inscrire";
    }
});
</script>

@endsection
