"# visionguardegwebsite" 
"# visionguardwebsite" 

## Checks before deploying

`npm test` runs two checks (needs PHP 8.2 on PATH, or `PHP_BIN=...`):

- `npm run lint` — `php -l` on every tracked PHP file.
- `node dev/smoke.js` — starts the demo-mode dev server, fetches the public pages and the admin pages
  (via the demo auto-login), fails on HTTP 5xx or PHP errors, and compares normalised output with
  `dev/smoke-baseline.json`. After an intentional change to page output, refresh it with `node dev/smoke.js --save`.

Note: pushes to `main` auto-deploy to the live site (see `.cpanel.yml` / `deploy-hook.php`), so run `npm test` first.
