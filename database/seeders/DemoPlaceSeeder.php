<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoPlaceSeeder extends Seeder
{
    public function run(): void
    {
        $this->place('vet@furshield.test', [
            'name' => 'Dr. Hassan Ali',
            'role' => UserRole::Vet,
            'phone' => '+92-300-4455667',
            'address' => 'Clinic Road, Lahore',
            'city' => 'Lahore',
            'latitude' => 31.5204000,
            'longitude' => 74.3587000,
            'specialization' => 'Small Animal Medicine',
            'experience_years' => 8,
            'bio' => 'Focused on preventive care, vaccinations, and feline health.',
        ], false);

        $this->place('vet.noor@furshield.test', [
            'name' => 'Dr. Noor Fatima',
            'role' => UserRole::Vet,
            'phone' => '+92-300-2200114',
            'address' => 'Clifton Block 5, Karachi',
            'city' => 'Karachi',
            'latitude' => 24.8138000,
            'longitude' => 67.0299000,
            'specialization' => 'Surgery',
            'experience_years' => 11,
            'bio' => 'Soft-tissue surgery and post-operative recovery.',
            'available_slots' => [
                ['weekday' => 2, 'start' => '09:00', 'end' => '17:00', 'slot_minutes' => 30],
                ['weekday' => 4, 'start' => '09:00', 'end' => '17:00', 'slot_minutes' => 30],
                ['weekday' => 6, 'start' => '09:00', 'end' => '13:00', 'slot_minutes' => 30],
            ],
        ]);

        $this->place('vet.omar@furshield.test', [
            'name' => 'Dr. Omar Siddiqui',
            'role' => UserRole::Vet,
            'phone' => '+92-300-5566778',
            'address' => 'F-6 Markaz, Islamabad',
            'city' => 'Islamabad',
            'latitude' => 33.7294000,
            'longitude' => 73.0931000,
            'specialization' => 'Dermatology',
            'experience_years' => 6,
            'bio' => 'Skin, coat, and allergy clinics.',
            'available_slots' => [
                ['weekday' => 1, 'start' => '10:00', 'end' => '16:00', 'slot_minutes' => 30],
                ['weekday' => 2, 'start' => '10:00', 'end' => '16:00', 'slot_minutes' => 30],
                ['weekday' => 3, 'start' => '10:00', 'end' => '16:00', 'slot_minutes' => 30],
                ['weekday' => 4, 'start' => '10:00', 'end' => '16:00', 'slot_minutes' => 30],
                ['weekday' => 5, 'start' => '10:00', 'end' => '16:00', 'slot_minutes' => 30],
            ],
        ]);

        $this->place('vet.hina@furshield.test', [
            'name' => 'Dr. Hina Qureshi',
            'role' => UserRole::Vet,
            'phone' => '+92-300-3344556',
            'address' => 'DHA Phase 6, Karachi',
            'city' => 'Karachi',
            'latitude' => 24.8300000,
            'longitude' => 67.0700000,
            'specialization' => 'Avian medicine',
            'experience_years' => 9,
            'bio' => 'Birds and other small exotic pets.',
            'available_slots' => [
                ['weekday' => 7, 'start' => '11:00', 'end' => '14:00', 'slot_minutes' => 30],
            ],
        ]);

        $this->place('shelter@furshield.test', [
            'name' => 'Paw Haven Shelter',
            'role' => UserRole::Shelter,
            'phone' => '+92-300-7788990',
            'address' => '45 Green Belt, Islamabad',
            'city' => 'Islamabad',
            'latitude' => 33.7100000,
            'longitude' => 73.0551000,
            'shelter_name' => 'Paw Haven Shelter',
            'contact_person' => 'Sara Malik',
        ], false);

        $this->place('shelter.creek@furshield.test', [
            'name' => 'Creek Side Shelter',
            'role' => UserRole::Shelter,
            'phone' => '+92-300-8899001',
            'address' => 'Boat Basin, Karachi',
            'city' => 'Karachi',
            'latitude' => 24.8607000,
            'longitude' => 67.0011000,
            'shelter_name' => 'Creek Side Shelter',
            'contact_person' => 'Demo desk',
        ]);

        $owner = User::query()->where('email', 'owner@furshield.test')->first();

        if ($owner === null) {
            return;
        }

        $this->rate($owner, 'vet@furshield.test', 5);
        $this->rate($owner, 'vet.noor@furshield.test', 4);
        $this->rate($owner, 'vet.omar@furshield.test', 3);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function place(string $email, array $attributes, bool $fillSchedule = true): void
    {
        $existing = User::query()->where('email', $email)->first();

        if ($existing !== null) {
            $updates = [
                'latitude' => $attributes['latitude'],
                'longitude' => $attributes['longitude'],
            ];

            if ($fillSchedule && $existing->available_slots === null && isset($attributes['available_slots'])) {
                $updates['available_slots'] = $attributes['available_slots'];
            }

            $existing->update($updates);

            return;
        }

        User::query()->create(array_merge($attributes, [
            'email' => $email,
            'password' => 'password',
        ]));
    }

    private function rate(User $owner, string $email, int $rating): void
    {
        $vet = User::query()->where('email', $email)->first();

        if ($vet === null) {
            return;
        }

        Feedback::query()->updateOrCreate(
            [
                'user_id' => $owner->id,
                'target_type' => User::class,
                'target_id' => $vet->id,
            ],
            [
                'rating' => $rating,
                'comment' => null,
            ],
        );
    }
}
