<?php

namespace App\Services;

use App\Exceptions\GoogleSignInException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class GoogleIdToken
{
    /**
     * @return array{email: string, name: string}
     */
    public function verify(string $jwt): array
    {
        $clientId = (string) config('services.google.client_id');

        if ($clientId === '') {
            throw new GoogleSignInException();
        }

        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            throw new GoogleSignInException();
        }

        $header = json_decode($this->base64UrlDecode($parts[0]), true);
        $claims = json_decode($this->base64UrlDecode($parts[1]), true);

        if (! is_array($header) || ! is_array($claims) || ($header['alg'] ?? null) !== 'RS256') {
            throw new GoogleSignInException();
        }

        $signature = $this->base64UrlDecode($parts[2]);
        $pem = $this->certificate($header['kid'] ?? null);

        $valid = openssl_verify($parts[0].'.'.$parts[1], $signature, $pem, OPENSSL_ALGO_SHA256);

        if ($valid !== 1) {
            throw new GoogleSignInException();
        }

        $this->assertClaims($claims, $clientId);

        $email = strtolower(trim((string) $claims['email']));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new GoogleSignInException();
        }

        $name = trim((string) ($claims['name'] ?? ''));

        return [
            'email' => $email,
            'name' => $name !== '' ? mb_substr($name, 0, 255) : mb_substr(strstr($email, '@', true) ?: 'Owner', 0, 255),
        ];
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    private function assertClaims(array $claims, string $clientId): void
    {
        $issuer = $claims['iss'] ?? null;

        if (! in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw new GoogleSignInException();
        }

        $audience = $claims['aud'] ?? null;
        $audiences = is_array($audience) ? $audience : [$audience];

        if (! in_array($clientId, $audiences, true)) {
            throw new GoogleSignInException();
        }

        $verified = $claims['email_verified'] ?? false;

        if ($verified !== true && $verified !== 'true') {
            throw new GoogleSignInException();
        }

        $now = time();
        $leeway = max(0, (int) config('services.google.clock_tolerance'));
        $expires = (int) ($claims['exp'] ?? 0);
        $issued = (int) ($claims['iat'] ?? 0);

        if ($expires < ($now - $leeway) || $issued > ($now + $leeway)) {
            throw new GoogleSignInException();
        }
    }

    private function certificate(?string $kid): string
    {
        if (! is_string($kid) || $kid === '') {
            throw new GoogleSignInException();
        }

        try {
            $keys = Cache::remember('google.oauth.certs', 3600, function (): array {
                $response = Http::timeout(5)->acceptJson()->get('https://www.googleapis.com/oauth2/v3/certs');

                if (! $response->ok() || ! is_array($response->json('keys'))) {
                    throw new GoogleSignInException();
                }

                return $response->json('keys');
            });
        } catch (GoogleSignInException $exception) {
            Cache::forget('google.oauth.certs');

            throw $exception;
        } catch (Throwable) {
            Cache::forget('google.oauth.certs');

            throw new GoogleSignInException();
        }

        foreach ($keys as $key) {
            if (! is_array($key) || ($key['kid'] ?? null) !== $kid) {
                continue;
            }

            if (($key['kty'] ?? null) !== 'RSA' || ! isset($key['n'], $key['e'])) {
                break;
            }

            return $this->pem((string) $key['n'], (string) $key['e']);
        }

        throw new GoogleSignInException();
    }

    private function pem(string $modulus, string $exponent): string
    {
        $modulusBytes = $this->base64UrlDecode($modulus);
        $exponentBytes = $this->base64UrlDecode($exponent);
        $bitString = "\x00".$this->sequence($this->integer($modulusBytes).$this->integer($exponentBytes));
        $algorithm = hex2bin('300d06092a864886f70d0101010500');
        $body = $this->sequence($algorithm."\x03".$this->length(strlen($bitString)).$bitString);

        return "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode($body), 64, "\n")."-----END PUBLIC KEY-----\n";
    }

    private function sequence(string $body): string
    {
        return "\x30".$this->length(strlen($body)).$body;
    }

    private function integer(string $bytes): string
    {
        if ($bytes !== '' && (ord($bytes[0]) & 0x80) !== 0) {
            $bytes = "\x00".$bytes;
        }

        return "\x02".$this->length(strlen($bytes)).$bytes;
    }

    private function length(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $encoded = ltrim(pack('N', $length), "\x00");

        return chr(0x80 | strlen($encoded)).$encoded;
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;

        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        if ($decoded === false) {
            throw new GoogleSignInException();
        }

        return $decoded;
    }
}
