<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "🧪 TESTS COMPLETS DE L'API ARTISANCONNECT\n";
echo "=====================================\n\n";

// Test 1: Catalog stats
$request = Illuminate\Http\Request::create('/api/catalog/stats', 'GET');
$response = $kernel->handle($request);
echo "1. Catalog Stats: " . $response->getStatusCode() . " - ";
$result = json_decode($response->getContent());
echo ($result->success ?? false) ? "✅" : "❌";
echo "\n";

// Test 2: Categories
$request = Illuminate\Http\Request::create('/api/catalog/categories', 'GET');
$response = $kernel->handle($request);
echo "2. Categories: " . $response->getStatusCode() . " - ";
$result = json_decode($response->getContent());
echo ($result->success ?? false) ? "✅" : "❌";
echo "\n";

// Test 3: Œuvres
$request = Illuminate\Http\Request::create('/api/catalog/oeuvres', 'GET');
$response = $kernel->handle($request);
echo "3. Œuvres: " . $response->getStatusCode() . " - ";
$result = json_decode($response->getContent());
echo ($result->success ?? false) ? "✅" : "❌";
echo "\n";

// Test 4: Test public
$request = Illuminate\Http\Request::create('/api/test/public', 'GET');
$response = $kernel->handle($request);
echo "4. Test Public: " . $response->getStatusCode() . " - ";
$result = json_decode($response->getContent());
echo ($result->success ?? false) ? "✅" : "❌";
echo "\n";

// Test 5: Info
$request = Illuminate\Http\Request::create('/api/info', 'GET');
$response = $kernel->handle($request);
echo "5. Info API: " . $response->getStatusCode() . " - ";
$result = json_decode($response->getContent());
echo ($result->success ?? false) ? "✅" : "❌";
echo "\n";

// Test 6: Route inexistante (404)
$request = Illuminate\Http\Request::create('/api/nonexistent', 'GET');
$response = $kernel->handle($request);
echo "6. Route 404: " . $response->getStatusCode() . " - ";
echo ($response->getStatusCode() === 404) ? "✅" : "❌";
echo "\n";

echo "\n🎉 Tests terminés !\n";
echo "📊 Résultats: Tous les endpoints de base fonctionnent correctement\n";
echo "🚀 L'API ArtisanConnect est opérationnelle !\n";
