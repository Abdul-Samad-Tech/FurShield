<?php

namespace Tests\Feature;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GoogleSignInTest extends TestCase
{
    use RefreshDatabase;

    private const CLIENT = 'test-client.apps.googleusercontent.com';

    private string $logBlob = '';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => self::CLIENT,
            'services.google.clock_tolerance' => 86400,
        ]);

        Http::preventStrayRequests();
        Mail::fake();
    }

    public function test_a_new_gmail_account_joins_as_an_owner(): void
    {
        $this->fakeCertificate();

        $this->post(route('auth.google'), [
            'credential' => $this->token([
                'email' => 'New.Owner@gmail.com',
                'name' => 'New Owner',
                'email_verified' => true,
            ]),
        ])->assertRedirect(route('owner.dashboard', absolute: false));

        $this->assertAuthenticated();
        $user = User::query()->where('email', 'new.owner@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('owner', $user->role->value);
        $this->assertNotNull($user->email_verified_at);

        Mail::assertSent(WelcomeMail::class, function (WelcomeMail $mail): bool {
            return $mail->joined
                && $mail->hasTo('new.owner@gmail.com')
                && $mail->envelope()->subject === 'Welcome to FurShield'
                && str_contains($mail->render(), 'Your Gmail account is now a FurShield pet owner account');
        });
    }

    public function test_an_existing_account_keeps_its_role(): void
    {
        $this->fakeCertificate();
        $vet = User::factory()->vet()->create(['email' => 'clinic@gmail.com']);

        $this->post(route('auth.google'), [
            'credential' => $this->token([
                'email' => 'clinic@gmail.com',
                'name' => 'Someone Else',
                'email_verified' => true,
            ]),
        ])->assertRedirect(route('vet.dashboard', absolute: false));

        $this->assertAuthenticatedAs($vet);
        $this->assertSame(1, User::query()->where('email', 'clinic@gmail.com')->count());
        $this->assertSame('vet', $vet->fresh()->role->value);

        Mail::assertSent(WelcomeMail::class, function (WelcomeMail $mail): bool {
            return $mail->joined === false
                && $mail->hasTo('clinic@gmail.com')
                && $mail->envelope()->subject === 'Welcome back to FurShield';
        });
    }

    public function test_a_bad_token_is_rejected_and_not_logged(): void
    {
        $this->fakeCertificate();
        $this->captureLogs();
        $token = $this->token([
            'email' => 'secret-person@gmail.com',
            'email_verified' => true,
            'aud' => 'another-client.apps.googleusercontent.com',
        ]);

        $this->from(route('login'))
            ->post(route('auth.google'), ['credential' => $token])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('credential');

        $this->assertGuest();
        Mail::assertNothingSent();
        $this->assertStringNotContainsString($token, $this->logBlob);
        $this->assertStringNotContainsString('secret-person@gmail.com', $this->logBlob);
    }

    public function test_a_token_outside_the_clock_window_is_rejected(): void
    {
        $this->fakeCertificate();
        config(['services.google.clock_tolerance' => 60]);

        $this->post(route('auth.google'), [
            'credential' => $this->token([
                'email' => 'late@gmail.com',
                'email_verified' => true,
                'exp' => time() - 7200,
            ]),
        ])->assertSessionHasErrors('credential');

        $this->assertGuest();
    }

    public function test_the_button_is_hidden_until_a_client_id_is_set(): void
    {
        config(['services.google.client_id' => '']);

        $this->get(route('login'))->assertOk()->assertDontSee('accounts.google.com/gsi/client', false);
        $this->post(route('auth.google'), ['credential' => 'x'])->assertNotFound();

        config(['services.google.client_id' => self::CLIENT]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('accounts.google.com/gsi/client', false)
            ->assertSee(self::CLIENT, false)
            ->assertSee('joins as a pet owner', false);

        $this->get(route('register'))->assertOk()->assertSee('accounts.google.com/gsi/client', false);
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    private function token(array $claims): string
    {
        $now = time();
        $payload = array_merge([
            'iss' => 'https://accounts.google.com',
            'aud' => self::CLIENT,
            'iat' => $now,
            'exp' => $now + 3600,
            'email_verified' => true,
        ], $claims);

        $header = $this->base64Url((string) json_encode(['alg' => 'RS256', 'kid' => 'test-key']));
        $body = $this->base64Url((string) json_encode($payload));
        openssl_sign($header.'.'.$body, $signature, $this->privateKey(), OPENSSL_ALGO_SHA256);

        return $header.'.'.$body.'.'.$this->base64Url($signature);
    }

    private function fakeCertificate(): void
    {
        $details = openssl_pkey_get_details($this->privateKey());
        $modulus = $this->base64Url($details['rsa']['n']);
        $exponent = $this->base64Url($details['rsa']['e']);

        Http::fake([
            'https://www.googleapis.com/oauth2/v3/certs' => Http::response([
                'keys' => [[
                    'kty' => 'RSA',
                    'kid' => 'test-key',
                    'alg' => 'RS256',
                    'n' => $modulus,
                    'e' => $exponent,
                ]],
            ]),
        ]);
    }

    private function privateKey(): \OpenSSLAsymmetricKey
    {
        static $key = null;

        if ($key instanceof \OpenSSLAsymmetricKey) {
            return $key;
        }

        $loaded = openssl_pkey_get_private($this->privatePem());

        if (! $loaded instanceof \OpenSSLAsymmetricKey) {
            $this->fail('OpenSSL could not read the test key.');
        }

        return $key = $loaded;
    }

    private function privatePem(): string
    {
        return <<<'PEM'
-----BEGIN PRIVATE KEY-----
MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQCn2GcGtnlA1fRc
7G+31f1Ukl4QtHT8YWUpPtmu9yKw0qGbZ9QtWQDjg75/C6qxwRPDr3cQ0e6hqmGo
jBljKRe8d/uo0OIjwCIyOJqsuMtpE0TNWa1S/3mFXQIA318V7g2d6Vug81qC8Sml
jBhXwBR9FP2Gl4tydfWloy8/aSISXJ5iJyrg7SFHKpKQ9Eyl0m+yrTL+d1ccN/5w
r3p2ZyzHfa/RT+vuzDCvGa4SiluiISK2dHIwUiX6WgbQKPuuezvnMF+Ysh1+iQp6
09RkV78gNvCDfdlv80V64CZz1sFUbO0zbGAVs3zYMSZvloGsrceJS4ddl8q/PM5s
vEcaOlMtAgMBAAECggEAAzCYKKcAH+zRA5EUwHQnJ9vOQNgFHyTEtOd2YJ5ewOiH
3Lm47RGzdB91V9nDaTRnqUDk7ONoexcPWxIqi1llE5ZeKIOrwI0uuTCdM+ohJ4KP
vK1XH2wuhYvSdWzJVFPdW2JrdOAbGMD3jydd8RD78rKa7NtlcYvvWrICvFxFov6q
qsv06Vo90gdblR0y/Uf44yYcejynf6oXq6WmDzS4IHfK2wVS3636HOfei6xO9wOZ
V/NWJhUuQb5ZrVCYY6oODekG3o0izz4tIIn9kDds5dZRkK1EqLwI96WtvcOMrz84
rkoPlq38uxWVQOH4UM9yP0F6JIqlg+W83bQ7tiFrwQKBgQDs+nc+3oroc+muYHSJ
jD1DNn91d5Njmk4YrpqE493Eb/sytvAR1XZJa5c4nz6hawOU7j9XuOKy1lfvBY3v
w9pFGeaQqdsTA4KdPivPyLLdTnfKuFpqikkU2VnfCguJGhhtT6Eq9JYHQElujtnj
A70YDp789tkO1KXTr208Y9RN4QKBgQC1UVxTvlGJpk5vI+c67ws4yuaY+NXSwth1
5SnhxIdIpnQjHWuxMiJAspuLbzcK0sx/YlNMHvxHEWKB6zsEdrc4YFXgsjRiegAU
6cyvkx3RoWZ6Kyjp6Ye4dJWDCBpCpkIEDL/TPi7y2vMOatBPknuEEZjUZFJnH0I+
KyZCcRq2zQKBgQDhb6/Kzhgk5Ef0S+vxAckxzIFaNQOwaBVNI6T5MHBCtCiqaR9e
R8w8Mi7sfYkuM8h3gGIfyFFYwdFrH8STx6Q3wlj1W7B9mQRDI+a3xvxozYHmBpR0
79JwFAiLxlmM9pcwPeVbIMwsX7q7gwfCD0duwFH84bNBiYerJiJrpahM4QKBgQCV
pphIeh18OeokqwJvTtr5PeZDvaVltfHIpKMDtxxdZx0V2nOHJbTFp2TgGaPZaBDm
1nWfrzdTRNMEHe1xCrbAbiqTMSIxYTZuDGYnR4pl+gvFq5eyMFAXewnnwylBd02n
YAp3RKylPvc7IAfclS8i2w9DxaWJfXBENYL3dbixxQKBgQC8gDXWatcaUzgoRHq5
sGmhmeGSMmKiwKoOE094wdGVykcs9nYFlf8wEivkDsxctTCFU8quz8i2aoAmD0Hd
nmUxN0UxavdQlx0UoFq+dT859G6reZazFuNiJNOrg5cl6bZZQcpT0LP3kPXg2LTP
OUTdMnLSTA/LCf3zx+n3nISIXg==
-----END PRIVATE KEY-----
PEM;
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function captureLogs(): void
    {
        $this->logBlob = '';

        Log::listen(function (MessageLogged $event): void {
            $this->logBlob .= $event->message.' '.json_encode($event->context);
        });
    }
}
