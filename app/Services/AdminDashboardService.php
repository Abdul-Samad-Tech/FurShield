<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Queries\Admin\ActivityFeedQuery;
use App\Queries\Admin\AppointmentFunnelQuery;
use App\Queries\Admin\KpiQuery;
use App\Queries\Admin\OrderRevenueQuery;
use App\Queries\Admin\RegistrationSeriesQuery;
use App\Queries\Admin\TopProductsQuery;
use App\Queries\Admin\UserDirectoryQuery;
use Illuminate\Http\Request;

class AdminDashboardService
{
    public function __construct(
        private KpiQuery $kpis,
        private RegistrationSeriesQuery $registrations,
        private AppointmentFunnelQuery $funnel,
        private OrderRevenueQuery $revenue,
        private TopProductsQuery $products,
        private ActivityFeedQuery $activity,
        private UserDirectoryQuery $users,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $charts = [
            'registrations' => $this->registrations->series(),
            'funnel' => $this->funnel->series(),
            'orders' => $this->revenue->series(),
            'products' => $this->products->series(),
        ];

        return [
            'kpis' => $this->kpis->cards(),
            'charts' => $charts,
            'activity' => $this->activity->recent(),
            'users' => $this->users->paginate($request),
            'filters' => $this->users->filters($request),
            'roles' => UserRole::cases(),
        ];
    }
}
