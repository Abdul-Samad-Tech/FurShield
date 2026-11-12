<?php

namespace App\Services;

use App\Exceptions\AssistantUnavailableException;
use App\Models\User;
use Generator;
use Illuminate\Support\Facades\Log;

class AssistantService
{
    public function __construct(
        private readonly AssistantGuard $guard,
        private readonly AssistantContextBuilder $context,
        private readonly AssistantTranscript $transcript,
        private readonly AssistantClient $client,
    ) {}

    /**
     * Events are plain arrays. Nothing in them includes the API key or the pet profile block.
     *
     * @return Generator<int, array<string, string>>
     */
    public function events(User $owner, string $message): Generator
    {
        $built = $this->context->build($owner);

        if ($this->guard->isEmergency($message, $built['species'])) {
            yield ['type' => 'emergency'];
        }

        $reply = '';

        try {
            if (! $this->client->available()) {
                throw new AssistantUnavailableException(null);
            }

            $messages = [
                ['role' => 'system', 'content' => $this->guard->systemPrompt()],
                ['role' => 'system', 'content' => $built['prompt']],
                ...$this->transcript->messages($owner),
                ['role' => 'user', 'content' => $message],
            ];

            foreach ($this->client->stream($messages) as $delta) {
                $next = $reply.$delta;

                if ($this->guard->containsDose($next)) {
                    $reply = (string) config('assistant.dose_refusal');
                    yield ['type' => 'replace', 'text' => $reply];
                    break;
                }

                $reply = $next;
                yield ['type' => 'delta', 'text' => $delta];

                if (mb_strlen($reply) >= 4000) {
                    break;
                }
            }
        } catch (AssistantUnavailableException $exception) {
            $this->logFailure($exception);
            $reply = (string) config('assistant.fallback');
            yield ['type' => 'fallback', 'text' => $reply];
        }

        if (trim($reply) === '') {
            $reply = (string) config('assistant.fallback');
            yield ['type' => 'fallback', 'text' => $reply];
        }

        $this->transcript->push($owner, $message, $reply);
        yield ['type' => 'done'];
    }

    private function logFailure(AssistantUnavailableException $exception): void
    {
        Log::warning('FurShield assistant unavailable.', array_filter([
            'status' => $exception->status,
            'exception' => $exception::class,
        ], fn (mixed $value): bool => $value !== null));
    }
}
