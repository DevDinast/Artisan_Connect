<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categorie;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Sculpture', 'description' => 'Œuvres sculptées en bois, pierre ou métal'],
            ['name' => 'Peinture', 'description' => 'Tableaux et œuvres picturales'],
            ['name' => 'Bijoux', 'description' => 'Bijoux artisanaux faits main'],
            ['name' => 'Décoration', 'description' => 'Objets décoratifs artisanaux'],
            ['name' => 'Artisanat traditionnel', 'description' => 'Produits issus du savoir-faire local'],
        ];

        foreach ($categories as $cat) {
            Categorie::create($cat);
        }
    }
}
