<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "🔍 DEBUG PANIER CORRIGÉ\n";
echo "========================\n\n";

// Obtenir un token
$loginData = [
    'email' => 'test@example.com',
    'password' => 'password123'
];

$request = Illuminate\Http\Request::create('/api/auth/login', 'POST', [], [], [], [], json_encode($loginData));
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
