<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Artisan>
 */
class ArtisanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create(['role' => 'artisan']);

        return [
            'user_id' => $user->id,
            'biographie' => $this->faker->paragraph(2),
            'specialite' => $this->faker->word(),
            'region' => $this->faker->city(),
            'adresse_atelier' => $this->faker->address(),
            'compte_valide' => true,
        ];
    }
}
