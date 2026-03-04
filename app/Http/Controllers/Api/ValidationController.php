<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ValiderOeuvreRequest;
use App\Http\Requests\Api\RefuserOeuvreRequest;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidationController extends Controller
{
    protected $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Obtenir les œuvres en attente de validation
     */
    public function getOeuvresEnAttente(Request $request)
    {
        try {
            $admin = $request->user()->administrateur;
            
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès administrateur requis'
                ], 403);
            }

            $result = $this->validationService->getOeuvresEnAttente();

            return response()->json([
                'success' => true,
                'message' => 'Œuvres en attente récupérées avec succès',
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des œuvres en attente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider une œuvre
     */
    public function validerOeuvre(ValiderOeuvreRequest $request, $id)
    {
        try {
            $admin = $request->user()->administrateur;
            
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès administrateur requis'
                ], 403);
            }

            $result = $this->validationService->validerOeuvre($id, $request->validated(), $admin->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation de l\'œuvre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refuser une œuvre
     */
    public function refuserOeuvre(RefuserOeuvreRequest $request, $id)
    {
        try {
            $admin = $request->user()->administrateur;
            
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès administrateur requis'
                ], 403);
            }

            $result = $this->validationService->refuserOeuvre($id, $request->validated(), $admin->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du refus de l\'œuvre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques de validation
     */
    public function getStatistiquesValidation(Request $request)
    {
        try {
            $admin = $request->user()->administrateur;
            
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès administrateur requis'
                ], 403);
            }

            $result = $this->validationService->getStatistiquesValidation();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques de validation récupérées avec succès',
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
     * Obtenir l'historique des validations
     */
    public function getHistoriqueValidations(Request $request)
    {
        try {
            $admin = $request->user()->administrateur;
            
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès administrateur requis'
                ], 403);
            }

            $result = $this->validationService->getHistoriqueValidations();

            return response()->json([
                'success' => true,
                'message' => 'Historique des validations récupéré avec succès',
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'historique',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
