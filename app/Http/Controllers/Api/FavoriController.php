<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favori;
use App\Models\Oeuvre;
use Illuminate\Http\Request;

class FavoriController extends Controller
{
    public function getFavoris(Request $request)
    {
        $acheteur = $request->user()->acheteur;

        $favoris = Favori::where('acheteur_id', $acheteur->id)
            ->with(['oeuvre.images', 'oeuvre.artisan'])
            ->get();

        return response()->json([
            'success' => true,
            'data'    => ['favoris' => $favoris],
            'message' => 'Favoris récupérés avec succès',
        ], 200);
    }

    public function ajouter(Request $request)
    {
        $acheteur = $request->user()->acheteur;

        $data = $request->validate([
            'oeuvre_id' => 'required|exists:oeuvres,id',
        ]);

        if (Favori::where('acheteur_id', $acheteur->id)->where('oeuvre_id', $data['oeuvre_id'])->exists()) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Cette œuvre est déjà dans vos favoris.',
            ], 422);
        }

        $favori = Favori::create([
            'acheteur_id' => $acheteur->id,
            'oeuvre_id'   => $data['oeuvre_id'],
        ]);

        Oeuvre::where('id', $data['oeuvre_id'])->increment('favoris_count');

        return response()->json([
            'success' => true,
            'data'    => ['favori' => $favori],
            'message' => 'Œuvre ajoutée aux favoris',
        ], 201);
    }

    public function supprimer(Request $request, $oeuvreId)
    {
        $acheteur = $request->user()->acheteur;

        $favori = Favori::where('acheteur_id', $acheteur->id)
            ->where('oeuvre_id', $oeuvreId)
            ->firstOrFail();

        $favori->delete();

        Oeuvre::where('id', $oeuvreId)->decrement('favoris_count');

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Œuvre retirée des favoris',
        ], 200);
    }
}
