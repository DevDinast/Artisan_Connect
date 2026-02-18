<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Obtenir les notifications d'un utilisateur
     */
    public function getNotifications($utilisateurId)
    {
        try {
            $notifications = Notification::where('utilisateur_id', $utilisateurId)
                ->latest()
                ->paginate(20);

            $stats = $this->getStatistiquesNotifications($utilisateurId);

            return [
                'success' => true,
                'data' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'last_page' => $notifications->lastPage(),
                ],
                'stats' => $stats['data']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des notifications',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les notifications non lues
     */
    public function getNotificationsNonLues($utilisateurId)
    {
        try {
            $notifications = Notification::where('utilisateur_id', $utilisateurId)
                ->where('lue', false)
                ->latest()
                ->take(50)
                ->get();

            return [
                'success' => true,
                'data' => $notifications,
                'count' => $notifications->count()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des notifications non lues',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function marquerCommeLue($notificationId, $utilisateurId)
    {
        try {
            $notification = Notification::where('id', $notificationId)
                ->where('utilisateur_id', $utilisateurId)
                ->firstOrFail();

            $notification->update([
                'lue' => true,
                'date_lecture' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Notification marquée comme lue avec succès',
                'data' => $notification->fresh()
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Notification non trouvée'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du marquage de la notification comme lue',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function marquerToutesCommeLues($utilisateurId)
    {
        try {
            $marquees = Notification::where('utilisateur_id', $utilisateurId)
                ->where('lue', false)
                ->update([
                    'lue' => true,
                    'date_lecture' => now(),
                ]);

            return [
                'success' => true,
                'message' => $marquees . ' notification(s) marquée(s) comme lue(s)',
                'notifications_marquees' => $marquees
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du marquage des notifications comme lues',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer une notification
     */
    public function supprimerNotification($notificationId, $utilisateurId)
    {
        try {
            $notification = Notification::where('id', $notificationId)
                ->where('utilisateur_id', $utilisateurId)
                ->firstOrFail();

            $notification->delete();

            return [
                'success' => true,
                'message' => 'Notification supprimée avec succès'
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Notification non trouvée'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de la notification',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les statistiques des notifications
     */
    public function getStatistiquesNotifications($utilisateurId)
    {
        try {
            $stats = [
                'total_notifications' => Notification::where('utilisateur_id', $utilisateurId)->count(),
                'non_lues' => Notification::where('utilisateur_id', $utilisateurId)->where('lue', false)->count(),
                'lues' => Notification::where('utilisateur_id', $utilisateurId)->where('lue', true)->count(),
                'aujourd_hui' => Notification::where('utilisateur_id', $utilisateurId)
                    ->whereDate('created_at', today())
                    ->count(),
                'cette_semaine' => Notification::where('utilisateur_id', $utilisateurId)
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'types' => $this->getRepartitionTypes($utilisateurId),
            ];

            return [
                'success' => true,
                'data' => $stats
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Créer une notification
     */
    public function creerNotification(array $data)
    {
        try {
            $notification = Notification::create([
                'utilisateur_id' => $data['utilisateur_id'],
                'type' => $data['type'],
                'titre' => $data['titre'],
                'message' => $data['message'],
                'lue' => false,
                'donnees_supplementaires' => $data['donnees_supplementaires'] ?? null,
            ]);

            return [
                'success' => true,
                'message' => 'Notification créée avec succès',
                'data' => $notification
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la création de la notification',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer une notification à plusieurs utilisateurs
     */
    public function envoyerNotificationMultiple(array $utilisateursIds, array $data)
    {
        try {
            $notifications = [];
            
            foreach ($utilisateursIds as $utilisateurId) {
                $notification = Notification::create([
                    'utilisateur_id' => $utilisateurId,
                    'type' => $data['type'],
                    'titre' => $data['titre'],
                    'message' => $data['message'],
                    'lue' => false,
                    'donnees_supplementaires' => $data['donnees_supplementaires'] ?? null,
                ]);
                
                $notifications[] = $notification;
            }

            return [
                'success' => true,
                'message' => count($notifications) . ' notification(s) envoyée(s) avec succès',
                'data' => $notifications
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi des notifications',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Nettoyer les anciennes notifications
     */
    public function nettoyerAnciennesNotifications($jours = 30)
    {
        try {
            $supprimees = Notification::where('created_at', '<', now()->subDays($jours))
                ->where('lue', true)
                ->delete();

            return [
                'success' => true,
                'message' => $supprimees . ' anciennes notifications supprimées',
                'notifications_supprimees' => $supprimees
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du nettoyage des notifications',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir la répartition des types de notifications
     */
    private function getRepartitionTypes($utilisateurId)
    {
        $types = Notification::where('utilisateur_id', $utilisateurId)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $repartition = [];
        foreach ($types as $type) {
            $repartition[$type->type] = $type->count;
        }

        return $repartition;
    }

    /**
     * Notifier un artisan (méthode utilitaire)
     */
    public function notifierArtisan($artisanId, $type, $titre, $message, $donnees = null)
    {
        return $this->creerNotification([
            'utilisateur_id' => $artisanId,
            'type' => $type,
            'titre' => $titre,
            'message' => $message,
            'donnees_supplementaires' => $donnees,
        ]);
    }

    /**
     * Notifier un acheteur (méthode utilitaire)
     */
    public function notifierAcheteur($acheteurId, $type, $titre, $message, $donnees = null)
    {
        return $this->creerNotification([
            'utilisateur_id' => $acheteurId,
            'type' => $type,
            'titre' => $titre,
            'message' => $message,
            'donnees_supplementaires' => $donnees,
        ]);
    }

    /**
     * Notifier un administrateur (méthode utilitaire)
     */
    public function notifierAdministrateur($adminId, $type, $titre, $message, $donnees = null)
    {
        return $this->creerNotification([
            'utilisateur_id' => $adminId,
            'type' => $type,
            'titre' => $titre,
            'message' => $message,
            'donnees_supplementaires' => $donnees,
        ]);
    }
}
