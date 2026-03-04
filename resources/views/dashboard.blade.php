<h1>Bienvenue,Dashboard  <span id="userName"></span> !</h1>
<button id="logoutBtn">Déconnexion</button>

<script type="module">
import { getUser, logout } from '/resources/js/auth.js';

const user = getUser();
if (user) {
    document.getElementById("userName").textContent = user.name;
} else {
    window.location.href = "/login";
}

document.getElementById("logoutBtn").addEventListener("click", logout);
</script>

                                                       