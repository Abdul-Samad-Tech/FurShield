<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\AdoptionListing;
use App\Models\Appointment;
use App\Models\CareArticle;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_demo_seed_fills_a_submission_dataset_with_local_images(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(3, User::query()->where('role', UserRole::Owner)->count());
        $this->assertGreaterThanOrEqual(8, Pet::query()->count());
        $this->assertGreaterThanOrEqual(4, User::query()->where('role', UserRole::Vet)->count());

        User::query()->where('role', UserRole::Vet)->each(function (User $vet): void {
            $this->assertNotNull($vet->latitude);
            $this->assertNotNull($vet->longitude);
            $this->assertNotEmpty($vet->available_slots);
        });

        $statuses = Appointment::query()->pluck('status')->map(fn (AppointmentStatus $status) => $status->value)->unique()->sort()->values()->all();
        $this->assertSame(
            ['approved', 'cancelled', 'completed', 'pending', 'rescheduled'],
            $statuses,
        );

        $today = Carbon::today()->toDateString();
        $soon = Carbon::today()->addDays(7)->toDateString();
        $this->assertTrue(HealthRecord::query()->whereDate('next_due_date', '<', $today)->exists());
        $this->assertTrue(HealthRecord::query()->whereDate('next_due_date', '>=', $today)->whereDate('next_due_date', '<=', $soon)->exists());
        $this->assertGreaterThan(0, $this->app['db']->table('app_notifications')->count());
        $this->assertGreaterThanOrEqual(5, AdoptionListing::query()->count());
        $this->assertGreaterThanOrEqual(6, CareArticle::query()->count());
        $this->assertNull(CareArticle::query()->whereNotNull('video_url')->value('video_url'));

        Product::query()->each(function (Product $product): void {
            $this->assertStringContainsString('/images/catalog-', $product->image_path);
            $this->assertStringNotContainsString('unsplash.com', $product->image_path);
        });

        Pet::query()->each(function (Pet $pet): void {
            $this->assertStringNotContainsString('unsplash.com', (string) $pet->photo_path);
        });
    }

    public function test_the_about_page_uses_a_local_photo(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('images/catalog-two-dogs.jpg', false)
            ->assertDontSee('images.unsplash.com', false);
    }
}
