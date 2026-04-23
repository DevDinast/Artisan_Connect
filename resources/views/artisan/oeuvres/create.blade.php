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

        <form id="oeuvreForm">

            <div class="form-group">
                <label>Titre <span style="color:red">*</span></label>
                <input type="text" id="titre" placeholder="Ex: Vase en argile" required maxlength="255">
            </div>

            <div class="form-group">
                <label>Description <span style="color:red">*</span></label>
                <textarea id="description" placeholder="Décrivez votre œuvre..." rows="4" required
                    style="width:100%;padding:0.6rem;border:1px solid #ddd;border-radius:0.5rem;resize:vertical"></textarea>
            </div>

            <div class="form-group">
                <label>Prix (FCFA) <span style="color:red">*</span></label>
                <input type="number" id="prix" placeholder="Ex: 15000" required min="0">
            </div>

            <div class="form-group">
                <label>Stock <span style="color:red">*</span></label>
                <input type="number" id="stock" placeholder="Ex: 1" required min="0" value="1">
            </div>

            <div class="form-group">
                <label>Catégorie <span style="color:red">*</span></label>
                <select id="categorie_id" required style="width:100%;padding:0.6rem;border:1px solid #ddd;border-radius:0.5rem">
                    <option value="">Choisir une catégorie</option>
                </select>
            </div>

            <div class="form-group">
                <label>Images <span style="color:#888">(au moins 1 image requise pour soumettre)</span></label>
                <input type="file" id="images" accept="image/*" multiple class="input-file">
                <div id="images-preview" style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.5rem"></div>
            </div>

            <div style="display:flex;gap:1rem;margin-top:1rem">
                <button type="submit" class="btn btn-full" id="submitBtn">
                    💾 Enregistrer en brouillon
                </button>
                <button type="button" class="btn btn-full" id="btnSoumettre"
                    style="background:#10b981;border-color:#10b981">
                    🚀 Soumettre pour validation
                </button>
            </div>

            <p class="auth-footer-text" style="margin-top:1rem">
                <a href="{{ route('dashboard.artisan') }}">← Retour au dashboard</a>
            </p>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    // CORRECTION 1 : plus de "const token" → on utilise "token" (var globale du layout)
    // CORRECTION 2 : plus de "const authHeaders" figé → fonction qui relit token à chaque appel
    // CORRECTION 3 : tout dans une IIFE pour éviter toute pollution de scope global

    let oeuvreCreeeId = null;

    function getAuthHeaders(extra) {
        return Object.assign({
            'Accept'        : 'application/json',
            'Authorization' : 'Bearer ' + token,
        }, extra || {});
    }

    async function getXsrf() {
        await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
        return decodeURIComponent(
            document.cookie.split('; ')
                .find(r => r.startsWith('XSRF-TOKEN='))
                ?.split('=')[1] ?? ''
        );
    }

    function showAlert(message, type) {
        const alertBox = document.getElementById('alert-box');
        const cls      = type === 'success' ? 'alert-success' : 'alert-error';
        alertBox.innerHTML = `<div class="alert ${cls}"><ul><li>${message}</li></ul></div>`;
        alertBox.scrollIntoView({ behavior: 'smooth' });
    }

    // ── Prévisualisation images ───────────────────────────────────────────────
    document.getElementById('images').addEventListener('change', function () {
        const preview = document.getElementById('images-preview');
        preview.innerHTML = '';
        Array.from(this.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                preview.innerHTML += `<img src="${e.target.result}" style="width:70px;height:70px;object-fit:cover;border-radius:0.25rem">`;
            };
            reader.readAsDataURL(file);
        });
    });

    // ── Charger les catégories ────────────────────────────────────────────────
    async function loadCategories() {
        try {
            const res  = await fetch('/api/v1/catalog/categories', { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            const cats = Array.isArray(json.data) ? json.data : (Array.isArray(json) ? json : (json.data?.data ?? []));
            const sel  = document.getElementById('categorie_id');

            if (!cats.length) {
                sel.innerHTML += '<option disabled>Aucune catégorie disponible</option>';
                return;
            }
            cats.forEach(c => {
                const opt       = document.createElement('option');
                opt.value       = c.id;
                opt.textContent = c.nom ?? c.name;
                sel.appendChild(opt);
            });
        } catch (e) {
            console.error('Erreur catégories', e);
        }
    }

    // ── Créer l'œuvre ─────────────────────────────────────────────────────────
    async function creerOeuvre(soumettre) {
        const btn  = document.getElementById('submitBtn');
        const btnS = document.getElementById('btnSoumettre');
        btn.disabled = btnS.disabled = true;
        document.getElementById('alert-box').innerHTML = '';

        if (!token) {
            showAlert('Vous n\'êtes pas connecté. <a href="{{ route("auth.login") }}">Se connecter</a>');
            btn.disabled = btnS.disabled = false;
            return;
        }

        try {
            const xsrf = await getXsrf();

            // ── Étape 1 : créer l'œuvre en brouillon ─────────────────────────
            const res = await fetch('/api/v1/artisan/oeuvres', {
                method      : 'POST',
                headers     : getAuthHeaders({ 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf }),
                credentials : 'include',
                body        : JSON.stringify({
                    titre        : document.getElementById('titre').value.trim(),
                    description  : document.getElementById('description').value.trim(),
                    prix         : document.getElementById('prix').value,
                    stock        : document.getElementById('stock').value,
                    categorie_id : document.getElementById('categorie_id').value,
                }),
            });
            const json = await res.json();

            if (!res.ok) {
                if (res.status === 401) { showAlert('Session expirée. <a href="{{ route("auth.login") }}">Reconnectez-vous</a>.'); return; }
                if (res.status === 403) { showAlert('Accès refusé : email non vérifié ou rôle incorrect.'); return; }
                const msgs = json.errors
                    ? Object.values(json.errors).flat().join(', ')
                    : (json.message ?? 'Erreur inconnue.');
                showAlert(msgs);
                return;
            }

            oeuvreCreeeId = json.data?.oeuvre?.id ?? json.data?.id ?? json.id;

            // ── Étape 2 : uploader les images ─────────────────────────────────
            const files = document.getElementById('images').files;
            if (files.length && oeuvreCreeeId) {
                const formData = new FormData();
                Array.from(files).forEach(f => formData.append('images[]', f));

                // CORRECTION : ne pas forcer Content-Type sur FormData
                // Le navigateur ajoute automatiquement le bon boundary multipart
                const imgRes  = await fetch(`/api/v1/artisan/oeuvres/${oeuvreCreeeId}/images`, {
                    method      : 'POST',
                    headers     : getAuthHeaders({ 'X-XSRF-TOKEN': xsrf }),
                    credentials : 'include',
                    body        : formData,
                });

                if (!imgRes.ok) {
                    const imgJson = await imgRes.json().catch(() => ({}));
                    showAlert('Œuvre créée mais erreur upload images : ' + (imgJson.message ?? ''));
                    btn.disabled = btnS.disabled = false;
                    return;
                }
            }

            // ── Étape 3 : soumettre pour validation ───────────────────────────
            if (soumettre && oeuvreCreeeId) {
                const soumRes  = await fetch(`/api/v1/artisan/oeuvres/${oeuvreCreeeId}/soumettre`, {
                    method      : 'PUT',
                    headers     : getAuthHeaders({ 'X-XSRF-TOKEN': xsrf }),
                    credentials : 'include',
                });
                const soumJson = await soumRes.json();

                if (!soumRes.ok || !soumJson.success) {
                    showAlert('Œuvre créée mais soumission échouée : ' + (soumJson.message ?? 'Erreur inconnue.'));
                    setTimeout(() => { window.location.href = '{{ route("dashboard.artisan") }}'; }, 3000);
                    return;
                }

                showAlert('Œuvre soumise pour validation ✓ — en attente de l\'admin.', 'success');
            } else {
                showAlert('Œuvre enregistrée en brouillon ✓', 'success');
            }

            setTimeout(() => { window.location.href = '{{ route("dashboard.artisan") }}'; }, 1500);

        } catch (e) {
            showAlert('Erreur réseau. Veuillez réessayer.');
            console.error(e);
        } finally {
            btn.disabled = btnS.disabled = false;
        }
    }

    document.getElementById('oeuvreForm').addEventListener('submit', e => { e.preventDefault(); creerOeuvre(false); });
    document.getElementById('btnSoumettre').addEventListener('click', () => creerOeuvre(true));

    loadCategories();
})();
</script>
@endpush
