<?php

namespace App\Http\Controllers\Owner;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Order;
use App\Services\OrderCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private OrderCheckoutService $checkout) {}

    public function create(Request $request): View
    {
        $order = Order::query()
            ->where('owner_id', $request->user()->id)
            ->where('status', OrderStatus::Cart)
            ->with('items.product')
            ->first();

        return view('owner.checkout.create', [
            'order' => $order,
            'user' => $request->user(),
        ]);
    }

    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        $order = $this->checkout->place($request->user(), $request->validated());

        return redirect()
            ->route('owner.orders.show', $order)
            ->with('success', 'Order placed. Your receipt is below.');
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->owner_id === $request->user()->id, 404);
        abort_if($order->status === OrderStatus::Cart, 404);

        $order->load(['items.product', 'payment']);

        return view('owner.orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->checkout->cancel($request->user(), $order);

        return redirect()
            ->route('owner.orders.show', $order)
            ->with('success', 'Order cancelled. Stock has been returned.');
    }
}
