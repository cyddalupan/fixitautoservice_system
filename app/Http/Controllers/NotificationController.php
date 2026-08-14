<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Real notification feed for the header bell.
     * Returns unread count + latest notifications as JSON.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($n) {
                $data = is_array($n->data) ? $n->data : (json_decode($n->data, true) ?? []);
                return [
                    'id' => $n->id,
                    'message' => $data['message'] ?? 'Notification',
                    'appointment_number' => $data['appointment_number'] ?? null,
                    'customer_name' => $data['customer_name'] ?? null,
                    'link' => $data['link'] ?? '/appointments',
                    'time' => $n->created_at ? $n->created_at->diffForHumans() : null,
                    'read' => $n->read_at !== null,
                ];
            });

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'items' => $notifications,
        ]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }
}
