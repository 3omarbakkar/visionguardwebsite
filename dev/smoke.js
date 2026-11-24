#!/usr/bin/env node
// Smoke / regression check against the demo-mode dev server.
//   node dev/smoke.js            fetch every page, fail on 5xx / PHP errors, compare with dev/smoke-baseline.json
//   node dev/smoke.js --save     (re)write the baseline from the current code
// Responses are normalised (CSRF tokens, timestamps, nonces) so only real output changes show up.
const { spawn } = require('child_process');
const crypto = require('crypto');
const fs = require('fs');
const path = require('path');

const port = process.env.SMOKE_PORT || '8099';
const base = `http://localhost:${port}`;
const baselineFile = path.join(__dirname, 'smoke-baseline.json');
const save = process.argv.includes('--save');

const PUBLIC = ['/', '/shop', '/planner', '/about', '/faq', '/contact', '/cart', '/account',
  '/category.php?c=cameras', '/product.php?s=x', '/privacy.html', '/terms.html', '/shipping.html',
  '/offline.html', '/robots.txt', '/sitemap.xml', '/llms.txt', '/manifest.webmanifest', '/meta-feed.php',
  '/push-key.php', '/order-status.php', '/admin/login.php', '/admin/staff_login.php'];
const ADMIN = ['dashboard', 'index', 'products', 'product_form', 'inventory', 'leads', 'customers',
  'staff', 'staff_orders', 'attendance', 'insights', 'hero', 'discounts', 'settings', 'account', 'notify'];
// These need MySQL / a working OpenSSL config, so demo mode (SQLite) throws on them. They are still
// baselined (any change in output is flagged) but their 500 / PHP error text is not treated as a failure.
const DEMO_LIMITED = new Set(['/admin/attendance.php', '/admin/hero.php', '/admin/discounts.php', '/admin/notify.php']);

const normalise = (s) => s
  // Checkouts differ in line endings (git autocrlf), which would change every line of every page.
  .replace(/\r/g, '')
  .replace(/name="csrf" value="[0-9a-f]+"/g, 'name="csrf" value="X"')
  .replace(/name=&quot;csrf&quot; value=&quot;[0-9a-f]+&quot;/g, 'name=&quot;csrf&quot; value=&quot;X&quot;')
  .replace(/\b\d\d\/\d\d\/20\d\d \d\d:\d\d(:\d\d)?/g, 'TS')
  .replace(/(<span>[^<\d]*)\d\d:\d\d(<\/span>)/g, '$1TS$2')
  .replace(/\b20\d\d-\d\d-\d\d[ T]\d\d:\d\d(:\d\d)?/g, 'TS')
  .replace(/\b\d{10,13}\b/g, 'N')
  // admin/index.php greets by time of day, so the baseline would flip at noon every day.
  .replace(/صباح الخير|مساء الخير/g, 'GREET')
  // The checkout path can contain spaces ("D:/Vision Guard/..."), so only stop at markup, quotes and
  // line breaks. PHP error text embeds this path, which made the baseline checkout-specific.
  .replace(/(?<![A-Za-z0-9])[A-Za-z]:[\\/][^<'"\n]*?\.dev-site/g, 'ROOT')
  .replace(/\\/g, '/')
  .replace(/nonce="[^"]*"/g, 'nonce="X"');
const sha = (s) => crypto.createHash('sha1').update(s).digest('hex').slice(0, 12);

async function get(url, cookie) {
  const r = await fetch(base + url, { redirect: 'manual', headers: cookie ? { cookie } : {} });
  const body = await r.text();
  return { status: r.status, loc: r.headers.get('location') || '', body, setCookie: r.headers.getSetCookie?.() || [] };
}

(async () => {
  const server = spawn(process.execPath, [path.join(__dirname, 'server.js')], {
    env: { ...process.env, PORT: port }, stdio: ['ignore', 'pipe', 'pipe'],
  });
  let log = '';
  server.stdout.on('data', (d) => (log += d));
  server.stderr.on('data', (d) => (log += d));
  const stop = () => { try { server.kill(); } catch (_) {} };
  process.on('exit', stop);

  for (let i = 0; ; i++) {
    try { await fetch(base + '/robots.txt'); break; } catch (_) {}
    if (i > 100) { console.error('server did not start\n' + log); stop(); process.exit(2); }
    await new Promise((r) => setTimeout(r, 200));
  }

  const results = {};
  const problems = [];
  const record = (name, r) => {
    results[name] = { status: r.status, loc: r.loc, hash: sha(normalise(r.body)) };
    if (r.status >= 500 && !DEMO_LIMITED.has(name)) problems.push(`${name}: HTTP ${r.status}`);
    if (!DEMO_LIMITED.has(name) && /(Fatal error|Parse error|Uncaught |Warning: |Notice: |Deprecated: )/.test(r.body)) problems.push(`${name}: PHP error text in output`);
  };

  for (const u of PUBLIC) record(u, await get(u));

  // demo auto-login, then walk the admin pages with the session cookie
  const login = await get('/admin/_demo.php');
  const cookie = login.setCookie.map((c) => c.split(';')[0]).join('; ');
  if (!cookie) problems.push('demo login did not set a session cookie');
  for (const p of ADMIN) record(`/admin/${p}.php`, await get(`/admin/${p}.php`, cookie));

  stop();

  if (save) {
    fs.writeFileSync(baselineFile, JSON.stringify(results, null, 2) + '\n');
    console.log(`baseline saved: ${Object.keys(results).length} URLs`);
  } else if (fs.existsSync(baselineFile)) {
    const old = JSON.parse(fs.readFileSync(baselineFile, 'utf8'));
    for (const k of new Set([...Object.keys(old), ...Object.keys(results)])) {
      if (JSON.stringify(old[k]) !== JSON.stringify(results[k])) problems.push(`${k}: differs from baseline (${JSON.stringify(old[k])} -> ${JSON.stringify(results[k])})`);
    }
  } else {
    console.log('no baseline yet; run with --save');
  }

  if (problems.length) { console.error('SMOKE FAILED:\n - ' + problems.join('\n - ')); process.exit(1); }
  console.log(`smoke OK (${Object.keys(results).length} URLs)`);
})();
