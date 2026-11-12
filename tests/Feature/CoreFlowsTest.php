<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\OrderStatus;
use App\Models\AppNotification;
use App\Models\Appointment;
use App\Models\HealthRecord;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pet;
use App\Models\Product;
use App\Models\User;
use App\Services\TriageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreFlowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_registrable_role_reaches_its_own_dashboard(): void
    {
        foreach ([
            'owner' => 'owner.dashboard',
            'vet' => 'vet.dashboard',
            'shelter' => 'shelter.dashboard',
        ] as $role => $dashboard) {
            $response = $this->post('/register', $this->registration($role, $role.'-flow@example.com'));

            $response->assertRedirect(route($dashboard));
            $this->assertAuthenticated();
            $this->assertSame($role, auth()->user()->role->value);

            auth()->logout();
        }
    }

    public function test_admin_cannot_be_registered(): void
    {
        $this->post('/register', $this->registration('admin', 'admin-flow@example.com'))
            ->assertSessionHasErrors('role');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'admin-flow@example.com']);
    }

    public function test_role_middleware_sends_each_account_away_from_another_area(): void
    {
        $owner = User::factory()->owner()->create();
        $vet = User::factory()->vet()->create();

        $this->get(route('owner.dashboard'))->assertRedirect(route('login'));

        $this->actingAs($vet)
            ->get(route('owner.pets.index'))
            ->assertRedirect(route('vet.dashboard'))
            ->assertSessionHas('success', 'You do not have access to that area.');

        $this->actingAs($owner)
            ->get(route('vet.appointments'))
            ->assertRedirect(route('owner.dashboard'))
            ->assertSessionHas('success', 'You do not have access to that area.');

        $this->actingAs($owner)
            ->get(route('shelter.listings'))
            ->assertRedirect(route('owner.dashboard'));

        $this->actingAs($owner)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_booking_keeps_a_slot_exclusive_and_blocks_illegal_cancels(): void
    {
        $this->travelTo('2026-11-12 08:00:00');
        $vet = User::factory()->vet()->create();
        $owner = User::factory()->owner()->create();
        $other = User::factory()->owner()->create();
        $pet = Pet::factory()->create(['owner_id' => $owner->id, 'name' => 'Milo']);
        $otherPet = Pet::factory()->create(['owner_id' => $other->id, 'name' => 'Luna']);

        $this->actingAs($owner)
            ->post(route('owner.appointments.store'), $this->booking($pet, $vet, '2026-11-12T09:00'))
            ->assertRedirect(route('owner.appointments.index'));

        $this->actingAs($other)
            ->from(route('owner.appointments.create'))
            ->post(route('owner.appointments.store'), $this->booking($otherPet, $vet, '2026-11-12T09:00'))
            ->assertRedirect(route('owner.appointments.create'))
            ->assertSessionHasErrors('appointment_time');

        $this->actingAs($other)
            ->from(route('owner.appointments.create'))
            ->post(route('owner.appointments.store'), $this->booking($otherPet, $vet, '2026-11-12T09:15'))
            ->assertSessionHasErrors('appointment_time');

        $this->assertSame(1, Appointment::query()->count());

        $visit = Appointment::query()->firstOrFail();

        $this->actingAs($other)
            ->patch(route('owner.appointments.cancel', $visit))
            ->assertForbidden();

        $this->assertSame(AppointmentStatus::Pending, $visit->fresh()->status);

        $visit->update(['status' => AppointmentStatus::Completed]);

        $this->actingAs($owner)
            ->from(route('owner.appointments.index'))
            ->patch(route('owner.appointments.cancel', $visit))
            ->assertRedirect(route('owner.appointments.index'))
            ->assertSessionHasErrors('appointment');

        $this->assertSame(AppointmentStatus::Completed, $visit->fresh()->status);
    }

    public function test_checkout_rejects_a_short_line_and_leaves_stock_and_the_cart_unchanged(): void
    {
        $owner = User::factory()->owner()->create();
        $kibble = Product::factory()->create([
            'name' => 'Salmon kibble',
            'stock_quantity' => 4,
            'price' => 10,
        ]);
        $toy = Product::factory()->create([
            'name' => 'Feather wand',
            'stock_quantity' => 1,
            'price' => 5,
        ]);

        $this->actingAs($owner)->post(route('owner.cart.add', $kibble), ['quantity' => 2]);
        $this->actingAs($owner)->post(route('owner.cart.add', $toy), ['quantity' => 1]);
        $toy->update(['stock_quantity' => 0]);

        $this->actingAs($owner)
            ->from(route('owner.cart.index'))
            ->post(route('owner.cart.checkout'))
            ->assertRedirect(route('owner.cart.index'))
            ->assertSessionHasErrors('cart');

        $this->assertSame(4, $kibble->fresh()->stock_quantity);
        $this->assertSame(0, $toy->fresh()->stock_quantity);
        $this->assertSame(OrderStatus::Cart, Order::query()->first()->status);
        $this->assertSame(0, Order::query()->where('status', OrderStatus::Placed)->count());
    }

    public function test_the_reminder_command_notifies_a_due_record_once(): void
    {
        $owner = User::factory()->owner()->create();
        $pet = Pet::factory()->create(['owner_id' => $owner->id, 'name' => 'Milo']);
        HealthRecord::factory()->create([
            'pet_id' => $pet->id,
            'next_due_date' => '2026-11-19',
        ]);

        $this->travelTo('2026-11-12');
        $this->artisan('furshield:send-reminders')->assertSuccessful();
        $this->artisan('furshield:send-reminders')->assertSuccessful();

        $this->assertSame(1, AppNotification::query()->count());
        $this->assertSame('Milo is due for care in 7 days', AppNotification::query()->value('title'));
        $this->assertSame($owner->id, AppNotification::query()->value('user_id'));
    }

    public function test_critical_triage_rules_outrank_a_mild_symptom(): void
    {
        $triage = app(TriageService::class);

        foreach (['difficulty_breathing', 'seizure', 'poisoning', 'bleeding', 'collapse', 'pale_gums'] as $symptom) {
            $result = $triage->assess('dog', [$symptom, 'itching']);

            $this->assertSame('emergency', $result['level'], $symptom);
            $this->assertSame('Emergency now', $result['title'], $symptom);
        }

        $this->assertSame('monitor', $triage->assess('dog', ['itching'])['level']);

        $this->post(route('triage.store'), [
            'species' => 'dog',
            'symptoms' => ['difficulty_breathing'],
        ])->assertRedirect(route('triage.result'));

        $this->get(route('triage.result'))
            ->assertOk()
            ->assertSee('Emergency now');
    }

    public function test_a_public_pet_page_hides_the_private_record(): void
    {
        $owner = User::factory()->owner()->create([
            'phone' => '5550199',
            'email' => 'secret-owner@example.com',
            'address' => 'SECRET-ADDRESS-LANE',
        ]);
        $pet = Pet::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'NAME-PIPPIN-QUILL',
            'breed' => 'BREED-ARCTIC',
            'medical_history' => 'HISTORY-PRIVATE-NOTE',
        ]);

        $page = $this->get(route('pets.public', $pet->public_token));

        $page->assertOk();
        $page->assertDontSee('NAME-PIPPIN-QUILL');
        $page->assertDontSee('BREED-ARCTIC');
        $page->assertDontSee('HISTORY-PRIVATE-NOTE');
        $page->assertDontSee('5550199');
        $page->assertDontSee('secret-owner@example.com');
        $page->assertDontSee('SECRET-ADDRESS-LANE');
        $page->assertDontSee('/owner/pets/'.$pet->id, false);
    }

    public function test_an_owner_cannot_open_another_owners_pets_orders_or_appointments(): void
    {
        $owner = User::factory()->owner()->create();
        $other = User::factory()->owner()->create();
        $vet = User::factory()->vet()->create();
        $pet = Pet::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'PRIVATE-MILO',
        ]);
        $visit = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'owner_id' => $owner->id,
            'vet_id' => $vet->id,
            'status' => AppointmentStatus::Pending,
        ]);
        $product = Product::factory()->create(['name' => 'SECRET-SALMON-KIBBLE']);
        $order = Order::factory()->placed()->create([
            'owner_id' => $owner->id,
            'total_amount' => 12.50,
        ]);
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_each' => 12.50,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.pets.show', $pet))
            ->assertOk()
            ->assertSee('PRIVATE-MILO');

        $this->actingAs($owner)
            ->get(route('owner.cart.orders'))
            ->assertOk()
            ->assertSee('SECRET-SALMON-KIBBLE');

        $this->actingAs($other)->get(route('owner.pets.show', $pet))->assertForbidden();
        $this->actingAs($other)->get(route('owner.pets.edit', $pet))->assertForbidden();
        $this->actingAs($other)->get(route('owner.pets.health', $pet))->assertForbidden();
        $this->actingAs($other)->get(route('owner.appointments.ics', $visit))->assertForbidden();
        $this->actingAs($other)->patch(route('owner.appointments.cancel', $visit))->assertForbidden();
        $this->actingAs($other)->get(route('owner.cart.items.show', $item))->assertForbidden();
        $this->actingAs($other)
            ->put(route('owner.cart.update', $item), ['quantity' => 4])
            ->assertForbidden();

        $this->actingAs($other)
            ->get(route('owner.pets.index'))
            ->assertOk()
            ->assertDontSee('PRIVATE-MILO');

        $this->actingAs($other)
            ->get(route('owner.appointments.index'))
            ->assertOk()
            ->assertDontSee('PRIVATE-MILO');

        $this->actingAs($other)
            ->get(route('owner.cart.orders'))
            ->assertOk()
            ->assertDontSee('SECRET-SALMON-KIBBLE');

        $this->assertSame(AppointmentStatus::Pending, $visit->fresh()->status);
        $this->assertSame(1, $item->fresh()->quantity);
        $this->assertSame($owner->id, $pet->fresh()->owner_id);
    }

    /**
     * @return array<string, string>
     */
    private function registration(string $role, string $email): array
    {
        return [
            'name' => 'Flow User',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => $role,
            'phone' => '03001234567',
            'address' => '12 Clifton Block 5',
            'shelter_name' => $role === 'shelter' ? 'Creek Side Shelter' : null,
            'contact_person' => $role === 'shelter' ? 'Flow User' : null,
            'city' => 'Karachi',
        ];
    }

    /**
     * @return array{pet_id: int, vet_id: int, appointment_time: string, reason: string}
     */
    private function booking(Pet $pet, User $vet, string $time): array
    {
        return [
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'appointment_time' => $time,
            'reason' => 'Checkup',
        ];
    }
}
