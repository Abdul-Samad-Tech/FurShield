<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $userRole = $user->role instanceof UserRole
            ? $user->role->value
            : (string) $user->role;

        $allowedRoles = collect($roles)
            ->map(fn (string $role): string => strtolower(trim($role)))
            ->all();

        if (! in_array(strtolower($userRole), $allowedRoles, true)) {
            if (in_array('admin', $allowedRoles, true)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->with('status', 'Admin access required. Login with admin@furshield.test / password');
            }

            return redirect()
                ->route($user->role->dashboardRoute())
                ->with('success', 'You do not have access to that area.');
        }

        return $next($request);
    }
}
