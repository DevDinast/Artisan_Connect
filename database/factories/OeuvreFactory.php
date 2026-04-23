<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Artisan;
use App\Models\Categorie;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Oeuvre>
 */
class OeuvreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $artisan = Artisan::factory()->create();
        $categorie = Categorie::factory()->create();

        return [
            'artisan_id' => $artisan->id,
            'categorie_id' => $categorie->id,
            'titre' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'prix' => $this->faker->numberBetween(1000, 100000),
            'statut' => 'validee',
            'vues' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
