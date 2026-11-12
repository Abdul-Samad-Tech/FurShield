<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class AssistantTranscript
{
    /**
     * @return list<array{role: string, content: string}>
     */
    public function messages(User $owner): array
    {
        $stored = Cache::get($this->key($owner), []);

        if (! is_array($stored)) {
            return [];
        }

        $clean = [];

        foreach ($stored as $turn) {
            if (! is_array($turn)) {
                continue;
            }

            $role = $turn['role'] ?? '';
            $content = $turn['content'] ?? '';

            if (! in_array($role, ['user', 'assistant'], true) || ! is_string($content) || $content === '') {
                continue;
            }

            $clean[] = [
                'role' => $role,
                'content' => mb_substr($content, 0, 2000),
            ];
        }

        return array_slice($clean, -8);
    }

    public function push(User $owner, string $userMessage, string $reply): void
    {
        $messages = $this->messages($owner);
        $messages[] = ['role' => 'user', 'content' => mb_substr($userMessage, 0, 800)];
        $messages[] = ['role' => 'assistant', 'content' => mb_substr($reply, 0, 2000)];

        Cache::put($this->key($owner), array_slice($messages, -8), now()->addHours(6));
    }

    public function clear(User $owner): void
    {
        Cache::forget($this->key($owner));
    }

    private function key(User $owner): string
    {
        return 'assistant:transcript:'.$owner->getKey();
    }
}
