<?php
// ============================================================
// Vision Guard - database configuration
// Fill these in with the values you get from cPanel > MySQL
// Databases (or the Database Wizard), then re-upload this file.
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'visionguard2026_vg');
define('DB_USER', 'visionguard2026_vg');
define('DB_PASS', 'wUNPHDTJTVi0oknwOKAGK*');

// Base URL of the site (no trailing slash) - used for redirects/links
define('SITE_URL', 'https://visionguardeg.com');

// Product photos live in the site's /images folder as p{id}.jpg,
// exactly how the storefront (index.php) already expects them -
// no front-end code changes needed when you upload a photo here.
define('IMAGES_DIR', dirname(__DIR__, 2) . '/images');   // .../public_html/images
define('IMAGES_URL_FROM_ADMIN', '../images');             // used inside /admin pages
define('IMAGES_URL_FROM_API', '../images');                // used inside /api pages
