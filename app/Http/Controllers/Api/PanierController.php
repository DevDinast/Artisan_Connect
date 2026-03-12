<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Panier;
use App\Models\Oeuvre;
use Illuminate\Http\Request;

class PanierController extends Controller
{
    public function getPanier(Request $request)
    {
        $acheteur = $request->user()->acheteur;

        $items = Panier::where('acheteur_id', $acheteur->id)
            ->with(['oeuvre.images', 'oeuvre.artisan'])
            ->get();

        $total = $items->sum(fn($item) => $item->oeuvre->prix * $item->quantite);

        return response()->json([
            'success' => true,
            'data'    => ['items' => $items, 'total' => $total],
            'message' => 'Panier récupéré avec succès',
        ], 200);
    }

    public function ajouter(Request $request)
    {
        $acheteur = $request->user()->acheteur;

        $data = $request->validate([
            'oeuvre_id' => 'required|exists:oeuvres,id',
            'quantite'  => 'integer|min:1',
        ]);

        $oeuvre   = Oeuvre::where('statut', 'validee')->findOrFail($data['oeuvre_id']);
        $quantite = $data['quantite'] ?? 1;

        if ($oeuvre->stock < $quantite) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Stock insuffisant.',
            ], 422);
        }

        $item = Panier::updateOrCreate(
            ['acheteur_id' => $acheteur->id, 'oeuvre_id' => $oeuvre->id],
            ['quantite'    => $quantite]
        );

        return response()->json([
            'success' => true,
            'data'    => ['item' => $item],
            'message' => 'Œuvre ajoutée au panier',
        ], 201);
    }

    public function modifierQuantite(Request $request, $id)
    {
        $acheteur = $request->user()->acheteur;
        $item     = Panier::where('acheteur_id', $acheteur->id)->findOrFail($id);

        $data = $request->validate([
            'quantite' => 'required|integer|min:1',
        ]);

        if ($item->oeuvre->stock < $data['quantite']) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Stock insuffisant.',
            ], 422);
        }

        $item->update(['quantite' => $data['quantite']]);

        return response()->json([
            'success' => true,
            'data'    => ['item' => $item->fresh()],
            'message' => 'Quantité mise à jour',
        ], 200);
    }

    public function supprimer(Request $request, $id)
    {
        $acheteur = $request->user()->acheteur;
        $item     = Panier::where('acheteur_id', $acheteur->id)->findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Article retiré du panier',
        ], 200);
    }
}
