<?php

namespace App\Queries\Admin;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class ReportingWindow
{
    public function currentStart(): Carbon
    {
        return now()->copy()->startOfDay()->subDays(6);
    }

    public function previousStart(): Carbon
    {
        return now()->copy()->startOfDay()->subDays(13);
    }

    /**
     * @return list<Carbon>
     */
    public function days(): array
    {
        $days = [];

        for ($i = 6; $i >= 0; $i--) {
            $days[] = now()->copy()->startOfDay()->subDays($i);
        }

        return $days;
    }

    /**
     * @return list<Carbon>
     */
    public function weeks(): array
    {
        $current = now()->copy()->startOfWeek(Carbon::MONDAY);
        $weeks = [];

        for ($i = 7; $i >= 0; $i--) {
            $weeks[] = $current->copy()->subWeeks($i);
        }

        return $weeks;
    }

    /**
     * @return list<string>
     */
    public function weekLabels(): array
    {
        return array_map(
            fn (Carbon $week): string => $week->format('M j'),
            $this->weeks(),
        );
    }

    public function inCurrent(CarbonInterface $moment): bool
    {
        return $moment->greaterThanOrEqualTo($this->currentStart());
    }

    public function inPrevious(CarbonInterface $moment): bool
    {
        return $moment->greaterThanOrEqualTo($this->previousStart())
            && $moment->lessThan($this->currentStart());
    }

    /**
     * @param  list<Carbon>  $days
     */
    public function dayIndex(CarbonInterface $moment, array $days): ?int
    {
        $key = $moment->copy()->timezone((string) config('app.timezone'))->toDateString();

        foreach ($days as $index => $day) {
            if ($day->toDateString() === $key) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param  list<Carbon>  $weeks
     */
    public function weekIndex(CarbonInterface $moment, array $weeks): ?int
    {
        foreach ($weeks as $index => $start) {
            $end = $start->copy()->addWeek();

            if ($moment->greaterThanOrEqualTo($start) && $moment->lessThan($end)) {
                return $index;
            }
        }

        return null;
    }
}
