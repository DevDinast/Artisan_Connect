<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Obtenir les notifications de l'utilisateur
     */
    public function getNotifications(Request $request)
    {
        try {
            $utilisateur = $request->user();

            $result = $this->notificationService->getNotifications($utilisateur->id);

            return response()->json([
                'success' => true,
                'message' => 'Notifications récupérées avec succès',
                'data' => $result['data'],
                'pagination' => $result['pagination'],
                'stats' => $result['stats']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les notifications non lues
     */
    public function getNotificationsNonLues(Request $request)
    {
        try {
            $utilisateur = $request->user();

            $result = $this->notificationService->getNotificationsNonLues($utilisateur->id);

            return response()->json([
                'success' => true,
                'message' => 'Notifications non lues récupérées avec succès',
                'data' => $result['data'],
                'count' => $result['count']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des notifications non lues',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function marquerCommeLue(Request $request, $id)
    {
        try {
            $utilisateur = $request->user();

            $result = $this->notificationService->marquerCommeLue($id, $utilisateur->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage de la notification comme lue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function marquerToutesCommeLues(Request $request)
    {
        try {
            $utilisateur = $request->user();

            $result = $this->notificationService->marquerToutesCommeLues($utilisateur->id);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage de toutes les notifications comme lues',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une notification
     */
    public function supprimerNotification(Request $request, $id)
    {
        try {
            $utilisateur = $request->user();

            $result = $this->notificationService->supprimerNotification($id, $utilisateur->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des notifications
     */
    public function getStatistiquesNotifications(Request $request)
    {
        try {
            $utilisateur = $request->user();

            $result = $this->notificationService->getStatistiquesNotifications($utilisateur->id);

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des notifications récupérées avec succès',
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
     * Créer une notification manuelle (pour les artisans/admins)
     */
    public function creerNotification(Request $request)
    {
        try {
            $utilisateur = $request->user();

            // Vérifier que l'utilisateur peut créer des notifications
            if (!$this->peutCreerNotification($utilisateur)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à créer des notifications'
                ], 403);
            }

            $result = $this->notificationService->creerNotification($request->validated());

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier si l'utilisateur peut créer des notifications
     */
    private function peutCreerNotification($utilisateur)
    {
        return $utilisateur->artisan || $utilisateur->administrateur;
    }
}
