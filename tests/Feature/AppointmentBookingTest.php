<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_owners_cannot_book_the_same_open_slot(): void
    {
        $this->travelTo('2026-11-12 08:00:00');
        $vet = $this->thursdayVet();
        [$ownerA, $petA] = $this->ownerWithPet('Milo');
        [$ownerB, $petB] = $this->ownerWithPet('Luna');

        $this->actingAs($ownerA)
            ->post(route('owner.appointments.store'), $this->booking($petA, $vet, '2026-11-12T09:00'))
            ->assertRedirect(route('owner.appointments.index'));

        $this->actingAs($ownerB)
            ->from(route('owner.appointments.create'))
            ->post(route('owner.appointments.store'), $this->booking($petB, $vet, '2026-11-12T09:00'))
            ->assertRedirect(route('owner.appointments.create'))
            ->assertSessionHasErrors('appointment_time');

        $this->assertSame(1, Appointment::query()->count());

        $this->actingAs($ownerB)
            ->post(route('owner.appointments.store'), $this->booking($petB, $vet, '2026-11-12T09:30'))
            ->assertRedirect(route('owner.appointments.index'));

        $this->assertSame(2, Appointment::query()->count());
    }

    public function test_booking_rejects_past_and_off_grid_times(): void
    {
        $this->travelTo('2026-11-12 10:00:00');
        $vet = $this->thursdayVet();
        [$owner, $pet] = $this->ownerWithPet('Milo');

        foreach (['2026-11-12T09:00', '2026-11-12T10:00', '2026-11-12T09:15', '2026-11-12T15:00'] as $time) {
            $this->actingAs($owner)
                ->from(route('owner.appointments.create'))
                ->post(route('owner.appointments.store'), $this->booking($pet, $vet, $time))
                ->assertSessionHasErrors('appointment_time');
        }

        $this->assertSame(0, Appointment::query()->count());

        $this->actingAs($owner)
            ->post(route('owner.appointments.store'), $this->booking($pet, $vet, '2026-11-12T10:30'))
            ->assertRedirect(route('owner.appointments.index'));
    }

    public function test_the_booking_page_offers_only_free_slots(): void
    {
        $this->travelTo('2026-11-12 08:00:00');
        $vet = $this->thursdayVet();
        [$owner, $pet] = $this->ownerWithPet('Milo');

        $this->actingAs($owner)
            ->get(route('owner.appointments.create', ['pet_id' => $pet->id, 'reason' => 'Rabies shot']))
            ->assertOk()
            ->assertSee('Earliest open time', false)
            ->assertSee('2026-11-12T09:00', false)
            ->assertSee('9:00 AM', false)
            ->assertSee('Rabies shot', false)
            ->assertSee('"step":2', false);

        $this->actingAs($owner)
            ->post(route('owner.appointments.store'), $this->booking($pet, $vet, '2026-11-12T09:00'))
            ->assertRedirect(route('owner.appointments.index'));

        $this->actingAs($owner)
            ->get(route('owner.appointments.create'))
            ->assertOk()
            ->assertDontSee('2026-11-12T09:00', false)
            ->assertSee('2026-11-12T09:30', false);
    }

    public function test_pending_and_approved_visits_can_be_cancelled_and_the_other_party_is_told(): void
    {
        $this->travelTo('2026-11-12 08:00:00');
        $vet = $this->thursdayVet();
        [$owner, $pet] = $this->ownerWithPet('Milo');
        [$stranger] = $this->ownerWithPet('Nori');
        $pending = $this->booked($owner, $pet, $vet, '2026-11-12T09:00');
        $approved = $this->booked($owner, $pet, $vet, '2026-11-12T09:30');
        $approved->update(['status' => AppointmentStatus::Approved]);

        $this->actingAs($stranger)
            ->patch(route('owner.appointments.cancel', $pending))
            ->assertForbidden();

        $this->actingAs($owner)
            ->patch(route('owner.appointments.cancel', $pending))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($owner)
            ->patch(route('owner.appointments.cancel', $approved))
            ->assertRedirect();

        $this->assertSame(AppointmentStatus::Cancelled, $pending->fresh()->status);
        $this->assertSame(AppointmentStatus::Cancelled, $approved->fresh()->status);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $vet->id,
            'title' => 'Visit cancelled',
        ]);
        $this->assertDatabaseMissing('app_notifications', [
            'user_id' => $owner->id,
            'title' => 'Visit cancelled',
        ]);
    }

    public function test_closed_visits_cannot_be_cancelled_or_rescheduled(): void
    {
        $this->travelTo('2026-11-12 08:00:00');
        $vet = $this->thursdayVet();
        [$owner, $pet] = $this->ownerWithPet('Milo');

        foreach ([AppointmentStatus::Completed, AppointmentStatus::Cancelled, AppointmentStatus::Rescheduled] as $status) {
            $appointment = Appointment::query()->create([
                'pet_id' => $pet->id,
                'owner_id' => $owner->id,
                'vet_id' => $vet->id,
                'appointment_time' => '2026-11-12 09:00:00',
                'status' => $status,
            ]);

            $this->actingAs($owner)
                ->from(route('owner.appointments.index'))
                ->patch(route('owner.appointments.cancel', $appointment))
                ->assertSessionHasErrors('appointment');

            $this->actingAs($owner)
                ->from(route('owner.appointments.index'))
                ->patch(route('owner.appointments.reschedule', $appointment), [
                    'appointment_time' => '2026-11-12T10:00',
                ])
                ->assertSessionHasErrors('appointment');

            $fresh = $appointment->fresh();
            $this->assertSame($status, $fresh->status);
            $this->assertSame('2026-11-12 09:00:00', $fresh->appointment_time->format('Y-m-d H:i:s'));
        }
    }

    public function test_a_pending_visit_moves_onto_a_free_slot_and_not_a_taken_one(): void
    {
        $this->travelTo('2026-11-12 08:00:00');
        $vet = $this->thursdayVet();
        [$owner, $pet] = $this->ownerWithPet('Milo');
        [$other, $otherPet] = $this->ownerWithPet('Luna');
        $visit = $this->booked($owner, $pet, $vet, '2026-11-12T09:00');
        $this->booked($other, $otherPet, $vet, '2026-11-12T09:30');

        $this->actingAs($owner)
            ->from(route('owner.appointments.create', ['reschedule' => $visit->id]))
            ->patch(route('owner.appointments.reschedule', $visit), [
                'appointment_time' => '2026-11-12T09:30',
            ])
            ->assertSessionHasErrors('appointment_time');

        $this->assertSame('2026-11-12 09:00:00', $visit->fresh()->appointment_time->format('Y-m-d H:i:s'));

        $this->actingAs($owner)
            ->patch(route('owner.appointments.reschedule', $visit), [
                'appointment_time' => '2026-11-12T10:00',
            ])
            ->assertRedirect(route('owner.appointments.index'));

        $moved = $visit->fresh();
        $this->assertSame(AppointmentStatus::Pending, $moved->status);
        $this->assertSame('2026-11-12 10:00:00', $moved->appointment_time->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $vet->id,
            'title' => 'Visit rescheduled',
        ]);
        $this->assertDatabaseMissing('app_notifications', [
            'user_id' => $owner->id,
            'title' => 'Visit rescheduled',
        ]);
    }

    public function test_cancelling_a_visit_opens_that_time_again(): void
    {
        $this->travelTo('2026-11-12 08:00:00');
        $vet = $this->thursdayVet();
        [$owner, $pet] = $this->ownerWithPet('Milo');
        [$other, $otherPet] = $this->ownerWithPet('Luna');
        $visit = $this->booked($owner, $pet, $vet, '2026-11-12T09:00');

        $this->actingAs($owner)->patch(route('owner.appointments.cancel', $visit));

        $this->actingAs($other)
            ->post(route('owner.appointments.store'), $this->booking($otherPet, $vet, '2026-11-12T09:00'))
            ->assertRedirect(route('owner.appointments.index'));
    }

    public function test_legacy_weekday_strings_still_open_bookable_slots(): void
    {
        $this->travelTo('2026-11-12 08:00:00');
        $vet = User::factory()->create([
            'role' => UserRole::Vet,
            'available_slots' => ['Thu 10:00'],
        ]);
        [$owner, $pet] = $this->ownerWithPet('Milo');

        $this->actingAs($owner)
            ->post(route('owner.appointments.store'), $this->booking($pet, $vet, '2026-11-12T10:00'))
            ->assertRedirect(route('owner.appointments.index'));

        $this->actingAs($owner)
            ->post(route('owner.appointments.store'), $this->booking($pet, $vet, '2026-11-12T10:30'))
            ->assertRedirect(route('owner.appointments.index'));

        $this->actingAs($owner)
            ->from(route('owner.appointments.create'))
            ->post(route('owner.appointments.store'), $this->booking($pet, $vet, '2026-11-12T11:00'))
            ->assertSessionHasErrors('appointment_time');
    }

    public function test_a_vet_saves_structured_hours_and_rejects_an_inverted_window(): void
    {
        $vet = User::factory()->create([
            'role' => UserRole::Vet,
            'available_slots' => ['Thu 10:00'],
        ]);

        $this->actingAs($vet)
            ->get(route('vet.profile'))
            ->assertOk()
            ->assertSee('Open hours', false)
            ->assertSee('Add window', false)
            ->assertSee('10:00', false);

        $this->actingAs($vet)
            ->patch(route('vet.profile.update'), [
                'windows_present' => '1',
                'specialization' => 'Dermatology',
                'windows' => [[
                    'weekday' => 4,
                    'start' => '09:00',
                    'end' => '11:00',
                    'slot_minutes' => 30,
                ]],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame([[
            'weekday' => 4,
            'start' => '09:00',
            'end' => '11:00',
            'slot_minutes' => 30,
        ]], $vet->fresh()->available_slots);

        $this->actingAs($vet)
            ->from(route('vet.profile'))
            ->patch(route('vet.profile.update'), [
                'windows_present' => '1',
                'windows' => [[
                    'weekday' => 4,
                    'start' => '11:00',
                    'end' => '09:00',
                    'slot_minutes' => 30,
                ]],
            ])
            ->assertSessionHasErrors('windows.0.end');

        $this->assertSame('09:00', $vet->fresh()->available_slots[0]['start']);

        $this->get(route('vets.show', $vet))
            ->assertOk()
            ->assertSee('Thursday 09:00', false);
    }

    public function test_a_vet_completes_a_visit_and_adds_the_treatment_together(): void
    {
        $this->travelTo('2026-11-12 08:00:00');
        $vet = $this->thursdayVet();
        [$owner, $pet] = $this->ownerWithPet('Milo');
        $visit = $this->booked($owner, $pet, $vet, '2026-11-12T09:00');
        $cancelled = $this->booked($owner, $pet, $vet, '2026-11-12T09:30');
        $cancelled->update(['status' => AppointmentStatus::Cancelled]);

        $this->actingAs($vet)
            ->post(route('vet.appointments.treatment', $visit), [
                'title' => 'Allergy shot',
                'diagnosis' => 'Pollen',
                'notes' => 'Recheck in a month.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(AppointmentStatus::Completed, $visit->fresh()->status);
        $this->assertSame('Recheck in a month.', $visit->fresh()->vet_notes);
        $this->assertDatabaseHas('health_records', [
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'title' => 'Allergy shot',
            'record_type' => 'treatment',
            'diagnosis' => 'Pollen',
        ]);
        $this->assertNull(HealthRecord::query()->first()->next_due_date);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $owner->id,
            'title' => 'Visit completed',
        ]);

        $this->actingAs($vet)
            ->from(route('vet.appointments'))
            ->post(route('vet.appointments.treatment', $visit), ['title' => 'Again'])
            ->assertSessionHasErrors('appointment');

        $this->actingAs($vet)
            ->from(route('vet.appointments'))
            ->post(route('vet.appointments.treatment', $cancelled), ['title' => 'Too late'])
            ->assertSessionHasErrors('appointment');

        $this->assertSame(1, HealthRecord::query()->count());
    }

    public function test_either_party_can_download_the_calendar_file(): void
    {
        $this->travelTo('2026-11-12 08:00:00');
        $vet = $this->thursdayVet();
        [$owner, $pet] = $this->ownerWithPet('Milo');
        [$stranger] = $this->ownerWithPet('Nori');
        $visit = $this->booked($owner, $pet, $vet, '2026-11-12T09:00');

        $this->actingAs($owner)
            ->get(route('owner.appointments.ics', $visit))
            ->assertOk()
            ->assertHeader('content-type', 'text/calendar; charset=utf-8')
            ->assertHeader('content-disposition', 'attachment; filename="furshield-visit.ics"')
            ->assertSee('BEGIN:VCALENDAR', false)
            ->assertSee('UID:appointment-'.$visit->id.'@furshield', false)
            ->assertSee('DTSTART:20261112T090000Z', false)
            ->assertSee('DTEND:20261112T093000Z', false);

        $this->actingAs($vet)
            ->get(route('vet.appointments.ics', $visit))
            ->assertOk()
            ->assertSee('BEGIN:VCALENDAR', false);

        $this->actingAs($stranger)
            ->get(route('owner.appointments.ics', $visit))
            ->assertForbidden();
    }

    public function test_the_vet_schedule_splits_today_upcoming_and_history(): void
    {
        $this->travelTo('2026-11-12 08:00:00');
        $vet = $this->thursdayVet();
        [$owner, $pet] = $this->ownerWithPet('Milo');
        $today = $this->booked($owner, $pet, $vet, '2026-11-12T09:00');
        $this->booked($owner, $pet, $vet, '2026-11-19T09:00');
        Appointment::query()->create([
            'pet_id' => $pet->id,
            'owner_id' => $owner->id,
            'vet_id' => $vet->id,
            'appointment_time' => '2026-11-05 09:00:00',
            'status' => AppointmentStatus::Completed,
            'reason' => 'Old checkup',
        ]);

        $this->actingAs($vet)
            ->get(route('vet.appointments'))
            ->assertOk()
            ->assertSee('Today', false)
            ->assertSee('Upcoming', false)
            ->assertSee('History', false)
            ->assertSee('aria-label="Approve '.$pet->name.'"', false)
            ->assertSee('Decline', false)
            ->assertSee('Complete and add treatment', false)
            ->assertSee('Old checkup', false);

        $this->actingAs($vet)
            ->patch(route('vet.appointments.update', $today), ['status' => 'cancelled'])
            ->assertRedirect()
            ->assertSessionHas('success', 'Appointment updated.');

        $this->assertSame(AppointmentStatus::Cancelled, $today->fresh()->status);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $owner->id,
            'title' => 'Visit cancelled',
        ]);
    }

    private function thursdayVet(): User
    {
        return User::factory()->create([
            'role' => UserRole::Vet,
            'available_slots' => [[
                'weekday' => 4,
                'start' => '09:00',
                'end' => '11:00',
                'slot_minutes' => 30,
            ]],
        ]);
    }

    /**
     * @return array{0: User, 1: Pet}
     */
    private function ownerWithPet(string $name): array
    {
        $owner = User::factory()->create();
        $pet = Pet::query()->create([
            'owner_id' => $owner->id,
            'name' => $name,
            'species' => 'Dog',
        ]);

        return [$owner, $pet];
    }

    /**
     * @return array{pet_id: int, vet_id: int, appointment_time: string, reason: string}
     */
    private function booking(Pet $pet, User $vet, string $time): array
    {
        return [
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'appointment_time' => $time,
            'reason' => 'Checkup',
        ];
    }

    private function booked(User $owner, Pet $pet, User $vet, string $time): Appointment
    {
        $this->actingAs($owner)
            ->post(route('owner.appointments.store'), $this->booking($pet, $vet, $time))
            ->assertRedirect(route('owner.appointments.index'));

        return Appointment::query()->where('pet_id', $pet->id)->where('appointment_time', str_replace('T', ' ', $time).':00')->firstOrFail();
    }
}
