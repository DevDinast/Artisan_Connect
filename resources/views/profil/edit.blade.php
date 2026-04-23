@extends('layouts.app')

@section('title', 'Mon profil - ArtisanConnect')

@section('content')

<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 600px">

        <div class="auth-header">
            <div class="auth-icon">👤</div>
            <h2>Mon profil</h2>
            <p>Mettez à jour vos informations personnelles</p>
        </div>

        <div id="alert-box"></div>

        <form id="profilForm">
            <div class="form-group">
                <label>Nom complet</label>
                <input type="text" name="name" id="name" placeholder="Jean Dupont" maxlength="20">
            </div>
            <div class="form-group">
                <label>Téléphone</label>
                <input type="text" name="telephone" id="telephone" placeholder="+229 00 00 00 00" maxlength="15">
            </div>

            <div id="artisan-fields" style="display:none">
                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" id="bio" placeholder="Parlez de votre art..." maxlength="255" rows="3"
                        style="width:100%;padding:0.6rem;border:1px solid #ddd;border-radius:0.5rem;resize:vertical"></textarea>
                </div>
                <div class="form-group">
                    <label>Spécialité</label>
                    <input type="text" name="specialite" id="specialite" placeholder="Ex: Peinture sur bois" maxlength="30">
                </div>
                <div class="form-group">
                    <label>Région</label>
                    <input type="text" name="region" id="region" placeholder="Ex: Cotonou" maxlength="30">
                </div>
                <div class="form-group">
                    <label>Adresse atelier</label>
                    <input type="text" name="atelier_adresse" id="atelier_adresse" placeholder="Ex: Rue 123, Zogbo" maxlength="30">
                </div>
            </div>

            <div id="acheteur-fields" style="display:none">
                <div class="form-group">
                    <label>Adresse de livraison</label>
                    <input type="text" name="adresse_livraison" id="adresse_livraison" placeholder="Ex: Quartier Aidjèdo" maxlength="30">
                </div>
                <div class="form-group">
                    <label>Préférences</label>
                    <input type="text" name="preferences" id="preferences" placeholder="Ex: Peinture, Bijoux" maxlength="20">
                </div>
            </div>

            <button type="submit" class="btn btn-full" id="submitBtn">Enregistrer</button>
            <p class="auth-footer-text" style="margin-top:1rem">
                <a href="{{ url()->previous() }}">← Retour</a>
            </p>
        </form>

        <div style="margin-top:2rem;border-top:1px solid #eee;padding-top:1.5rem">
            <h3 style="margin-bottom:1rem">Photo de profil</h3>
            <div id="avatar-preview" style="margin-bottom:1rem"></div>
            <input type="file" id="avatarInput" accept="image/jpeg,image/png" style="margin-bottom:0.5rem">
            <button class="btn" id="avatarBtn" style="margin-top:0.5rem">Mettre à jour la photo</button>
            <div id="avatar-alert"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// "token" est déclaré en var dans layouts/app.blade.php
const authHeaders = { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` };

async function loadProfil() {
    try {
        const res  = await fetch('/api/v1/me', { headers: authHeaders, credentials: 'include' });
        const json = await res.json();
        const user = json.data?.user ?? json;

        document.getElementById('name').value      = user.name      ?? '';
        document.getElementById('telephone').value = user.telephone ?? '';

        if (user.role === 'artisan') {
            document.getElementById('artisan-fields').style.display = 'block';
            document.getElementById('bio').value             = user.artisan?.bio             ?? '';
            document.getElementById('specialite').value      = user.artisan?.specialite      ?? '';
            document.getElementById('region').value          = user.artisan?.region          ?? '';
            document.getElementById('atelier_adresse').value = user.artisan?.atelier_adresse ?? '';
        } else if (user.role === 'acheteur') {
            document.getElementById('acheteur-fields').style.display = 'block';
            document.getElementById('adresse_livraison').value = user.acheteur?.adresse_livraison ?? '';
            document.getElementById('preferences').value       = user.acheteur?.preferences       ?? '';
        }

        if (user.avatar) {
            const avatarUrl = user.avatar.startsWith('http') ? user.avatar : `/storage/${user.avatar}`;
            document.getElementById('avatar-preview').innerHTML =
                `<img src="${avatarUrl}" style="width:80px;height:80px;border-radius:50%;object-fit:cover"
                      onerror="this.style.display='none'">`;
        }
    } catch (e) { console.error(e); }
}

document.getElementById('profilForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn      = document.getElementById('submitBtn');
    const alertBox = document.getElementById('alert-box');
    btn.disabled    = true;
    btn.textContent = 'Enregistrement...';
    alertBox.innerHTML = '';

    const fields = ['name','telephone','bio','specialite','region','atelier_adresse','adresse_livraison','preferences'];
    const body = {};
    fields.forEach(f => { const el = document.getElementById(f); if (el?.value.trim()) body[f] = el.value.trim(); });

    try {
        await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
        const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');

        const res  = await fetch('/api/v1/me', {
            method: 'PUT',
            headers: { ...authHeaders, 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            body: JSON.stringify(body),
        });
        const json = await res.json();

        if (!res.ok) {
            const msgs = json.errors
                ? Object.values(json.errors).flat().map(e=>`<li>${e}</li>`).join('')
                : `<li>${json.message ?? 'Erreur.'}</li>`;
            alertBox.innerHTML = `<div class="alert alert-error"><ul>${msgs}</ul></div>`;
            return;
        }
        alertBox.innerHTML = `<div class="alert alert-success"><ul><li>Profil mis à jour ✓</li></ul></div>`;
    } catch (e) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Erreur réseau.</li></ul></div>`;
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Enregistrer';
    }
});

document.getElementById('avatarBtn').addEventListener('click', async function() {
    const file     = document.getElementById('avatarInput').files[0];
    const alertBox = document.getElementById('avatar-alert');
    if (!file) { alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Sélectionnez une image.</li></ul></div>`; return; }

    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
    const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');
    const formData = new FormData();
    formData.append('avatar', file);

    try {
        const res  = await fetch('/api/v1/me/avatar', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}`, 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            body: formData,
        });
        const json = await res.json();
        if (!res.ok) {
            alertBox.innerHTML = `<div class="alert alert-error"><ul><li>${json.message ?? 'Erreur.'}</li></ul></div>`;
            return;
        }
        document.getElementById('avatar-preview').innerHTML =
            `<img src="${json.data?.avatar_url}" style="width:80px;height:80px;border-radius:50%;object-fit:cover">`;
        alertBox.innerHTML = `<div class="alert alert-success"><ul><li>Photo mise à jour ✓</li></ul></div>`;
    } catch (e) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Erreur réseau.</li></ul></div>`;
    }
});

loadProfil();
</script>
@endpush

@endsection
