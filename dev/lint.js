#!/usr/bin/env node
// php -l over every tracked PHP file. Usage: npm run lint   (PHP_BIN=/path/to/php to override)
const { spawnSync } = require('child_process');
const php = process.env.PHP_BIN || 'php';
const files = spawnSync('git', ['ls-files', '*.php'], { encoding: 'utf8' }).stdout.split('\n').filter(Boolean);
let bad = 0;
for (const f of files) {
  const r = spawnSync(php, ['-l', f], { encoding: 'utf8' });
  if (r.error) { console.error('PHP not found; install PHP 8.2 or set PHP_BIN'); process.exit(2); }
  if (r.status !== 0) { bad++; console.error((r.stdout + r.stderr).trim()); }
}
console.log(bad ? `${bad} file(s) failed lint` : `lint OK (${files.length} PHP files)`);
process.exit(bad ? 1 : 0);
