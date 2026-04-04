@extends('layouts.app')
@section('title', 'Inscription - ArtisanConnect')
@section('content')

<div class="auth-wrapper">
    <div class="auth-card" style="max-width:520px">
        <div class="auth-header">
            <div class="auth-icon">✦</div>
            <h2>Créer un compte</h2>
            <p>Rejoignez la communauté ArtisanConnect</p>
        </div>

        <div id="alert-box"></div>

        <form id="registerForm">
            <div class="role-selector">
                <label class="role-option">
                    <input type="radio" name="role" value="acheteur" checked>
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
                <label>Nom complet <span style="color:var(--terra)">*</span></label>
                <input type="text" id="name" placeholder="Jean Dupont" required maxlength="100">
            </div>
            <div class="form-group">
                <label>Email <span style="color:var(--terra)">*</span></label>
                <input type="email" id="email" placeholder="vous@exemple.com" required>
            </div>
            <div class="form-group">
                <label>Mot de passe <span style="color:var(--terra)">*</span></label>
                <input type="password" id="password" placeholder="Minimum 8 caractères" required>
            </div>
            <div class="form-group">
                <label>Confirmer le mot de passe <span style="color:var(--terra)">*</span></label>
                <input type="password" id="password_confirmation" placeholder="Répétez le mot de passe" required>
            </div>
            <div class="form-group">
                <label>Photo de profil <span class="optional">(optionnel)</span></label>
                <input type="file" id="avatar" accept="image/*" class="input-file">
            </div>

            <button type="submit" class="btn btn-full" id="submitBtn">Créer mon compte</button>
        </form>

        <p class="auth-footer-text" style="margin-top:1.25rem">
            Déjà un compte ? <a href="{{ route('auth.login') }}">Se connecter</a>
        </p>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn      = document.getElementById('submitBtn');
    const alertBox = document.getElementById('alert-box');
    btn.disabled    = true;
    btn.textContent = 'Création...';
    alertBox.innerHTML = '';

    const role = document.querySelector('input[name="role"]:checked')?.value ?? 'acheteur';

    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
    const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');

    try {
        const formData = new FormData();
        formData.append('name',                  document.getElementById('name').value.trim());
        formData.append('email',                 document.getElementById('email').value.trim());
        formData.append('password',              document.getElementById('password').value);
        formData.append('password_confirmation', document.getElementById('password_confirmation').value);
        formData.append('role',                  role);
        const avatar = document.getElementById('avatar').files[0];
        if (avatar) formData.append('avatar', avatar);

        const res  = await fetch('/api/v1/auth/register', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            body: formData,
        });
        const json = await res.json();

        if (!res.ok) {
            const msgs = json.errors
                ? Object.values(json.errors).flat().map(e=>`<li>${e}</li>`).join('')
                : `<li>${json.message ?? 'Erreur.'}</li>`;
            alertBox.innerHTML = `<div class="alert alert-error"><ul>${msgs}</ul></div>`;
            return;
        }

        if (json.data?.token) localStorage.setItem('token', json.data.token);
        alertBox.innerHTML = `<div class="alert alert-success"><ul><li>Compte créé avec succès ! Redirection...</li></ul></div>`;

        const dashLinks = { 'artisan':'dashboard/artisan', 'acheteur':'dashboard/acheteur', 'administrateur':'dashboard/admin' };
        setTimeout(() => { window.location.href = '/' + (dashLinks[role] ?? ''); }, 1200);

    } catch (e) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Erreur réseau. Réessayez.</li></ul></div>`;
        console.error(e);
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Créer mon compte';
    }
});
</script>

@endsection