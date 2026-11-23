<?php
// ============================================================
// Vision Guard - configuration
// Database credentials are NOT stored in this file (it is public).
// Put them in admin/includes/config.local.php (gitignored, create it on the
// server - see config.local.sample.php), or set the VG_DB_USER / VG_DB_PASS
// environment variables.
// ============================================================

if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}
defined('DB_HOST') || define('DB_HOST', (string)(getenv('VG_DB_HOST') ?: 'localhost'));
defined('DB_NAME') || define('DB_NAME', (string)(getenv('VG_DB_NAME') ?: 'visionguard2026_vg'));
defined('DB_USER') || define('DB_USER', (string)(getenv('VG_DB_USER') ?: ''));
defined('DB_PASS') || define('DB_PASS', (string)(getenv('VG_DB_PASS') ?: ''));

// Base URL of the site (no trailing slash) - used for redirects/links
define('SITE_URL', 'https://visionguardeg.com');

// Product photos live in the site's /images folder as p{id}.jpg,
// exactly how the storefront (index.php) already expects them -
// no front-end code changes needed when you upload a photo here.
define('IMAGES_DIR', dirname(__DIR__, 2) . '/images');   // .../public_html/images
define('IMAGES_URL_FROM_ADMIN', '../images');             // used inside /admin pages
define('IMAGES_URL_FROM_API', '../images');                // used inside /api pages
