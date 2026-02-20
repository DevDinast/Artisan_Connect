<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AjouterPanierRequest;
use App\Http\Requests\Api\UpdatePanierRequest;
use App\Services\PanierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanierController extends Controller
{
    protected $panierService;

    public function __construct(PanierService $panierService)
    {
        $this->panierService = $panierService;
    }

    /**
     * Ajouter une œuvre au panier
     */
    public function ajouter(AjouterPanierRequest $request)
    {
        try {
            $acheteur = $request->user()->loadMissing('acheteur')->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->panierService->ajouterAuPanier(
                $acheteur->id,
                $request->validated()
            );

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout au panier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le contenu du panier
     */
    public function getPanier(Request $request)
    {
        try {
            $acheteur = $request->user()->loadMissing('acheteur')->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->panierService->getContenuPanier($acheteur->id);

            return response()->json([
                'success' => true,
                'message' => 'Panier récupéré avec succès',
                'data' => $result['data'],
                'stats' => $result['stats']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du panier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour la quantité d'un article dans le panier
     */
    public function mettreAJour(UpdatePanierRequest $request, $id)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->panierService->mettreAJourQuantite(
                $acheteur->id,
                $id,
                $request->validated()
            );

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du panier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un article du panier
     */
    public function supprimer(Request $request, $id)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->panierService->supprimerDuPanier($acheteur->id, $id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du panier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vider le panier
     */
    public function vider(Request $request)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->panierService->viderPanier($acheteur->id);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du vidage du panier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques du panier
     */
    public function getStats(Request $request)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->panierService->getStatsPanier($acheteur->id);

            return response()->json([
                'success' => true,
                'message' => 'Statistiques du panier récupérées avec succès',
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
}
