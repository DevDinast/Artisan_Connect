<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateFavoriRequest;
use App\Services\FavoriService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriController extends Controller
{
    protected $favoriService;

    public function __construct(FavoriService $favoriService)
    {
        $this->favoriService = $favoriService;
    }

    /**
     * Ajouter une œuvre aux favoris
     */
    public function ajouterFavori(CreateFavoriRequest $request)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->favoriService->ajouterFavori($acheteur->id, $request->validated());

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout aux favoris',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les favoris de l'utilisateur
     */
    public function getFavoris(Request $request)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->favoriService->getFavoris($acheteur->id);

            return response()->json([
                'success' => true,
                'message' => 'Favoris récupérés avec succès',
                'data' => $result['data'],
                'pagination' => $result['pagination'],
                'stats' => $result['stats']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des favoris',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un favori
     */
    public function supprimerFavori(Request $request, $id)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->favoriService->supprimerFavori($id, $acheteur->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du favori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier si une œuvre est dans les favoris
     */
    public function verifierFavori(Request $request, $oeuvreId)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->favoriService->verifierFavori($oeuvreId, $acheteur->id);

            return response()->json([
                'success' => true,
                'message' => 'Vérification des favoris effectuée',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification des favoris',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des favoris
     */
    public function getStatistiquesFavoris(Request $request)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->favoriService->getStatistiquesFavoris($acheteur->id);

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des favoris récupérées avec succès',
                'data' => $result['data']
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
     * Obtenir les favoris par catégorie
     */
    public function getFavorisParCategorie(Request $request, $categorieId)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->favoriService->getFavorisParCategorie($acheteur->id, $categorieId);

            return response()->json([
                'success' => true,
                'message' => 'Favoris par catégorie récupérés avec succès',
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des favoris par catégorie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les favoris récents
     */
    public function getFavorisRecents(Request $request)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->favoriService->getFavorisRecents($acheteur->id);

            return response()->json([
                'success' => true,
                'message' => 'Favoris récents récupérés avec succès',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des favoris récents',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
