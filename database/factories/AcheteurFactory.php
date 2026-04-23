<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Acheteur>
 */
class AcheteurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create(['role' => 'acheteur']);

        return [
            'user_id' => $user->id,
            'adresse_livraison' => json_encode(['adresse' => $this->faker->address()]),
        ];
    }
}
