@props([
    'values' => [],
    'label' => 'Trend',
])

@php
    $numbers = collect($values)->map(fn ($value) => (float) $value)->values();

    if ($numbers->count() < 2) {
        $numbers->prepend(0);
    }

    $min = (float) $numbers->min();
    $max = (float) $numbers->max();
    $span = $max - $min;
    $width = 120;
    $height = 36;
    $last = $numbers->count() - 1;
    $points = $numbers->map(function (float $value, int $index) use ($min, $span, $width, $height, $last): string {
        $x = $last === 0 ? 0 : ($index / $last) * $width;
        $ratio = $span == 0.0 ? 0.5 : ($value - $min) / $span;
        $y = $height - 4 - ($ratio * ($height - 8));

        return round($x, 1).','.round($y, 1);
    })->implode(' ');
    $summary = $numbers->map(fn (float $value) => rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.'))->implode(', ');
@endphp

<svg viewBox="0 0 120 36" class="h-9 w-full" fill="none" role="img" aria-label="{{ $label }}: {{ $summary }}">
    <polyline points="{{ $points }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
</svg>
