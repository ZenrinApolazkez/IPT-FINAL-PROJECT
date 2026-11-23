<?php

namespace App\Providers;

use App\Models\Appointment;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\View;

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
        RateLimiter::for('login', function (Request $request): Limit {
            $username = Str::transliterate(Str::lower($request->string('username')->toString()));

            return Limit::perMinute(5)->by($username.'|'.$request->ip());
        });

        ViewFacade::composer('layouts.app', function (View $view): void {
            $user = auth()->user();
            $pendingAppointmentCount = $user && in_array($user->role, ['admin', 'staff'], true)
                ? Appointment::query()->where('status', 'pending')->count()
                : 0;

            $view->with('pendingAppointmentCount', $pendingAppointmentCount);
        });
    }
}
