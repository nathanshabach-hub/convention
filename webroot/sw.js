/**
 * Accelerate Convention Portal - Service Worker
 * Handles: static asset caching, offline fallback, evaluation sync queue
 */

const APP_BASE_PATH = location.pathname.replace(/\/sw\.js$/, '');

function appUrl(path) {
  return APP_BASE_PATH + path;
}

const CACHE_VERSION = 'acp-v1';
const STATIC_CACHE = CACHE_VERSION + '-static';
const DATA_CACHE = CACHE_VERSION + '-data';

const STATIC_ASSETS = [
  appUrl('/offline.html'),
  appUrl('/img/pwa-icon-192.png'),
  appUrl('/img/pwa-icon-512.png'),
  appUrl('/img/front/main-logo.png'),
  appUrl('/css/front/bootstrap.min.css'),
  appUrl('/css/front/style_front.css'),
  appUrl('/css/front/forms.css'),
];

const CACHEABLE_ROUTES = [
  /\/conventionregistrations\/judgeevents\//,
  /\/conventionregistrations\/judgeevententries\//,
  /\/judgeevaluations\/addnew\//,
  /\/conventionregistrations\/myregistrations/,
  /\/conventionregistrations\/teachers/,
  /\/conventionregistrations\/students/,
  /\/conventionregistrations\/managestudentevents\//,
  /\/conventionregistrations\/evententries\//,
  /\/eventsubmissions\/spellingseventsentries\//,
  /\/eventsubmissions\/distanceseventsentries\//,
  /\/users\/dashboard/,
  /\/users\/editprofile/,
  /\/users\/applyforjudge/,
  /\/users\/judgeexperience/,
];

const NEVER_CACHE = [
  /\/admin\//,
  /\/users\/login/,
  /\/users\/logout/,
  /\/judgeevaluations\/syncpending/,
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(cache => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key !== STATIC_CACHE && key !== DATA_CACHE)
          .map(key => caches.delete(key))
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const req = event.request;
  const url = new URL(req.url);

  if (url.origin !== location.origin) return;
  if (NEVER_CACHE.some(re => re.test(url.pathname))) return;

  if (req.method === 'POST' && /\/judgeevaluations\/addnew\//.test(url.pathname)) {
    event.respondWith(handleEvaluationPost(req));
    return;
  }

  if (req.mode === 'navigate') {
    event.respondWith(handleNavigate(req));
    return;
  }

  event.respondWith(cacheFirst(req));
});

async function handleNavigate(req) {
  try {
    const networkResponse = await fetch(req);
    if (CACHEABLE_ROUTES.some(re => re.test(new URL(req.url).pathname))) {
      const cache = await caches.open(DATA_CACHE);
      cache.put(req, networkResponse.clone());
    }
    return networkResponse;
  } catch (_) {
    const cached = await caches.match(req);
    if (cached) return cached;
    return caches.match(appUrl('/offline.html'));
  }
}

async function cacheFirst(req) {
  const cached = await caches.match(req);
  if (cached) return cached;

  try {
    const networkResponse = await fetch(req);
    if (networkResponse.ok) {
      const cache = await caches.open(STATIC_CACHE);
      cache.put(req, networkResponse.clone());
    }
    return networkResponse;
  } catch (_) {
    return new Response('', { status: 408 });
  }
}

async function handleEvaluationPost(req) {
  try {
    return await fetch(req.clone());
  } catch (_) {
    await queuePendingEvaluation(req.clone());
    return new Response(
      JSON.stringify({ queued: true, message: 'Evaluation saved offline. Will sync when online.' }),
      {
        status: 202,
        headers: {
          'Content-Type': 'application/json',
          'X-ACP-Offline-Queued': '1'
        }
      }
    );
  }
}

async function queuePendingEvaluation(req) {
  try {
    const formData = await req.formData();
    const payload = {};
    for (const [key, value] of formData.entries()) {
      payload[key] = value;
    }

    const db = await openDB();
    const tx = db.transaction('pending_evaluations', 'readwrite');
    const store = tx.objectStore('pending_evaluations');
    store.add({
      url: req.url,
      method: req.method,
      payload,
      headers: Object.fromEntries(req.headers.entries()),
      queuedAt: new Date().toISOString()
    });

    await new Promise((resolve, reject) => {
      tx.oncomplete = resolve;
      tx.onerror = reject;
    });

    db.close();

    const clients = await self.clients.matchAll({ includeUncontrolled: true });
    clients.forEach(client => client.postMessage({ type: 'EVAL_QUEUED' }));
  } catch (err) {
    console.error('[SW] Failed to queue evaluation:', err);
  }
}

self.addEventListener('sync', event => {
  if (event.tag === 'sync-evaluations') {
    event.waitUntil(syncPendingEvaluations());
  }
});

async function syncPendingEvaluations() {
  const db = await openDB();
  const tx = db.transaction('pending_evaluations', 'readonly');
  const store = tx.objectStore('pending_evaluations');
  const all = await new Promise((resolve, reject) => {
    const request = store.getAll();
    request.onsuccess = () => resolve(request.result);
    request.onerror = reject;
  });
  db.close();

  if (all.length === 0) return;

  const payload = all.map(ev => ({ localId: ev.id, url: ev.url, payload: ev.payload }));

  let data;
  try {
    const response = await fetch(appUrl('/judgeevaluations/syncpending'), {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ evaluations: payload })
    });
    data = response.ok ? await response.json() : null;
  } catch (err) {
    console.error('[SW] Sync batch request failed:', err);
    return;
  }

  if (!data || !data.results) return;

  for (const result of data.results) {
    if (result.ok) {
      await removePendingEvaluation(result.localId);
      const clients = await self.clients.matchAll({ includeUncontrolled: true });
      clients.forEach(client => client.postMessage({ type: 'EVAL_SYNCED', id: result.localId }));
    }
  }

  if (data.synced > 0) {
    const clients = await self.clients.matchAll({ includeUncontrolled: true });
    clients.forEach(client => client.postMessage({
      type: 'SYNC_COMPLETE',
      synced: data.synced,
      failed: data.failed
    }));
  }
}

async function removePendingEvaluation(id) {
  const db = await openDB();
  const tx = db.transaction('pending_evaluations', 'readwrite');
  tx.objectStore('pending_evaluations').delete(id);
  await new Promise((resolve, reject) => {
    tx.oncomplete = resolve;
    tx.onerror = reject;
  });
  db.close();
}

function openDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('acp_offline', 1);
    request.onupgradeneeded = event => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains('pending_evaluations')) {
        const store = db.createObjectStore('pending_evaluations', { keyPath: 'id', autoIncrement: true });
        store.createIndex('queuedAt', 'queuedAt', { unique: false });
      }
      if (!db.objectStoreNames.contains('judge_cache')) {
        db.createObjectStore('judge_cache', { keyPath: 'key' });
      }
    };
    request.onsuccess = event => resolve(event.target.result);
    request.onerror = reject;
  });
}
