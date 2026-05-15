const CACHE_NAME = 'evelink-v2';
const CORE_ASSETS = [
  '/',
  '/manifest.webmanifest',
  '/css/app.css',
  '/offline.html',
  '/icons/dashboard.svg',
  '/icons/event.svg',
  '/icons/registrations.svg',
  '/icons/attendance.svg',
  '/icons/app-icon.svg',
  '/icons/app-icon-maskable.svg'
];

const STATIC_PATHS = [
  '/css/',
  '/build/',
  '/icons/'
];

const STATIC_EXTENSIONS = ['.css', '.js', '.mjs', '.svg', '.png', '.jpg', '.jpeg', '.webp', '.gif', '.ico', '.json'];

const isStaticAsset = (request) => {
  const url = new URL(request.url);

  if (STATIC_PATHS.some((path) => url.pathname.startsWith(path))) {
    return true;
  }

  return STATIC_EXTENSIONS.some((extension) => url.pathname.endsWith(extension));
};

const cacheFirst = async (request) => {
  const cached = await caches.match(request);

  if (cached) {
    return cached;
  }

  const response = await fetch(request);
  const cache = await caches.open(CACHE_NAME);
  cache.put(request, response.clone());
  return response;
};

const networkFirst = async (request) => {
  try {
    const response = await fetch(request);
    const cache = await caches.open(CACHE_NAME);
    cache.put(request, response.clone());
    return response;
  } catch (error) {
    const cached = await caches.match(request);
    if (cached) {
      return cached;
    }

    throw error;
  }
};

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(CORE_ASSETS))
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
    ))
  );
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') {
    return;
  }

  if (isStaticAsset(event.request)) {
    event.respondWith(cacheFirst(event.request));
    return;
  }

  if (event.request.mode === 'navigate') {
    event.respondWith(networkFirst(event.request).catch(() => caches.match('/offline.html')));
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then(response => {
        const clone = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
        return response;
      })
      .catch(() => caches.match(event.request).then(cached => cached || caches.match('/offline.html')))
  );
});
