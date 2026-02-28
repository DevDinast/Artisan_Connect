<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorieSeeder::class,
            ArtisanSeeder::class,
            OeuvreSeeder::class,
            ImageSeeder::class,
            UserSeeder::class,
        ]);
    }
}
