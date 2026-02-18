<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreerCommandeRequest;
use App\Services\CommandeService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommandeController extends Controller
{
    protected $commandeService;
    protected $transactionService;

    public function __construct(CommandeService $commandeService, TransactionService $transactionService)
    {
        $this->commandeService = $commandeService;
        $this->transactionService = $transactionService;
    }

    /**
     * Créer une nouvelle commande
     */
    public function creerCommande(CreerCommandeRequest $request)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->commandeService->creerCommande($acheteur->id, $request->validated());

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la commande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les commandes de l'acheteur
     */
    public function getCommandes(Request $request)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->commandeService->getCommandesAcheteur($acheteur->id);

            return response()->json([
                'success' => true,
                'message' => 'Commandes récupérées avec succès',
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des commandes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'une commande
     */
    public function getDetailCommande(Request $request, $id)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->commandeService->getDetailCommande($id, $acheteur->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails de la commande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Annuler une commande
     */
    public function annulerCommande(Request $request, $id)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->commandeService->annulerCommande($id, $acheteur->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation de la commande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirmer la réception d'une commande
     */
    public function confirmerReception(Request $request, $id)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->commandeService->confirmerReception($id, $acheteur->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation de réception',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les transactions d'une commande
     */
    public function getTransactionsCommande(Request $request, $id)
    {
        try {
            $acheteur = $request->user()->acheteur;
            
            if (!$acheteur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil acheteur non trouvé'
                ], 404);
            }

            $result = $this->transactionService->getTransactionsCommande($id, $acheteur->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
