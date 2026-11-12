@props(['id'])

<div
    class="mt-fs-sm"
    data-chart="{{ $id }}"
    x-data='{"status":"loading","id":@json($id)}'
    x-on:fs-chart.window="if ($event.detail.id === id || $event.detail.id === '*') status = $event.detail.status"
>
    <div x-show="status === 'loading'" x-cloak>
        <x-ui.skeleton />
    </div>
    <p x-show="status === 'error'" x-cloak class="rounded-fs-md border border-danger/30 bg-danger-soft px-fs-sm py-fs-xs text-sm text-text" role="alert">
        This chart could not be loaded. The figures below still have the numbers.
    </p>
    <div data-chart-canvas class="mb-fs-sm" x-show="status === 'ready'" x-cloak></div>
    <div x-show="status !== 'loading'">
        {{ $slot }}
    </div>
</div>
