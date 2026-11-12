<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ProductCategory;
use App\Models\OrderItem;
use App\Models\Pet;
use App\Models\Product;
use App\Models\User;
use App\Support\DummyImages;
use App\Support\Furshield;
use Illuminate\Support\Collection;

class ShopCatalogService
{
    /**
     * Species words used to match a pet to shop copy. A product that names a different species is left out.
     *
     * @var array<string, list<string>>
     */
    private const TOKENS = [
        'dog' => ['dog', 'puppy', 'kibble', 'harness', 'chew'],
        'cat' => ['cat', 'kitten', 'feather'],
        'bird' => ['bird', 'avian', 'perch'],
        'rabbit' => ['rabbit', 'hay'],
        'fish' => ['fish', 'aquarium'],
    ];

    /**
     * @return array{pet: ?Pet, products: Collection<int, Product>}
     */
    public function recommendation(User $owner): array
    {
        $pet = $owner->pets()->latest('id')->first();

        if ($pet === null) {
            return ['pet' => null, 'products' => collect()];
        }

        $species = $this->speciesKey($pet->species);

        $products = Product::query()
            ->where('is_active', true)
            ->get()
            ->map(fn (Product $product): array => [
                'product' => $product,
                'score' => $this->score($product, $species, $pet->age),
            ])
            ->filter(fn (array $row): bool => $row['score'] > 0)
            ->sortByDesc('score')
            ->take(4)
            ->pluck('product')
            ->values();

        return ['pet' => $pet, 'products' => $products];
    }

    /**
     * @return Collection<int, Product>
     */
    public function buyAgain(User $owner): Collection
    {
        $ids = OrderItem::query()
            ->whereHas('order', function ($query) use ($owner): void {
                $query->where('owner_id', $owner->id)->where('status', OrderStatus::Placed);
            })
            ->latest('id')
            ->pluck('product_id')
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $products = Product::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->get()
            ->keyBy('id');

        return $ids
            ->map(fn (mixed $id): ?Product => $products->get((int) $id))
            ->filter()
            ->take(4)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function card(Product $product, ?User $user): array
    {
        $image = DummyImages::describe($product->image_path, 'product', $product->id);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => (string) $product->description,
            'category' => $product->category?->label() ?? 'Shop',
            'price' => (float) $product->price,
            'price_label' => Furshield::money($product->price),
            'stock' => (int) $product->stock_quantity,
            'low_stock' => $product->isLowStock(),
            'rating' => number_format((float) $product->average_rating, 1),
            'image' => $image['url'],
            'url' => route('products.show', $product),
            'add_url' => $user?->isOwner() ? route('owner.cart.add', $product) : null,
        ];
    }

    private function speciesKey(?string $species): ?string
    {
        $value = strtolower(trim((string) $species));

        foreach (array_keys(self::TOKENS) as $key) {
            if (str_contains($value, $key)) {
                return $key;
            }
        }

        return null;
    }

    private function score(Product $product, ?string $species, ?int $age): int
    {
        if ($species === null) {
            return 0;
        }

        $haystack = strtolower($product->name.' '.$product->description);

        foreach (self::TOKENS as $key => $tokens) {
            if ($key === $species) {
                continue;
            }

            foreach ($tokens as $token) {
                if (str_contains($haystack, $token)) {
                    return 0;
                }
            }
        }

        $matched = false;

        foreach (self::TOKENS[$species] as $token) {
            if (str_contains($haystack, $token)) {
                $matched = true;
                break;
            }
        }

        if (! $matched) {
            return 0;
        }

        $score = 5;
        $young = $age !== null && $age <= 1;
        $senior = $age !== null && $age >= 7;
        $adult = ! $young && ! $senior;

        if ($young && in_array($product->category, [ProductCategory::Toys, ProductCategory::Training], true)) {
            $score += 2;
        }

        if ($senior && $product->category === ProductCategory::Health) {
            $score += 2;
        }

        if ($adult && $product->category === ProductCategory::Food) {
            $score += 1;
        }

        return $score;
    }
}
