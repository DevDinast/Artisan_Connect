<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Artisan Connect</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">

    <header class="bg-white shadow p-4">
        <h1 class="text-xl font-bold text-primary">
            Artisan Connect
        </h1>
    </header>

    <main class="p-6">
        @yield('content')
    </main>

</body>
</html>
                                                       