/// <reference lib="webworker" />
import { precacheAndRoute, cleanupOutdatedCaches, createHandlerBoundToURL } from 'workbox-precaching';
import { registerRoute, NavigationRoute } from 'workbox-routing';
import { CacheFirst, StaleWhileRevalidate, NetworkFirst } from 'workbox-strategies';
import { CacheableResponsePlugin } from 'workbox-cacheable-response';
import { ExpirationPlugin } from 'workbox-expiration';

declare let self: ServiceWorkerGlobalScope & {
  __WB_MANIFEST: any;
};

// 1. Cleanup outdated caches and precache build assets
cleanupOutdatedCaches();
precacheAndRoute(self.__WB_MANIFEST || []);

// 2. SPA Navigation Route: serve index.html for in-app navigation
const handler = createHandlerBoundToURL('/index.html');
const navigationRoute = new NavigationRoute(handler, {
  denylist: [/^\/api\//, /^\/audio\//],
});
registerRoute(navigationRoute);

// 3. Google Fonts Stylesheets (StaleWhileRevalidate)
registerRoute(
  /^https:\/\/fonts\.googleapis\.com\/.*/i,
  new StaleWhileRevalidate({
    cacheName: 'google-fonts-stylesheets',
    plugins: [new CacheableResponsePlugin({ statuses: [0, 200] })],
  })
);

// 4. Google Fonts Webfonts (CacheFirst)
registerRoute(
  /^https:\/\/fonts\.gstatic\.com\/.*/i,
  new CacheFirst({
    cacheName: 'google-fonts-webfonts',
    plugins: [
      new ExpirationPlugin({ maxEntries: 30, maxAgeSeconds: 60 * 60 * 24 * 365 }),
      new CacheableResponsePlugin({ statuses: [0, 200] }),
    ],
  })
);

// 5. Educational Audio Cache (CacheFirst for Spanish TTS streams & MP3s)
registerRoute(
  /\/api\/v1\/audio\/(tts|capabilities).*/i,
  new CacheFirst({
    cacheName: 'educational-audio-cache',
    plugins: [
      new ExpirationPlugin({ maxEntries: 80, maxAgeSeconds: 60 * 60 * 24 * 30 }),
      new CacheableResponsePlugin({ statuses: [0, 200] }),
    ],
  })
);

// 6. Public Curriculum Levels & Learning Path (NetworkFirst with 3s timeout)
registerRoute(
  /\/api\/v1\/(levels|learning-path)$/i,
  new NetworkFirst({
    cacheName: 'public-curriculum-cache',
    networkTimeoutSeconds: 3,
    plugins: [
      new ExpirationPlugin({ maxEntries: 10, maxAgeSeconds: 60 * 60 * 24 * 7 }),
      new CacheableResponsePlugin({ statuses: [0, 200] }),
    ],
  })
);

// 7. Handle SKIP_WAITING message for seamless PWA updates
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

// 8. REAL WEB PUSH NOTIFICATION HANDLER (Delivered when app is in background or closed)
self.addEventListener('push', (event) => {
  if (!event.data) {
    return;
  }

  let notificationData: {
    title: string;
    body: string;
    icon?: string;
    badge?: string;
    dir?: 'rtl' | 'ltr' | 'auto';
    lang?: string;
    data?: {
      url?: string;
      type?: string;
      timestamp?: string;
    };
  };

  try {
    notificationData = event.data.json();
  } catch {
    notificationData = {
      title: 'Ruta Español 🇪🇸',
      body: event.data.text() || 'تذكير بموعد درس الإسبانية اليومي!',
    };
  }

  const title = notificationData.title || 'Ruta Español 🇪🇸';
  const options: any = {
    body: notificationData.body,
    icon: notificationData.icon || '/pwa-192x192.png',
    badge: notificationData.badge || '/pwa-192x192.png',
    dir: notificationData.dir || 'rtl',
    lang: notificationData.lang || 'ar',
    tag: notificationData.data?.type || 'ruta-reminder',
    renotify: true,
    data: notificationData.data || { url: '/dashboard' },
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

// 9. WEB PUSH NOTIFICATION CLICK HANDLER (Deep-link navigation & window focusing)
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = event.notification.data?.url || '/dashboard';
  const fullTargetUrl = new URL(targetUrl, self.location.origin).href;

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      // If a tab is already open with the app, focus it and navigate
      for (const client of windowClients) {
        if ('focus' in client) {
          if (client.url === fullTargetUrl) {
            return client.focus();
          }
          if ('navigate' in client) {
            return (client as any).navigate(fullTargetUrl).then((c: WindowClient) => c.focus());
          }
        }
      }
      // If no tab is open, open a new window at the target deep-link
      if (self.clients.openWindow) {
        return self.clients.openWindow(fullTargetUrl);
      }
    })
  );
});
