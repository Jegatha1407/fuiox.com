<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    private function userId() { return session('auth_user'); }

    // ── LIST NOTIFICATIONS ────────────────────────
    public function index()
    {
        $notifications = DB::table('notifications')
            ->where('user_id', $this->userId())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function($n) {
                $n->created_at = $n->created_at
                    ? \Carbon\Carbon::parse($n->created_at)->diffForHumans()
                    : '';
                return $n;
            });

        return response()->json(['notifications' => $notifications]);
    }

    // ── UNREAD COUNT ──────────────────────────────
    public function count()
    {
        $count = DB::table('notifications')
            ->where('user_id', $this->userId())
            ->where('is_read', 0)
            ->count();

        return response()->json(['count' => $count]);
    }

    // ── MARK ONE AS READ ──────────────────────────
    public function markRead($id)
    {
        DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update(['is_read' => 1, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ── MARK ALL AS READ ──────────────────────────
    public function markAllRead()
    {
        DB::table('notifications')
            ->where('user_id', $this->userId())
            ->where('is_read', 0)
            ->update(['is_read' => 1, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ── SEND NOTIFICATION (static helper) ─────────
    public static function send(int $userId, string $type, string $title, string $message, array $data = [])
    {
        try {
            DB::table('notifications')->insert([
                'user_id'    => $userId,
                'type'       => $type,
                'title'      => $title,
                'message'    => $message,
                'is_read'    => 0,
                'data'       => !empty($data) ? json_encode($data) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notification failed: ' . $e->getMessage());
        }
    }
}