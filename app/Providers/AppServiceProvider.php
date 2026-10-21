<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', function ($user = null) {
            // sesuaikan dengan data session login Anda
            return in_array(session('username'), ['administrator'])
                || strtolower(session('akses')) === 'superadmin';
        });
    }
}
