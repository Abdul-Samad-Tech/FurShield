@props([
    'label',
    'name',
    'id' => null,
    'hint' => null,
    'error' => null,
    'rows' => 4,
    'disabled' => false,
    'required' => false,
])

@php
    $fieldId = $id ?: $name;
    $hintId = $hint ? $fieldId.'-hint' : null;
    $errorId = $error ? $fieldId.'-error' : null;
    $describedBy = collect([$hintId, $errorId])->filter()->implode(' ');
    $control = 'block w-full rounded-fs-md border bg-surface-raised px-fs-sm py-fs-xs text-sm text-text shadow-fs-xs placeholder:text-text-muted motion-safe:transition motion-safe:duration-fs-fast motion-safe:ease-fs-standard focus:border-focus focus:outline-none focus:ring-2 focus:ring-focus/40 disabled:cursor-not-allowed disabled:bg-surface-sunken disabled:text-text-muted';
    $control .= $error ? ' border-danger' : ' border-border-strong';
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'grid gap-fs-2xs']) }}>
    <label for="{{ $fieldId }}" class="text-sm font-medium text-text">
        {{ $label }}
        @if ($required)<span class="text-danger" aria-hidden="true">*</span>@endif
    </label>
    <textarea
        id="{{ $fieldId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($error) aria-invalid="true" @endif
        @if ($required) required @endif
        @disabled($disabled)
        {{ $attributes->except('class')->merge(['class' => $control]) }}
    >{{ $slot }}</textarea>
    @if ($hint)
        <p id="{{ $hintId }}" class="text-sm text-text-muted">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $errorId }}" class="text-sm text-danger" role="alert">{{ $error }}</p>
    @endif
</div>
