<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\UrlGenerator;

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
    public function boot(UrlGenerator $url): void
    {
        // Force HTTPS in production (Render uses X-Forwarded-Proto header)
        if ($this->app->environment('production')) {
            $url->forceScheme('https');
            
            // Ensure APP_URL is HTTPS
            if (env('APP_URL') && !str_starts_with(env('APP_URL'), 'https://')) {
                $url->forceRootUrl(str_replace('http://', 'https://', env('APP_URL')));
            }
        }
    }
}
