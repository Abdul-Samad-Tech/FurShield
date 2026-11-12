<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pet>
 */
class PetFactory extends Factory
{
    protected $model = Pet::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->owner(),
            'name' => fake()->firstName(),
            'species' => 'Dog',
            'breed' => 'Mixed',
            'age' => 3,
            'gender' => 'female',
            'medical_history' => null,
            'gallery' => [],
        ];
    }
}
