<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Administrateur;

class NotificationService
{
    /**
     * Envoyer une notification à un utilisateur
     */
    public function notifier(int $userId, string $type, string $titre, string $message, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type'    => $type,
            'titre'   => $titre,
            'message' => $message,
            'data'    => !empty($data) ? json_encode($data) : null,
            'lue'     => false,
        ]);
    }

    /**
     * Notifier tous les administrateurs
     */
    public function notifierAdmins(string $type, string $titre, string $message, array $data = []): void
    {
        $admins = Administrateur::all();
        foreach ($admins as $admin) {
            $this->notifier($admin->user_id, $type, $titre, $message, $data);
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function marquerLue(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) return false;

        $notification->update(['lue' => true]);
        return true;
    }

    /**
     * Marquer toutes les notifications d'un user comme lues
     */
    public function marquerToutesLues(int $userId): void
    {
        Notification::where('user_id', $userId)
            ->where('lue', false)
            ->update(['lue' => true]);
    }

    /**
     * Compter les notifications non lues d'un user
     */
    public function compterNonLues(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('lue', false)
            ->count();
    }
}
