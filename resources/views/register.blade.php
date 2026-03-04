<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<form id="registerForm">
    <select id="role" required>
        <option value="">Choisir un rôle</option>
        <option value="artisan">Artisan</option>
        <option value="client">Client</option>
    </select>

    <input type="text" id="name" placeholder="Nom complet" required>
    <input type="email" id="email" placeholder="Email" required>
    <input type="password" id="password" placeholder="Mot de passe" required>
    <input type="password" id="password_confirmation" placeholder="Confirmer mot de passe" required>

    <button type="submit">S’inscrire</button>
</form>

<script type="module">
import api from '/resources/js/services/api.js';
import { setAuth } from '/resources/js/utils/auth.js';

const form = document.getElementById("registerForm");

form.addEventListener("submit", async (e) => {
    e.preventDefault();

    try {
        const response = await api.post("/register", {
            role: document.getElementById("role").value,
            name: document.getElementById("name").value,
            email: document.getElementById("email").value,
            password: document.getElementById("password").value,
            password_confirmation: document.getElementById("password_confirmation").value,
        });

        // Stocke token et user
        setAuth(response.data.user, response.data.token);

        alert("Inscription réussie !");
        window.location.href = "/dashboard";

    } catch (err) {
        alert(err.response.data.message || "Erreur inscription");
    }
});
</script>
