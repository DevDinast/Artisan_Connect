<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "🔍 DEBUG AUTHENTIFICATION\n";
echo "========================\n\n";

// Test Register simple
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

echo "Register Status: " . $response->getStatusCode() . "\n";
echo "Register Content: " . $response->getContent() . "\n\n";

// Test Login simple
$loginData = [
    'email' => $data['email'],
    'password' => 'password123'
];

$request = Illuminate\Http\Request::create('/api/auth/login', 'POST', [], [], [], [], json_encode($loginData));
$request->headers->set('Content-Type', 'application/json');
$response = $kernel->handle($request);

echo "Login Status: " . $response->getStatusCode() . "\n";
echo "Login Content: " . $response->getContent() . "\n\n";
