<?php

namespace App\Http\Controllers\Owner;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $carts) {}

    public function index(): View
    {
        $order = Order::query()
            ->where('owner_id', auth()->id())
            ->where('status', OrderStatus::Cart)
            ->with('items.product')
            ->first();

        return view('owner.cart.index', compact('order'));
    }

    public function showItem(OrderItem $orderItem): RedirectResponse
    {
        $cart = Order::query()
            ->where('owner_id', auth()->id())
            ->where('status', OrderStatus::Cart)
            ->first();

        abort_unless($cart !== null && $orderItem->order_id === $cart->id, 403);

        return redirect()->route('owner.cart.index');
    }

    public function add(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $message = $this->carts->add($request->user(), $product, $validated['quantity']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'cart' => $this->carts->snapshot($request->user()),
            ]);
        }

        return back()->with('success', $message);
    }

    public function update(Request $request, OrderItem $orderItem): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $message = $this->carts->update($request->user(), $orderItem, $validated['quantity']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'cart' => $this->carts->snapshot($request->user()),
            ]);
        }

        return back()->with('success', $message);
    }

    public function remove(Request $request, OrderItem $orderItem): JsonResponse|RedirectResponse
    {
        $message = $this->carts->remove($request->user(), $orderItem);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'cart' => $this->carts->snapshot($request->user()),
            ]);
        }

        return back()->with('success', $message);
    }

    public function checkout(Request $request): JsonResponse|RedirectResponse
    {
        $message = $this->carts->checkout($request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('owner.cart.orders'),
            ]);
        }

        return redirect()->route('owner.cart.orders')->with('success', $message);
    }

    public function orders(): View
    {
        $orders = Order::query()
            ->where('owner_id', auth()->id())
            ->where('status', OrderStatus::Placed)
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return view('owner.cart.orders', compact('orders'));
    }
}
