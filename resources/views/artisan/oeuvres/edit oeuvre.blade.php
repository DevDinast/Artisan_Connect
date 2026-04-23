@extends('layouts.app')

@section('title', 'Modifier l\'œuvre - ArtisanConnect')

@section('content')

<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 650px">

        <div class="auth-header">
            <div class="auth-icon">✏️</div>
            <h2>Modifier l'œuvre</h2>
            <p>Mettez à jour les informations de votre création</p>
        </div>

        <div id="alert-box"></div>
        <div id="loading" style="text-align:center;padding:2rem;color:var(--text-mid)">Chargement...</div>

        <form id="oeuvreForm" style="display:none">

            <div class="form-group">
                <label>Titre <span style="color:red">*</span></label>
                <input type="text" name="titre" id="titre" required maxlength="100">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="description" rows="4"
                    style="width:100%;padding:0.6rem;border:1px solid #ddd;border-radius:0.5rem;resize:vertical"></textarea>
            </div>

            <div class="form-group">
                <label>Prix (FCFA) <span style="color:red">*</span></label>
                <input type="number" name="prix" id="prix" required min="0">
            </div>

            <div class="form-group">
                <label>Catégorie <span style="color:red">*</span></label>
                <select name="categorie_id" id="categorie_id" required
                    style="width:100%;padding:0.6rem;border:1px solid #ddd;border-radius:0.5rem">
                    <option value="">Choisir une catégorie</option>
                </select>
            </div>

            <div class="form-group">
                <label>Images actuelles</label>
                <div id="images-actuelles" style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.5rem"></div>
            </div>

            <div class="form-group">
                <label>Ajouter de nouvelles images</label>
                <input type="file" name="images[]" id="images" accept="image/*" multiple class="input-file">
            </div>

            <div style="display:flex;gap:1rem">
    <button type="submit" class="btn btn-full" id="submitBtn">💾 Enregistrer</button>
    <button type="button" id="btnSoumettre"
        class="btn btn-full" style="background:#10b981;border-color:#10b981">
        🚀 Soumettre pour validation
    </button>
</div>
<p style="color:var(--text-light);font-size:0.8rem;margin-top:0.5rem;text-align:center">
    Les œuvres refusées ou en attente repassent en brouillon lors d'une modification.
</p>

            <p class="auth-footer-text" style="margin-top:1rem">
                <a href="{{ route('dashboard.artisan') }}">← Retour au dashboard</a>
            </p>
        </form>
    </div>
</div>

@push('scripts')
<script>
// "token" est déclaré en var dans layouts/app.blade.php
const oeuvreId    = {{ $id }};
const authHeaders = { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` };

async function loadCategories(selectedId) {
    try {
        const res  = await fetch('/api/v1/catalog/categories', { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        const cats = Array.isArray(json.data) ? json.data : (Array.isArray(json) ? json : json.data?.data ?? []);
        const sel  = document.getElementById('categorie_id');
        cats.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nom ?? c.name;
            if (c.id == selectedId) opt.selected = true;
            sel.appendChild(opt);
        });
    } catch (e) { console.error('Erreur catégories', e); }
}

async function loadOeuvre() {
    try {
        const res  = await fetch(`/api/v1/artisan/oeuvres/${oeuvreId}`, { headers: authHeaders, credentials: 'include' });
        const json = await res.json();
        const o    = json.data ?? json;

        document.getElementById('loading').style.display    = 'none';
        document.getElementById('oeuvreForm').style.display = 'block';

        document.getElementById('titre').value       = o.titre       ?? '';
        document.getElementById('description').value = o.description ?? '';
        document.getElementById('prix').value        = o.prix        ?? '';

        await loadCategories(o.categorie_id ?? o.categorie?.id);

        const images = o.images ?? [];
        if (images.length) {
            document.getElementById('images-actuelles').innerHTML = images.map(img => {
                const src = img.url ?? (img.chemin ? `/storage/${img.chemin}` : '');
                return `
                <div style="position:relative">
                    <img src="${src}" style="width:70px;height:70px;object-fit:cover;border-radius:0.25rem">
                    <button type="button" onclick="supprimerImage(${img.id}, this)"
                        style="position:absolute;top:-6px;right:-6px;background:red;color:white;border:none;border-radius:50%;width:18px;height:18px;cursor:pointer;font-size:10px;line-height:18px;text-align:center">✕</button>
                </div>`;
            }).join('');
        }
        // Afficher un message selon le statut
const alertBox = document.getElementById('alert-box');
if (o.statut === 'en_attente') {
    alertBox.innerHTML = `<div class="alert" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1rem">
        ⏳ Cette œuvre est en attente de validation. Vous pouvez encore la modifier (elle repassera en brouillon).
    </div>`;
} else if (o.statut === 'refusee') {
    alertBox.innerHTML = `<div class="alert" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1rem">
        ✗ Œuvre refusée — Motif : <strong>${o.motif_refus ?? '(non précisé)'}</strong><br>
        <small>Modifiez votre œuvre et soumettez-la à nouveau.</small>
    </div>`;
}
    } catch (e) {
        document.getElementById('loading').textContent = 'Œuvre introuvable.';
        console.error(e);
    }
}

async function supprimerImage(imageId, btn) {
    if (!confirm('Supprimer cette image ?')) return;
    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
    const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');
    try {
        const res = await fetch(`/api/v1/artisan/images/${imageId}`, {
            method: 'DELETE',
            headers: { ...authHeaders, 'X-XSRF-TOKEN': xsrf },
            credentials: 'include',
        });
        if (res.ok) btn.closest('div').remove();
    } catch (e) { alert('Erreur réseau.'); }
}

async function sauvegarder(soumettre = false) {
    const alertBox = document.getElementById('alert-box');
    const btn      = document.getElementById('submitBtn');
    const btnS     = document.getElementById('btnSoumettre');
    btn.disabled = btnS.disabled = true;
    alertBox.innerHTML = '';

    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
    const xsrf = decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'');

    try {
        const body = {
            titre        : document.getElementById('titre').value.trim(),
            description  : document.getElementById('description').value.trim(),
            prix         : document.getElementById('prix').value,
            categorie_id : document.getElementById('categorie_id').value,
        };

        const res  = await fetch(`/api/v1/artisan/oeuvres/${oeuvreId}`, {
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

        const files = document.getElementById('images').files;
        if (files.length) {
            const formData = new FormData();
            Array.from(files).forEach(f => formData.append('images[]', f));
            await fetch(`/api/v1/artisan/oeuvres/${oeuvreId}/images`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}`, 'X-XSRF-TOKEN': xsrf },
                credentials: 'include',
                body: formData,
            });
        }

        if (soumettre) {
            const soumRes  = await fetch(`/api/v1/artisan/oeuvres/${oeuvreId}/soumettre`, {
                method: 'PUT',
                headers: { ...authHeaders, 'X-XSRF-TOKEN': xsrf },
                credentials: 'include',
            });
            const soumJson = await soumRes.json();
            if (!soumRes.ok || !soumJson.success) {
                alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Soumission échouée : ${soumJson.message ?? 'Erreur.'}</li></ul></div>`;
                return;
            }
        }

        alertBox.innerHTML = `<div class="alert alert-success"><ul><li>${soumettre ? 'Soumise pour validation ✓' : 'Modifications enregistrées ✓'}</li></ul></div>`;
        setTimeout(() => { window.location.href = '{{ route("dashboard.artisan") }}'; }, 1500);

    } catch (e) {
        alertBox.innerHTML = `<div class="alert alert-error"><ul><li>Erreur réseau.</li></ul></div>`;
    } finally {
        btn.disabled = btnS.disabled = false;
    }
}

document.getElementById('oeuvreForm').addEventListener('submit', function(e) { e.preventDefault(); sauvegarder(false); });
document.getElementById('btnSoumettre').addEventListener('click', function() { sauvegarder(true); });

loadOeuvre();
</script>
@endpush

@endsection
