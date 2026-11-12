<?php

namespace Database\Factories;

use App\Models\HealthRecord;
use App\Models\Pet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HealthRecord>
 */
class HealthRecordFactory extends Factory
{
    protected $model = HealthRecord::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pet_id' => Pet::factory(),
            'vet_id' => null,
            'visit_date' => '2026-01-01',
            'next_due_date' => '2026-11-19',
            'recurrence_days' => 365,
            'reminder_sent_at' => null,
            'record_type' => 'vaccination',
            'title' => 'Rabies',
        ];
    }
}
