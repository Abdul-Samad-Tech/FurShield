@if (isset($errors) && $errors->any())
    <div {{ $attributes->merge(['class' => 'mb-fs-sm rounded-fs-md border border-danger/30 bg-danger-soft px-fs-sm py-fs-xs text-sm text-danger']) }} role="alert" tabindex="-1">
        <p class="font-medium">
            {{ $errors->count() === 1 ? 'There is a problem to fix.' : 'There are '.$errors->count().' problems to fix.' }}
        </p>
        <ul class="mt-fs-2xs list-disc space-y-fs-3xs pl-fs-md">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
