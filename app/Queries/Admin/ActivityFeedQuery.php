<?php

namespace App\Queries\Admin;

use App\Enums\OrderStatus;
use App\Models\AdoptionInterest;
use App\Models\AdoptionListing;
use App\Models\Appointment;
use App\Models\Order;
use App\Models\User;
use App\Support\Furshield;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ActivityFeedQuery
{
    /**
     * @return list<array{title: string, meta: string, time: string, datetime: string, href: ?string}>
     */
    public function recent(): array
    {
        $items = collect()
            ->concat($this->users())
            ->concat($this->appointments())
            ->concat($this->orders())
            ->concat($this->listings())
            ->concat($this->interests())
            ->sortByDesc(fn (array $item): int => $item['stamp'])
            ->take(12)
            ->map(function (array $item): array {
                unset($item['stamp']);

                return $item;
            })
            ->values();

        return $items->all();
    }

    /**
     * @return Collection<int, array{title: string, meta: string, time: string, datetime: string, href: ?string, stamp: int}>
     */
    private function users(): Collection
    {
        return User::query()
            ->latest()
            ->limit(8)
            ->get(['id', 'name', 'role', 'created_at'])
            ->map(fn (User $user): array => $this->item(
                $user->name.' joined',
                $user->role->label(),
                $user->created_at,
                $user->isAdmin() ? null : route('admin.user-privileges.edit', $user),
            ));
    }

    /**
     * @return Collection<int, array{title: string, meta: string, time: string, datetime: string, href: ?string, stamp: int}>
     */
    private function appointments(): Collection
    {
        return Appointment::query()
            ->with(['pet:id,name', 'owner:id,name', 'vet:id,name'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Appointment $appointment): array => $this->item(
                ($appointment->owner?->name ?? 'An owner').' requested a visit for '.($appointment->pet?->name ?? 'a pet'),
                'With '.($appointment->vet?->name ?? 'a vet').' · '.$appointment->status->label(),
                $appointment->created_at,
                null,
            ));
    }

    /**
     * @return Collection<int, array{title: string, meta: string, time: string, datetime: string, href: ?string, stamp: int}>
     */
    private function orders(): Collection
    {
        return Order::query()
            ->where('status', OrderStatus::Placed)
            ->with(['owner:id,name', 'items.product:id,name'])
            ->orderByRaw('COALESCE(order_date, created_at) DESC')
            ->limit(8)
            ->get()
            ->map(fn (Order $order): array => $this->item(
                ($order->owner?->name ?? 'An owner').' placed an order',
                Furshield::money((float) $order->total_amount),
                $order->order_date ?? $order->created_at,
                null,
            ));
    }

    /**
     * @return Collection<int, array{title: string, meta: string, time: string, datetime: string, href: ?string, stamp: int}>
     */
    private function listings(): Collection
    {
        return AdoptionListing::query()
            ->with('shelter:id,name,shelter_name')
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (AdoptionListing $listing): array {
                $shelter = $listing->shelter;
                $name = $shelter?->shelter_name ?: $shelter?->name ?: 'A shelter';

                return $this->item(
                    $name.' published '.$listing->pet_name,
                    $listing->species.' · '.$listing->status->label(),
                    $listing->created_at,
                    route('adopt.show', $listing),
                );
            });
    }

    /**
     * @return Collection<int, array{title: string, meta: string, time: string, datetime: string, href: ?string, stamp: int}>
     */
    private function interests(): Collection
    {
        return AdoptionInterest::query()
            ->with(['owner:id,name', 'listing:id,pet_name'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (AdoptionInterest $interest): array => $this->item(
                ($interest->owner?->name ?? 'An owner').' sent interest in '.($interest->listing?->pet_name ?? 'a listing'),
                'Interest sent to the shelter',
                $interest->created_at,
                $interest->listing ? route('adopt.show', $interest->listing) : null,
            ));
    }

    /**
     * @return array{title: string, meta: string, time: string, datetime: string, href: ?string, stamp: int}
     */
    private function item(string $title, string $meta, ?CarbonInterface $at, ?string $href): array
    {
        $at ??= now();

        return [
            'title' => $title,
            'meta' => $meta,
            'time' => $at->timezone((string) config('app.timezone'))->format('M j, Y g:i A'),
            'datetime' => $at->toAtomString(),
            'href' => $href,
            'stamp' => $at->getTimestamp(),
        ];
    }
}
