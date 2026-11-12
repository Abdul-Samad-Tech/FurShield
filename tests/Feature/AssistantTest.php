<?php

namespace Tests\Feature;

use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use App\Services\AssistantGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AssistantTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'sk-test-DO-NOT-LEAK';

    private string $logBlob = '';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'assistant.enabled' => false,
            'assistant.api_key' => '',
            'assistant.per_minute' => 12,
        ]);

        Http::preventStrayRequests();
    }

    public function test_the_sheet_is_hidden_until_it_is_enabled_and_never_prints_the_key(): void
    {
        $owner = User::factory()->owner()->create();
        $vet = User::factory()->vet()->create();

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertDontSee('id="assistant-sheet"', false);

        config([
            'assistant.enabled' => true,
            'assistant.api_key' => self::KEY,
        ]);

        $this->actingAs($owner)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('id="assistant-sheet"', false)
            ->assertSee('does not give medication doses', false)
            ->assertSee('Emergency check', false)
            ->assertDontSee(self::KEY, false);

        $this->actingAs($vet)
            ->get(route('vet.dashboard'))
            ->assertOk()
            ->assertDontSee('id="assistant-sheet"', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="assistant-sheet"', false);

        $script = file_get_contents(resource_path('js/assistant.js'));
        $this->assertIsString($script);
        $this->assertStringNotContainsString('api_key', $script);
        $this->assertStringNotContainsString('FURSHIELD_ASSISTANT', $script);
    }

    public function test_a_missing_key_streams_a_fallback_and_does_not_call_the_api(): void
    {
        config(['assistant.enabled' => true, 'assistant.api_key' => '']);
        $this->captureLogs();

        $owner = User::factory()->owner()->create();

        $body = $this->actingAs($owner)
            ->postJson(route('owner.assistant.store'), ['message' => 'SECRET-QUESTION about Milo'])
            ->assertOk()
            ->streamedContent();

        Http::assertNothingSent();
        $this->assertStringContainsString('can\'t answer right now', $body);
        $this->assertStringNotContainsString('SECRET-QUESTION', $this->logBlob);
        $this->assertStringContainsString('"type":"fallback"', $body);
    }

    public function test_context_sent_to_the_model_is_limited_to_profile_and_recent_records(): void
    {
        config(['assistant.enabled' => true, 'assistant.api_key' => self::KEY]);
        Http::fake([
            '*' => Http::response($this->sse('Keep fresh water available and ask a veterinarian if this continues.'), 200, [
                'Content-Type' => 'text/event-stream',
            ]),
        ]);

        $owner = User::factory()->owner()->create([
            'email' => 'secret-owner@example.com',
            'phone' => '5550199',
            'address' => 'SECRET-ADDRESS',
        ]);
        $pet = Pet::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Milo',
            'species' => 'Dog',
            'age' => 7,
            'medical_history' => 'Pollen allergy noted on the record.',
        ]);
        HealthRecord::factory()->create([
            'pet_id' => $pet->id,
            'title' => 'Rabies booster',
            'record_type' => 'vaccination',
            'symptoms' => 'scratching',
            'allergies' => 'SECRET-ALLERGY-POLLEN',
            'medications' => 'SECRET-MED 8mg',
            'treatment' => 'SECRET-TREATMENT',
            'diagnosis' => 'SECRET-DIAGNOSIS',
            'lab_results' => 'SECRET-LAB',
            'notes' => 'SECRET-NOTES',
        ]);
        Pet::factory()->create(['name' => 'SECRET-OTHER-PET']);

        $body = $this->actingAs($owner)
            ->postJson(route('owner.assistant.store'), ['message' => 'What should I watch for?'])
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('fresh water', $body);
        $this->assertStringNotContainsString(self::KEY, $body);
        $this->assertStringNotContainsString('SECRET-ALLERGY-POLLEN', $body);
        $this->assertStringNotContainsString('secret-owner@example.com', $body);

        Http::assertSent(function (Request $request): bool {
            $payload = $request->body();
            $authorization = $request->header('Authorization')[0] ?? '';

            $this->assertSame('Bearer '.self::KEY, $authorization);
            $this->assertStringContainsString('You do not diagnose', $payload);
            $this->assertStringContainsString('You do not give a medication dose', $payload);
            $this->assertStringContainsString('recommend a veterinarian', $payload);
            $this->assertStringContainsString('emergency check', $payload);
            $this->assertStringContainsString('Species: Dog', $payload);
            $this->assertStringContainsString('Age: 7 years', $payload);
            $this->assertStringContainsString('SECRET-ALLERGY-POLLEN', $payload);
            $this->assertStringContainsString('Pollen allergy noted', $payload);
            $this->assertStringContainsString('Rabies booster', $payload);
            $this->assertStringContainsString('scratching', $payload);
            $this->assertStringNotContainsString('SECRET-MED', $payload);
            $this->assertStringNotContainsString('SECRET-TREATMENT', $payload);
            $this->assertStringNotContainsString('SECRET-DIAGNOSIS', $payload);
            $this->assertStringNotContainsString('SECRET-LAB', $payload);
            $this->assertStringNotContainsString('SECRET-NOTES', $payload);
            $this->assertStringNotContainsString('secret-owner@example.com', $payload);
            $this->assertStringNotContainsString('5550199', $payload);
            $this->assertStringNotContainsString('SECRET-ADDRESS', $payload);
            $this->assertStringNotContainsString('SECRET-OTHER-PET', $payload);
            $this->assertStringNotContainsString(self::KEY, $payload);

            return str_ends_with($request->url(), '/chat/completions');
        });
    }

    public function test_red_flag_symptoms_mark_the_stream_for_the_emergency_button(): void
    {
        config(['assistant.enabled' => true, 'assistant.api_key' => self::KEY]);
        Http::fake(['*' => Http::response($this->sse('Contact a veterinarian now.'), 200)]);

        $owner = User::factory()->owner()->create();
        Pet::factory()->create([
            'owner_id' => $owner->id,
            'species' => 'Cat',
            'name' => 'Luna',
        ]);

        $gasping = $this->actingAs($owner)
            ->postJson(route('owner.assistant.store'), ['message' => 'Milo is gasping'])
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('"type":"emergency"', $gasping);

        $guard = app(AssistantGuard::class);
        $this->assertTrue($guard->isEmergency('the cat is straining to urinate', ['cat']));
        $this->assertTrue($guard->isEmergency('the rabbit stopped eating', ['rabbit']));
        $this->assertFalse($guard->isEmergency('the dog is not eating', ['dog']));
        $this->assertFalse($guard->isEmergency('the dog is straining to urinate', ['dog']));
        $this->assertTrue($guard->containsDose('Give 5 mg tonight'));
        $this->assertSame('Give [dose omitted] tonight', $guard->redactDoses('Give 5 mg tonight'));
    }

    public function test_a_dose_in_the_model_reply_is_replaced_before_it_is_streamed(): void
    {
        config(['assistant.enabled' => true, 'assistant.api_key' => self::KEY]);
        Http::fake(['*' => Http::response($this->sse("Give 5 mg tonight.\n"), 200)]);

        $owner = User::factory()->owner()->create();

        $body = $this->actingAs($owner)
            ->postJson(route('owner.assistant.store'), ['message' => 'How much medicine?'])
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString("I can't give a medication dose", $body);
        $this->assertStringNotContainsString('5 mg', $body);
    }

    public function test_an_api_failure_logs_no_pet_or_message_text_and_streams_the_fallback(): void
    {
        config(['assistant.enabled' => true, 'assistant.api_key' => self::KEY]);
        $this->captureLogs();
        Http::fake([
            '*' => Http::response('SECRET-ALLERGY-POLLEN '.self::KEY, 503),
        ]);

        $owner = User::factory()->owner()->create();
        Pet::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Milo',
            'species' => 'Dog',
            'age' => 4,
        ]);
        $pet = Pet::query()->where('owner_id', $owner->id)->firstOrFail();
        HealthRecord::factory()->create([
            'pet_id' => $pet->id,
            'allergies' => 'SECRET-ALLERGY-POLLEN',
        ]);

        $body = $this->actingAs($owner)
            ->postJson(route('owner.assistant.store'), ['message' => 'SECRET-QUESTION about the allergy'])
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('"type":"fallback"', $body);
        $this->assertStringNotContainsString('SECRET-ALLERGY-POLLEN', $body);
        $this->assertStringNotContainsString('SECRET-QUESTION', $body);
        $this->assertStringNotContainsString(self::KEY, $body);

        $blob = $this->logBlob;
        $this->assertStringNotContainsString('SECRET-ALLERGY-POLLEN', $blob);
        $this->assertStringNotContainsString('SECRET-QUESTION', $blob);
        $this->assertStringNotContainsString(self::KEY, $blob);
        $this->assertStringContainsString('503', $blob);
    }

    public function test_each_owner_is_rate_limited_and_can_clear_the_transcript(): void
    {
        config([
            'assistant.enabled' => true,
            'assistant.api_key' => self::KEY,
            'assistant.per_minute' => 2,
        ]);
        Http::fake(['*' => Http::response($this->sse('Ask a veterinarian.'), 200)]);

        $owner = User::factory()->owner()->create();
        $other = User::factory()->owner()->create();

        $this->actingAs($owner)->postJson(route('owner.assistant.store'), ['message' => 'First note'])->assertOk()->streamedContent();
        $this->actingAs($owner)->postJson(route('owner.assistant.store'), ['message' => 'SECOND-SECRET'])->assertOk()->streamedContent();
        $this->actingAs($owner)->postJson(route('owner.assistant.store'), ['message' => 'Third note'])->assertStatus(429);
        $this->actingAs($other)->postJson(route('owner.assistant.store'), ['message' => 'Other owner'])->assertOk()->streamedContent();

        $cached = Cache::get('assistant:transcript:'.$owner->id);
        $this->assertIsArray($cached);
        $this->assertStringContainsString('SECOND-SECRET', json_encode($cached));

        $this->actingAs($owner)->postJson(route('owner.assistant.reset'))->assertOk();
        $this->assertNull(Cache::get('assistant:transcript:'.$owner->id));
    }

    public function test_other_roles_and_a_disabled_assistant_cannot_call_it(): void
    {
        $owner = User::factory()->owner()->create();
        $vet = User::factory()->vet()->create();

        $this->postJson(route('owner.assistant.store'), ['message' => 'Hello'])->assertUnauthorized();
        $this->actingAs($vet)->postJson(route('owner.assistant.store'), ['message' => 'Hello'])->assertRedirect(route('vet.dashboard'));
        $this->actingAs($owner)->postJson(route('owner.assistant.store'), ['message' => 'Hello'])->assertNotFound();

        config(['assistant.enabled' => true, 'assistant.api_key' => '']);
        $this->actingAs($owner)->postJson(route('owner.assistant.store'), ['message' => '   '])->assertJsonValidationErrors('message');
    }

    private function captureLogs(): void
    {
        $this->logBlob = '';

        Log::listen(function (MessageLogged $event): void {
            $this->logBlob .= $event->message.' '.json_encode($event->context);
        });
    }

    private function sse(string $text): string
    {
        return 'data: '.json_encode(['choices' => [['delta' => ['content' => $text]]]])."\n\ndata: [DONE]\n";
    }
}
