<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->owner(),
            'total_amount' => 0,
            'status' => OrderStatus::Cart,
            'order_date' => null,
        ];
    }

    public function placed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Placed,
            'order_date' => now(),
        ]);
    }
}
