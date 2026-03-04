<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Oeuvre;
use App\Models\Artisan;
use App\Models\Categorie;

class OeuvreSeeder extends Seeder
{
    public function run(): void
    {
        $artisans = Artisan::all();
        $categories = Categorie::all();

        foreach ($artisans as $artisan) {
            for ($i = 1; $i <= 3; $i++) {
                Oeuvre::create([
                    'artisan_id' => $artisan->id,
                    'categorie_id' => $categories->random()->id,
                    'titre' => 'Oeuvre ' . $i . ' de l’artisan ' . $artisan->nom,
                    'description' => 'Description de test pour cette œuvre artisanale.',
                    'prix' => rand(10000, 100000),
                    'stock' => rand(1, 10),
                    'statut' => 'valide',
                ]);
            }
        }
    }
}
