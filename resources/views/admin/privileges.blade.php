@extends('layouts.dashboard')

@section('title', 'Privileges Matrix')


@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-display text-4xl font-bold">Privileges matrix</h1>
        <p class="mt-2 max-w-2xl text-ink-mute">
            Tick checkboxes to allow features for each role. Rows are privileges, columns are roles.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.privileges.update') }}" class="surface mt-8 overflow-hidden p-0" x-data>
    @csrf
    @method('PUT')

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-left text-sm">
            <thead class="bg-tide text-white">
                <tr>
                    <th class="sticky left-0 z-10 bg-tide px-4 py-4 font-display text-base font-semibold">Privilege</th>
                    @foreach ($roles as $role)
                        <th class="px-4 py-4 text-center font-display text-base font-semibold whitespace-nowrap">
                            {{ $role->label() }}
                            <div class="mt-2">
                                <button
                                    type="button"
                                    class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] uppercase tracking-wide hover:bg-white/25"
                                    @click="
                                        document.querySelectorAll('[data-role=\'{{ $role->value }}\']').forEach((el) => el.checked = true)
                                    "
                                >All</button>
                                <button
                                    type="button"
                                    class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] uppercase tracking-wide hover:bg-white/25"
                                    @click="
                                        document.querySelectorAll('[data-role=\'{{ $role->value }}\']').forEach((el) => el.checked = false)
                                    "
                                >None</button>
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($privileges as $index => $privilege)
                    <tr class="{{ $index % 2 === 0 ? 'bg-white/80' : 'bg-mist/50' }} border-b border-mist-deep/70">
                        <td class="sticky left-0 z-10 bg-inherit px-4 py-3 font-medium text-ink">
                            {{ $privilege->label() }}
                            <div class="text-xs font-normal text-ink-mute">{{ $privilege->value }}</div>
                        </td>
                        @foreach ($roles as $role)
                            @php
                                $checked = $matrix[$role->value][$privilege->value] ?? false;
                            @endphp
                            <td class="px-4 py-3 text-center">
                                <label class="inline-flex cursor-pointer items-center justify-center">
                                    <input
                                        type="checkbox"
                                        name="matrix[{{ $role->value }}][{{ $privilege->value }}]"
                                        value="1"
                                        data-role="{{ $role->value }}"
                                        data-privilege="{{ $privilege->value }}"
                                        class="h-5 w-5 rounded border-mist-deep text-tide focus:ring-tide/40"
                                        @checked($checked)
                                    >
                                </label>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-mist-deep bg-white/70 px-4 py-4">
        <p class="text-sm text-ink-mute">Changes apply immediately after save. Admin always retains full access.</p>
        <button type="submit" class="btn-primary">Save privileges</button>
    </div>
</form>
@endsection
