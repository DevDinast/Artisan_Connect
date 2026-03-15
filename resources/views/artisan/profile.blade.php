
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil Artisan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        .profile-card {
            max-width: 37.5rem; margin: auto; background: #fff; padding: 1.25rem;
            border-radius: 0.5rem; box-shadow: 0 0.12rem 0.37rem rgba(0,0,0,0.1);
        }
        .profile-card img {
            width: 7.5rem; height: 7.5rem; border-radius: 50%; object-fit: cover;
        }
        .profile-info { margin-top: 0.9rem; }
        .profile-info h2 { margin: 0; font-size: 1.5em; }
        .profile-info p { margin: 0.31rem 0; color: #555; }
        .whatsapp-btn {
            display: inline-block; margin-top: 0.9rem; padding: 0.62rem 0.9rem;
            background: #25D366; color: #fff; text-decoration: none;
            border-radius: 0.31rem; font-weight: bold;
        }
        .whatsapp-btn:hover { background: #20b858; }
    </style>
</head>
<body>
    <div class="profile-card">
        <!-- Photo artisan -->
        <img src="{{ $artisan->photo ?? 'default.jpg' }}" alt="Photo artisan">

        <!-- Infos artisan -->
        <div class="profile-info">
            <h2>{{ $artisan->nom }}</h2>
            <p><strong>Métier :</strong> {{ $artisan->metier }}</p>
            <p><strong>Compétences :</strong> {{ $artisan->competences }}</p>
            <p><strong>Description :</strong> {{ $artisan->description }}</p>
            <p><strong>Téléphone :</strong> {{ $artisan->telephone }}</p>
        </div>

        <!-- Bouton du WhatsApp -->
        <a class="whatsapp-btn" 
           href="https://wa.me/{{ $artisan->telephone }}" 
           target="_blank">
           Contacter via WhatsApp
        </a>
    </div>
</body>
</html>
