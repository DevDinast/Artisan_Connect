<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Acheteur;
use App\Models\Oeuvre;

class ApiPanierTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test get panier
     */
    public function test_get_panier(): void
    {
        // Create user and acheteur
        $user = User::factory()->create(['role' => 'acheteur']);
        $acheteur = new Acheteur();
        $acheteur->user_id = $user->id;
        $acheteur->adresse_livraison = json_encode(['adresse' => '123 Test St']);
        $acheteur->save();

        $token = $user->createToken('authToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/acheteur/panier');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data', 'message']);
    }

    /**
     * Test add item to panier
     */
    public function test_add_item_to_panier(): void
    {
        // Create user and acheteur
        $user = User::factory()->create(['role' => 'acheteur']);
        $acheteur = new Acheteur();
        $acheteur->user_id = $user->id;
        $acheteur->adresse_livraison = json_encode(['adresse' => '123 Test St']);
        $acheteur->save();

        // Create oeuvre
        $oeuvre = Oeuvre::factory()->create(['statut' => 'validee', 'stock' => 5]);

        $token = $user->createToken('authToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/acheteur/panier', [
                'oeuvre_id' => $oeuvre->id,
                'quantite' => 2
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'data' => ['item'], 'message']);
    }
}
