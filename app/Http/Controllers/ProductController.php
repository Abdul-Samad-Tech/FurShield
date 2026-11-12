<?php

namespace App\Http\Controllers;

use App\Enums\ProductCategory;
use App\Models\Feedback;
use App\Models\Product;
use App\Services\ShopCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, ShopCatalogService $catalog): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($request->filled('sort'), function ($q) use ($request): void {
                match ($request->string('sort')->toString()) {
                    'price_asc' => $q->orderBy('price'),
                    'price_desc' => $q->orderByDesc('price'),
                    'rating' => $q->orderByDesc('average_rating'),
                    default => $q->latest(),
                };
            }, fn ($q) => $q->latest())
            ->paginate(12)
            ->withQueryString();

        $categories = ProductCategory::cases();
        $user = $request->user();
        $recommendation = ['pet' => null, 'products' => collect()];
        $buyAgain = collect();

        if ($user?->isOwner()) {
            $recommendation = $catalog->recommendation($user);
            $buyAgain = $catalog->buyAgain($user);
        }

        $cards = [];

        foreach ($products as $product) {
            $cards[(string) $product->id] = $catalog->card($product, $user);
        }

        foreach ($recommendation['products'] as $product) {
            $cards[(string) $product->id] = $catalog->card($product, $user);
        }

        foreach ($buyAgain as $product) {
            $cards[(string) $product->id] = $catalog->card($product, $user);
        }

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'recommendation' => $recommendation,
            'buyAgain' => $buyAgain,
            'catalog' => $cards,
        ]);
    }

    public function show(Request $request, Product $product, ShopCatalogService $catalog): View
    {
        $feedbacks = Feedback::query()
            ->where('target_type', Product::class)
            ->where('target_id', $product->id)
            ->with('user')
            ->latest()
            ->get();

        $card = $catalog->card($product, $request->user());

        return view('products.show', compact('product', 'feedbacks', 'card'));
    }

    public function rate(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        Feedback::query()->updateOrCreate(
            [
                'user_id' => Auth::id(),
                'target_type' => Product::class,
                'target_id' => $product->id,
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        $avg = Feedback::query()
            ->where('target_type', Product::class)
            ->where('target_id', $product->id)
            ->avg('rating');

        $product->update(['average_rating' => round((float) $avg, 2)]);

        return back()->with('success', 'Thanks for your rating.');
    }
}
