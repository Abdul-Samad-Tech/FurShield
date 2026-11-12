<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\AdoptionInterest;
use App\Models\AdoptionListing;
use App\Models\Appointment;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_dashboard_names_the_next_due_care(): void
    {
        $this->travelTo('2026-11-12');
        $owner = User::factory()->create();
        $pet = Pet::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Milo',
            'species' => 'Dog',
        ]);
        HealthRecord::query()->create([
            'pet_id' => $pet->id,
            'visit_date' => '2025-11-17',
            'record_type' => 'vaccination',
            'title' => 'Rabies shot',
            'next_due_date' => '2026-11-17',
            'recurrence_days' => 365,
        ]);

        $response = $this->actingAs($owner)->get(route('owner.dashboard'));

        $response->assertOk();
        $response->assertSee('Next best action', false);
        $response->assertSee("Milo's Rabies shot is due in 5 days");
        $response->assertSee('Book now', false);
        $response->assertSee('Care radar', false);
        $response->assertSee('data-count="1"', false);
        $response->assertSee('Emergency', false);
    }

    public function test_owner_without_pets_gets_an_empty_dashboard(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Add your first pet', false)
            ->assertSee('No pets yet', false)
            ->assertSee('No visits booked', false);
    }

    public function test_vet_can_approve_or_decline_from_the_dashboard(): void
    {
        $this->travelTo('2026-11-12 09:00:00');
        $owner = User::factory()->create();
        $vet = User::factory()->create(['role' => UserRole::Vet]);
        $pet = Pet::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Luna',
            'species' => 'Cat',
        ]);
        $appointment = Appointment::query()->create([
            'pet_id' => $pet->id,
            'owner_id' => $owner->id,
            'vet_id' => $vet->id,
            'appointment_time' => '2026-11-12 15:00:00',
            'status' => AppointmentStatus::Pending,
            'reason' => 'Checkup',
        ]);

        $this->actingAs($vet)
            ->get(route('vet.dashboard'))
            ->assertOk()
            ->assertSee('Next patient', false)
            ->assertSee('Approve', false)
            ->assertSee('Decline', false)
            ->assertSee('aria-label="Approve Luna"', false);

        $this->actingAs($vet)
            ->patch(route('vet.appointments.update', $appointment), ['status' => 'approved'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(AppointmentStatus::Approved, $appointment->fresh()->status);
    }

    public function test_shelter_dashboard_shows_the_interest_funnel(): void
    {
        $shelter = User::factory()->create(['role' => UserRole::Shelter, 'shelter_name' => 'Harbor Paws']);
        $owner = User::factory()->create();
        $listing = AdoptionListing::query()->create([
            'shelter_id' => $shelter->id,
            'pet_name' => 'Nori',
            'species' => 'Cat',
            'status' => 'available',
        ]);
        AdoptionInterest::query()->create([
            'listing_id' => $listing->id,
            'owner_id' => $owner->id,
            'message' => 'I can visit this weekend.',
            'status' => 'pending',
        ]);

        $this->actingAs($shelter)
            ->get(route('shelter.dashboard'))
            ->assertOk()
            ->assertSee('Interest funnel', false)
            ->assertSee('Interested', false)
            ->assertSee('Nori', false)
            ->assertSee('data-count="1"', false);
    }
}
