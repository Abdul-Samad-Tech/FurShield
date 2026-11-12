@props(['id'])

<div
    role="tabpanel"
    id="fs-panel-{{ $id }}"
    aria-labelledby="fs-tab-{{ $id }}"
    x-show='active === @json($id)'
    x-cloak
    class="pt-fs-sm text-sm text-text"
>
    {{ $slot }}
</div>
