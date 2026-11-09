<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\File;

$root = dirname(__DIR__);
$output = $root . '/netlify-dist';
$apiBaseUrl = rtrim((string) getenv('ARTISAN_API_BASE_URL'), '/');

if ($apiBaseUrl === '' || !filter_var($apiBaseUrl, FILTER_VALIDATE_URL)) {
    fwrite(STDERR, "ARTISAN_API_BASE_URL must be set to the Laravel API origin (for example https://artisanconnect-api.onrender.com).\n");
    exit(1);
}

$apiScheme = parse_url($apiBaseUrl, PHP_URL_SCHEME);
$apiHost = parse_url($apiBaseUrl, PHP_URL_HOST);
$apiPath = parse_url($apiBaseUrl, PHP_URL_PATH);
$isLocalApiHost = in_array($apiHost, ['localhost', '127.0.0.1', '::1'], true);
if (
    !in_array($apiScheme, ['http', 'https'], true)
    || ($apiScheme !== 'https' && !$isLocalApiHost)
    || !in_array($apiPath, [null, '', '/'], true)
) {
    fwrite(STDERR, "ARTISAN_API_BASE_URL must be an HTTPS origin without a path (HTTP is allowed for localhost).\n");
    exit(1);
}

require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$staticOrigin = 'https://artisanconnect-static.invalid';
config(['app.url' => $staticOrigin]);
$app['url']->forceRootUrl($staticOrigin);

if (is_dir($output)) {
    File::deleteDirectory($output);
}

File::ensureDirectoryExists($output);
File::copy($root . '/resources/css/app.css', $output . '/app.css');
File::copy($root . '/netlify/runtime.js', $output . '/netlify-runtime.js');
File::copy($root . '/public/favicon.ico', $output . '/favicon.ico');
File::copy($root . '/public/robots.txt', $output . '/robots.txt');
if (is_dir($root . '/public/images')) {
    File::copyDirectory($root . '/public/images', $output . '/images');
}
File::ensureDirectoryExists($output . '/__netlify');

$artisan = (object) [
    'id' => 42424242,
    'name' => '',
    'avatar' => null,
    'telephone' => null,
    'artisan' => (object) [
        'specialite' => null,
        'biographie' => null,
        'bio' => null,
        'region' => null,
        'adresse_atelier' => null,
        'atelier_adresse' => null,
    ],
];

$pages = [
    ['index.html', 'home', ['artisans' => collect()]],
    ['catalogue/index.html', 'catalogue.categories', []],
    ['__netlify/oeuvre.html', 'catalogue.show', ['id' => 42424242]],
    ['__netlify/artisan.html', 'catalogue.artisan', ['artisan' => $artisan]],
    ['auth/login/index.html', 'auth.login', []],
    ['auth/register/index.html', 'auth.register', []],
    ['login/index.html', 'auth.login', []],
    ['dashboard/acheteur/index.html', 'acheteur.dashboard', []],
    ['dashboard/artisan/index.html', 'artisan.dashboard', []],
    ['dashboard/admin/index.html', 'admin.dashboard', []],
    ['panier/index.html', 'acheteur.panier', []],
    ['commandes/index.html', 'acheteur.commandes', []],
    ['profil/index.html', 'profil.edit', []],
    ['artisan/oeuvres/create/index.html', 'artisan.oeuvres.create', []],
    ['__netlify/edit-oeuvre.html', 'artisan.oeuvres.edit', ['id' => 42424242]],
];

foreach ($pages as [$relativePath, $view, $data]) {
    $html = view($view, $data)->render();
    $html = str_replace(
        [$staticOrigin, 'http://artisanconnect-static.invalid'],
        '',
        $html
    );
    $html = str_replace('href=""', 'href="/"', $html);
    $runtimeConfig = '<script>window.ARTISAN_API_BASE_URL = '
        . json_encode($apiBaseUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
        . ';</script><script src="/netlify-runtime.js"></script>';
    $html = preg_replace('/<head>/', '<head>' . $runtimeConfig, $html, 1);

    $destination = $output . '/' . $relativePath;
    File::ensureDirectoryExists(dirname($destination));
    file_put_contents($destination, $html);
}

fwrite(STDOUT, 'Exported ' . count($pages) . " static pages to netlify-dist.\n");
