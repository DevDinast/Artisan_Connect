<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 DEBUG DÉTAILLÉ PANIER\n";
echo "========================\n\n";

// Vérifier la table favoris
try {
    $favoris = \App\Models\Favori::where('acheteur_id', 6)->where('type', 'panier')->get();
    echo "Favoris panier pour acheteur 6: " . $favoris->count() . "\n";
    
    foreach ($favoris as $f) {
        echo "- ID: {$f->id}, Œuvre: {$f->oeuvre_id}, Quantité: {$f->quantite}\n";
    }
} catch (Exception $e) {
    echo "Erreur favoris: " . $e->getMessage() . "\n";
}

// Vérifier le service directement
try {
    $panierService = new \App\Services\PanierService();
    $result = $panierService->getContenuPanier(6);
    
    echo "\nRésultat service:\n";
    echo "Success: " . ($result['success'] ?? 'false') . "\n";
    echo "Data exists: " . (isset($result['data']) ? 'true' : 'false') . "\n";
    echo "Stats exists: " . (isset($result['stats']) ? 'true' : 'false') . "\n";
    
    if (!$result['success']) {
        echo "Error: " . ($result['error'] ?? 'Unknown') . "\n";
    }
    
} catch (Exception $e) {
    echo "Erreur service: " . $e->getMessage() . "\n";
}
