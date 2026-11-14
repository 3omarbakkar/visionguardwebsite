#!/usr/bin/env node
// Local dev server for the PHP site.  Usage:
//   npm run dev            demo mode: temp copy of the site + SQLite sample data (no MySQL needed)
//   npm run dev:real       serve the repo as-is (needs MySQL configured in admin/includes/config.php)
//   PORT=3000 npm run dev  change the port
const { spawn, spawnSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const real = process.argv.includes('--real');
const port = process.env.PORT || '8000';
const php = process.env.PHP_BIN || 'php';

const v = spawnSync(php, ['-v']);
if (v.error || v.status !== 0) {
  console.error('PHP was not found. Install PHP 8+ (https://www.php.net/downloads) or set PHP_BIN=/path/to/php');
  process.exit(1);
}
const mods = spawnSync(php, ['-m']).stdout.toString().toLowerCase();
if (!real && !mods.includes('pdo_sqlite')) { console.error('Demo mode needs the PHP pdo_sqlite extension (enable it in php.ini).'); process.exit(1); }

let docroot = root;
if (!real) {
  docroot = path.join(root, '.dev-site');
  fs.rmSync(docroot, { recursive: true, force: true });
  const skip = new Set(['.git', 'node_modules', '.dev-site', 'dev', 'demo.sqlite']);
  fs.mkdirSync(docroot, { recursive: true });
  for (const name of fs.readdirSync(root)) {
    if (skip.has(name)) continue;
    fs.cpSync(path.join(root, name), path.join(docroot, name), { recursive: true });
  }
  fs.copyFileSync(path.join(__dirname, 'demo-db.php'), path.join(docroot, 'admin', 'includes', 'db.php'));
  fs.copyFileSync(path.join(__dirname, 'demo-admin-login.php'), path.join(docroot, 'admin', '_demo.php'));
  fs.rmSync(path.join(root, 'demo.sqlite'), { force: true });
}

console.log(`\nVision Guard (${real ? 'real database' : 'demo data'}) -> http://localhost:${port}\n` +
  (real ? '' : `Demo mode: sample products/orders in SQLite.\nAdmin (auto-login, no password): http://localhost:${port}/admin/_demo.php\nSome admin pages that need MySQL features (e.g. discounts) won't work in demo mode.\n`) + 'Ctrl+C to stop.\n');
const child = spawn(php, ['-S', `localhost:${port}`, '-t', docroot, path.join(__dirname, 'router.php')], { stdio: 'inherit' });
const stop = () => { child.kill(); if (!real) fs.rmSync(docroot, { recursive: true, force: true }); process.exit(0); };
process.on('SIGINT', stop); process.on('SIGTERM', stop);
child.on('exit', (c) => process.exit(c || 0));
