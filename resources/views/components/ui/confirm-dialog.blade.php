@props([
    'name' => 'confirm',
    'title',
    'message' => null,
    'confirm' => 'Confirm',
    'cancel' => 'Cancel',
    'action' => null,
    'method' => 'POST',
    'variant' => 'danger',
])

<x-ui.modal :name="$name" :title="$title">
    @isset($trigger)
        <x-slot:trigger>{{ $trigger }}</x-slot:trigger>
    @endisset

    @if ($message)
        <p class="text-sm text-text-muted">{{ $message }}</p>
    @endif

    {{ $slot }}

    @if ($action)
        <form method="POST" action="{{ $action }}" class="mt-fs-md flex flex-wrap justify-end gap-fs-2xs">
            @csrf
            @if (strtoupper($method) !== 'POST')
                @method($method)
            @endif
            <x-ui.button type="button" variant="ghost" @click="close()">{{ $cancel }}</x-ui.button>
            <x-ui.button type="submit" :variant="$variant">{{ $confirm }}</x-ui.button>
        </form>
    @else
        <form
            class="mt-fs-md flex flex-wrap justify-end gap-fs-2xs"
            @submit.prevent='close(); $dispatch("fs-toast", { message: @json($confirm." confirmed."), variant: "success" })'
        >
            <x-ui.button type="button" variant="ghost" @click="close()">{{ $cancel }}</x-ui.button>
            <x-ui.button type="submit" :variant="$variant">{{ $confirm }}</x-ui.button>
        </form>
    @endif
</x-ui.modal>
