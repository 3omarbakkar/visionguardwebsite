<?php
// Copy to config.local.php ON THE SERVER ONLY, then fill in the real values.
// config.local.php is gitignored and is never overwritten by a deploy.
// Verify it with:  php ~/vg-check-db.php   (see the PR description)
define('DB_HOST', 'localhost');
define('DB_NAME', 'visionguard2026_vg');
define('DB_USER', 'CPANEL_DB_USER');
define('DB_PASS', 'CPANEL_DB_PASSWORD');

// TEMPORARY: uncomment to also allow username + password sign-in on admin/login.php
// (e.g. while Google sign-in is being set up). Remove the line to turn it off again.
// define('VG_ADMIN_PASSWORD_LOGIN', true);
