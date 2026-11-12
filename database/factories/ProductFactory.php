<?php

namespace Database\Factories;

use App\Enums\ProductCategory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'category' => ProductCategory::Food,
            'price' => 12.50,
            'description' => fake()->sentence(),
            'stock_quantity' => 20,
            'image_path' => null,
            'is_active' => true,
            'average_rating' => 0,
        ];
    }
}
