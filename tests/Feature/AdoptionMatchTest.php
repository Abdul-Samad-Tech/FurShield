<?php

namespace Tests\Feature;

use App\Enums\AdoptionStatus;
use App\Enums\UserRole;
use App\Models\AdoptionListing;
use App\Models\User;
use App\Services\AdoptionMatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdoptionMatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_quiet_apartment_ranks_the_calm_cat_first(): void
    {
        [$buddy, $mochi] = $this->listings();
        $answers = [
            'home' => 'apartment',
            'kids' => 'no',
            'pets' => 'no',
            'experience' => 'first',
            'activity' => 'quiet',
        ];
        $service = app(AdoptionMatchService::class);

        $this->assertGreaterThan($service->score($buddy, $answers), $service->score($mochi, $answers));

        $this->get(route('adopt.index', $answers))
            ->assertOk()
            ->assertSeeInOrder(['Mochi', 'Buddy'])
            ->assertSee($service->score($mochi, $answers).'% match')
            ->assertSee('Find my match')
            ->assertSee('Deck')
            ->assertSee('Interested');
    }

    public function test_an_active_house_ranks_the_dog_first(): void
    {
        [$buddy, $mochi] = $this->listings();
        $answers = [
            'home' => 'house',
            'kids' => 'yes',
            'pets' => 'yes',
            'experience' => 'experienced',
            'activity' => 'high',
        ];

        $this->get(route('adopt.index', $answers))
            ->assertOk()
            ->assertSeeInOrder(['Buddy', 'Mochi']);

        $this->assertGreaterThan(
            app(AdoptionMatchService::class)->score($mochi, $answers),
            app(AdoptionMatchService::class)->score($buddy, $answers)
        );
    }

    public function test_a_partial_quiz_does_not_sort_or_show_a_percentage(): void
    {
        $this->listings();

        $this->get(route('adopt.index', ['home' => 'apartment']))
            ->assertOk()
            ->assertDontSee('Closest matches are first')
            ->assertSee('"match":null', false);
    }

    public function test_interest_from_the_deck_returns_json_and_marks_the_listing_pending(): void
    {
        [, $mochi] = $this->listings();
        $owner = User::factory()->create(['role' => UserRole::Owner]);

        $this->actingAs($owner)
            ->postJson(route('adopt.interest', $mochi), ['message' => 'We have a quiet apartment.'])
            ->assertOk()
            ->assertJsonPath('message', 'Interest submitted to the shelter.');

        $this->assertSame(AdoptionStatus::Pending, $mochi->fresh()->status);
        $this->assertDatabaseHas('adoption_interests', [
            'listing_id' => $mochi->id,
            'owner_id' => $owner->id,
        ]);
    }

    /**
     * @return array{0: AdoptionListing, 1: AdoptionListing}
     */
    private function listings(): array
    {
        $shelter = User::factory()->create([
            'role' => UserRole::Shelter,
            'shelter_name' => 'Paw House',
            'city' => 'Karachi',
        ]);

        $buddy = AdoptionListing::query()->create([
            'shelter_id' => $shelter->id,
            'pet_name' => 'Buddy',
            'species' => 'Dog',
            'breed' => 'Indie Mix',
            'age' => 1,
            'gender' => 'Male',
            'health_status' => 'Vaccinated and dewormed',
            'description' => 'Playful and friendly with kids. Looking for an active home.',
            'status' => AdoptionStatus::Available,
        ]);

        $mochi = AdoptionListing::query()->create([
            'shelter_id' => $shelter->id,
            'pet_name' => 'Mochi',
            'species' => 'Cat',
            'breed' => 'Domestic Shorthair',
            'age' => 2,
            'gender' => 'Female',
            'health_status' => 'Spayed, healthy appetite',
            'description' => 'Calm lap cat who loves sunny windowsills.',
            'status' => AdoptionStatus::Available,
        ]);

        return [$buddy, $mochi];
    }
}
