<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Services\NotificationFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = AppNotification::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount(NotificationFeedService $feed): JsonResponse
    {
        return response()->json($feed->unread(Auth::user()));
    }

    public function latest(NotificationFeedService $feed): JsonResponse
    {
        return response()->json($feed->latest(Auth::user()));
    }

    public function markRead(Request $request, AppNotification $notification, NotificationFeedService $feed): RedirectResponse|JsonResponse
    {
        abort_unless($notification->user_id === Auth::id(), 403);
        $notification->markRead();

        if ($request->expectsJson()) {
            return response()->json($feed->unread($request->user()));
        }

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(): RedirectResponse
    {
        AppNotification::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now('UTC')]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
