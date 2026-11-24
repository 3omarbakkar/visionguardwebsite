<?php
// Verifies that the LIVE database has the schema the admin panel expects.
//
// Why this exists: migrations in this repo are deliberately not deployed (*.sql is excluded in
// .cpanel.yml and deploy-hook.php), so they have to be run by hand. When one is forgotten the
// symptom is silent - admin/google_login.php catches the PDOException and redirects to
// login.php?e=setup, which says only "setup incomplete". This script names the missing piece.
//
// Run it on the server (dev/ is not deployed, so copy it up first):
//   php ~/check-admin-schema.php
// Exits 0 when everything the panel needs is present, 1 otherwise.

$home = getenv('HOME') ?: dirname(__DIR__);
$f = $home . '/public_html/admin/includes/config.local.php';
if (!is_file($f)) { $f = dirname(__DIR__) . '/admin/includes/config.local.php'; }
echo "config file   : $f\n";
if (!is_file($f)) { echo "\nRESULT: FAIL - no config.local.php found\n"; exit(1); }

require $f;
defined('DB_HOST') || define('DB_HOST', (string)(getenv('VG_DB_HOST') ?: 'localhost'));
defined('DB_NAME') || define('DB_NAME', (string)(getenv('VG_DB_NAME') ?: 'visionguard2026_vg'));
defined('DB_USER') || define('DB_USER', (string)(getenv('VG_DB_USER') ?: ''));
defined('DB_PASS') || define('DB_PASS', (string)(getenv('VG_DB_PASS') ?: ''));
printf("database      : %s as %s\n\n", DB_NAME, DB_USER === '' ? '(EMPTY)' : DB_USER);

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Throwable $e) {
    echo "RESULT: FAIL - cannot connect: " . $e->getMessage() . "\n";
    exit(1);
}

// table => [column => which migration adds it]
$required = [
    'admin_users' => [
        'id'            => '(base schema)',
        'username'      => '(base schema)',
        'role'          => '(base schema)',
        'active'        => '(base schema)',
        'google_email'  => 'migration-admin-google.sql',
    ],
];

$problems = [];
foreach ($required as $table => $columns) {
    try {
        $have = [];
        foreach ($pdo->query('SHOW COLUMNS FROM `' . $table . '`') as $row) { $have[$row['Field']] = true; }
    } catch (Throwable $e) {
        $problems[] = "table `$table` is missing entirely";
        echo "$table: MISSING TABLE\n";
        continue;
    }
    foreach ($columns as $col => $source) {
        $ok = isset($have[$col]);
        printf("%-12s %-14s %s%s\n", $table, $col, $ok ? 'ok' : 'MISSING', $ok ? '' : "  <- add via $source");
        if (!$ok) { $problems[] = "`$table`.`$col` missing - run $source"; }
    }
}

// The column can exist while nobody is linked to it, which fails sign-in just as completely.
if (!$problems) {
    $n = (int)$pdo->query("SELECT COUNT(*) FROM admin_users WHERE google_email IS NOT NULL AND google_email <> '' AND active = 1 AND role = 'admin'")->fetchColumn();
    echo "\nlinked admins : $n\n";
    if ($n === 0) {
        $problems[] = "no active admin row has google_email set - nobody can sign in";
    }
}

if ($problems) {
    echo "\nRESULT: FAIL\n - " . implode("\n - ", $problems) . "\n";
    exit(1);
}
echo "\nRESULT: OK - admin panel schema is complete\n";
