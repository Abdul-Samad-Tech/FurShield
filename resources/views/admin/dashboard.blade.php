@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('content')
@php
    $sortUrl = function (string $column) use ($filters): string {
        $direction = $filters['sort'] === $column && $filters['direction'] === 'asc' ? 'desc' : 'asc';

        return route('admin.dashboard', array_filter([
            'q' => $filters['q'],
            'role' => $filters['role'],
            'sort' => $column,
            'direction' => $direction,
        ], fn (string $value): bool => $value !== ''));
    };
    $ariaSort = function (string $column) use ($filters): string {
        if ($filters['sort'] !== $column) {
            return 'none';
        }

        return $filters['direction'] === 'asc' ? 'ascending' : 'descending';
    };
@endphp

<div data-reveal>
    <div class="flex flex-wrap items-end justify-between gap-fs-sm">
        <div>
            <h1 class="font-display text-4xl font-bold text-text">Admin overview</h1>
            <p class="mt-fs-2xs max-w-2xl text-text-muted">Welcome back, {{ auth()->user()->name }}. Counts and deltas cover the last 7 days, compared with the 7 days before that.</p>
        </div>
        <div class="flex flex-wrap gap-fs-2xs">
            <x-ui.button variant="secondary" :href="route('admin.user-privileges')">User privileges</x-ui.button>
            <x-ui.button variant="secondary" :href="route('admin.privileges')">Role matrix</x-ui.button>
        </div>
    </div>
</div>

<div class="mt-fs-md grid gap-fs-sm sm:grid-cols-2 xl:grid-cols-4" data-reveal>
    @foreach ($kpis as $kpi)
        <x-ui.stat-card
            :label="$kpi['label']"
            :value="$kpi['value']"
            :icon="$kpi['icon']"
            :count="$kpi['count']"
            :delta="$kpi['delta']"
            :trend="$kpi['trend']"
        >
            <x-slot:sparkline>
                <x-ui.sparkline :values="$kpi['sparkline']" :label="$kpi['sparkline_label']" />
            </x-slot:sparkline>
        </x-ui.stat-card>
    @endforeach
</div>

<div class="mt-fs-md grid gap-fs-sm xl:grid-cols-2" data-admin-charts data-currency="{{ \App\Support\Furshield::currencySymbol() }}">
    <x-ui.card>
        <h2 class="font-display text-2xl font-semibold text-text">Registrations by role</h2>
        <p class="mt-fs-3xs text-sm text-text-muted">New accounts in each of the last 8 weeks.</p>
        @if ($charts['registrations']['blank'])
            <x-ui.empty-state icon="user" title="No new accounts" description="No accounts were created in the last 8 weeks." />
        @else
            <x-admin.chart-frame id="registrations">
                <table class="w-full text-left text-sm">
                    <caption class="sr-only">Registrations by role over the last 8 weeks</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="py-fs-2xs pr-fs-sm font-medium text-text">Week of</th>
                            @foreach ($charts['registrations']['series'] as $series)
                                <th scope="col" class="py-fs-2xs pr-fs-sm font-medium text-text">{{ $series['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($charts['registrations']['labels'] as $index => $label)
                            <tr class="border-t border-border">
                                <th scope="row" class="py-fs-2xs pr-fs-sm font-medium text-text">{{ $label }}</th>
                                @foreach ($charts['registrations']['series'] as $series)
                                    <td class="py-fs-2xs pr-fs-sm text-text">{{ $series['values'][$index] }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-admin.chart-frame>
        @endif
    </x-ui.card>

    <x-ui.card>
        <h2 class="font-display text-2xl font-semibold text-text">Appointments by status</h2>
        <p class="mt-fs-3xs text-sm text-text-muted">A snapshot of current visits. It is not a conversion rate.</p>
        @if ($charts['funnel']['blank'])
            <x-ui.empty-state icon="calendar" title="No appointments yet" description="Visit counts will appear here after an owner books." />
        @else
            <x-admin.chart-frame id="funnel">
                <table class="w-full text-left text-sm">
                    <caption class="sr-only">Appointments by status</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="py-fs-2xs pr-fs-sm font-medium text-text">Status</th>
                            <th scope="col" class="py-fs-2xs font-medium text-text">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($charts['funnel']['stages'] as $stage)
                            <tr class="border-t border-border">
                                <th scope="row" class="py-fs-2xs pr-fs-sm font-medium text-text">{{ $stage['label'] }}</th>
                                <td class="py-fs-2xs text-text">{{ $stage['count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-admin.chart-frame>
        @endif
    </x-ui.card>

    <x-ui.card>
        <div class="flex flex-wrap items-start justify-between gap-fs-sm">
            <div>
                <h2 class="font-display text-2xl font-semibold text-text">Orders and revenue</h2>
                <p class="mt-fs-3xs text-sm text-text-muted">Placed orders and their totals by week. Carts are not included.</p>
            </div>
            <x-ui.button variant="secondary" size="sm" :href="route('admin.orders.export')">
                <x-ui.icon name="download" size="sm" />
                Export orders
            </x-ui.button>
        </div>
        <p class="mt-fs-3xs text-sm text-text-muted">The export includes placed and cancelled orders. Open carts are left out.</p>
        @if ($charts['orders']['blank'])
            <x-ui.empty-state icon="shopping-bag" title="No placed orders" description="No placed orders in the last 8 weeks." />
        @else
            <x-admin.chart-frame id="orders">
                <table class="w-full text-left text-sm">
                    <caption class="sr-only">Placed orders and revenue by week</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="py-fs-2xs pr-fs-sm font-medium text-text">Week of</th>
                            <th scope="col" class="py-fs-2xs pr-fs-sm font-medium text-text">Orders</th>
                            <th scope="col" class="py-fs-2xs font-medium text-text">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($charts['orders']['labels'] as $index => $label)
                            <tr class="border-t border-border">
                                <th scope="row" class="py-fs-2xs pr-fs-sm font-medium text-text">{{ $label }}</th>
                                <td class="py-fs-2xs pr-fs-sm text-text">{{ $charts['orders']['orders'][$index] }}</td>
                                <td class="py-fs-2xs text-text">{{ \App\Support\Furshield::money($charts['orders']['revenue'][$index]) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-admin.chart-frame>
        @endif
    </x-ui.card>

    <x-ui.card>
        <h2 class="font-display text-2xl font-semibold text-text">Top products</h2>
        <p class="mt-fs-3xs text-sm text-text-muted">Units sold on placed orders.</p>
        @if ($charts['products']['blank'])
            <x-ui.empty-state icon="shopping-bag" title="No product ranking" description="No placed orders yet, so there is no product ranking." />
        @else
            <x-admin.chart-frame id="products">
                <table class="w-full text-left text-sm">
                    <caption class="sr-only">Top products by units sold</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="py-fs-2xs pr-fs-sm font-medium text-text">Product</th>
                            <th scope="col" class="py-fs-2xs pr-fs-sm font-medium text-text">Units</th>
                            <th scope="col" class="py-fs-2xs font-medium text-text">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($charts['products']['rows'] as $row)
                            <tr class="border-t border-border">
                                <th scope="row" class="py-fs-2xs pr-fs-sm font-medium text-text">{{ $row['name'] }}</th>
                                <td class="py-fs-2xs pr-fs-sm text-text">{{ $row['units'] }}</td>
                                <td class="py-fs-2xs text-text">{{ \App\Support\Furshield::money($row['revenue']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-admin.chart-frame>
        @endif
    </x-ui.card>

    <script type="application/json" id="admin-charts">@json($charts)</script>
</div>

<div class="mt-fs-md grid gap-fs-sm xl:grid-cols-[0.8fr_1.2fr]">
    <x-ui.card>
        <h2 class="font-display text-2xl font-semibold text-text">Recent activity</h2>
        <p class="mt-fs-3xs text-sm text-text-muted">The latest accounts, visits, orders, listings, and interest.</p>
        @if ($activity === [])
            <x-ui.empty-state icon="inbox" title="No activity yet" description="New accounts and care events will show up here." />
        @else
            <ol class="mt-fs-sm grid gap-fs-sm">
                @foreach ($activity as $item)
                    <li class="border-t border-border pt-fs-sm first:border-t-0 first:pt-0">
                        <p class="font-medium text-text">
                            @if ($item['href'])
                                <a href="{{ $item['href'] }}" class="underline decoration-border-strong underline-offset-4 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">{{ $item['title'] }}</a>
                            @else
                                {{ $item['title'] }}
                            @endif
                        </p>
                        <p class="mt-fs-3xs text-sm text-text">{{ $item['meta'] }}</p>
                        <time class="mt-fs-3xs block text-sm text-text-muted" datetime="{{ $item['datetime'] }}">{{ $item['time'] }}</time>
                    </li>
                @endforeach
            </ol>
        @endif
    </x-ui.card>

    <x-ui.card>
        <div class="flex flex-wrap items-start justify-between gap-fs-sm">
            <div>
                <h2 class="font-display text-2xl font-semibold text-text">Accounts</h2>
                <p class="mt-fs-3xs text-sm text-text-muted" role="status">{{ $users->total() }} {{ \Illuminate\Support\Str::plural('account', $users->total()) }}</p>
            </div>
            <x-ui.button variant="secondary" size="sm" :href="route('admin.users.export', array_filter([
                'q' => $filters['q'],
                'role' => $filters['role'],
                'sort' => $filters['sort'],
                'direction' => $filters['direction'],
            ], fn (string $value): bool => $value !== ''))">
                <x-ui.icon name="download" size="sm" />
                Export users
            </x-ui.button>
        </div>

        <form method="GET" action="{{ route('admin.dashboard') }}" class="mt-fs-sm grid gap-fs-sm md:grid-cols-[1.4fr_1fr_auto] md:items-end">
            <x-ui.input id="admin-user-q" name="q" label="Search" :value="$filters['q']" placeholder="Name or email" />
            <x-ui.select id="admin-user-role" name="role" label="Role">
                <option value="">All roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected($filters['role'] === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </x-ui.select>
            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
            <input type="hidden" name="direction" value="{{ $filters['direction'] }}">
            <div class="flex flex-wrap gap-fs-2xs">
                <x-ui.button type="submit">Filter</x-ui.button>
                @if ($filters['q'] !== '' || $filters['role'] !== '')
                    <x-ui.button variant="secondary" :href="route('admin.dashboard', ['sort' => $filters['sort'], 'direction' => $filters['direction']])">Clear</x-ui.button>
                @endif
            </div>
        </form>

        @if ($users->isEmpty())
            <x-ui.empty-state icon="user" title="No accounts match" description="Try another name, email, or role." />
        @else
            <div class="mt-fs-sm overflow-x-auto">
                <table class="w-full min-w-[36rem] text-left text-sm">
                    <caption class="sr-only">Accounts</caption>
                    <thead>
                        <tr class="border-b border-border">
                            <th scope="col" aria-sort="{{ $ariaSort('name') }}" class="py-fs-2xs pr-fs-sm">
                                <a href="{{ $sortUrl('name') }}" class="inline-flex min-h-11 items-center font-medium text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Name</a>
                            </th>
                            <th scope="col" aria-sort="{{ $ariaSort('email') }}" class="py-fs-2xs pr-fs-sm">
                                <a href="{{ $sortUrl('email') }}" class="inline-flex min-h-11 items-center font-medium text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Email</a>
                            </th>
                            <th scope="col" aria-sort="{{ $ariaSort('role') }}" class="py-fs-2xs pr-fs-sm">
                                <a href="{{ $sortUrl('role') }}" class="inline-flex min-h-11 items-center font-medium text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Role</a>
                            </th>
                            <th scope="col" aria-sort="{{ $ariaSort('created_at') }}" class="py-fs-2xs">
                                <a href="{{ $sortUrl('created_at') }}" class="inline-flex min-h-11 items-center font-medium text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus">Joined</a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="border-b border-border">
                                <th scope="row" class="py-fs-xs pr-fs-sm font-medium text-text">{{ $user->name }}</th>
                                <td class="py-fs-xs pr-fs-sm text-text">{{ $user->email }}</td>
                                <td class="py-fs-xs pr-fs-sm">
                                    <x-ui.badge :variant="match ($user->role) {
                                        \App\Enums\UserRole::Owner => 'soft',
                                        \App\Enums\UserRole::Vet => 'warm',
                                        \App\Enums\UserRole::Admin => 'solid',
                                        default => 'neutral',
                                    }">{{ $user->role->label() }}</x-ui.badge>
                                </td>
                                <td class="py-fs-xs text-text">
                                    <time datetime="{{ $user->created_at?->toAtomString() }}">{{ $user->created_at?->format('M j, Y') }}</time>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="mt-fs-sm">{{ $users->links() }}</div>
            @endif
        @endif
    </x-ui.card>
</div>
@endsection
