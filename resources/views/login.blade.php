<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<form id="loginForm">
    <input type="email" id="email" placeholder="Email" required>
    <input type="password" id="password" placeholder="Mot de passe" required>
    <button type="submit">Connexion</button>
</form>

<script type="module">
import api from '/resources/js/services/api.js';
import { setAuth } from '/resources/js/utils/auth.js';

const form = document.getElementById("loginForm");

form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    try {
        const response = await api.post("/login", {
            email,
            password
        });

        // Sauvegarde token et utilisateur
        setAuth(response.data.user, response.data.token);

        // Redirection vers dashboard
        window.location.href = "/dashboard";

    } catch (err) {
        alert(err.response.data.message || "Erreur de connexion");
    }
});
</script>
