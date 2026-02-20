<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "🔧 TEST CORRECTION LOGIN\n";
echo "=======================\n\n";

// Créer un utilisateur de test
$data = [
    'nom' => 'Test',
    'prenom' => 'Login',
    'email' => 'testlogin@example.com',
    'mot_de_passe' => 'password123',
    'mot_de_passe_confirmation' => 'password123',
    'role' => 'acheteur'
];

// 1. Register
$request = Illuminate\Http\Request::create('/api/auth/register', 'POST', [], [], [], [], json_encode($data));
$request->headers->set('Content-Type', 'application/json');
$response = $kernel->handle($request);
echo "1. Register: " . $response->getStatusCode() . " - ";
$result = json_decode($response->getContent());
echo ($result->success ?? false) ? "✅" : "❌";
echo "\n";

// 2. Login
$loginData = [
    'email' => $data['email'],
    'mot_de_passe' => 'password123'
];

$request = Illuminate\Http\Request::create('/api/auth/login', 'POST', [], [], [], [], json_encode($loginData));
$request->headers->set('Content-Type', 'application/json');
$response = $kernel->handle($request);
echo "2. Login: " . $response->getStatusCode() . " - ";
$result = json_decode($response->getContent());
echo ($result->success ?? false) ? "✅" : "❌";
if ($result->success ?? false) {
    $token = $result->data->token ?? null;
    echo " (Token OK)";
} else {
    echo " (Erreur: " . ($result->message ?? 'Unknown') . ")";
}
echo "\n";

// 3. Test route /api/user
if ($token) {
    $request = Illuminate\Http\Request::create('/api/user', 'GET');
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $response = $kernel->handle($request);
    echo "3. GET /api/user: " . $response->getStatusCode() . " - ";
    $result = json_decode($response->getContent());
    echo ($result->success ?? false) ? "✅" : "❌";
    echo "\n";
    
    // 4. Test PUT /api/user
    $updateData = ['nom' => 'Updated'];
    $request = Illuminate\Http\Request::create('/api/user', 'PUT', [], [], [], [], json_encode($updateData));
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $request->headers->set('Content-Type', 'application/json');
    $response = $kernel->handle($request);
    echo "4. PUT /api/user: " . $response->getStatusCode() . " - ";
    $result = json_decode($response->getContent());
    echo ($result->success ?? false) ? "✅" : "❌";
    echo "\n";
}

echo "\n🎉 Tests de correction terminés !\n";
