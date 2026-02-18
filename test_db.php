<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 VÉRIFICATION BASE DE DONNÉES\n";
echo "==============================\n\n";

// Vérifier les utilisateurs
$users = App\Models\Utilisateur::where('role', 'acheteur')->get();
echo "Nombre d'acheteurs: " . $users->count() . "\n";

foreach ($users as $user) {
    echo "- " . $user->email . "\n";
    
    // Vérifier si le profil acheteur existe
    $acheteur = $user->acheteur;
    echo "  Profil acheteur: " . ($acheteur ? "Oui (ID: {$acheteur->id})" : "Non") . "\n";
}

// Créer un test utilisateur si aucun n'existe
if ($users->count() === 0) {
    echo "\n📝 Création d'un utilisateur de test...\n";
    
    $user = App\Models\Utilisateur::create([
        'nom' => 'Test',
        'prenom' => 'User',
        'email' => 'testuser@example.com',
        'mot_de_passe' => Hash::make('password123'),
        'role' => 'acheteur',
        'telephone' => '770000000',
        'email_verifie_le' => now(),
        'actif' => true,
    ]);
    
    // Créer le profil acheteur
    $acheteur = $user->acheteur()->create([]);
    
    echo "✅ Utilisateur créé: " . $user->email . "\n";
    echo "✅ Profil acheteur créé: ID " . $acheteur->id . "\n";
    
    // Créer un token
    $token = $user->createToken('test_token')->plainTextToken;
    echo "✅ Token créé: " . substr($token, 0, 30) . "...\n";
}

echo "\n🎉 Vérification terminée !\n";
