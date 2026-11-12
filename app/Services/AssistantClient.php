<?php

namespace App\Services;

use App\Exceptions\AssistantUnavailableException;
use Generator;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\StreamInterface;
use Throwable;

class AssistantClient
{
    public function available(): bool
    {
        $key = config('assistant.api_key');

        return config('assistant.enabled') === true && is_string($key) && $key !== '';
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return Generator<int, string>
     */
    public function stream(array $messages): Generator
    {
        $key = config('assistant.api_key');

        if (! is_string($key) || $key === '') {
            throw new AssistantUnavailableException(null);
        }

        try {
            $response = Http::withToken($key)
                ->accept('text/event-stream')
                ->withOptions([
                    'stream' => true,
                ])
                ->timeout((int) config('assistant.timeout'))
                ->connectTimeout(5)
                ->post(config('assistant.base_url').'/chat/completions', [
                    'model' => (string) config('assistant.model'),
                    'stream' => true,
                    'temperature' => 0.2,
                    'max_tokens' => 500,
                    'messages' => $messages,
                ]);
        } catch (Throwable) {
            throw new AssistantUnavailableException(null);
        }

        $status = $response->status();

        if ($status < 200 || $status >= 300) {
            throw new AssistantUnavailableException($status);
        }

        try {
            yield from $this->deltas($response->toPsrResponse()->getBody());
        } catch (AssistantUnavailableException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new AssistantUnavailableException($status);
        }
    }

    /**
     * @return Generator<int, string>
     */
    private function deltas(StreamInterface $body): Generator
    {
        $buffer = '';
        $mode = null;
        $produced = 0;

        while (! $body->eof()) {
            $chunk = $body->read(2048);

            if ($chunk === '') {
                break;
            }

            if ($mode === null) {
                $mode = str_starts_with(ltrim($chunk), 'data:') ? 'sse' : 'json';
            }

            $buffer .= $chunk;

            if ($mode === 'json') {
                continue;
            }

            $produced = yield from $this->consumeSse($buffer, $produced);

            if ($produced >= 200) {
                return;
            }
        }

        if ($mode === 'json') {
            $content = $this->contentFromJson($buffer);

            if ($content !== null) {
                yield $content;
            }

            return;
        }

        if (trim($buffer) !== '') {
            yield from $this->consumeSse($buffer, $produced, true);
        }
    }

    /**
     * @return int
     */
    private function consumeSse(string &$buffer, int $produced, bool $flush = false): Generator
    {
        $limit = $flush ? 1 : 0;

        while (($pos = strpos($buffer, "\n")) !== false || ($flush && trim($buffer) !== '' && $limit-- > 0)) {
            if (($pos = strpos($buffer, "\n")) !== false) {
                $line = trim(substr($buffer, 0, $pos));
                $buffer = substr($buffer, $pos + 1);
            } else {
                $line = trim($buffer);
                $buffer = '';
            }

            if ($line === 'data: [DONE]') {
                return $produced;
            }

            $delta = $this->deltaFromLine($line);

            if ($delta === null) {
                continue;
            }

            $produced++;
            yield $delta;

            if ($produced >= 200) {
                return $produced;
            }
        }

        return $produced;
    }

    private function deltaFromLine(string $line): ?string
    {
        if (! str_starts_with($line, 'data:')) {
            return null;
        }

        $payload = trim(substr($line, 5));

        if ($payload === '' || $payload === '[DONE]') {
            return null;
        }

        $json = json_decode($payload, true);

        if (! is_array($json)) {
            return null;
        }

        $content = $json['choices'][0]['delta']['content'] ?? null;

        return is_string($content) && $content !== '' ? $content : null;
    }

    private function contentFromJson(string $raw): ?string
    {
        $json = json_decode($raw, true);
        $content = is_array($json) ? ($json['choices'][0]['message']['content'] ?? null) : null;

        return is_string($content) && $content !== '' ? $content : null;
    }
}
