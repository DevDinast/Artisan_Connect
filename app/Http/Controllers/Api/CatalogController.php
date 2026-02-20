<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Oeuvre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    /**
     * Obtenir la liste hiérarchique des catégories
     */
    public function categories(Request $request)
    {
        try {
            $query = Categorie::query();

            // Filtre pour n'inclure que les catégories racines ou toutes
            if ($request->has('roots_only') && $request->boolean('roots_only')) {
                $query->roots();
            }

            // Filtre par catégorie parente
            if ($request->has('parent_id')) {
                $query->where('parent_id', $request->parent_id);
            }

            // Inclure les enfants si demandé
            if ($request->has('with_children') && $request->boolean('with_children')) {
                $categories = $query->with('enfants')->get();
            } else {
                $categories = $query->get();
            }

            return response()->json([
                'success' => true,
                'message' => 'Catégories récupérées avec succès',
                'data' => $categories,
                'count' => $categories->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des catégories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir la liste des œuvres publiques
     */
    public function oeuvres(Request $request)
    {
        try {
            $query = Oeuvre::with([
                'artisan.utilisateur:id,nom,prenom,region',
                'categorie:id,nom,slug',
                'images' => function ($q) {
                    $q->principale()->byOrder();
                }
            ])
            ->where('statut', 'validee')
            ->where('quantite_disponible', '>', 0);

            // Filtre par catégorie
            if ($request->has('categorie_id')) {
                $categorieId = $request->categorie_id;
                $query->where('categorie_id', $categorieId);
                
                // Inclure les sous-catégories si demandé
                if ($request->has('include_subcategories') && $request->boolean('include_subcategories')) {
                    $subCategories = $this->getSubCategories($categorieId);
                    $query->whereIn('categorie_id', $subCategories);
                }
            }

            // Filtre par prix
            if ($request->has('prix_min')) {
                $query->where('prix', '>=', (float) $request->prix_min);
            }
            if ($request->has('prix_max')) {
                $query->where('prix', '<=', (float) $request->prix_max);
            }

            // Filtre par région de l'artisan
            if ($request->has('region')) {
                $query->whereHas('artisan', function ($q) use ($request) {
                    $q->where('region', 'like', '%' . $request->region . '%');
                });
            }

            // Filtre par spécialité de l'artisan
            if ($request->has('specialite')) {
                $query->whereHas('artisan', function ($q) use ($request) {
                    $q->where('specialite', 'like', '%' . $request->specialite . '%');
                });
            }

            // Recherche full-text
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('titre', 'like', '%' . $searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $searchTerm . '%')
                      ->orWhere('materiaux', 'like', '%' . $searchTerm . '%');
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'recent');
            switch ($sortBy) {
                case 'prix_asc':
                    $query->orderBy('prix', 'asc');
                    break;
                case 'prix_desc':
                    $query->orderBy('prix', 'desc');
                    break;
                case 'populaire':
                    $query->withCount(['transactions' => function ($q) {
                        $q->where('statut', 'payee');
                    }])->orderBy('transactions_count', 'desc');
                    break;
                case 'recent':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 50); // Maximum 50 par page
            $oeuvres = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Œuvres récupérées avec succès',
                'data' => $oeuvres->items(),
                'pagination' => [
                    'current_page' => $oeuvres->currentPage(),
                    'per_page' => $oeuvres->perPage(),
                    'total' => $oeuvres->total(),
                    'last_page' => $oeuvres->lastPage(),
                    'from' => $oeuvres->firstItem(),
                    'to' => $oeuvres->lastItem(),
                ],
                'filters' => [
                    'applied' => $request->only(['categorie_id', 'prix_min', 'prix_max', 'region', 'specialite', 'search']),
                    'available_categories' => Categorie::roots()->with('enfants')->get(),
                    'available_regions' => $this->getAvailableRegions(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des œuvres',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'une œuvre spécifique
     */
    public function showOeuvre($id)
    {
        try {
            $oeuvre = Oeuvre::with([
                'artisan.utilisateur:id,nom,prenom,telephone,biographie,specialite,region,adresse_atelier',
                'categorie:id,nom,slug,description,parent_id',
                'images' => function ($q) {
                    $q->byOrder();
                },
                'avis' => function ($q) {
                    $q->published()->with('acheteur.utilisateur:id,nom,prenom')->latest();
                }
            ])
            ->where('statut', 'validee')
            ->findOrFail($id);

            // Incrémenter le compteur de vues
            $this->incrementViews($id);

            // Calculer la note moyenne
            $averageRating = $oeuvre->avis->avg('note') ?? 0;
            $totalAvis = $oeuvre->avis->count();

            return response()->json([
                'success' => true,
                'message' => 'Détails de l\'œuvre récupérés avec succès',
                'data' => [
                    'oeuvre' => $oeuvre,
                    'stats' => [
                        'average_rating' => round($averageRating, 2),
                        'total_reviews' => $totalAvis,
                        'views_count' => $this->getViewsCount($id),
                    ]
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Œuvre non trouvée'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails de l\'œuvre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les œuvres similaires
     */
    public function similarOeuvres($id, Request $request)
    {
        try {
            $oeuvre = Oeuvre::findOrFail($id);
            
            $similar = Oeuvre::with([
                'artisan.utilisateur:id,nom,prenom,region',
                'categorie:id,nom,slug',
                'images' => function ($q) {
                    $q->principale()->byOrder();
                }
            ])
            ->where('statut', 'validee')
            ->where('quantite_disponible', '>', 0)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($oeuvre) {
                $query->where('categorie_id', $oeuvre->categorie_id)
                      ->orWhereHas('artisan', function ($q) use ($oeuvre) {
                          $q->where('region', $oeuvre->artisan->region);
                      });
            })
            ->limit($request->get('limit', 6))
            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Œuvres similaires récupérées avec succès',
                'data' => $similar
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Œuvre non trouvée'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des œuvres similaires',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques du catalogue
     */
    public function stats()
    {
        try {
            $stats = [
                'total_oeuvres' => Oeuvre::where('statut', 'validee')->count(),
                'total_categories' => Categorie::count(),
                'total_artisans' => DB::table('artisans')->where('compte_valide', true)->count(),
                'average_price' => Oeuvre::where('statut', 'validee')->avg('prix'),
                'price_range' => [
                    'min' => Oeuvre::where('statut', 'validee')->min('prix'),
                    'max' => Oeuvre::where('statut', 'validee')->max('prix'),
                ],
                'categories_by_count' => DB::table('oeuvres')
                    ->join('categories', 'oeuvres.categorie_id', '=', 'categories.id')
                    ->where('oeuvres.statut', 'validee')
                    ->select('categories.nom', DB::raw('COUNT(*) as count'))
                    ->groupBy('categories.id', 'categories.nom')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistiques du catalogue récupérées avec succès',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Obtenir les sous-catégories récursivement
     */
    private function getSubCategories($parentId)
    {
        $subCategories = collect([$parentId]);
        $categories = Categorie::where('parent_id', $parentId)->get();
        
        foreach ($categories as $category) {
            $subCategories = $subCategories->merge($this->getSubCategories($category->id));
        }
        
        return $subCategories->unique()->toArray();
    }

    /**
     * Helper: Obtenir les régions disponibles
     */
    private function getAvailableRegions()
    {
        return DB::table('artisans')
            ->where('compte_valide', true)
            ->whereNotNull('region')
            ->select('region')
            ->distinct()
            ->pluck('region')
            ->sort()
            ->values();
    }

    /**
     * Helper: Incrémenter le compteur de vues
     */
    private function incrementViews($oeuvreId)
    {
        // Pour l'instant, on pourrait utiliser une table de statistiques
        // ou un cache Redis pour stocker les vues
        // Implementation simplifiée pour le moment
        DB::table('oeuvres')
            ->where('id', $oeuvreId)
            ->increment('updated_at'); // Simple placeholder
    }

    /**
     * Helper: Obtenir le nombre de vues
     */
    private function getViewsCount($oeuvreId)
    {
        // Placeholder - à implémenter avec une table de statistiques
        return 0;
    }
}
