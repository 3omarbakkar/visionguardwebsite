<?php
// Verifies that the server's config.local.php will work with the NEW config.php,
// BEFORE merging. Prints no password. Run:  php ~/vg-check-db.php
$home = getenv('HOME') ?: dirname(__DIR__);
$f = $home . '/public_html/admin/includes/config.local.php';
echo "expected path : $f\n";

foreach (glob($home . '/{,public_html/,public_html/admin/,public_html/admin/includes/}config.local.php', GLOB_BRACE) ?: [] as $found) {
    echo "found copy    : $found\n";
}
if (!is_file($f)) { echo "\nRESULT: FAIL - no config.local.php at the expected path (see 'found copy' lines above for strays)\n"; exit(1); }
echo "perms/size    : " . substr(sprintf('%o', fileperms($f)), -4) . " / " . filesize($f) . " bytes\n\n";

require $f;   // a syntax error here fatals with PHP's own message = also a FAIL
defined('DB_HOST') || define('DB_HOST', (string)(getenv('VG_DB_HOST') ?: 'localhost'));
defined('DB_NAME') || define('DB_NAME', (string)(getenv('VG_DB_NAME') ?: 'visionguard2026_vg'));
defined('DB_USER') || define('DB_USER', (string)(getenv('VG_DB_USER') ?: ''));
defined('DB_PASS') || define('DB_PASS', (string)(getenv('VG_DB_PASS') ?: ''));

printf("host=%s  db=%s  user=%s  pass=%s\n", DB_HOST, DB_NAME,
    DB_USER === '' ? '(EMPTY)' : DB_USER,
    DB_PASS === '' ? '(EMPTY)' : '(set, ' . strlen(DB_PASS) . ' chars)');

if (DB_USER === '' || DB_PASS === '')  { echo "\nRESULT: FAIL - credentials are empty\n"; exit(1); }
if (DB_USER === 'CPANEL_DB_USER' || DB_PASS === 'CPANEL_DB_PASSWORD') { echo "\nRESULT: FAIL - still the placeholder values from the sample file\n"; exit(1); }

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $n = $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    echo "db connect    : OK (admin_users rows: $n)\n";
    echo "\nRESULT: OK - safe to merge\n";
} catch (Throwable $e) {
    echo "db connect    : FAILED - " . $e->getMessage() . "\n";
    echo "\nRESULT: FAIL - do not merge\n";
    exit(1);
}
