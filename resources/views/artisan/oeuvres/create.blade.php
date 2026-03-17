@extends('layouts.app')

@section('title', 'Ajouter une œuvre - ArtisanConnect')

@section('content')

<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 650px">

        <div class="auth-header">
            <div class="auth-icon">🖼️</div>
            <h2>Ajouter une œuvre</h2>
            <p>Publiez une nouvelle création dans votre atelier</p>
        </div>

        <div id="alert-box"></div>

        <form id="oeuvreForm" enctype="multipart/form-data">

            <div class="form-group">
                <label>Titre <span style="color:red">*</span></label>
                <input type="text" name="titre" id="titre" placeholder="Ex: Vase en argile" required maxlength="100">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="description" placeholder="Décrivez votre œuvre..." rows="4"
                    style="width:100%;padding:0.6rem;border:1px solid #ddd;border-radius:0.5rem;resize:vertical"></textarea>
            </div>

            <div class="form-group">
                <label>Prix (FCFA) <span style="color:red">*</span></label>
                <input type="number" name="prix" id="prix" placeholder="Ex: 15000" required min="0">
            </div>

            <div class="form-group">
                <label>Catégorie <span style="color:red">*</span></label>
                <select name="categorie_id" id="categorie_id" required style="width:100%;padding:0.6rem;border:1px solid #ddd;border-radius:0.5rem">
                    <option value="">Choisir une catégorie</option>
                </select>
            </div>

            <div class="form-group">
                <label>Images <span class="optional">(jusqu'à 5 images)</span></label>
                <input type="file" name="images[]" id="images" accept="image/*" multiple class="input-file">
            </div>

            <div style="display:flex;gap:1rem">
                <button type="submit" class="btn btn-full" id="submitBtn">
                    Enregistrer en brouillon
                </button>
                <button type="button" class="btn btn-full" id="btnSoumettre"
                    style="background:#10b981;border-color:#10b981">
                    Soumettre pour validation
                </button>
            </div>

            <p class="auth-footer-text" style="margin-top:1rem">
                <a href="{{ route('dashboard.artisan') }}">← Retour au dashboard</a>
            </p>
        </form>
    </div>
</div>

<script>
const token = localStorage.getItem('token');
const authHeaders = { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` };
let oeuvreCreeeId = null;

async function loadCategories() {
    try {
        const res  = await fetch('/api/v1/catalog/categories', { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        // ✅ Fix : gérer les deux formats (tableau direct ou paginé)
        const cats = Array.isArray(json.data) ? json.data : (Array.isArray(json) ? json : json.data?.data ?? []);
        const sel  = document.getElementById('categorie_id');

        if (!cats.length) {
            sel.innerHTML += '<option disabled>Aucune catégorie disponible</option>';
            return;
        }

        cats.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nom;
            sel.appendChild(opt);
        });
    } catch (e) { console.error('Erreur catégories', e); }
}

async function creerOeuvre(soumettre = false) {
    const alertBox = document.getElementById('alert-box');
    const btn      = document.getElementById('submitBtn');
    const btnS     = document.getElementById('btnSoumettre');
    btn.disabled = btnS.disabled = true;
    alertBox.innerHTML = '';

    // ✅ Vérifier que le token existe
    if (!token) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Vous n'êtes pas connecté. <a href="{{ route('auth.login') }}">Se connecter</a></li></ul></div>`;
        btn.disabled = btnS.disabled = false;
        return;
    }

    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
    const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');

    try {
        const body = {
            titre        : document.getElementById('titre').value.trim(),
            description  : document.getElementById('description').value.trim(),
            prix         : document.getElementById('prix').value,
            categorie_id : document.getElementById('categorie_id').value,
        };

        const res  = await fetch('/api/v1/artisan/oeuvres', {
            method: 'POST',
            headers: { ...authHeaders, 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
            body: JSON.stringify(body),
        });
        const json = await res.json();

        if (!res.ok) {
            // ✅ Message clair si 403
            if (res.status === 403) {
                alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Accès refusé (403) : vérifiez que votre email est vérifié et que votre compte est bien de type artisan.</li></ul></div>`;
                return;
            }
            const msgs = json.errors
                ? Object.values(json.errors).flat().map(e=>`<li>${e}</li>`).join('')
                : `<li>${json.message ?? 'Erreur.'}</li>`;
            alertBox.innerHTML = `<div class="alert alert-error"><ul>${msgs}</ul></div>`;
            return;
        }

        oeuvreCreeeId = json.data?.id ?? json.id;

        const files = document.getElementById('images').files;
        if (files.length && oeuvreCreeeId) {
            const formData = new FormData();
            Array.from(files).forEach(f => formData.append('images[]', f));
            await fetch(`/api/v1/artisan/oeuvres/${oeuvreCreeeId}/images`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}`, 'X-XSRF-TOKEN': xsrf },
                credentials: 'include',
                body: formData,
            });
        }

        if (soumettre && oeuvreCreeeId) {
            await fetch(`/api/v1/artisan/oeuvres/${oeuvreCreeeId}/soumettre`, {
                method: 'PUT',
                headers: { ...authHeaders, 'X-XSRF-TOKEN': xsrf },
                credentials: 'include',
            });
        }

        alertBox.innerHTML = `<div class="alert alert-success"><ul><li>
            ${soumettre ? 'Œuvre soumise pour validation ✓' : 'Œuvre enregistrée en brouillon ✓'}
        </li></ul></div>`;
        setTimeout(() => { window.location.href = '{{ route("dashboard.artisan") }}'; }, 1500);

    } catch (e) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Erreur réseau.</li></ul></div>`;
        console.error(e);
    } finally {
        btn.disabled = btnS.disabled = false;
    }
}

document.getElementById('oeuvreForm').addEventListener('submit', function(e) { e.preventDefault(); creerOeuvre(false); });
document.getElementById('btnSoumettre').addEventListener('click', function() { creerOeuvre(true); });

loadCategories();
</script>

@endsection
