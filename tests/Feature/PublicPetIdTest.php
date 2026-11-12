<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Appointment;
use App\Models\HealthRecord;
use App\Models\InsurancePolicy;
use App\Models\Pet;
use App\Models\PetSighting;
use App\Models\User;
use App\Support\PublicPetCard;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PublicPetIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_hides_private_fields_until_the_owner_shares_them(): void
    {
        [$owner, $pet] = $this->privatePet();

        $hidden = $this->get(route('pets.public', $pet->public_token));

        $hidden->assertOk();
        $this->assertStringNotContainsString('/owner/pets/'.$pet->id, $hidden->getContent());
        $this->assertNoPrivatePayload($hidden);
        foreach ($this->secrets() as $secret) {
            $hidden->assertDontSee($secret);
        }
        $hidden->assertDontSee('NAME-PIPPIN-QUILL');
        $hidden->assertDontSee('SPECIES-FOX');
        $hidden->assertDontSee('BREED-ARCTIC');
        $hidden->assertDontSee('ALLERGY-CASHEW');
        $hidden->assertDontSee('MEDICATION-APOQUEL');
        $hidden->assertDontSee('5550199');
        $hidden->assertDontSee('SECRET-PHOTO-FILE');
        $hidden->assertDontSee('SECRET-LAST-SEEN');
        $hidden->assertDontSee('I found this pet');

        $this->actingAs($owner)->patch(route('owner.pets.public-id.update', $pet), [
            'fields_present' => '1',
            'fields' => ['name', 'species', 'allergies', 'phone'],
            'is_lost' => '1',
            'lost_note' => 'SECRET-LAST-SEEN',
        ])->assertRedirect();

        $shared = $this->get(route('pets.public', $pet->public_token));

        $shared->assertOk();
        $shared->assertSee('NAME-PIPPIN-QUILL');
        $shared->assertSee('SPECIES-FOX');
        $shared->assertSee('ALLERGY-CASHEW');
        $shared->assertSee('tel:+923005550199', false);
        $shared->assertSee('This pet is lost');
        $shared->assertSee('SECRET-LAST-SEEN');
        $shared->assertSee('I found this pet');
        $shared->assertDontSee('BREED-ARCTIC');
        $shared->assertDontSee('MEDICATION-APOQUEL');
        $shared->assertDontSee('wa.me/923005550199', false);
        foreach ($this->secrets() as $secret) {
            $shared->assertDontSee($secret);
        }
        $this->assertNoPrivatePayload($shared);
    }

    public function test_public_page_still_hides_records_when_every_tag_field_is_on(): void
    {
        [$owner, $pet] = $this->privatePet();

        $this->actingAs($owner)->patch(route('owner.pets.public-id.update', $pet), [
            'fields' => array_keys(\App\Services\PetIdService::FIELDS),
        ])->assertRedirect();

        $page = $this->get(route('pets.public', $pet->fresh()->public_token));

        $page->assertOk();
        $page->assertSee('NAME-PIPPIN-QUILL');
        $page->assertSee('BREED-ARCTIC');
        $page->assertSee('ALLERGY-CASHEW');
        $page->assertSee('MEDICATION-APOQUEL');
        $page->assertSee('https://wa.me/923005550199', false);
        $page->assertSee('SECRET-PHOTO-FILE.jpg', false);
        foreach ($this->secrets() as $secret) {
            $page->assertDontSee($secret);
        }
        $page->assertDontSee('SECRET-LAST-SEEN');
        $this->assertNoPrivatePayload($page);
    }

    public function test_a_found_report_notifies_the_owner_and_stays_off_the_public_page(): void
    {
        [$owner, $pet] = $this->privatePet();
        $pet->forceFill([
            'is_lost' => true,
            'lost_note' => 'Near the river gate',
            'public_fields' => ['name' => true],
        ])->save();

        $this->post(route('pets.public.found', $pet->public_token), [
            'finder_name' => 'FINDER-NOOR',
            'message' => 'FOUND-NEAR-THE-GATE',
            'latitude' => '24.5',
            'longitude' => '67.25',
        ])->assertRedirect(route('pets.public', $pet->public_token));

        $sighting = PetSighting::query()->first();
        $this->assertNotNull($sighting);
        $this->assertSame(24.5, $sighting->latitude);
        $this->assertSame(67.25, $sighting->longitude);

        $notification = AppNotification::query()->where('user_id', $owner->id)->first();
        $this->assertNotNull($notification);
        $this->assertSame('NAME-PIPPIN-QUILL may have been found', $notification->title);
        $this->assertStringContainsString('FOUND-NEAR-THE-GATE', $notification->message);
        $this->assertStringContainsString('FINDER-NOOR', $notification->message);
        $this->assertStringContainsString('Location: 24.500000, 67.250000', $notification->message);
        $this->assertStringNotContainsString('SECRET-MEDICAL-HISTORY', $notification->message);
        $this->assertSame(route('owner.pets.show', $pet), $notification->link);

        $this->get(route('pets.public', $pet->public_token))
            ->assertDontSee('FOUND-NEAR-THE-GATE')
            ->assertDontSee('FINDER-NOOR')
            ->assertDontSee('SECRET-MEDICAL-HISTORY');
    }

    public function test_a_found_report_is_rejected_when_the_pet_is_not_lost(): void
    {
        [, $pet] = $this->privatePet();

        $this->post(route('pets.public.found', $pet->public_token), [
            'message' => 'FOUND-NEAR-THE-GATE',
        ])->assertNotFound();

        $this->assertSame(0, PetSighting::query()->count());
        $this->assertSame(0, AppNotification::query()->count());
    }

    public function test_replacing_the_tag_retires_the_old_link(): void
    {
        [$owner, $pet] = $this->privatePet();
        $old = $pet->public_token;

        $this->actingAs($owner)
            ->post(route('owner.pets.public-token.regenerate', $pet))
            ->assertRedirect();

        $pet->refresh();
        $this->assertNotSame($old, $pet->public_token);
        $this->assertSame(40, strlen($pet->public_token));

        $this->get('/p/'.$old)->assertNotFound();
        $this->get(route('pets.public', $pet->public_token))
            ->assertOk()
            ->assertDontSee('SECRET-MEDICAL-HISTORY');

        $tag = $this->actingAs($owner)->get(route('owner.pets.tag', $pet));
        $tag->assertOk();
        $this->assertStringContainsString('image/svg+xml', (string) $tag->headers->get('content-type'));
        $this->assertStringContainsString($pet->public_token, $tag->getContent());
        $this->assertStringNotContainsString('SECRET-MEDICAL-HISTORY', $tag->getContent());
        $this->assertStringNotContainsString($old, $tag->getContent());
    }

    public function test_another_owner_cannot_change_the_tag(): void
    {
        [, $pet] = $this->privatePet();
        $other = User::factory()->create();
        $token = $pet->public_token;

        $this->actingAs($other)
            ->post(route('owner.pets.public-token.regenerate', $pet))
            ->assertForbidden();

        $this->assertSame($token, $pet->fresh()->public_token);
    }

    public function test_public_routes_are_rate_limited(): void
    {
        [, $pet] = $this->privatePet();
        $pet->forceFill(['is_lost' => true])->save();

        RateLimiter::for('pet-public', fn (Request $request) => Limit::perMinute(2)->by($request->ip()));
        RateLimiter::for('pet-found', fn (Request $request) => Limit::perMinute(2)->by($request->ip()));

        $this->get(route('pets.public', $pet->public_token))->assertOk();
        $this->get(route('pets.public', $pet->public_token))->assertOk();
        $this->get(route('pets.public', $pet->public_token))->assertStatus(429);

        $this->post(route('pets.public.found', $pet->public_token), [
            'message' => 'First sighting',
        ])->assertRedirect();
        $this->post(route('pets.public.found', $pet->public_token), [
            'message' => 'Second sighting',
        ])->assertRedirect();
        $this->post(route('pets.public.found', $pet->public_token), [
            'message' => 'Third sighting',
        ])->assertStatus(429);
    }

    /**
     * @return array{0: User, 1: Pet}
     */
    private function privatePet(): array
    {
        $owner = User::factory()->create([
            'name' => 'SECRET-OWNER-NAME',
            'email' => 'secret-owner@hidden.test',
            'phone' => '+92-300-5550199',
            'address' => 'SECRET-ADDRESS-LANE',
            'city' => 'SECRET-CITY-QUAY',
        ]);
        $vet = User::factory()->create([
            'role' => 'vet',
            'name' => 'SECRET-VET-NAME',
            'email' => 'secret-vet@hidden.test',
            'phone' => '+92-300-5550100',
            'address' => 'SECRET-VET-ADDRESS',
        ]);

        $pet = Pet::query()->create([
            'owner_id' => $owner->id,
            'name' => 'NAME-PIPPIN-QUILL',
            'species' => 'SPECIES-FOX',
            'breed' => 'BREED-ARCTIC',
            'age' => 7,
            'gender' => 'SECRET-GENDER-NEUTER',
            'medical_history' => 'SECRET-MEDICAL-HISTORY',
            'photo_path' => 'pets/SECRET-PHOTO-FILE.jpg',
            'gallery' => ['pets/SECRET-GALLERY-FRAME.jpg'],
            'is_lost' => false,
            'lost_note' => 'SECRET-LAST-SEEN',
        ]);

        HealthRecord::query()->create([
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'visit_date' => '2026-01-01',
            'record_type' => 'illness',
            'title' => 'SECRET-RECORD-TITLE',
            'diagnosis' => 'SECRET-DIAGNOSIS',
            'treatment' => 'SECRET-TREATMENT',
            'symptoms' => 'SECRET-SYMPTOMS',
            'medications' => 'MEDICATION-APOQUEL',
            'lab_results' => 'SECRET-LAB-PANEL',
            'allergies' => 'ALLERGY-CASHEW',
            'notes' => 'SECRET-CLINICAL-NOTES',
            'document_path' => 'records/SECRET-DOCUMENT.pdf',
        ]);

        InsurancePolicy::query()->create([
            'pet_id' => $pet->id,
            'provider_name' => 'SECRET-INSURER',
            'policy_number' => 'SECRET-POLICY-NUMBER',
            'claim_notes' => 'SECRET-CLAIM-NOTES',
        ]);

        Appointment::query()->create([
            'pet_id' => $pet->id,
            'owner_id' => $owner->id,
            'vet_id' => $vet->id,
            'appointment_time' => '2026-11-12 10:30:00',
            'status' => 'pending',
            'reason' => 'SECRET-VISIT-REASON',
            'owner_notes' => 'SECRET-OWNER-NOTES',
            'vet_notes' => 'SECRET-VET-NOTES',
        ]);

        return [$owner, $pet];
    }

    /**
     * @return list<string>
     */
    private function secrets(): array
    {
        return [
            'SECRET-OWNER-NAME',
            'secret-owner@hidden.test',
            'SECRET-ADDRESS-LANE',
            'SECRET-CITY-QUAY',
            'SECRET-VET-NAME',
            'secret-vet@hidden.test',
            '5550100',
            'SECRET-VET-ADDRESS',
            'SECRET-GENDER-NEUTER',
            'SECRET-MEDICAL-HISTORY',
            'SECRET-GALLERY-FRAME',
            'SECRET-RECORD-TITLE',
            'SECRET-DIAGNOSIS',
            'SECRET-TREATMENT',
            'SECRET-SYMPTOMS',
            'SECRET-LAB-PANEL',
            'SECRET-CLINICAL-NOTES',
            'SECRET-DOCUMENT.pdf',
            'SECRET-INSURER',
            'SECRET-POLICY-NUMBER',
            'SECRET-CLAIM-NOTES',
            'SECRET-VISIT-REASON',
            'SECRET-OWNER-NOTES',
            'SECRET-VET-NOTES',
        ];
    }

    private function assertNoPrivatePayload(\Illuminate\Testing\TestResponse $response): void
    {
        foreach ($response->viewData() as $value) {
            $this->assertNotInstanceOf(Pet::class, $value);
            $this->assertNotInstanceOf(User::class, $value);
            $this->assertNotInstanceOf(HealthRecord::class, $value);
            $this->assertNotInstanceOf(InsurancePolicy::class, $value);
            $this->assertNotInstanceOf(Appointment::class, $value);
            $this->assertNotInstanceOf(PetSighting::class, $value);
        }

        $this->assertInstanceOf(PublicPetCard::class, $response->viewData('card'));
    }
}
