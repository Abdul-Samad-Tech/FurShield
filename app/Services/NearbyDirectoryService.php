<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Feedback;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class NearbyDirectoryService
{
    public function __construct(private AppointmentService $appointments) {}

    public function haversineKm(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $earth = 6371.0;
        $latDelta = deg2rad($toLat - $fromLat);
        $lngDelta = deg2rad($toLng - $fromLng);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($lngDelta / 2) ** 2;

        return $earth * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    public function openNow(User $vet, ?CarbonInterface $moment = null): bool
    {
        $moment = $this->moment($moment);
        $minutes = ($moment->hour * 60) + $moment->minute;

        foreach ($this->appointments->windows($vet) as $window) {
            if ($window['weekday'] !== $moment->dayOfWeekIso) {
                continue;
            }

            $start = $this->minutesPastMidnight($window['start']);
            $end = $this->minutesPastMidnight($window['end']);

            if ($minutes >= $start && $minutes < $end) {
                return true;
            }
        }

        return false;
    }

    public function availableToday(User $vet, ?CarbonInterface $moment = null): bool
    {
        $moment = $this->moment($moment);

        foreach ($this->appointments->windows($vet) as $window) {
            if ($window['weekday'] === $moment->dayOfWeekIso) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{clinics: list<array<string, mixed>>, specializations: list<string>}
     */
    public function vets(
        ?float $latitude,
        ?float $longitude,
        ?string $city,
        ?string $specialization,
        ?float $minRating,
        bool $availableToday,
        ?CarbonInterface $moment = null,
    ): array {
        $moment = $this->moment($moment);
        $vets = User::query()
            ->where('role', UserRole::Vet)
            ->when($city !== null && $city !== '', fn ($query) => $query->where('city', 'like', '%'.$city.'%'))
            ->orderBy('name')
            ->get();

        $specializations = $vets
            ->map(fn (User $vet): string => $vet->specialization ?: 'General practice')
            ->unique()
            ->sort()
            ->values()
            ->all();

        $ratings = $this->ratings($vets->pluck('id'));
        $origin = $latitude !== null && $longitude !== null;

        $clinics = $vets
            ->map(function (User $vet) use ($ratings, $latitude, $longitude, $origin, $moment): array {
                $lat = $vet->latitude !== null ? (float) $vet->latitude : null;
                $lng = $vet->longitude !== null ? (float) $vet->longitude : null;
                $distance = ($origin && $lat !== null && $lng !== null)
                    ? $this->haversineKm($latitude, $longitude, $lat, $lng)
                    : null;
                $rating = $ratings[$vet->id] ?? null;

                return [
                    'id' => $vet->id,
                    'name' => $vet->name,
                    'specialization' => $vet->specialization ?: 'General practice',
                    'city' => $vet->city,
                    'phone' => $vet->phone,
                    'tel' => $this->telHref($vet->phone),
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'rating' => $rating,
                    'open_now' => $this->openNow($vet, $moment),
                    'available_today' => $this->availableToday($vet, $moment),
                    'hours' => $this->appointments->summary($vet),
                    'distance_km' => $distance,
                    'distance_label' => $distance === null ? null : $this->distanceLabel($distance),
                    'url' => route('vets.show', $vet),
                    'directions_url' => $this->directionsUrl($lat, $lng, $origin ? $latitude : null, $origin ? $longitude : null),
                ];
            })
            ->when($specialization !== null && $specialization !== '', fn (Collection $clinics) => $clinics->where('specialization', $specialization))
            ->when($minRating !== null, fn (Collection $clinics) => $clinics->filter(
                fn (array $clinic): bool => $clinic['rating'] !== null && $clinic['rating'] >= $minRating
            ))
            ->when($availableToday, fn (Collection $clinics) => $clinics->where('available_today', true))
            ->sortBy([
                [fn (array $left, array $right): int => ($left['distance_km'] === null) <=> ($right['distance_km'] === null)],
                [fn (array $left, array $right): int => ($left['distance_km'] ?? INF) <=> ($right['distance_km'] ?? INF)],
                [fn (array $left, array $right): int => $left['name'] <=> $right['name']],
            ])
            ->values()
            ->all();

        return [
            'clinics' => $clinics,
            'specializations' => $specializations,
        ];
    }

    private function moment(?CarbonInterface $moment): CarbonInterface
    {
        return ($moment ?? now())->timezone(config('app.timezone'));
    }

    /**
     * @param  Collection<int, int|string>  $ids
     * @return array<int, float>
     */
    private function ratings(Collection $ids): array
    {
        if ($ids->isEmpty()) {
            return [];
        }

        return Feedback::query()
            ->selectRaw('target_id, AVG(rating) as average_rating')
            ->where('target_type', User::class)
            ->whereIn('target_id', $ids)
            ->groupBy('target_id')
            ->get()
            ->mapWithKeys(fn ($row): array => [(int) $row->target_id => round((float) $row->average_rating, 1)])
            ->all();
    }

    private function minutesPastMidnight(string $clock): int
    {
        [$hour, $minute] = array_map('intval', explode(':', $clock));

        return ($hour * 60) + $minute;
    }

    private function distanceLabel(float $km): string
    {
        if ($km < 0.05) {
            return 'Nearby';
        }

        if ($km < 1) {
            return (string) (int) round($km * 1000).' m';
        }

        return number_format($km, 1).' km';
    }

    private function telHref(?string $phone): ?string
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return null;
        }

        $digits = preg_replace('/[^\d+]/', '', $phone) ?? '';

        if ($digits === '' || $digits === '+') {
            return null;
        }

        return 'tel:'.$digits;
    }

    private function directionsUrl(?float $lat, ?float $lng, ?float $fromLat, ?float $fromLng): ?string
    {
        if ($lat === null || $lng === null) {
            return null;
        }

        $to = rawurlencode($lat.','.$lng);
        $url = 'https://www.openstreetmap.org/directions?to='.$to;

        if ($fromLat !== null && $fromLng !== null) {
            $url .= '&from='.rawurlencode($fromLat.','.$fromLng);
        }

        return $url;
    }
}
