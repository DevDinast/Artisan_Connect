<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InitierPaiementRequest;
use App\Services\PaiementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaiementController extends Controller
{
    protected $paiementService;

    public function __construct(PaiementService $paiementService)
    {
        $this->paiementService = $paiementService;
    }

    /**
     * Initier un paiement Mobile Money
     */
    public function initierPaiement(InitierPaiementRequest $request)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->paiementService->initierPaiement(
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
                'message' => 'Erreur lors de l\'initialisation du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier le statut d'un paiement
     */
    public function getStatutPaiement(Request $request, $id)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->paiementService->getStatutPaiement($id, $acheteur->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du statut du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirmer un paiement (callback Mobile Money)
     */
    public function confirmerPaiement(Request $request, $id)
    {
        try {
            $result = $this->paiementService->confirmerPaiement($id, $request->all());

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Annuler un paiement
     */
    public function annulerPaiement(Request $request, $id)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->paiementService->annulerPaiement($id, $acheteur->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir l'historique des paiements
     */
    public function getHistoriquePaiements(Request $request)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->paiementService->getHistoriquePaiements($acheteur->id);

            return response()->json([
                'success' => true,
                'message' => 'Historique des paiements récupéré avec succès',
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'historique des paiements',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les méthodes de paiement disponibles
     */
    public function getMethodesPaiement(Request $request)
    {
        try {
            $result = $this->paiementService->getMethodesPaiement();

            return response()->json([
                'success' => true,
                'message' => 'Méthodes de paiement récupérées avec succès',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des méthodes de paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
