<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/catalog/stats', 'GET');

try {
    $response = $kernel->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
    
    $kernel->terminate($request, $response);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
