<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AssistantService;
use App\Services\AssistantTranscript;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AssistantController extends Controller
{
    public function store(Request $request, AssistantService $assistant): StreamedResponse
    {
        abort_unless(config('assistant.enabled') === true, 404);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:'.(int) config('assistant.max_chars')],
        ]);

        $message = trim($validated['message']);

        if ($message === '') {
            throw ValidationException::withMessages([
                'message' => 'Write a message first.',
            ]);
        }

        $owner = $request->user();
        abort_unless($owner instanceof User, 403);

        return response()->stream(function () use ($assistant, $owner, $message): void {
            try {
                foreach ($assistant->events($owner, $message) as $event) {
                    $this->emit($event);
                }
            } catch (Throwable $exception) {
                Log::warning('FurShield assistant stopped.', [
                    'exception' => $exception::class,
                ]);
                $this->emit([
                    'type' => 'fallback',
                    'text' => (string) config('assistant.fallback'),
                ]);
                $this->emit(['type' => 'done']);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function reset(Request $request, AssistantTranscript $transcript): JsonResponse
    {
        abort_unless(config('assistant.enabled') === true, 404);

        $owner = $request->user();
        abort_unless($owner instanceof User, 403);

        $transcript->clear($owner);

        return response()->json(['ok' => true]);
    }

    /**
     * @param  array<string, string>  $event
     */
    private function emit(array $event): void
    {
        echo 'data: '.json_encode($event, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)."\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}
