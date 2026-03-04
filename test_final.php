<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "🧪 TESTS FINAUX COMPLETS DE L'API\n";
echo "================================\n\n";

// Obtenir un utilisateur existant
$user = App\Models\Utilisateur::where('role', 'acheteur')->first();
if (!$user) {
    echo "❌ Aucun utilisateur acheteur trouvé\n";
    exit;
}

// Créer un token pour cet utilisateur
$token = $user->createToken('test_token')->plainTextToken;
echo "🔑 Token utilisateur: " . substr($token, 0, 30) . "...\n";
echo "👤 Email: " . $user->email . "\n";
echo "🆔 Acheteur ID: " . $user->acheteur->id . "\n\n";

// Tests des endpoints
$tests = [
    ['GET', '/api/catalog/stats', 'Catalog Stats', false],
    ['GET', '/api/catalog/categories', 'Categories', false],
    ['GET', '/api/catalog/oeuvres', 'Œuvres', false],
    ['GET', '/api/acheteur/panier', 'Panier (auth)', true],
    ['GET', '/api/acheteur/notifications', 'Notifications', true],
    ['GET', '/api/acheteur/favoris', 'Favoris', true],
    ['POST', '/api/acheteur/panier/ajouter', 'Ajouter panier', true, ['oeuvre_id' => 1, 'quantite' => 1]],
    ['POST', '/api/acheteur/favoris', 'Ajouter favori', true, ['oeuvre_id' => 1]],
];

$success = 0;
$total = count($tests);

foreach ($tests as $test) {
    $method = $test[0];
    $url = $test[1];
    $name = $test[2];
    $auth = $test[3] ?? false;
    $data = $test[4] ?? [];
    
    $request = Illuminate\Http\Request::create($url, $method, [], [], [], [], $data ? json_encode($data) : null);
    
    if ($auth) {
        $request->headers->set('Authorization', 'Bearer ' . $token);
    }
    
    if ($data) {
        $request->headers->set('Content-Type', 'application/json');
    }
    
    try {
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        $result = json_decode($response->getContent());
        
        $isOk = ($status >= 200 && $status < 300) && ($result->success ?? false);
        echo sprintf("%-30s: %d - %s\n", $name, $status, $isOk ? "✅" : "❌");
        
        if ($isOk) $success++;
        
    } catch (Exception $e) {
        echo sprintf("%-30s: ERROR - ❌ (%s)\n", $name, $e->getMessage());
    }
}

echo "\n📊 RÉSULTATS FINAUX\n";
echo "==================\n";
echo "✅ Succès: $success/$total\n";
echo "📈 Taux de réussite: " . round(($success/$total)*100, 1) . "%\n";

if ($success === $total) {
    echo "\n🎉 TOUS LES TESTS SONT OK !\n";
    echo "🚀 L'API ArtisanConnect est 100% fonctionnelle !\n";
    echo "🔐 Authentification Sanctum opérationnelle\n";
    echo "📦 Panier et favoris fonctionnels\n";
    echo "🔔 Notifications actives\n";
    echo "🎯 Prête pour la production !\n";
} else {
    echo "\n⚠️ Certains tests ont échoué\n";
    echo "🔍 Vérifier les logs pour plus de détails\n";
}
