<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\UrlGenerator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(UrlGenerator $url): void
    {
        if ($this->app->environment('production')) {
            $url->forceScheme('https');

            if (env('APP_URL') && !str_starts_with(env('APP_URL'), 'https://')) {
                $url->forceRootUrl(str_replace('http://', 'https://', env('APP_URL')));
            }
        }
    }
}
