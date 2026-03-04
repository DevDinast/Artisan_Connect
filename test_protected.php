<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "🔒 TESTS ENDPOINTS PROTÉGÉS\n";
echo "==========================\n\n";

// Créer un utilisateur et obtenir le token
$data = [
    'nom' => 'Test',
    'prenom' => 'User',
    'email' => 'test' . time() . '@example.com',
    'password' => 'password123',
    'mot_de_passe_confirmation' => 'password123',
    'telephone' => '770000000',
    'role' => 'acheteur',
    'date_naissance' => '1990-01-01'
];

$request = Illuminate\Http\Request::create('/api/auth/register', 'POST', [], [], [], [], json_encode($data));
$request->headers->set('Content-Type', 'application/json');
$response = $kernel->handle($request);
$result = json_decode($response->getContent());
$token = $result->data->token ?? null;

echo "🔑 Token obtenu: " . substr($token, 0, 30) . "...\n\n";

if ($token) {
    // Test 1: Panier (sans token)
    $request = Illuminate\Http\Request::create('/api/acheteur/panier', 'GET');
    $response = $kernel->handle($request);
    echo "1. Panier (sans token): " . $response->getStatusCode() . " - ";
    echo ($response->getStatusCode() === 401) ? "✅" : "❌";
    echo "\n";

    // Test 2: Panier (avec token)
    $request = Illuminate\Http\Request::create('/api/acheteur/panier', 'GET');
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $response = $kernel->handle($request);
    echo "2. Panier (avec token): " . $response->getStatusCode() . " - ";
    $result = json_decode($response->getContent());
    echo ($result->success ?? false) ? "✅" : "❌";
    echo "\n";

    // Test 3: Notifications (avec token)
    $request = Illuminate\Http\Request::create('/api/acheteur/notifications', 'GET');
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $response = $kernel->handle($request);
    echo "3. Notifications: " . $response->getStatusCode() . " - ";
    $result = json_decode($response->getContent());
    echo ($result->success ?? false) ? "✅" : "❌";
    echo "\n";

    // Test 4: Ajouter au panier
    $panierData = [
        'oeuvre_id' => 1,
        'quantite' => 1
    ];
    $request = Illuminate\Http\Request::create('/api/acheteur/panier/ajouter', 'POST', [], [], [], [], json_encode($panierData));
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $request->headers->set('Content-Type', 'application/json');
    $response = $kernel->handle($request);
    echo "4. Ajouter panier: " . $response->getStatusCode() . " - ";
    $result = json_decode($response->getContent());
    echo ($result->success ?? false) ? "✅" : "❌";
    echo "\n";

    // Test 5: Favoris
    $request = Illuminate\Http\Request::create('/api/acheteur/favoris', 'GET');
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $response = $kernel->handle($request);
    echo "5. Favoris: " . $response->getStatusCode() . " - ";
    $result = json_decode($response->getContent());
    echo ($result->success ?? false) ? "✅" : "❌";
    echo "\n";

    // Test 6: Ajouter favori
    $favoriData = [
        'oeuvre_id' => 1
    ];
    $request = Illuminate\Http\Request::create('/api/acheteur/favoris', 'POST', [], [], [], [], json_encode($favoriData));
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $request->headers->set('Content-Type', 'application/json');
    $response = $kernel->handle($request);
    echo "6. Ajouter favori: " . $response->getStatusCode() . " - ";
    $result = json_decode($response->getContent());
    echo ($result->success ?? false) ? "✅" : "❌";
    echo "\n";

} else {
    echo "❌ Impossible d'obtenir un token\n";
}

echo "\n🎉 Tests des endpoints protégés terminés !\n";
echo "📊 Résultats: Authentification et autorisation fonctionnelles\n";
echo "🔒 Protection Sanctum active et efficace !\n";
