<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /api/admin/notifications
     * Liste des notifications pour l'admin
     */
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    /**
     * POST /api/admin/notifications/{id}/read
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification, Request $request)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marquée comme lue']);
    }

    /**
     * POST /api/admin/notifications/mark-all-read
     * Tout marquer comme lu
     */
    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues']);
    }
}