<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateOeuvreRequest;
use App\Http\Requests\Api\UpdateOeuvreRequest;
use App\Services\OeuvreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtisanController extends Controller
{
    protected $oeuvreService;

    public function __construct(OeuvreService $oeuvreService)
    {
        $this->oeuvreService = $oeuvreService;
    }

    /**
     * Obtenir le tableau de bord de l'artisan
     */
    public function dashboard(Request $request)
    {
        try {
            $artisan = $request->user()->artisan;
            
            if (!$artisan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil artisan non trouvé'
                ], 404);
            }

            $stats = $this->oeuvreService->getStatistiquesArtisan($artisan->id);

            return response()->json([
                'success' => true,
                'message' => 'Dashboard artisan récupéré avec succès',
                'data' => [
                    'artisan' => $artisan->load('utilisateur:id,nom,prenom,email,telephone'),
                    'stats' => $stats['data'] ?? [],
                    'recent_oeuvres' => $this->getRecentOeuvres($artisan->id),
                    'pending_validations' => $this->getPendingValidations($artisan->id),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle œuvre
     */
    public function creerOeuvre(CreateOeuvreRequest $request)
    {
        try {
            $artisan = $request->user()->artisan;
            
            if (!$artisan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil artisan non trouvé'
                ], 404);
            }

            $result = $this->oeuvreService->creerOeuvre($request->validated(), $artisan->id);

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'œuvre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une œuvre existante
     */
    public function mettreAJourOeuvre(UpdateOeuvreRequest $request, $id)
    {
        try {
            $artisan = $request->user()->artisan;
            
            if (!$artisan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil artisan non trouvé'
                ], 404);
            }

            $result = $this->oeuvreService->mettreAJourOeuvre($request->validated(), $id, $artisan->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                $statusCode = isset($result['raison']) ? 422 : 404;
                return response()->json($result, $statusCode);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'œuvre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une œuvre
     */
    public function supprimerOeuvre(Request $request, $id)
    {
        try {
            $artisan = $request->user()->artisan;
            
            if (!$artisan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil artisan non trouvé'
                ], 404);
            }

            $result = $this->oeuvreService->supprimerOeuvre($id, $artisan->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'œuvre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soumettre une œuvre pour validation
     */
    public function soumettreOeuvre(Request $request, $id)
    {
        try {
            $artisan = $request->user()->artisan;
            
            if (!$artisan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil artisan non trouvé'
                ], 404);
            }

            $result = $this->oeuvreService->soumettreOeuvre($id, $artisan->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                $statusCode = isset($result['raison']) ? 422 : 404;
                return response()->json($result, $statusCode);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission de l\'œuvre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir la liste des œuvres de l'artisan
     */
    public function mesOeuvres(Request $request)
    {
        try {
            $artisan = $request->user()->artisan;
            
            if (!$artisan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil artisan non trouvé'
                ], 404);
            }

            $query = \App\Models\Oeuvre::with(['categorie', 'images' => function ($q) {
                $q->principale()->byOrder();
            }])
            ->where('artisan_id', $artisan->id);

            // Filtres
            if ($request->has('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->has('categorie_id')) {
                $query->where('categorie_id', $request->categorie_id);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min($request->get('per_page', 15), 50);
            $oeuvres = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Œuvres de l\'artisan récupérées avec succès',
                'data' => $oeuvres->items(),
                'pagination' => [
                    'current_page' => $oeuvres->currentPage(),
                    'per_page' => $oeuvres->perPage(),
                    'total' => $oeuvres->total(),
                    'last_page' => $oeuvres->lastPage(),
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
     * Obtenir les détails d'une œuvre spécifique de l'artisan
     */
    public function detailOeuvre(Request $request, $id)
    {
        try {
            $artisan = $request->user()->artisan;
            
            if (!$artisan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil artisan non trouvé'
                ], 404);
            }

            $oeuvre = \App\Models\Oeuvre::with([
                'categorie',
                'images' => function ($q) {
                    $q->byOrder();
                },
                'avis' => function ($q) {
                    $q->published()->with('acheteur.utilisateur:id,nom,prenom')->latest();
                }
            ])
            ->where('id', $id)
            ->where('artisan_id', $artisan->id)
            ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Détails de l\'œuvre récupérés avec succès',
                'data' => $oeuvre
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
     * Obtenir les œuvres récentes
     */
    private function getRecentOeuvres($artisanId)
    {
        return \App\Models\Oeuvre::with(['categorie', 'images' => function ($q) {
                $q->principale()->byOrder();
            }])
            ->where('artisan_id', $artisanId)
            ->latest()
            ->take(5)
            ->get();
    }

    /**
     * Obtenir les œuvres en attente de validation
     */
    private function getPendingValidations($artisanId)
    {
        return \App\Models\Oeuvre::with(['categorie', 'images' => function ($q) {
                $q->principale()->byOrder();
            }])
            ->where('artisan_id', $artisanId)
            ->where('statut', 'en_attente')
            ->latest()
            ->get();
    }
}
