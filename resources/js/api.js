/**
 * ArtisanConnect — Helper API centralisé
 * Inclure ce fichier dans layouts/app.blade.php :
 * <script src="{{ asset('js/api.js') }}"></script>
 */

const API_BASE = '/api/v1';

/**
 * Récupère le token XSRF depuis les cookies (pour Sanctum)
 */
function getXsrfToken() {
    return decodeURIComponent(
        document.cookie.split('; ').find(r => r.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''
    );
}

/**
 * Récupère le token Bearer depuis localStorage
 */
function getBearerToken() {
    return localStorage.getItem('token');
}

/**
 * Appel API générique
 * @param {string} endpoint  - ex: '/catalog/oeuvres'
 * @param {object} options   - fetch options (method, body, etc.)
 * @param {boolean} auth     - true = ajoute le Bearer token
 */
async function apiCall(endpoint, options = {}, auth = false) {
    // Rafraîchir le cookie CSRF avant chaque appel
    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });

    const headers = {
        'Accept': 'application/json',
        'X-XSRF-TOKEN': getXsrfToken(),
        ...(options.headers || {}),
    };

    // Ajouter le Bearer token si authentification requise
    if (auth) {
        const token = getBearerToken();
        if (token) headers['Authorization'] = `Bearer ${token}`;
    }

    // Ne pas forcer Content-Type si FormData (le navigateur le gère)
    if (!(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(`${API_BASE}${endpoint}`, {
        credentials: 'include',
        ...options,
        headers,
    });

    const data = await response.json();
    return { response, data };
}

/**
 * Affiche les erreurs API dans une alertBox
 */
function displayErrors(alertBox, data) {
    let messages = '';
    if (data.errors) {
        messages = Object.values(data.errors).flat().map(e => `<li>${e}</li>`).join('');
    } else {
        messages = `<li>${data.detail || data.message || data.error || 'Une erreur est survenue.'}</li>`;
    }
    alertBox.innerHTML = `<div class="alert alert-error"><ul>${messages}</ul></div>`;
}
