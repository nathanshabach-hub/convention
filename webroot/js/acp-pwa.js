/**
 * Accelerate Convention Portal – PWA Client
 * Registers service worker, manages offline badge, handles sync messages
 */

(function () {
  'use strict';

  if (!('serviceWorker' in navigator)) return;

  // ── Register Service Worker ────────────────────────────────────────────────
  navigator.serviceWorker.register('/sw.js', { scope: '/' })
    .then(function (reg) {
      console.log('[ACP] Service Worker registered, scope:', reg.scope);

      // Request background sync permission when it becomes available
      if ('sync' in reg) {
        navigator.serviceWorker.ready.then(function (swReg) {
          swReg.sync.register('sync-evaluations').catch(function () {
            // Sync not available in this browser – will fall back to online check
          });
        });
      }
    })
    .catch(function (err) {
      console.warn('[ACP] Service Worker registration failed:', err);
    });

  // ── Listen for messages from SW ───────────────────────────────────────────
  navigator.serviceWorker.addEventListener('message', function (event) {
    var data = event.data;
    if (!data || !data.type) return;

    if (data.type === 'EVAL_QUEUED') {
      refreshPendingBadge();
      showOfflineToast('Evaluation saved offline. It will sync when you reconnect.');
    }

    if (data.type === 'EVAL_SYNCED') {
      refreshPendingBadge();
      showOfflineToast('Evaluation synced successfully!', 'success');
    }

    if (data.type === 'SYNC_COMPLETE') {
      refreshPendingBadge();
      if (data.synced > 0) {
        showOfflineToast(data.synced + ' evaluation' + (data.synced > 1 ? 's' : '') + ' synced!', 'success');
      }
      if (data.failed > 0) {
        showOfflineToast(data.failed + ' evaluation' + (data.failed > 1 ? 's' : '') + ' failed — will retry.');
      }
    }
  });

  // ── Online/offline status bar ─────────────────────────────────────────────
  function updateOnlineStatus() {
    var bar = document.getElementById('acp-offline-bar');
    if (!bar) return;

    if (navigator.onLine) {
      bar.style.display = 'none';
      // Try to flush any pending evaluations now we're back online
      navigator.serviceWorker.ready.then(function (reg) {
        if ('sync' in reg) {
          reg.sync.register('sync-evaluations').catch(function () {});
        } else {
          // Fallback: direct flush for browsers without Background Sync
          flushPendingEvaluations();
        }
      });
    } else {
      bar.style.display = 'flex';
    }
  }

  window.addEventListener('online', updateOnlineStatus);
  window.addEventListener('offline', updateOnlineStatus);

  // ── Pending count badge ───────────────────────────────────────────────────
  function refreshPendingBadge() {
    if (!window.indexedDB) return;
    var req = indexedDB.open('acp_offline', 1);
    req.onsuccess = function (e) {
      var db = e.target.result;
      if (!db.objectStoreNames.contains('pending_evaluations')) {
        db.close();
        renderBadge(0);
        return;
      }
      var tx = db.transaction('pending_evaluations', 'readonly');
      var store = tx.objectStore('pending_evaluations');
      var countReq = store.count();
      countReq.onsuccess = function () {
        renderBadge(countReq.result);
        db.close();
      };
    };
    req.onerror = function () { renderBadge(0); };
  }

  function renderBadge(count) {
    var badge = document.getElementById('acp-sync-badge');
    if (!badge) return;
    if (count > 0) {
      badge.textContent = count + ' pending sync';
      badge.style.display = 'inline-flex';
    } else {
      badge.style.display = 'none';
    }
  }

  // ── Fallback flush for browsers without Background Sync ───────────────────
  function flushPendingEvaluations() {
    if (!window.indexedDB) return;
    var openReq = indexedDB.open('acp_offline', 1);
    openReq.onsuccess = function (e) {
      var db = e.target.result;
      if (!db.objectStoreNames.contains('pending_evaluations')) { db.close(); return; }
      var tx = db.transaction('pending_evaluations', 'readonly');
      var all = [];
      tx.objectStore('pending_evaluations').openCursor().onsuccess = function (ev) {
        var cursor = ev.target.result;
        if (cursor) { all.push(cursor.value); cursor.continue(); }
        else {
          db.close();
          if (all.length === 0) return;

          // Batch-post to syncpending endpoint
          var payload = all.map(function (ev) {
            return { localId: ev.id, url: ev.url, payload: ev.payload };
          });

          fetch('/judgeevaluations/syncpending', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ evaluations: payload })
          })
          .then(function (resp) { return resp.ok ? resp.json() : null; })
          .then(function (data) {
            if (!data || !data.results) return;
            data.results.forEach(function (r) {
              if (r.ok) removePendingEvaluation(r.localId);
            });
            if (data.synced > 0) {
              showOfflineToast(data.synced + ' evaluation' + (data.synced > 1 ? 's' : '') + ' synced successfully!', 'success');
            }
            if (data.failed > 0) {
              showOfflineToast(data.failed + ' evaluation' + (data.failed > 1 ? 's' : '') + ' failed to sync. Will retry.');
            }
          })
          .catch(function () { /* still offline */ });
        }
      };
    };
  }

  function removePendingEvaluation(id) {
    var openReq = indexedDB.open('acp_offline', 1);
    openReq.onsuccess = function (e) {
      var db = e.target.result;
      var tx = db.transaction('pending_evaluations', 'readwrite');
      tx.objectStore('pending_evaluations').delete(id);
      tx.oncomplete = function () { db.close(); refreshPendingBadge(); };
    };
  }

  // ── Toast notification ────────────────────────────────────────────────────
  function showOfflineToast(message, type) {
    var toast = document.createElement('div');
    toast.style.cssText = [
      'position:fixed', 'bottom:20px', 'left:50%', 'transform:translateX(-50%)',
      'background:' + (type === 'success' ? '#2a7a4f' : '#1c2452'),
      'color:#fff', 'padding:12px 22px', 'border-radius:10px',
      'font-size:14px', 'font-weight:600', 'z-index:99999',
      'box-shadow:0 6px 20px rgba(0,0,0,0.25)',
      'max-width:90vw', 'text-align:center',
      'transition:opacity 0.4s ease'
    ].join(';');
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function () {
      toast.style.opacity = '0';
      setTimeout(function () { toast.remove(); }, 400);
    }, 4000);
  }

  // ── Install prompt (A2HS) ─────────────────────────────────────────────────
  var deferredInstallPrompt = null;

  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferredInstallPrompt = e;
    var btn = document.getElementById('acp-install-btn');
    if (btn) btn.style.display = 'inline-flex';
  });

  document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'acp-install-btn') {
      if (deferredInstallPrompt) {
        deferredInstallPrompt.prompt();
        deferredInstallPrompt.userChoice.then(function () {
          deferredInstallPrompt = null;
          var btn = document.getElementById('acp-install-btn');
          if (btn) btn.style.display = 'none';
        });
      }
    }
  });

  // ── Init ──────────────────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    updateOnlineStatus();
    refreshPendingBadge();
    // Phase 2: pre-cache judge data if on a judge page
    precacheJudgeDataIfApplicable();
    // Phase 4: warm read-only pages for school/student navigation
    precacheReadOnlyLinksFromPage();
  });

  // ── Phase 2: Pre-cache judge data for offline use ─────────────────────────
  function precacheJudgeDataIfApplicable() {
    if (!navigator.onLine) return;
    if (!window.indexedDB) return;

    // Only run on judge-relevant pages
    var path = window.location.pathname;
    var isJudgePage = /\/(judgeevents|judgeevententries|judgeevaluations\/addnew)\//.test(path);
    if (!isJudgePage) return;

    // Extract conv_reg_slug from current URL if present
    var convRegSlug = null;
    var slugMatch = path.match(/(convention-registration-[\w-]+)/);
    if (slugMatch) convRegSlug = slugMatch[1];

    var apiUrl = '/conventionregistrations/precachejudgedata' + (convRegSlug ? '/' + convRegSlug : '');

    fetch(apiUrl, { credentials: 'same-origin' })
      .then(function (resp) { return resp.ok ? resp.json() : null; })
      .then(function (data) {
        if (!data || !data.ok) return;
        storeJudgeCacheData(data);
        markOfflineReady();
      })
      .catch(function () { /* offline or error, skip */ });
  }

  function storeJudgeCacheData(data) {
    if (!window.indexedDB) return;
    var req = indexedDB.open('acp_offline', 1);
    req.onsuccess = function (e) {
      var db = e.target.result;
      if (!db.objectStoreNames.contains('judge_cache')) { db.close(); return; }
      var tx = db.transaction('judge_cache', 'readwrite');
      var store = tx.objectStore('judge_cache');
      store.put({ key: 'judge_data_' + (data.conv_reg_slug || 'default'), value: data });
      tx.oncomplete = function () { db.close(); };
    };
  }

  function markOfflineReady() {
    var existing = document.getElementById('acp-offline-ready-tag');
    if (existing) return;
    var tag = document.createElement('span');
    tag.id = 'acp-offline-ready-tag';
    tag.title = 'Event data cached for offline use';
    tag.style.cssText = 'position:fixed;bottom:8px;right:12px;background:#2a7a4f;color:#fff;border-radius:999px;padding:4px 12px;font-size:12px;font-weight:700;z-index:9998;opacity:0.85;pointer-events:none;';
    tag.textContent = '✓ Offline ready';
    document.body.appendChild(tag);
    setTimeout(function () {
      tag.style.transition = 'opacity 0.8s';
      tag.style.opacity = '0';
      setTimeout(function () { tag.remove(); }, 800);
    }, 3000);
  }

  // ── Phase 4: Pre-cache read-only navigation links ───────────────────────
  function precacheReadOnlyLinksFromPage() {
    if (!navigator.onLine) return;

    var allowList = [
      /^\/users\/(dashboard|editprofile|applyforjudge|judgeexperience)\/?$/,
      /^\/conventionregistrations\/(myregistrations|teachers|students)\/?$/,
      /^\/conventionregistrations\/(judgeevents|judgeevententries|managestudentevents)\//,
      /^\/eventsubmissions\/(spellingseventsentries|distanceseventsentries)\//
    ];

    var links = Array.prototype.slice.call(document.querySelectorAll('a[href]'));
    var seen = {};
    var targets = [];

    links.forEach(function (a) {
      var href = a.getAttribute('href') || '';
      if (!href || href.charAt(0) !== '/') return;
      if (seen[href]) return;

      var allowed = allowList.some(function (re) { return re.test(href); });
      if (!allowed) return;

      seen[href] = true;
      targets.push(href);
    });

    // Keep this bounded to avoid unnecessary traffic on slow links.
    targets.slice(0, 12).forEach(function (url) {
      fetch(url, {
        credentials: 'same-origin',
        headers: { 'X-ACP-Precache': 'phase4' }
      }).catch(function () {
        // Non-fatal: offline or network instability.
      });
    });
  }

})();
