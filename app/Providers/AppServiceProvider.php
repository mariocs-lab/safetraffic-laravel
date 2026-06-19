<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Wajib ditambahkan

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

public function boot(): void
    {
        // Mutlak paksa HTTPS tanpa syarat
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}