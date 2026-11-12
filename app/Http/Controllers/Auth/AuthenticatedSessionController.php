<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login', [
            'demoRoles' => [
                ['label' => UserRole::Owner->label(), 'email' => 'owner@furshield.test', 'icon' => 'user'],
                ['label' => UserRole::Vet->label(), 'email' => 'vet@furshield.test', 'icon' => 'stethoscope'],
                ['label' => UserRole::Shelter->label(), 'email' => 'shelter@furshield.test', 'icon' => 'house'],
                ['label' => UserRole::Admin->label(), 'email' => 'admin@furshield.test', 'icon' => 'layout-dashboard'],
            ],
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $dashboard = $request->user()->role->dashboardRoute();

        return redirect()->intended(route($dashboard, absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
