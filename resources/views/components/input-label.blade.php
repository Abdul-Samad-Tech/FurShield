@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-text-muted']) }}>
    {{ $value ?? $slot }}
</label>
