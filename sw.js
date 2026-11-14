/* Vision Guard service worker. Bump VERSION on every deploy. */
const VERSION = 'vg-v11';
const STATIC = `${VERSION}-static`;
const PAGES = `${VERSION}-pages`;
const IMAGES = `${VERSION}-img`;
const PRECACHE = ['/offline.html', '/icons/icon-192.png', '/pwa.css', '/pwa.js'];
const MAX_PAGES = 40;
const MAX_IMAGES = 150;

async function trim(name, max) {
  const c = await caches.open(name);
  const keys = await c.keys();
  for (let i = 0; i < keys.length - max; i++) await c.delete(keys[i]);
}

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(STATIC).then((c) => c.addAll(PRECACHE.map((u) => new Request(u, { cache: 'reload' })))).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => !k.startsWith(VERSION)).map((k) => caches.delete(k))))
      .then(() => (self.registration.navigationPreload ? self.registration.navigationPreload.enable() : null))
      .then(() => self.clients.claim())
  );
});

const skip = (url) =>
  url.pathname.startsWith('/admin') ||
  url.pathname.includes('checkout') ||
  url.pathname.includes('login') ||
  url.pathname.includes('/api/') ||
  url.pathname.endsWith('.php');

self.addEventListener('fetch', (e) => {
  const req = e.request;
  const url = new URL(req.url);
  if (req.method !== 'GET' || url.origin !== location.origin || skip(url)) return;

  // Pages: network first (with navigation preload for speed), fall back to cache, then offline page.
  if (req.mode === 'navigate') {
    e.respondWith((async () => {
      try {
        const res = (await e.preloadResponse) || (await fetch(req));
        if (res && res.ok) {
          const copy = res.clone();
          caches.open(PAGES).then((c) => c.put(req, copy)).then(() => trim(PAGES, MAX_PAGES));
        }
        return res;
      } catch (_) {
        return (await caches.match(req)) || (await caches.match('/offline.html'));
      }
    })());
    return;
  }

  // Images: cache first, capped.
  if (req.destination === 'image') {
    e.respondWith(
      caches.match(req).then((hit) => hit || fetch(req).then((res) => {
        const copy = res.clone();
        caches.open(IMAGES).then(async (c) => {
          await c.put(req, copy);
          await trim(IMAGES, MAX_IMAGES);
        });
        return res;
      }))
    );
    return;
  }

  // CSS/JS/fonts: stale while revalidate.
  e.respondWith(
    caches.match(req).then((hit) => {
      const net = fetch(req, { cache: 'no-cache' }).then((res) => { const copy = res.clone(); caches.open(STATIC).then((c) => c.put(req, copy)); return res; }).catch(() => hit);
      return hit || net;
    })
  );
});

self.addEventListener('message', (e) => { if (e.data === 'SKIP_WAITING') self.skipWaiting(); });

// Push notifications (needs server-side sending, see INSTALL.md)
self.addEventListener('push', (e) => {
  let d = {};
  try { d = e.data ? e.data.json() : {}; } catch (_) {}
  e.waitUntil(self.registration.showNotification(d.title || 'Vision Guard', {
    body: d.body || 'عرض جديد على كاميرات المراقبة',
    icon: '/icons/icon-192.png',
    badge: '/icons/icon-192.png',
    dir: 'rtl', lang: 'ar',
    data: { url: d.url || '/' }
  }));
});

self.addEventListener('notificationclick', (e) => {
  e.notification.close();
  const target = (e.notification.data && e.notification.data.url) || '/';
  e.waitUntil(clients.matchAll({ type: 'window' }).then((list) => {
    for (const c of list) if ('focus' in c) { c.navigate(target); return c.focus(); }
    return clients.openWindow(target);
  }));
});
