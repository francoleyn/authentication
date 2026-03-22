<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(15);

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    public function unread(Request $request)
    {
        $notifications = $request->user()->unreadNotifications;

        return response()->json([
            'unread_count' => $notifications->count(),
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification,
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted',
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string|max:50',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->notify(new GeneralNotification(
            $request->title,
            $request->message,
            $request->type ?? 'general'
        ));

        return response()->json([
            'message' => 'Notification sent successfully',
        ]);
    }

    public function sendToAll(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string|max:50',
        ]);

        $users = User::all();
        foreach ($users as $user) {
            $user->notify(new GeneralNotification(
                $request->title,
                $request->message,
                $request->type ?? 'announcement'
            ));
        }

        return response()->json([
            'message' => 'Notification sent to all users',
            'users_notified' => $users->count(),
        ]);
    }
}
