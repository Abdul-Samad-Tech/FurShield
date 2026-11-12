<?php

namespace Tests\Feature;

use App\Enums\CareCategory;
use App\Enums\ProductCategory;
use App\Enums\UserRole;
use App\Models\AdoptionListing;
use App\Models\AppNotification;
use App\Models\Appointment;
use App\Models\CareArticle;
use App\Models\Pet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SearchAndAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_is_role_aware_and_keeps_private_pets_private(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $other = User::factory()->create(['role' => UserRole::Owner]);
        $vet = User::factory()->create([
            'role' => UserRole::Vet,
            'name' => 'Dr River',
            'city' => 'Karachi',
            'specialization' => 'Dermatology',
        ]);
        $strangerVet = User::factory()->create(['role' => UserRole::Vet, 'name' => 'Dr Other']);

        $pet = Pet::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Milo Private',
            'species' => 'Dog',
        ]);
        Pet::query()->create([
            'owner_id' => $other->id,
            'name' => 'Hidden Hound',
            'species' => 'Dog',
        ]);

        Appointment::query()->create([
            'pet_id' => $pet->id,
            'owner_id' => $owner->id,
            'vet_id' => $vet->id,
            'appointment_time' => Carbon::parse('2026-11-20 10:00:00', 'UTC'),
            'status' => 'pending',
        ]);

        Product::query()->create([
            'name' => 'Salmon kibble',
            'category' => ProductCategory::Food,
            'price' => 12.50,
            'description' => 'A daily food',
            'is_active' => true,
        ]);
        Product::query()->create([
            'name' => 'Retired toy',
            'category' => ProductCategory::Toys,
            'price' => 4,
            'is_active' => false,
        ]);

        CareArticle::query()->create([
            'title' => 'Weekly brushing',
            'category' => CareCategory::Hygiene,
            'summary' => 'Brush gently',
            'body' => 'Use a soft brush.',
            'is_published' => true,
        ]);
        CareArticle::query()->create([
            'title' => 'Weekly brushing secret',
            'category' => CareCategory::Hygiene,
            'summary' => 'Unpublished',
            'body' => 'Do not show this.',
            'is_published' => false,
        ]);

        $shelter = User::factory()->create(['role' => UserRole::Shelter]);
        AdoptionListing::query()->create([
            'shelter_id' => $shelter->id,
            'pet_name' => 'Barnaby',
            'species' => 'Rabbit',
        ]);

        $guest = collect($this->getJson(route('search', ['q' => 'milo']))->assertOk()->json('items'))->pluck('label');
        $this->assertFalse($guest->contains('Milo Private'));

        $guestCatalog = collect($this->getJson(route('search', ['q' => 'weekly']))->assertOk()->json('items'))->pluck('label');
        $this->assertTrue($guestCatalog->contains('Weekly brushing'));
        $this->assertFalse($guestCatalog->contains('Weekly brushing secret'));

        $guestShop = collect($this->getJson(route('search', ['q' => 'salmon']))->assertOk()->json('items'))->pluck('label');
        $this->assertTrue($guestShop->contains('Salmon kibble'));
        $this->assertFalse($guestShop->contains('Retired toy'));

        $guestVet = collect($this->getJson(route('search', ['q' => 'river']))->assertOk()->json('items'))->pluck('label');
        $this->assertTrue($guestVet->contains('Dr River'));

        $guestPet = collect($this->getJson(route('search', ['q' => 'barnaby']))->assertOk()->json('items'))->pluck('label');
        $this->assertTrue($guestPet->contains('Barnaby'));

        $ownerItems = collect($this->actingAs($owner)->getJson(route('search', ['q' => 'milo']))->assertOk()->json('items'));
        $this->assertTrue($ownerItems->pluck('label')->contains('Milo Private'));
        $this->assertSame(route('owner.pets.show', $pet), $ownerItems->firstWhere('label', 'Milo Private')['url']);
        $this->assertFalse($ownerItems->pluck('label')->contains('Hidden Hound'));

        $actions = collect($this->actingAs($owner)->getJson(route('search', ['q' => 'book']))->assertOk()->json('items'))->pluck('label');
        $this->assertTrue($actions->contains('Book appointment'));
        $cart = collect($this->actingAs($owner)->getJson(route('search', ['q' => 'cart']))->assertOk()->json('items'))->pluck('label');
        $this->assertTrue($cart->contains('Open cart'));
        $health = collect($this->actingAs($owner)->getJson(route('search', ['q' => 'health']))->assertOk()->json('items'));
        $this->assertTrue($health->pluck('label')->contains('Add health record'));
        $this->assertSame(route('owner.pets.health', $pet), $health->firstWhere('label', 'Add health record')['url']);

        $empty = collect($this->actingAs($owner)->getJson(route('search'))->assertOk()->json('items'))->pluck('label');
        $this->assertTrue($empty->contains('Book appointment'));
        $this->assertTrue($empty->contains('Add health record'));
        $this->assertTrue($empty->contains('Open cart'));
        $this->assertFalse($empty->contains('Salmon kibble'));

        $vetItems = collect($this->actingAs($vet)->getJson(route('search', ['q' => 'milo']))->assertOk()->json('items'));
        $this->assertTrue($vetItems->pluck('label')->contains('Milo Private'));
        $this->assertSame(route('vet.pets.history', $pet), $vetItems->firstWhere('label', 'Milo Private')['url']);

        $vetActions = collect($this->actingAs($vet)->getJson(route('search', ['q' => 'book']))->assertOk()->json('items'))->pluck('label');
        $this->assertFalse($vetActions->contains('Book appointment'));

        $stranger = collect($this->actingAs($strangerVet)->getJson(route('search', ['q' => 'milo']))->assertOk()->json('items'))->pluck('label');
        $this->assertFalse($stranger->contains('Milo Private'));
    }

    public function test_search_and_alerts_are_in_both_shells(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('id="command-palette"', false)
            ->assertSee('aria-label="Search"', false);

        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('id="command-palette"', false)
            ->assertSee('aria-label="Search"', false)
            ->assertSee('alert-panel', false);
    }

    public function test_notification_feed_is_cheap_private_and_markable(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $this->get(route('notifications.unread-count'))->assertRedirect(route('login'));
        $this->get(route('notifications.latest'))->assertRedirect(route('login'));

        foreach (range(1, 6) as $number) {
            AppNotification::query()->create([
                'user_id' => $owner->id,
                'title' => 'Alert '.$number,
                'message' => 'Message '.$number,
            ]);
        }

        $foreign = AppNotification::query()->create([
            'user_id' => $other->id,
            'title' => 'Other person alert',
            'message' => 'Not yours',
        ]);

        $this->actingAs($owner)
            ->getJson(route('notifications.unread-count'))
            ->assertOk()
            ->assertJsonPath('count', 6);

        $latest = $this->actingAs($owner)
            ->getJson(route('notifications.latest'))
            ->assertOk()
            ->json();

        $this->assertCount(5, $latest['items']);
        $this->assertSame('Alert 6', $latest['items'][0]['title']);
        $this->assertSame(6, $latest['count']);
        $labels = collect($latest['items'])->pluck('title');
        $this->assertFalse($labels->contains('Other person alert'));
        $this->assertFalse($labels->contains('Alert 1'));

        $first = AppNotification::query()->where('user_id', $owner->id)->where('title', 'Alert 6')->firstOrFail();

        $this->actingAs($owner)
            ->postJson(route('notifications.read', $first))
            ->assertOk()
            ->assertJsonPath('count', 5);

        $this->assertNotNull($first->fresh()->read_at);

        $this->actingAs($owner)
            ->postJson(route('notifications.read', $foreign))
            ->assertForbidden();
    }
}
