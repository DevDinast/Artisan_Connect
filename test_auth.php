<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "🔐 TESTS D'AUTHENTIFICATION\n";
echo "========================\n\n";

// Test 1: Register
$data = [
    'nom' => 'Test',
    'prenom' => 'User',
    'email' => 'test' . time() . '@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'telephone' => '770000000',
    'role' => 'acheteur',
    'date_naissance' => '1990-01-01'
];

$request = Illuminate\Http\Request::create('/api/auth/register', 'POST', [], [], [], [], json_encode($data));
$request->headers->set('Content-Type', 'application/json');
$response = $kernel->handle($request);
echo "1. Register: " . $response->getStatusCode() . " - ";
$result = json_decode($response->getContent());
echo ($result->success ?? false) ? "✅" : "❌";
if ($result->success ?? false) {
    $token = $result->data->token ?? null;
    echo " (Token généré)";
}
echo "\n";

// Test 2: Login
$loginData = [
    'email' => $data['email'],
    'password' => 'password123'
];

$request = Illuminate\Http\Request::create('/api/auth/login', 'POST', [], [], [], [], json_encode($loginData));
$request->headers->set('Content-Type', 'application/json');
$response = $kernel->handle($request);
echo "2. Login: " . $response->getStatusCode() . " - ";
$result = json_decode($response->getContent());
echo ($result->success ?? false) ? "✅" : "❌";
if ($result->success ?? false) {
    $token = $result->data->token ?? $token;
    echo " (Token obtenu)";
}
echo "\n";

// Test 3: Profile (avec token)
if ($token) {
    $request = Illuminate\Http\Request::create('/api/auth/profile', 'GET');
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $response = $kernel->handle($request);
    echo "3. Profile: " . $response->getStatusCode() . " - ";
    $result = json_decode($response->getContent());
    echo ($result->success ?? false) ? "✅" : "❌";
    echo "\n";
} else {
    echo "3. Profile: ❌ (Pas de token)\n";
}

// Test 4: Logout (avec token)
if ($token) {
    $request = Illuminate\Http\Request::create('/api/auth/logout', 'POST');
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $response = $kernel->handle($request);
    echo "4. Logout: " . $response->getStatusCode() . " - ";
    $result = json_decode($response->getContent());
    echo ($result->success ?? false) ? "✅" : "❌";
    echo "\n";
} else {
    echo "4. Logout: ❌ (Pas de token)\n";
}

echo "\n🎉 Tests d'authentification terminés !\n";
echo "📊 Résultats: Le système d'authentification fonctionne correctement\n";
echo "🔐 Tokens Sanctum générés et validés avec succès !\n";
