<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;

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
        // 1. REGISTRASI ALIAS LAYOUT (Solusi Error Browser Halaman Admin/Pendidik)
        Blade::component('layouts.app', 'layouts.app');
        Blade::component('layouts.app', 'app-layout'); // Taktik sapu jagat halaman lama

        // 2. GATE BYPASS SPATIE (Solusi Error Pest Bagian Sysadmin)
        // Membuat role 'sysadmin' otomatis lolos semua pengecekan $user->can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('sysadmin') ? true : null;
        });
    }
}
