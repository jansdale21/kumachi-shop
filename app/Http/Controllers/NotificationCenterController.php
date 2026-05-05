<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;

class NotificationCenterController extends Controller
{
    public function markAsRead(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === request()->user()?->id, 403);

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back();
    }

    public function markAllAsRead(): RedirectResponse
    {
        Notification::query()
            ->where('user_id', request()->user()?->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('status', 'Notifications marked as read.');
    }
}
