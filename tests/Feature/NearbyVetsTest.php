<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Feedback;
use App\Models\User;
use App\Services\NearbyDirectoryService;
use Database\Seeders\DemoPlaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NearbyVetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_haversine_distance_between_one_degree_of_longitude_at_the_equator(): void
    {
        $km = app(NearbyDirectoryService::class)->haversineKm(0, 0, 0, 1);

        $this->assertEqualsWithDelta(111.19, $km, 0.05);
    }

    public function test_directory_sorts_by_distance_and_marks_who_is_open(): void
    {
        $this->travelTo('2026-11-12 10:00:00');
        $near = $this->vet('Dr. Noor Fatima', 24.8138, 67.0299, 'Karachi', 'Surgery', $this->thursdayWindow());
        $far = $this->vet('Dr. Hassan Ali', 31.5204, 74.3587, 'Lahore', 'Small Animal Medicine', [
            ['weekday' => 1, 'start' => '09:00', 'end' => '13:00', 'slot_minutes' => 30],
        ]);
        $this->rate($near, 4);
        $this->rate($far, 5);

        $response = $this->get(route('vets.index', [
            'latitude' => 24.8138,
            'longitude' => 67.0299,
        ]));

        $response->assertOk();
        $response->assertSeeInOrder(['Dr. Noor Fatima', 'Dr. Hassan Ali']);
        $response->assertSee('Nearby');
        $response->assertSee('Sorted by distance from the location you shared.');
        $this->assertStringContainsString('Open now', $this->card($response->getContent(), 'Dr. Noor Fatima'));
        $this->assertStringNotContainsString('Open now', $this->card($response->getContent(), 'Dr. Hassan Ali'));
        $response->assertSee('tel:+923001112233', false);
        $response->assertSee('md:hidden', false);
        $response->assertSee('openstreetmap.org/directions?to=', false);
        $response->assertSee('data-nearby', false);
    }

    public function test_available_today_and_rating_filters_narrow_the_list(): void
    {
        $this->travelTo('2026-11-12 18:00:00');
        $open = $this->vet('Dr. Noor Fatima', 24.8138, 67.0299, 'Karachi', 'Surgery', $this->thursdayWindow());
        $sunday = $this->vet('Dr. Hina Qureshi', 24.8300, 67.0700, 'Karachi', 'Avian medicine', [
            ['weekday' => 7, 'start' => '11:00', 'end' => '14:00', 'slot_minutes' => 30],
        ]);
        $low = $this->vet('Dr. Omar Siddiqui', 33.7294, 73.0931, 'Islamabad', 'Dermatology', [
            ['weekday' => 4, 'start' => '10:00', 'end' => '16:00', 'slot_minutes' => 30],
        ]);
        $this->rate($open, 4);
        $this->rate($low, 3);

        $today = $this->get(route('vets.index', ['today' => 1]));
        $today->assertOk();
        $today->assertSee('Dr. Noor Fatima');
        $today->assertSee('Dr. Omar Siddiqui');
        $today->assertDontSee('Dr. Hina Qureshi');
        $this->assertStringNotContainsString('Open now', $today->getContent());

        $rated = $this->get(route('vets.index', ['rating' => 4]));
        $rated->assertOk();
        $rated->assertSee('Dr. Noor Fatima');
        $rated->assertDontSee('Dr. Omar Siddiqui');
        $rated->assertDontSee('Dr. Hina Qureshi');

        $city = $this->get(route('vets.index', ['city' => 'Karachi']));
        $city->assertOk();
        $city->assertSee('Dr. Noor Fatima');
        $city->assertSee('Dr. Hina Qureshi');
        $city->assertDontSee('Dr. Omar Siddiqui');
    }

    public function test_page_works_without_geolocation_and_rejects_bad_coordinates(): void
    {
        $this->vet('Dr. Noor Fatima', null, null, 'Karachi', 'Surgery', $this->thursdayWindow());

        $page = $this->get(route('vets.index'));
        $page->assertOk();
        $page->assertSee('Vets near me');
        $page->assertSee('id="vet-city"', false);
        $page->assertSee('Use my location');
        $page->assertSee('This clinic is not on the map yet.');
        $page->assertSee('The map loads when you reach it.');
        $page->assertDontSee('Directions');

        $this->from(route('vets.index'))
            ->get(route('vets.index', ['latitude' => 91, 'longitude' => 67]))
            ->assertRedirect(route('vets.index'))
            ->assertSessionHasErrors('latitude');
    }

    public function test_demo_seeder_sets_coordinates_without_replacing_existing_hours(): void
    {
        $hassan = User::factory()->create([
            'name' => 'Dr. Hassan Ali',
            'email' => 'vet@furshield.test',
            'role' => UserRole::Vet,
            'latitude' => null,
            'longitude' => null,
            'available_slots' => [
                ['weekday' => 1, 'start' => '10:00', 'end' => '11:00', 'slot_minutes' => 30],
            ],
        ]);

        $this->seed(DemoPlaceSeeder::class);

        $hassan->refresh();
        $this->assertSame('31.5204000', $hassan->latitude);
        $this->assertSame('74.3587000', $hassan->longitude);
        $this->assertSame(1, $hassan->available_slots[0]['weekday']);
        $this->assertSame('10:00', $hassan->available_slots[0]['start']);

        $shelter = User::query()->where('email', 'shelter@furshield.test')->firstOrFail();
        $this->assertNotNull($shelter->latitude);
        $this->assertNotNull($shelter->longitude);

        $noor = User::query()->where('email', 'vet.noor@furshield.test')->firstOrFail();
        $this->assertSame('Karachi', $noor->city);
        $this->assertNotNull($noor->latitude);
    }

    /**
     * @param  list<array{weekday: int, start: string, end: string, slot_minutes: int}>  $slots
     */
    private function vet(string $name, ?float $lat, ?float $lng, string $city, string $specialization, array $slots): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => UserRole::Vet,
            'phone' => '+92-300-1112233',
            'city' => $city,
            'latitude' => $lat,
            'longitude' => $lng,
            'specialization' => $specialization,
            'available_slots' => $slots,
        ]);
    }

    /**
     * @return list<array{weekday: int, start: string, end: string, slot_minutes: int}>
     */
    private function thursdayWindow(): array
    {
        return [
            ['weekday' => 4, 'start' => '09:00', 'end' => '17:00', 'slot_minutes' => 30],
        ];
    }

    private function rate(User $vet, int $rating): void
    {
        $owner = User::factory()->create();

        Feedback::query()->create([
            'user_id' => $owner->id,
            'target_type' => User::class,
            'target_id' => $vet->id,
            'rating' => $rating,
            'comment' => null,
        ]);
    }

    private function card(string $html, string $name): string
    {
        foreach (preg_split('/(?=<article\b)/', $html) ?: [] as $chunk) {
            if (str_contains($chunk, '>'.$name.'</h2>')) {
                return $chunk;
            }
        }

        return '';
    }
}
