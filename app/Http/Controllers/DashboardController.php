<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboards): RedirectResponse|View
    {
        $user = Auth::user();

        return match ($user->role) {
            UserRole::Owner => view('owner.dashboard', $dashboards->owner($user)),
            UserRole::Vet => view('vet.dashboard', $dashboards->vet($user)),
            UserRole::Shelter => view('shelter.dashboard', $dashboards->shelter($user)),
            UserRole::Admin => redirect()->route('admin.dashboard'),
        };
    }
}
