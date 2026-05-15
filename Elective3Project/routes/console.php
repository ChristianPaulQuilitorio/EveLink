<?php

use App\Http\Controllers\DashboardController;
use App\Models\Event;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('pagecache:warm', function () {
    $baseUrl = rtrim((string) config('app.url'), '/');

    if ($baseUrl === '') {
        $this->error('APP_URL is not configured.');
        return self::FAILURE;
    }

    $paths = [
        '/',
        '/portal',
    ];

    $events = Event::query()
        ->select(['id'])
        ->whereDate('event_date', '>=', now()->toDateString())
        ->orderBy('event_date')
        ->limit(5)
        ->get();

    foreach ($events as $event) {
        $paths[] = '/portal/events/' . $event->id;
    }

    $warmStatus = [];

    foreach (array_unique($paths) as $path) {
        try {
            $response = Http::baseUrl($baseUrl)->timeout(20)->get($path);
            $warmStatus[] = $path . ':' . $response->status();
        } catch (\Throwable $e) {
            $warmStatus[] = $path . ':error';
        }
    }

    app(DashboardController::class)->index();

    $this->info('Warmed pages: ' . implode(', ', $warmStatus));

    return self::SUCCESS;
})->purpose('Warm the important cached pages after deployment');
