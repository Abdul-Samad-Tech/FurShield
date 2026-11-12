<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pet_id' => Pet::factory(),
            'owner_id' => User::factory()->owner(),
            'vet_id' => User::factory()->vet(),
            'appointment_time' => '2026-11-12 09:00:00',
            'status' => AppointmentStatus::Pending,
            'reason' => 'Checkup',
        ];
    }
}
