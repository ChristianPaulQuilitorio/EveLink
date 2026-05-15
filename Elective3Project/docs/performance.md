Performance improvements and runbook

Quick runtime commands (run on the server):

php artisan config:cache
php artisan route:cache
php artisan view:cache

Enable PHP opcache in php.ini (production):
- opcache.enable=1
- opcache.memory_consumption=256
- opcache.max_accelerated_files=10000

Server recommendations:
- Enable gzip and/or brotli at the webserver (nginx/Apache) level.
- Serve `public/build` assets from a CDN with long cache TTL and origin-pull.
- Configure HTTP/2 or HTTP/3 for faster multiplexing.

Asset & image work:
- Run `npm run build` (Vite) to emit hashed assets to `public/build` for long caching.
- Convert images to WebP/AVIF and serve via `srcset` or dynamic conversions.
- Use `loading="lazy"` for non-critical images.

Application caching:
- Use a page cache for public pages (done with `PageCache` middleware).
- Cache heavy DB results with `Cache::remember()` and tag-based invalidation.

Monitoring & audit:
- Run Lighthouse audits and schedule them.
- Use a real-user monitoring tool (Sentry, NewRelic, Datadog) for production.

Notes:
- The repository already uses Vite with hashed builds (see resources/views/layouts/*.blade.php)
- I added `public/.htaccess` caching rules and a lightweight `PageCache` middleware.

If you want, I can:
- Add image conversion tooling and an asset optimization script.
- Integrate a cache-busting invalidation hook when events are updated.
- Create nginx configuration snippets for Brotli and caching.
