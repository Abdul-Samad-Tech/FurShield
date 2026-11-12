<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use App\Services\TriageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TriageTest extends TestCase
{
    use RefreshDatabase;

    public function test_difficulty_breathing_is_always_an_emergency(): void
    {
        $triage = app(TriageService::class);

        foreach (array_keys(config('triage.species')) as $species) {
            $result = $triage->assess($species, ['difficulty_breathing', 'itching']);

            $this->assertSame('emergency', $result['level'], $species);
            $this->assertSame('Emergency now', $result['title'], $species);
            $this->assertSame('breathing', $result['rule'], $species);
        }
    }

    public function test_critical_symptoms_outrank_a_mild_one(): void
    {
        $triage = app(TriageService::class);

        foreach (['seizure', 'poisoning', 'bleeding', 'collapse', 'pale_gums'] as $symptom) {
            $result = $triage->assess('dog', [$symptom, 'itching']);

            $this->assertSame('emergency', $result['level'], $symptom);
            $this->assertSame($symptom, $result['rule'], $symptom);
        }
    }

    public function test_species_changes_the_result_for_the_same_symptom(): void
    {
        $triage = app(TriageService::class);

        $this->assertSame('emergency', $triage->assess('cat', ['straining_to_urinate'])['level']);
        $this->assertSame('soon', $triage->assess('dog', ['straining_to_urinate'])['level']);
        $this->assertSame('emergency', $triage->assess('rabbit', ['not_eating'])['level']);
        $this->assertSame('soon', $triage->assess('cat', ['not_eating'])['level']);
        $this->assertSame('soon', $triage->assess('bird', ['not_eating'])['level']);
        $this->assertSame('monitor', $triage->assess('dog', ['not_eating'])['level']);
        $this->assertSame('soon', $triage->assess('dog', ['limping'])['level']);
        $this->assertSame('See a vet within 24 hours', $triage->assess('dog', ['limping'])['title']);
        $this->assertSame('monitor', $triage->assess('dog', ['itching'])['level']);
        $this->assertSame('Monitor at home', $triage->assess('dog', ['itching'])['title']);
    }

    public function test_unknown_input_is_rejected(): void
    {
        $triage = app(TriageService::class);

        try {
            $triage->assess('dragon', ['itching']);
            $this->fail('An unknown species was accepted.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $this->expectException(InvalidArgumentException::class);
        $triage->assess('dog', []);
    }

    public function test_the_public_check_shows_an_emergency_panel_and_the_nearest_vet(): void
    {
        config(['furshield.emergency_phone' => '+92-300-4455667']);
        $near = User::factory()->create([
            'name' => 'Dr. Near',
            'role' => UserRole::Vet,
            'city' => 'Karachi',
            'latitude' => 24.8138,
            'longitude' => 67.0299,
            'phone' => '+92-300-2200114',
        ]);
        User::factory()->create([
            'name' => 'Dr. Far',
            'role' => UserRole::Vet,
            'city' => 'Lahore',
            'latitude' => 31.5204,
            'longitude' => 74.3587,
            'phone' => '+92-300-4455667',
        ]);

        $this->get(route('triage'))
            ->assertOk()
            ->assertSee('Symptom check')
            ->assertSee('This is not a diagnosis.')
            ->assertSee('Difficulty breathing')
            ->assertSee('Dog');

        $this->followingRedirects()
            ->post(route('triage.store'), [
                'species' => 'dog',
                'symptoms' => ['difficulty_breathing', 'itching'],
                'latitude' => 24.8138,
                'longitude' => 67.0299,
            ])
            ->assertOk()
            ->assertSee('Emergency now')
            ->assertSee('Difficulty breathing is always an emergency, for every species.')
            ->assertSee('This is not a diagnosis.')
            ->assertSee('tel:+923004455667', false)
            ->assertSee('bg-danger', false)
            ->assertSee('data-nearby', false)
            ->assertSeeInOrder(['Dr. Near', 'Dr. Far'])
            ->assertSee('Save summary to the health timeline')
            ->assertSee(route('vets.show', $near), false);
    }

    public function test_mild_symptoms_do_not_show_the_emergency_panel(): void
    {
        config(['furshield.emergency_phone' => null]);

        $this->followingRedirects()
            ->post(route('triage.store'), [
                'species' => 'dog',
                'symptoms' => ['itching'],
            ])
            ->assertOk()
            ->assertSee('Monitor at home')
            ->assertDontSee('Emergency now')
            ->assertDontSee('Nearest vets')
            ->assertDontSee('tel:', false);

        $this->followingRedirects()
            ->post(route('triage.store'), [
                'species' => 'dog',
                'symptoms' => ['limping'],
            ])
            ->assertOk()
            ->assertSee('See a vet within 24 hours')
            ->assertDontSee('Emergency now');
    }

    public function test_a_result_requires_a_real_check_and_known_symptoms(): void
    {
        $this->get(route('triage.result'))
            ->assertRedirect(route('triage'))
            ->assertSessionHasErrors('triage');

        $this->from(route('triage'))
            ->post(route('triage.store'), [
                'species' => 'dog',
                'symptoms' => ['not-a-symptom'],
            ])
            ->assertRedirect(route('triage'))
            ->assertSessionHasErrors('symptoms.0');
    }

    public function test_an_owner_can_save_the_summary_without_storing_a_diagnosis(): void
    {
        $owner = User::factory()->create();
        $pet = Pet::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Milo',
            'species' => 'Dog',
        ]);

        $this->actingAs($owner)
            ->post(route('triage.save'), [
                'pet_id' => $pet->id,
                'species' => 'dog',
                'symptoms' => ['difficulty_breathing'],
            ])
            ->assertRedirect(route('owner.pets.health', $pet))
            ->assertSessionHas('success');

        $record = HealthRecord::query()->firstOrFail();
        $this->assertSame('illness', $record->record_type);
        $this->assertSame('Symptom check', $record->title);
        $this->assertSame('Difficulty breathing', $record->symptoms);
        $this->assertNull($record->diagnosis);
        $this->assertNull($record->treatment);
        $this->assertNull($record->next_due_date);
        $this->assertStringContainsString('Emergency now', $record->notes);
        $this->assertStringContainsString('not a diagnosis', strtolower($record->notes));
    }

    public function test_a_mild_check_is_not_saved_as_an_emergency(): void
    {
        $owner = User::factory()->create();
        $pet = Pet::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Luna',
            'species' => 'Cat',
        ]);
        $other = Pet::query()->create([
            'owner_id' => User::factory()->create()->id,
            'name' => 'Other',
            'species' => 'Dog',
        ]);

        $this->actingAs($owner)
            ->post(route('triage.save'), [
                'pet_id' => $pet->id,
                'species' => 'cat',
                'symptoms' => ['itching'],
            ])
            ->assertRedirect(route('owner.pets.health', $pet));

        $this->assertStringContainsString('Monitor at home', HealthRecord::query()->firstOrFail()->notes);
        $this->assertStringNotContainsString('Emergency now', HealthRecord::query()->firstOrFail()->notes);

        $this->actingAs($owner)
            ->post(route('triage.save'), [
                'pet_id' => $other->id,
                'species' => 'dog',
                'symptoms' => ['itching'],
            ])
            ->assertNotFound();
    }

    public function test_guests_and_vets_cannot_save_a_summary(): void
    {
        $this->post(route('triage.save'), [
            'species' => 'dog',
            'symptoms' => ['seizure'],
        ])->assertRedirect(route('login'));

        $vet = User::factory()->create(['role' => UserRole::Vet]);

        $this->actingAs($vet)
            ->post(route('triage.save'), [
                'species' => 'dog',
                'symptoms' => ['seizure'],
            ])
            ->assertRedirect(route('vet.dashboard'));
    }
}
