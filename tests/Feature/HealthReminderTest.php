<?php

namespace Tests\Feature;

use App\Enums\CareStatus;
use App\Models\AppNotification;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schedule;
use Tests\TestCase;

class HealthReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_notifies_each_window_once(): void
    {
        $owner = User::factory()->create();
        $pet = $this->pet($owner, 'Milo');
        $this->record($pet, '2026-11-19');

        $this->travelTo('2026-11-12');
        $this->artisan('furshield:send-reminders')->assertSuccessful();
        $this->artisan('furshield:send-reminders')->assertSuccessful();

        $this->assertSame(1, AppNotification::query()->count());
        $this->assertSame('Milo is due for care in 7 days', AppNotification::query()->value('title'));

        $this->travelTo('2026-11-14');
        $this->artisan('furshield:send-reminders')->assertSuccessful();
        $this->assertSame(1, AppNotification::query()->count());

        $this->travelTo('2026-11-18');
        $this->artisan('furshield:send-reminders')->assertSuccessful();
        $this->artisan('furshield:send-reminders')->assertSuccessful();

        $this->assertSame(2, AppNotification::query()->count());
        $this->assertTrue(AppNotification::query()->where('title', 'Milo is due for care tomorrow')->exists());

        $this->travelTo('2026-11-19');
        $this->artisan('furshield:send-reminders')->assertSuccessful();
        $this->assertSame(2, AppNotification::query()->count());

        $this->travelTo('2026-11-20');
        $this->artisan('furshield:send-reminders')->assertSuccessful();
        $this->artisan('furshield:send-reminders')->assertSuccessful();

        $this->assertSame(3, AppNotification::query()->count());
        $this->assertTrue(AppNotification::query()->where('title', 'Milo is overdue for care')->exists());
        $this->assertSame(3, AppNotification::query()->where('user_id', $owner->id)->count());
    }

    public function test_command_sends_only_the_current_window(): void
    {
        $owner = User::factory()->create();
        $pet = $this->pet($owner, 'Luna');
        $this->record($pet, '2026-11-13');

        $this->travelTo('2026-11-12');
        $this->artisan('furshield:send-reminders')->assertSuccessful();

        $this->assertSame(1, AppNotification::query()->count());
        $this->assertSame('Luna is due for care tomorrow', AppNotification::query()->value('title'));
    }

    public function test_command_skips_records_outside_the_reminder_window(): void
    {
        $owner = User::factory()->create();
        $pet = $this->pet($owner);

        $this->record($pet, '2026-11-20');
        $this->record($pet, null);

        $this->travelTo('2026-11-12');
        $this->artisan('furshield:send-reminders')->assertSuccessful();

        $this->assertSame(0, AppNotification::query()->count());
    }

    public function test_reminders_are_scheduled_daily(): void
    {
        $match = collect(Schedule::events())->first(
            fn ($event) => str_contains((string) $event->command, 'furshield:send-reminders')
        );

        $this->assertNotNull($match);
        $this->assertSame('0 0 * * *', $match->expression);
    }

    public function test_care_status_uses_the_worst_open_due_date(): void
    {
        $this->travelTo('2026-11-12');
        $owner = User::factory()->create();

        $onTrack = $this->pet($owner, 'On Track');
        $this->record($onTrack, '2026-12-01');
        $this->assertSame(CareStatus::OnTrack, $onTrack->careStatus());

        $quiet = $this->pet($owner, 'Quiet');
        $this->assertSame(CareStatus::OnTrack, $quiet->careStatus());

        $soon = $this->pet($owner, 'Soon');
        $this->record($soon, '2026-11-19');
        $this->record($soon, '2027-01-01');
        $this->assertSame(CareStatus::DueSoon, $soon->fresh()->careStatus());

        $overdue = $this->pet($owner, 'Late');
        $this->record($overdue, '2026-11-11');
        $this->record($overdue, '2026-11-18');
        $this->assertSame(CareStatus::Overdue, $overdue->fresh()->careStatus());

        $dueToday = $this->pet($owner, 'Today');
        $this->record($dueToday, '2026-11-12');
        $this->assertSame(CareStatus::DueSoon, $dueToday->fresh()->careStatus());
    }

    public function test_pet_cards_and_dashboard_show_the_care_status(): void
    {
        $this->travelTo('2026-11-12');
        $owner = User::factory()->create();
        $pet = $this->pet($owner, 'Milo');
        $this->record($pet, '2026-11-10');

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Overdue', false);

        $this->actingAs($owner)
            ->get(route('owner.pets.index'))
            ->assertOk()
            ->assertSee('Overdue', false);
    }

    public function test_add_record_applies_preset_recurrence(): void
    {
        $owner = User::factory()->create();
        $pet = $this->pet($owner);

        $this->actingAs($owner)->post(route('owner.pets.health.store', $pet), [
            'visit_date' => '2026-01-01',
            'record_type' => 'vaccination',
            'title' => 'Rabies',
        ])->assertRedirect();

        $vaccine = HealthRecord::query()->where('record_type', 'vaccination')->first();
        $this->assertSame(365, $vaccine->recurrence_days);
        $this->assertSame('2027-01-01', $vaccine->next_due_date->toDateString());
        $this->assertNull($vaccine->reminder_sent_at);

        $this->actingAs($owner)->post(route('owner.pets.health.store', $pet), [
            'visit_date' => '2026-02-01',
            'record_type' => 'surgery',
            'title' => 'Spay',
        ])->assertRedirect();

        $surgery = HealthRecord::query()->where('record_type', 'surgery')->first();
        $this->assertNull($surgery->recurrence_days);
        $this->assertNull($surgery->next_due_date);
    }

    private function pet(User $owner, string $name = 'Milo'): Pet
    {
        return Pet::query()->create([
            'owner_id' => $owner->id,
            'name' => $name,
            'species' => 'Dog',
        ]);
    }

    private function record(Pet $pet, ?string $due): HealthRecord
    {
        return HealthRecord::query()->create([
            'pet_id' => $pet->id,
            'visit_date' => '2026-01-01',
            'record_type' => 'vaccination',
            'title' => 'Rabies',
            'next_due_date' => $due,
            'recurrence_days' => $due === null ? null : 365,
        ]);
    }
}
