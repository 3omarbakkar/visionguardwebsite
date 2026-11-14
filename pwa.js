/* Vision Guard PWA bootstrap: service worker, install prompt (AR/EN), update toast, bottom nav. */
(function () {
  'use strict';
  var isAr = (document.documentElement.lang || 'ar').indexOf('ar') === 0;
  var T = isAr
    ? { title: 'ثبّت تطبيق Vision Guard', sub: 'وصول أسرع وإشعارات بالعروض', go: 'تثبيت', close: 'إغلاق',
        ios: 'اضغط زر المشاركة ثم "إضافة إلى الشاشة الرئيسية"', iosApp: 'افتح الموقع في Safari لتثبيت التطبيق (⋯ ثم فتح في المتصفح)', upd: 'يوجد تحديث جديد', updBtn: 'تحديث',
        home: 'الرئيسية', shop: 'المنتجات', plan: 'المخطط', chat: 'واتساب', orders: 'طلباتي',
        pushT: 'فعّل إشعارات العروض', pushGo: 'تفعيل', pushErr: 'تعذر تفعيل الإشعارات. حاول مرة أخرى.' }
    : { title: 'Install Vision Guard app', sub: 'Faster access and offer alerts', go: 'Install', close: 'Close',
        ios: 'Tap Share, then "Add to Home Screen"', iosApp: 'Open this page in Safari to install the app (\u22ef then Open in Browser)', upd: 'A new version is ready', updBtn: 'Update',
        home: 'Home', shop: 'Shop', plan: 'Planner', chat: 'WhatsApp', orders: 'Orders',
        pushT: 'Get offer notifications', pushGo: 'Enable', pushErr: 'Could not enable notifications. Please try again.' };

  var standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
  if (standalone) document.documentElement.classList.add('vg-standalone');

  // 1) Service worker + update toast
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/sw.js').then(function (reg) {
        reg.addEventListener('updatefound', function () {
          var w = reg.installing;
          if (!w) return;
          w.addEventListener('statechange', function () {
            if (w.state === 'installed' && navigator.serviceWorker.controller) showUpdate(w);
          });
        });
      }).catch(function () {});
    });
    var reloaded = false;
    navigator.serviceWorker.addEventListener('controllerchange', function () {
      if (reloaded) return; reloaded = true; location.reload();
    });
  }
  function showUpdate(worker) {
    var el = document.createElement('div');
    el.id = 'vg-update'; el.className = 'show'; el.setAttribute('role', 'status');
    el.innerHTML = '<span style="flex:1">' + T.upd + '</span><button type="button">' + T.updBtn + '</button>';
    el.querySelector('button').onclick = function () { worker.postMessage('SKIP_WAITING'); };
    document.body.appendChild(el);
  }

  // 2) Compact translucent bar: install prompt (browser) or notification opt-in (installed app).
  // days = 0: remember the dismissal only for this visit (sessionStorage), so the bar returns on the next visit.
  function lsGet(k, days) { try { return +(days ? localStorage : sessionStorage).getItem(k) || 0; } catch (e) { return 0; } }
  function lsSet(k, days) { try { (days ? localStorage : sessionStorage).setItem(k, Date.now()); } catch (e) {} }
  var deferred = null;
  var ios = /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;

  function banner(text, goLabel, onGo, key, days) {
    if (document.getElementById('vg-install')) return;
    if (days ? Date.now() - lsGet(key, days) < days * 864e5 : lsGet(key, 0)) return;
    var el = document.createElement('div');
    el.id = 'vg-install'; el.setAttribute('role', 'dialog'); el.setAttribute('aria-label', text);
    el.innerHTML = '<span class="t">' + text + '</span>' +
      (goLabel ? '<button class="go" type="button">' + goLabel + '</button>' : '') +
      '<button class="x" type="button" aria-label="' + T.close + '">&times;</button>';
    document.body.appendChild(el);
    requestAnimationFrame(function () { el.classList.add('show'); });
    el.querySelector('.x').onclick = function () { el.remove(); lsSet(key, days); };
    var go = el.querySelector('.go');
    if (go) go.onclick = function () { onGo(el); };
  }

  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault(); deferred = e;
    if (standalone) return;
    setTimeout(function () {
      banner(T.title, T.go, function (el) {
        if (!deferred) return;
        deferred.prompt();
        deferred.userChoice.then(function () { deferred = null; el.remove(); });
      }, 'vg-install-dismissed', 0);
    }, 8000);
  });
  window.addEventListener('appinstalled', function () {
    var el = document.getElementById('vg-install'); if (el) el.remove();
  });
  var inApp = /FBAN|FBAV|FB_IAB|Instagram|Messenger|TikTok|Line\//i.test(navigator.userAgent);
  if (ios && !standalone) setTimeout(function () { banner(inApp ? T.iosApp : T.ios, '', null, 'vg-install-dismissed', 0); }, 12000);

  // Offer notifications opt-in: only inside the installed app, once permission is undecided.
  if (standalone && 'Notification' in window && 'PushManager' in window && Notification.permission === 'default') {
    setTimeout(function () {
      banner(T.pushT, T.pushGo, function (el) {
        window.vgEnablePush().then(function (ok) { if (ok) { el.remove(); lsSet('vg-push-dismissed', 30); } else { var t = el.querySelector('.t'); if (t) t.textContent = T.pushErr; } });
      }, 'vg-push-dismissed', 30);
    }, 20000);
  }

  // 3) Bottom nav inside the installed app only (CSS hides it elsewhere)
  var path = location.pathname;
  var nav = document.createElement('nav');
  nav.id = 'vg-nav'; nav.setAttribute('aria-label', isAr ? 'التنقل الرئيسي' : 'Main');
  function ic(d) { return '<svg viewBox="0 0 24 24" aria-hidden="true">' + d + '</svg>'; }
  var items = [
    ['/', T.home, ic('<path d="M3 11l9-8 9 8M5 10v10h14V10"/>'), path === '/'],
    ['/c/wifi-cameras', T.shop, ic('<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>'), path.indexOf('/c/') === 0],
    ['/#planner', T.plan, ic('<path d="M3 6l6-3 6 3 6-3v15l-6 3-6-3-6 3zM9 3v15M15 6v15"/>'), false],
    ['https://wa.me/201105006854', T.chat, ic('<path d="M21 12a9 9 0 1 1-4-7.5L21 3l-1.5 4A9 9 0 0 1 21 12z"/>'), false],
    ['/order-status.php', T.orders, ic('<path d="M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8M12 13v8"/>'), path.indexOf('/order-status') === 0]
  ];
  nav.innerHTML = items.map(function (i) {
    return '<a href="' + i[0] + '"' + (i[3] ? ' aria-current="page"' : '') + '>' + i[2] + '<span>' + i[1] + '</span></a>';
  }).join('');
  document.addEventListener('DOMContentLoaded', function () { document.body.appendChild(nav); });
  if (document.readyState !== 'loading') document.body.appendChild(nav);

  // 4) Push subscribe. Called from a user tap. Fetches the public VAPID key from the server.
  window.vgEnablePush = function () {
    if (!('PushManager' in window) || !('serviceWorker' in navigator)) return Promise.resolve(false);
    return fetch('/push-key.php').then(function (r) { return r.json(); }).then(function (j) {
      if (!j.key) return false;
      return Notification.requestPermission().then(function (p) {
        if (p !== 'granted') return false;
        return navigator.serviceWorker.ready.then(function (reg) {
          var s = j.key.replace(/-/g, '+').replace(/_/g, '/');
          var raw = atob(s + '='.repeat((4 - s.length % 4) % 4));
          var key = Uint8Array.from(raw, function (ch) { return ch.charCodeAt(0); });
          return reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: key });
        }).then(function (sub) {
          return fetch('/push-subscribe.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(sub) });
        }).then(function (r) { if (!r.ok) return false; return r.json().then(function (j) { return !!(j && j.ok); }); });
      });
    }).catch(function () { return false; });
  };
})();
