<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "🔍 DEBUG LOGIN DÉTAILLÉ\n";
echo "========================\n\n";

// Test login simple
$loginData = [
    'email' => 'test@example.com',
    'mot_de_passe' => 'password123'
];

try {
    $request = Illuminate\Http\Request::create('/api/auth/login', 'POST', [], [], [], [], json_encode($loginData));
    $request->headers->set('Content-Type', 'application/json');
    $response = $kernel->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
