<?php

namespace App\Providers;

use App\Models\AppNotification;
use App\Services\CartService;
use App\Services\DashboardNavigation;
use App\Services\ShellBadgeService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('pet-public', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('pet-found', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by((string) ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('assistant', function (Request $request) {
            $owner = $request->user()?->getAuthIdentifier() ?: $request->ip();

            return Limit::perMinute(max(1, (int) config('assistant.per_minute')))->by('assistant:'.$owner);
        });

        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();
            $badges = app(ShellBadgeService::class)->forUser($user);
            $items = $user ? app(DashboardNavigation::class)->items($user, $badges) : [];

            $latestId = 0;

            if ($user) {
                $latestId = (int) (AppNotification::query()
                    ->where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->max('id') ?? 0);
            }

            $view->with([
                'shellBadges' => $badges,
                'dashboardNav' => $items,
                'alertLatestId' => $latestId,
                'cartState' => $user?->isOwner() ? app(CartService::class)->snapshot($user) : null,
            ]);
        });
    }
}
