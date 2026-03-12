<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Oeuvre;
use App\Models\Artisan;
use App\Models\Transaction;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * GET /v1/catalog/categories
     */
    public function categories()
    {
        $categories = Categorie::with('children')
            ->whereNull('parent_id')
            ->orderBy('ordre')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => ['categories' => $categories],
            'message' => 'Catégories récupérées avec succès',
        ], 200);
    }

    /**
     * GET /v1/catalog/oeuvres
     * Filtres : categorie_id, prix_min, prix_max, region, sort, search
     */
    public function oeuvres(Request $request)
    {
        $query = Oeuvre::query()->where('statut', 'validee');

        // Filtre catégorie
        $query->when($request->categorie_id, fn($q, $v) => $q->where('categorie_id', $v));

        // Filtre prix
        $query->when($request->prix_min, fn($q, $v) => $q->where('prix', '>=', $v));
        $query->when($request->prix_max, fn($q, $v) => $q->where('prix', '<=', $v));

        // Filtre région via artisan
        $query->when($request->region, function ($q, $region) {
            $q->whereHas('artisan', fn($q) => $q->where('region', $region));
        });

        // Recherche full-text
        $query->when($request->search, function ($q, $search) {
            $q->where(function ($q) use ($search) {
                $q->where('titre', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        });

        // Tri
        switch ($request->get('sort', 'recent')) {
            case 'prix_asc':  $query->orderBy('prix', 'asc'); break;
            case 'prix_desc': $query->orderBy('prix', 'desc'); break;
            case 'populaire': $query->orderBy('vues', 'desc'); break;
            default:          $query->orderBy('created_at', 'desc'); break;
        }

        $query->with(['artisan', 'categorie', 'images']);
        $oeuvres = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => [
                'oeuvres'    => $oeuvres->items(),
                'pagination' => [
                    'total'        => $oeuvres->total(),
                    'per_page'     => $oeuvres->perPage(),
                    'current_page' => $oeuvres->currentPage(),
                    'last_page'    => $oeuvres->lastPage(),
                    'has_more'     => $oeuvres->hasMorePages(),
                ],
            ],
            'message' => 'Œuvres récupérées avec succès',
        ], 200);
    }

    /**
     * GET /v1/catalog/oeuvres/{id}
     * Détail d'une œuvre + incrémentation vues
     */
    public function showOeuvre($id)
    {
        $oeuvre = Oeuvre::findOrFail($id);

        // Vérifier que l'œuvre est bien publiée
        if ($oeuvre->statut !== 'validee') {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Œuvre non trouvée',
            ], 404);
        }

        // Incrémenter le compteur de vues
        $oeuvre->increment('vues');

        // Charger toutes les relations utiles
        $oeuvre->load(['artisan.user', 'categorie', 'images', 'avis']);

        return response()->json([
            'success' => true,
            'data'    => ['oeuvre' => $oeuvre],
            'message' => 'Œuvre récupérée avec succès',
        ], 200);
    }

    /**
     * GET /v1/catalog/oeuvres/{id}/similar
     * Œuvres similaires (même catégorie, max 6)
     */
    public function similarOeuvres($id)
    {
        $oeuvre = Oeuvre::findOrFail($id);

        $similaires = Oeuvre::where('statut', 'validee')
            ->where('categorie_id', $oeuvre->categorie_id)
            ->where('id', '!=', $id)
            ->with(['artisan', 'images'])
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => ['oeuvres' => $similaires],
            'message' => 'Œuvres similaires récupérées avec succès',
        ], 200);
    }

    /**
     * GET /v1/catalog/stats
     */
    public function stats()
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'stats' => [
                    'total_oeuvres'  => Oeuvre::where('statut', 'validee')->count(),
                    'total_artisans' => Artisan::where('compte_valide', true)->count(),
                    'total_ventes'   => Transaction::where('statut', 'payee')->count(),
                ],
            ],
            'message' => 'Statistiques récupérées avec succès',
        ], 200);
    }
}
