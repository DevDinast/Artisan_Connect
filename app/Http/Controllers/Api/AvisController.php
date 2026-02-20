<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateAvisRequest;
use App\Http\Requests\Api\UpdateAvisRequest;
use App\Services\AvisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvisController extends Controller
{
    protected $avisService;

    public function __construct(AvisService $avisService)
    {
        $this->avisService = $avisService;
    }

    /**
     * Créer un nouvel avis
     */
    public function creerAvis(CreateAvisRequest $request)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->avisService->creerAvis($acheteur->id, $request->validated());

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'avis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les avis d'une œuvre
     */
    public function getAvisOeuvre(Request $request, $oeuvreId)
    {
        try {
            $result = $this->avisService->getAvisOeuvre($oeuvreId);

            return response()->json([
                'success' => true,
                'message' => 'Avis de l\'œuvre récupérés avec succès',
                'data' => $result['data'],
                'stats' => $result['stats'],
                'pagination' => $result['pagination']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des avis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un avis
     */
    public function mettreAJourAvis(UpdateAvisRequest $request, $id)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->avisService->mettreAJourAvis($id, $acheteur->id, $request->validated());

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'avis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un avis
     */
    public function supprimerAvis(Request $request, $id)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->avisService->supprimerAvis($id, $acheteur->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'avis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les avis de l'utilisateur connecté
     */
    public function getMesAvis(Request $request)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->avisService->getAvisAcheteur($acheteur->id);

            return response()->json([
                'success' => true,
                'message' => 'Vos avis récupérés avec succès',
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de vos avis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des avis d'un artisan
     */
    public function getStatistiquesAvisArtisan(Request $request, $artisanId)
    {
        try {
            $result = $this->avisService->getStatistiquesAvisArtisan($artisanId);

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des avis récupérées avec succès',
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
     * Signaler un avis inapproprié
     */
    public function signalerAvis(Request $request, $id)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->avisService->signalerAvis($id, $request->input('motif'));

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du signalement de l\'avis',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
