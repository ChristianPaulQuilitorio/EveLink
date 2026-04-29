<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SupabaseService
{
    public function rest(?string $path = null): PendingRequest
    {
        $baseUrl = rtrim((string) config('supabase.url'), '/') . '/rest/v1';

        if ($path !== null && $path !== '') {
            $baseUrl .= '/' . ltrim($path, '/');
        }

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->withHeaders($this->headers());
    }

    public function auth(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('supabase.url'), '/') . '/auth/v1')
            ->acceptJson()
            ->withHeaders($this->headers());
    }

    public function storage(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('supabase.url'), '/') . '/storage/v1')
            ->acceptJson()
            ->withHeaders($this->headers());
    }

    protected function headers(): array
    {
        $apiKey = (string) (
            config('supabase.secret_key')
            ?: config('supabase.service_role_key')
            ?: config('supabase.anon_key')
        );

        return array_filter([
            'apikey' => $apiKey,
            'Authorization' => $apiKey !== '' ? 'Bearer ' . $apiKey : null,
            'X-Client-Info' => 'laravel-event-organizer',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Accept-Profile' => config('supabase.schema', 'public'),
            'Content-Profile' => config('supabase.schema', 'public'),
        ]);
    }
}