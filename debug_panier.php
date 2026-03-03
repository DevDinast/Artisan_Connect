<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

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

echo "Token: " . substr($token, 0, 30) . "...\n";

// Test panier avec debug
try {
    $request = Illuminate\Http\Request::create('/api/acheteur/panier', 'GET');
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $response = $kernel->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
