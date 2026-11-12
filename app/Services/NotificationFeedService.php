<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Str;

class NotificationFeedService
{
    /**
     * @return array{count: int, latest_id: int}
     */
    public function unread(User $user): array
    {
        $row = AppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->selectRaw('COUNT(*) as unread_count, MAX(id) as latest_id')
            ->first();

        return [
            'count' => (int) ($row->unread_count ?? 0),
            'latest_id' => (int) ($row->latest_id ?? 0),
        ];
    }

    /**
     * @return array{count: int, items: list<array{id: int, title: string, message: string, url: ?string, read: bool, when: string}>}
     */
    public function latest(User $user, int $limit = 5): array
    {
        $items = AppNotification::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (AppNotification $notification): array => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => Str::limit((string) $notification->message, 140),
                'url' => $notification->link,
                'read' => $notification->read_at !== null,
                'when' => $notification->created_at?->diffForHumans() ?? '',
            ])
            ->all();

        return [
            'count' => $this->unread($user)['count'],
            'items' => $items,
        ];
    }
}
