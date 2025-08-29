<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Models\CoachingTicket;
use App\Observers\UserObserver;

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
    // Register user observer to ensure programmatic user creation also receives a free ticket
    User::observe(UserObserver::class);

    // Define admin gate: users with is_admin = true
    Gate::define('admin', function (?User $user) {
        return $user && $user->is_admin;
    });
    }
}
