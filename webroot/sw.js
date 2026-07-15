/**
 * Accelerate Convention Portal – Service Worker
 * Handles: static asset caching, offline fallback, evaluation sync queue
 */

const CACHE_VERSION = 'acp-v1';
const STATIC_CACHE = CACHE_VERSION + '-static';
const DATA_CACHE   = CACHE_VERSION + '-data';

// Static assets to cache on install (shell)
const STATIC_ASSETS = [
  '/offline.html',
  '/img/pwa-icon-192.png',
  '/img/pwa-icon-512.png',
  '/img/front/main-logo.png',
  '/css/front/bootstrap.min.css',
  '/css/front/style_front.css',
  '/css/front/forms.css',
];

// Routes whose responses should be cached for offline reading (judge-facing pages)
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

// Routes that should NEVER be served from cache (mutations / admin pages)
const NEVER_CACHE = [
  /\/admin\//,
  /\/users\/login/,
  /\/users\/logout/,
  /\/judgeevaluations\/syncpending/,
];

// ─── Install ──────────────────────────────────────────────────────────────────
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(cache => {
      return cache.addAll(STATIC_ASSETS.map(url => new Request(url, { cache: 'reload' })))
        .catch(err => {
          // Non-fatal: some assets may not exist yet on localhost
          console.warn('[SW] Static pre-cache error (non-fatal):', err);
        });
    }).then(() => self.skipWaiting())
  );
});

// ─── Activate – clean up old caches ──────────────────────────────────────────
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

// ─── Fetch ────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', event => {
  const req = event.request;
  const url = new URL(req.url);

  // Only handle same-origin requests
  if (url.origin !== location.origin) return;

  // Never cache these
  if (NEVER_CACHE.some(re => re.test(url.pathname))) return;

  // POST: judge evaluation submission – intercept when offline
  if (req.method === 'POST' && /\/judgeevaluations\/addnew\//.test(url.pathname)) {
    event.respondWith(handleEvaluationPost(req));
    return;
  }

  // GET: navigation / page request
  if (req.mode === 'navigate') {
    event.respondWith(handleNavigate(req));
    return;
  }

  // GET: static assets – cache first
  event.respondWith(cacheFirst(req));
});

// ─── Navigation handler: network first, fall back to cache then offline page ──
async function handleNavigate(req) {
  try {
    const networkResponse = await fetch(req);
    // Cache judge-facing pages for offline reading
    if (CACHEABLE_ROUTES.some(re => re.test(new URL(req.url).pathname))) {
      const cache = await caches.open(DATA_CACHE);
      cache.put(req, networkResponse.clone());
    }
    return networkResponse;
  } catch (_) {
    const cached = await caches.match(req);
    if (cached) return cached;
    return caches.match('/offline.html');
  }
}

// ─── Cache-first handler for static assets ───────────────────────────────────
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

// ─── Offline evaluation POST handler ─────────────────────────────────────────
async function handleEvaluationPost(req) {
  try {
    // Online: pass through normally
    const networkResponse = await fetch(req.clone());
    return networkResponse;
  } catch (_) {
    // Offline: queue in IndexedDB and return synthetic success response
    await queuePendingEvaluation(req.clone());
    // Return a redirect response to the judge events list (graceful UX)
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

// ─── Queue evaluation to IndexedDB ───────────────────────────────────────────
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

    // Notify all open clients to update the pending count badge
    const clients = await self.clients.matchAll({ includeUncontrolled: true });
    clients.forEach(client => client.postMessage({ type: 'EVAL_QUEUED' }));
  } catch (err) {
    console.error('[SW] Failed to queue evaluation:', err);
  }
}

// ─── Background Sync: flush pending evaluations ──────────────────────────────
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
    const req = store.getAll();
    req.onsuccess = () => resolve(req.result);
    req.onerror = reject;
  });
  db.close();

  if (all.length === 0) return;

  // Batch-send via the dedicated sync endpoint
  const payload = all.map(ev => ({ localId: ev.id, url: ev.url, payload: ev.payload }));

  let data;
  try {
    const response = await fetch('/judgeevaluations/syncpending', {
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

// ─── IndexedDB helper ────────────────────────────────────────────────────────
function openDB() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open('acp_offline', 1);
    req.onupgradeneeded = e => {
      const db = e.target.result;
      if (!db.objectStoreNames.contains('pending_evaluations')) {
        const store = db.createObjectStore('pending_evaluations', { keyPath: 'id', autoIncrement: true });
        store.createIndex('queuedAt', 'queuedAt', { unique: false });
      }
      if (!db.objectStoreNames.contains('judge_cache')) {
        db.createObjectStore('judge_cache', { keyPath: 'key' });
      }
    };
    req.onsuccess = e => resolve(e.target.result);
    req.onerror = reject;
  });
}
