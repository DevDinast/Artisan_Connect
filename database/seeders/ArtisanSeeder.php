<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Artisan;
use App\Models\User;

class ArtisanSeeder extends Seeder
{
    public function run(): void
    {
        // On suppose que tes users "artisan" existent déjà dans users
        $artisans_users = User::where('role', 'artisan')->get();

        foreach ($artisans_users as $user) {
            Artisan::create([
                'utilisateur_id' => $user->id,
                'biographie' => 'Biographie de ' . $user->name,
                'specialite' => 'Sculpture',
                'region' => 'Cotonou',
                'adresse_atelier' => 'Rue de l’artisanat',
                'compte_valide' => true,
            ]);
        }
    }
}
