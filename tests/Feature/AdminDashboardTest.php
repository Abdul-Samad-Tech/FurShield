<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductCategory;
use App\Enums\UserRole;
use App\Models\AdoptionListing;
use App\Models\Appointment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pet;
use App\Models\Product;
use App\Models\User;
use App\Queries\Admin\ActivityFeedQuery;
use App\Queries\Admin\OrderExportQuery;
use App\Queries\Admin\RegistrationSeriesQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Model::preventLazyLoading(false);

        parent::tearDown();
    }

    public function test_guests_and_other_roles_cannot_open_the_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $owner = User::factory()->create(['role' => UserRole::Owner]);

        $this->actingAs($owner)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.users.export'))->assertRedirect(route('login'));
        $this->get(route('admin.orders.export'))->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_deltas_charts_activity_and_the_account_table(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-12 15:00:00', 'UTC'));

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'name' => 'Ada Admin',
        ]);
        $recent = User::factory()->create(['role' => UserRole::Owner, 'name' => 'Recent Owner']);
        $alsoRecent = User::factory()->create(['role' => UserRole::Vet, 'name' => 'Recent Vet']);
        $previous = User::factory()->create(['role' => UserRole::Owner, 'name' => 'Earlier Owner']);
        $old = User::factory()->create(['role' => UserRole::Shelter, 'name' => 'Old Shelter']);
        $recent->forceFill(['created_at' => Carbon::parse('2026-11-10 09:00:00')])->save();
        $alsoRecent->forceFill(['created_at' => Carbon::parse('2026-11-08 09:00:00')])->save();
        $previous->forceFill(['created_at' => Carbon::parse('2026-11-02 09:00:00')])->save();
        $old->forceFill(['created_at' => Carbon::parse('2026-10-01 09:00:00')])->save();

        $pet = Pet::query()->create([
            'owner_id' => $recent->id,
            'name' => 'Milo',
            'species' => 'Dog',
        ]);
        Appointment::query()->create([
            'pet_id' => $pet->id,
            'owner_id' => $recent->id,
            'vet_id' => $alsoRecent->id,
            'appointment_time' => now()->addDay(),
            'status' => AppointmentStatus::Pending,
            'reason' => 'Checkup',
        ]);

        $sold = Product::query()->create([
            'name' => 'Salmon Kibble',
            'category' => ProductCategory::Food,
            'price' => 10,
            'stock_quantity' => 20,
            'is_active' => true,
        ]);
        $cartOnly = Product::query()->create([
            'name' => 'Cart Only Toy',
            'category' => ProductCategory::Toys,
            'price' => 4,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
        $placed = Order::query()->create([
            'owner_id' => $recent->id,
            'total_amount' => 20,
            'status' => OrderStatus::Placed,
            'order_date' => Carbon::parse('2026-11-11 10:00:00'),
        ]);
        OrderItem::query()->create([
            'order_id' => $placed->id,
            'product_id' => $sold->id,
            'quantity' => 2,
            'price_each' => 10,
        ]);
        $earlier = Order::query()->create([
            'owner_id' => $previous->id,
            'total_amount' => 5,
            'status' => OrderStatus::Placed,
            'order_date' => Carbon::parse('2026-11-01 10:00:00'),
        ]);
        OrderItem::query()->create([
            'order_id' => $earlier->id,
            'product_id' => $sold->id,
            'quantity' => 1,
            'price_each' => 5,
        ]);
        $cart = Order::query()->create([
            'owner_id' => $recent->id,
            'total_amount' => 4,
            'status' => OrderStatus::Cart,
        ]);
        OrderItem::query()->create([
            'order_id' => $cart->id,
            'product_id' => $cartOnly->id,
            'quantity' => 1,
            'price_each' => 4,
        ]);

        AdoptionListing::query()->create([
            'shelter_id' => $old->id,
            'pet_name' => 'Buddy',
            'species' => 'Dog',
            'status' => 'available',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Admin overview');
        $response->assertSee('+2 vs the previous 7 days');
        $response->assertSee('+$15.00 vs the previous 7 days');
        $response->assertSee('Same as the previous 7 days');
        $response->assertSee('Registrations by role');
        $response->assertSee('Appointments by status');
        $response->assertSee('Orders and revenue');
        $response->assertSee('Top products');
        $response->assertSee('Salmon Kibble');
        $response->assertSee('Recent activity');
        $response->assertSee('Recent Owner requested a visit for Milo');
        $response->assertSee('Old Shelter published Buddy');
        $response->assertSee('Pet Owner');
        $response->assertSee('Veterinarian');
        $response->assertSee('Export users');
        $response->assertSee('Export orders');
        $response->assertSee('id="admin-charts"', false);
        $response->assertDontSee('Cart Only Toy');
    }

    public function test_account_table_searches_filters_and_sorts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'name' => 'Ada Admin']);
        User::factory()->create(['role' => UserRole::Owner, 'name' => 'Aaron Animal', 'email' => 'aaron@furshield.test']);
        User::factory()->create(['role' => UserRole::Vet, 'name' => 'Zelda Vet', 'email' => 'zelda@furshield.test']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['q' => 'Zelda']))
            ->assertOk()
            ->assertSee('Zelda Vet')
            ->assertSee('1 account')
            ->assertDontSee('aaron@furshield.test');

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['role' => 'vet']))
            ->assertOk()
            ->assertSee('Zelda Vet')
            ->assertSee('1 account')
            ->assertDontSee('aaron@furshield.test');

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['role' => 'not-a-role', 'sort' => 'name;drop', 'direction' => 'sideways']))
            ->assertOk()
            ->assertSee('Aaron Animal')
            ->assertSee('Zelda Vet');

        $sorted = $this->actingAs($admin)->get(route('admin.dashboard', [
            'sort' => 'name',
            'direction' => 'asc',
        ]));

        $sorted->assertOk();
        $sorted->assertSeeInOrder(['Aaron Animal', 'Ada Admin', 'Zelda Vet']);
    }

    public function test_csv_exports_match_the_filters_and_skip_open_carts(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'name' => '=Ada Admin',
            'email' => 'ada@furshield.test',
        ]);
        User::factory()->create(['role' => UserRole::Owner, 'name' => 'Aaron Animal', 'email' => 'aaron@furshield.test']);
        $owner = User::factory()->create(['role' => UserRole::Owner, 'name' => 'Recent Owner', 'email' => 'recent@furshield.test']);
        $sold = Product::query()->create([
            'name' => 'Salmon Kibble',
            'category' => ProductCategory::Food,
            'price' => 10,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);
        $toy = Product::query()->create([
            'name' => 'Cart Only Toy',
            'category' => ProductCategory::Toys,
            'price' => 4,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);
        $placed = Order::query()->create([
            'owner_id' => $owner->id,
            'total_amount' => 20,
            'status' => OrderStatus::Placed,
            'order_date' => now(),
        ]);
        OrderItem::query()->create([
            'order_id' => $placed->id,
            'product_id' => $sold->id,
            'quantity' => 2,
            'price_each' => 10,
        ]);
        $cart = Order::query()->create([
            'owner_id' => $owner->id,
            'total_amount' => 4,
            'status' => OrderStatus::Cart,
        ]);
        OrderItem::query()->create([
            'order_id' => $cart->id,
            'product_id' => $toy->id,
            'quantity' => 1,
            'price_each' => 4,
        ]);

        $users = $this->actingAs($admin)->get(route('admin.users.export', ['q' => 'Aaron']));
        $users->assertOk();
        $userCsv = $users->streamedContent();
        $this->assertStringContainsString('text/csv', (string) $users->headers->get('content-type'));
        $this->assertStringContainsString('Name,Email,Role,City,Phone,Joined', $userCsv);
        $this->assertStringContainsString('Aaron Animal', $userCsv);
        $this->assertStringNotContainsString('Recent Owner', $userCsv);

        $allUsers = $this->actingAs($admin)->get(route('admin.users.export'))->streamedContent();
        $this->assertStringContainsString("'=Ada Admin", $allUsers);

        Model::preventLazyLoading();
        $orders = app(OrderExportQuery::class)->rows();
        $lines = [];
        foreach ($orders as $row) {
            $lines[] = implode(',', $row);
        }
        Model::preventLazyLoading(false);

        $orderCsv = implode("\n", $lines);
        $this->assertStringContainsString('Salmon Kibble', $orderCsv);
        $this->assertStringContainsString('Recent Owner', $orderCsv);
        $this->assertStringNotContainsString('Cart Only Toy', $orderCsv);

        $download = $this->actingAs($admin)->get(route('admin.orders.export'));
        $download->assertOk();
        $this->assertStringContainsString('furshield-orders-', (string) $download->headers->get('content-disposition'));
        $this->assertStringContainsString('Salmon Kibble', $download->streamedContent());
        $this->assertStringNotContainsString('Cart Only Toy', $download->streamedContent());
    }

    public function test_reporting_queries_eager_load_and_bucket_registrations_by_week(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-12 15:00:00', 'UTC'));

        $owner = User::factory()->create(['role' => UserRole::Owner, 'name' => 'Recent Owner']);
        $vet = User::factory()->create(['role' => UserRole::Vet, 'name' => 'Recent Vet']);
        $shelter = User::factory()->create(['role' => UserRole::Shelter, 'name' => 'Paw Haven', 'shelter_name' => 'Paw Haven']);
        $owner->forceFill(['created_at' => Carbon::parse('2026-10-20 09:00:00')])->save();

        $pet = Pet::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Milo',
            'species' => 'Dog',
        ]);
        Appointment::query()->create([
            'pet_id' => $pet->id,
            'owner_id' => $owner->id,
            'vet_id' => $vet->id,
            'appointment_time' => now()->addDay(),
            'status' => AppointmentStatus::Pending,
        ]);
        AdoptionListing::query()->create([
            'shelter_id' => $shelter->id,
            'pet_name' => 'Buddy',
            'species' => 'Dog',
            'status' => 'available',
        ]);
        $product = Product::query()->create([
            'name' => 'Salmon Kibble',
            'category' => ProductCategory::Food,
            'price' => 10,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);
        $order = Order::query()->create([
            'owner_id' => $owner->id,
            'total_amount' => 10,
            'status' => OrderStatus::Placed,
            'order_date' => now(),
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_each' => 10,
        ]);

        Model::preventLazyLoading();
        $activity = app(ActivityFeedQuery::class)->recent();
        foreach (app(OrderExportQuery::class)->rows() as $row) {
            $this->assertNotSame('', $row[6]);
        }
        Model::preventLazyLoading(false);

        $this->assertTrue(collect($activity)->contains(fn (array $item): bool => str_contains($item['title'], 'Milo')));
        $this->assertTrue(collect($activity)->contains(fn (array $item): bool => str_contains($item['title'], 'Paw Haven published Buddy')));

        $series = app(RegistrationSeriesQuery::class)->series();
        $owners = collect($series['series'])->firstWhere('key', 'owner');
        $this->assertSame(1, array_sum($owners['values']));
        $this->assertFalse($series['blank']);
    }

    public function test_reporting_indexes_exist(): void
    {
        $expected = [
            'users' => ['users_role_index', 'users_name_index', 'users_created_at_index'],
            'appointments' => ['appointments_status_index', 'appointments_created_at_index', 'appointments_appointment_time_index'],
            'orders' => ['orders_status_order_date_index', 'orders_created_at_index'],
            'adoption_listings' => ['adoption_listings_status_index', 'adoption_listings_created_at_index'],
            'adoption_interests' => ['adoption_interests_status_index', 'adoption_interests_created_at_index'],
        ];

        foreach ($expected as $table => $names) {
            $indexes = collect(Schema::getIndexes($table))->pluck('name');

            foreach ($names as $name) {
                $this->assertTrue($indexes->contains($name), $table.' is missing '.$name);
            }
        }
    }
}
