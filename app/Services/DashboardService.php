<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\OrderStatus;
use App\Models\AdoptionInterest;
use App\Models\AdoptionListing;
use App\Models\Appointment;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    private const DUE_WINDOW_DAYS = 30;

    /**
     * @return array<string, mixed>
     */
    public function owner(User $user): array
    {
        $pets = Pet::query()
            ->where('owner_id', $user->id)
            ->with('healthRecords')
            ->withCount('healthRecords')
            ->orderBy('name')
            ->get();

        $upcoming = Appointment::query()
            ->where('owner_id', $user->id)
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Approved, AppointmentStatus::Rescheduled])
            ->where('appointment_time', '>=', Carbon::now())
            ->with(['pet', 'vet'])
            ->orderBy('appointment_time')
            ->take(5)
            ->get();

        $radar = $this->careRadar($pets);
        $dueCount = collect($radar)->sum(fn (array $items) => count($items));
        $placed = $user->orders()
            ->where('status', OrderStatus::Placed)
            ->latest('order_date')
            ->take(8)
            ->get()
            ->reverse()
            ->values();
        $cartCount = $user->orders()
            ->where('status', OrderStatus::Cart)
            ->withCount('items')
            ->first()?->items_count ?? 0;
        $orderDelta = match (true) {
            $cartCount > 0 => $cartCount.' in the cart',
            $placed->isEmpty() => 'No placed orders yet',
            default => 'Recent order totals',
        };

        return [
            'pets' => $pets,
            'upcoming' => $upcoming,
            'nextAction' => $this->nextAction($pets, $radar, $upcoming),
            'radar' => $radar,
            'dueCount' => $dueCount,
            'placedCount' => $user->orders()->where('status', OrderStatus::Placed)->count(),
            'cartCount' => $cartCount,
            'orderDelta' => $orderDelta,
            'sparkline' => $placed->map(fn ($order) => (float) $order->total_amount)->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function vet(User $user): array
    {
        $open = [AppointmentStatus::Pending, AppointmentStatus::Approved, AppointmentStatus::Rescheduled];
        $today = Appointment::query()
            ->where('vet_id', $user->id)
            ->whereIn('status', $open)
            ->whereBetween('appointment_time', [Carbon::today(), Carbon::today()->endOfDay()])
            ->with(['pet', 'owner'])
            ->orderBy('appointment_time')
            ->get();
        $pending = Appointment::query()
            ->where('vet_id', $user->id)
            ->where('status', AppointmentStatus::Pending)
            ->with(['pet', 'owner'])
            ->orderBy('appointment_time')
            ->get();
        $next = Appointment::query()
            ->where('vet_id', $user->id)
            ->whereIn('status', $open)
            ->where('appointment_time', '>=', Carbon::now())
            ->with(['pet', 'owner'])
            ->orderBy('appointment_time')
            ->first();

        return [
            'today' => $today,
            'pending' => $pending,
            'next' => $next,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function shelter(User $user): array
    {
        $interests = AdoptionInterest::query()
            ->whereHas('listing', fn ($query) => $query->where('shelter_id', $user->id))
            ->with(['listing', 'owner'])
            ->latest()
            ->get();

        return [
            'listingCount' => AdoptionListing::query()->where('shelter_id', $user->id)->count(),
            'interestCount' => $interests->count(),
            'stages' => $this->funnelStages($interests),
            'rejectedCount' => $interests->where('status', 'rejected')->count(),
            'interests' => $interests->take(6),
        ];
    }

    /**
     * @param  Collection<int, Pet>  $pets
     * @return array<int, list<array<string, mixed>>>
     */
    private function careRadar(Collection $pets): array
    {
        $radar = [];

        foreach ($pets as $pet) {
            $items = [];

            foreach ($pet->healthRecords as $record) {
                if ($record->next_due_date === null) {
                    continue;
                }

                $days = $this->daysUntil($record->next_due_date);
                if ($days > self::DUE_WINDOW_DAYS) {
                    continue;
                }

                $items[] = $this->careItem($pet, $record, $days);
            }

            usort($items, fn (array $left, array $right) => $left['days'] <=> $right['days']);
            $radar[$pet->id] = $items;
        }

        return $radar;
    }

    /**
     * @param  Collection<int, Pet>  $pets
     * @param  array<int, list<array<string, mixed>>>  $radar
     * @param  Collection<int, Appointment>  $upcoming
     * @return array{title: string, body: string, label: string, href: string, tone: string}
     */
    private function nextAction(Collection $pets, array $radar, Collection $upcoming): array
    {
        if ($pets->isEmpty()) {
            return [
                'title' => 'Add your first pet',
                'body' => 'Health, visits, and reminders start with a pet record.',
                'label' => 'Add pet',
                'href' => route('owner.pets.create'),
                'tone' => 'calm',
            ];
        }

        $urgent = collect($radar)
            ->flatMap(fn (array $items) => $items)
            ->sortBy('days')
            ->first();

        if (is_array($urgent)) {
            return [
                'title' => $urgent['pet']->name."'s ".$urgent['title'].' '.$urgent['phrase'],
                'body' => 'Book a visit so this stays on the record.',
                'label' => 'Book now',
                'href' => route('owner.appointments.create', [
                    'pet_id' => $urgent['pet']->id,
                    'reason' => $urgent['title'],
                ]),
                'tone' => $urgent['days'] < 0 ? 'danger' : 'warning',
            ];
        }

        $later = $this->soonestCare($pets);

        if (is_array($later)) {
            return [
                'title' => $later['pet']->name."'s ".$later['title'].' '.$later['phrase'],
                'body' => 'Nothing is due in the next 30 days. This is the next date on the record.',
                'label' => 'View record',
                'href' => route('owner.pets.health', $later['pet']),
                'tone' => 'calm',
            ];
        }

        $visit = $upcoming->first();

        if ($visit !== null) {
            $when = $visit->appointment_time->timezone(config('app.timezone'))->format('M j, g:i A');

            return [
                'title' => $visit->pet->name.' sees '.$visit->vet->name.' on '.$when,
                'body' => $visit->reason ?: 'This visit is on the schedule.',
                'label' => 'View visit',
                'href' => route('owner.appointments.index'),
                'tone' => 'calm',
            ];
        }

        $pet = $pets->first();

        return [
            'title' => $pet->name.' is on track',
            'body' => 'Nothing is due in the next 30 days.',
            'label' => 'Add a record',
            'href' => route('owner.pets.health', $pet),
            'tone' => 'calm',
        ];
    }

    /**
     * @param  Collection<int, Pet>  $pets
     * @return array<string, mixed>|null
     */
    private function soonestCare(Collection $pets): ?array
    {
        $soonest = null;

        foreach ($pets as $pet) {
            foreach ($pet->healthRecords as $record) {
                if ($record->next_due_date === null) {
                    continue;
                }

                $item = $this->careItem($pet, $record, $this->daysUntil($record->next_due_date));
                if ($soonest === null || $item['days'] < $soonest['days']) {
                    $soonest = $item;
                }
            }
        }

        return $soonest;
    }

    /**
     * @return array<string, mixed>
     */
    private function careItem(Pet $pet, HealthRecord $record, int $days): array
    {
        $overdue = $days < 0;

        return [
            'pet' => $pet,
            'title' => $record->title,
            'due' => $record->next_due_date,
            'days' => $days,
            'phrase' => $this->duePhrase($days),
            'label' => $overdue ? 'Overdue' : 'Due soon',
            'variant' => $overdue ? 'danger' : 'warning',
        ];
    }

    private function duePhrase(int $days): string
    {
        if ($days < 0) {
            $count = abs($days);

            return $count === 1 ? 'was due yesterday' : "was due {$count} days ago";
        }

        if ($days === 0) {
            return 'is due today';
        }

        if ($days === 1) {
            return 'is due tomorrow';
        }

        return "is due in {$days} days";
    }

    private function daysUntil(Carbon $due): int
    {
        return (int) round(Carbon::today()->diffInDays($due->copy()->startOfDay(), false));
    }

    /**
     * @param  Collection<int, AdoptionInterest>  $interests
     * @return list<array{key: string, label: string, count: int, width: int, fill: string}>
     */
    private function funnelStages(Collection $interests): array
    {
        $definitions = [
            'pending' => ['Interested', 'fill-tide'],
            'accepted' => ['Accepted', 'fill-tide-bright'],
            'finalized' => ['Adopted', 'fill-bloom'],
        ];
        $counts = [];

        foreach ($definitions as $key => $definition) {
            $counts[$key] = $interests->where('status', $key)->count();
        }

        $max = max($counts) ?: 1;
        $stages = [];

        foreach ($definitions as $key => [$label, $fill]) {
            $stages[] = [
                'key' => $key,
                'label' => $label,
                'count' => $counts[$key],
                'width' => $counts[$key] === 0 ? 0 : max(18, (int) round(($counts[$key] / $max) * 100)),
                'fill' => $fill,
            ];
        }

        return $stages;
    }
}
