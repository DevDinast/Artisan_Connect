<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
     <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ArtisanConnect')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <header>
        <nav class="navbar">
            <a href="{{ url('/') }}" class="logo">ArtisanConnect</a>
            <ul class="nav-links">
                <li><a href="{{ route('auth.register') }}">S’inscrire</a></li>
                <li><a href="{{ route('auth.login') }}">Connexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        @yield('content')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} ArtisanConnect. Tous droits réservés.</p>
    </footer>

</body>
</html>
