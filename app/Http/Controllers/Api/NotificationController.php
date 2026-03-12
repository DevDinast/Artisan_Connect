<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        Notification::where('user_id', $user->id)
            ->where('lue', false)
            ->update(['lue' => true]);

        return response()->json([
            'success' => true,
            'data'    => ['notifications' => $notifications->items()],
            'message' => 'Notifications récupérées avec succès',
        ], 200);
    }
}
