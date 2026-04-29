<?php

return [
    'url' => env('SUPABASE_URL', ''),
    'anon_key' => env('SUPABASE_ANON_KEY', ''),
    'secret_key' => env('SUPABASE_SECRET_KEY', ''),
    'service_role_key' => env('SUPABASE_SERVICE_ROLE_KEY', ''),
    'database_url' => env('SUPABASE_DATABASE_URL', env('DATABASE_URL', '')),
    'schema' => env('SUPABASE_SCHEMA', 'public'),
    'storage_bucket' => env('SUPABASE_STORAGE_BUCKET', 'event-assets'),
];