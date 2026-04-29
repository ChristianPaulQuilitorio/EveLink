# Event Organizer

Laravel starter for an event organizer app with Supabase wired in from day one.

## Stack

- Laravel 12
- Supabase Auth, Postgres, and Storage
- Laravel HTTP client for Supabase requests

## Supabase setup

Set these values in your local `.env` file before you start building the wireframe:

- `SUPABASE_URL`
- `SUPABASE_ANON_KEY`
- `SUPABASE_SERVICE_ROLE_KEY`
- `SUPABASE_DATABASE_URL`
- `SUPABASE_SCHEMA`
- `SUPABASE_STORAGE_BUCKET`

If you want the app to talk directly to the Supabase database, switch your database connection settings to the Supabase Postgres credentials in `.env`.

## Starter surface

- `config/supabase.php` holds the integration settings.
- `app/Services/SupabaseService.php` provides configured HTTP clients for REST, Auth, and Storage.

## Next step

Send the wireframe and I’ll shape the Laravel views, routes, and Supabase-backed data flow around it.
