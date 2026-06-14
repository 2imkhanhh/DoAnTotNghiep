<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('api')->user();
        
        $notifications = $user->notifications()->paginate(15);
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    public function markAsRead($id)
    {
        $user = auth('api')->user();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
    }

    public function markAllAsRead()
    {
        $user = auth('api')->user();
        $user->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
