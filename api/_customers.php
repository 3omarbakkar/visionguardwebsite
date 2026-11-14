<?php
// Customer accounts helper: session + tables. Included by customer.php and lead.php.
require_once __DIR__ . '/../admin/includes/db.php';
require_once __DIR__ . '/_stock.php';

function vg_session() {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    // Harden cookie/session handling. HTTPS may be reported as the string "off".
    $https = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
        || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_name('vgc');
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 30,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function vg_boot() {
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS vg_auth_attempts (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        action_name VARCHAR(16) NOT NULL,
        phone_hash CHAR(64) NOT NULL,
        ip_hash CHAR(64) NOT NULL,
        attempted_at DATETIME NOT NULL,
        KEY idx_auth_phone_action_time (phone_hash, action_name, attempted_at),
        KEY idx_auth_ip_action_time (ip_hash, action_name, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS vg_public_lead_attempts (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        lead_type VARCHAR(20) NOT NULL,
        phone_hash CHAR(64) NOT NULL,
        ip_hash CHAR(64) NOT NULL,
        attempted_at DATETIME NOT NULL,
        KEY idx_public_lead_ip_type_time (ip_hash, lead_type, attempted_at),
        KEY idx_public_lead_phone_type_time (phone_hash, lead_type, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        pass_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL DEFAULT NULL,
        UNIQUE KEY uq_phone (phone)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    // Email-based accounts: add customers.email (unique) and let phone be empty for new accounts.
    $ec = $pdo->query("SHOW COLUMNS FROM customers LIKE 'email'")->fetch();
    if (!$ec) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN email VARCHAR(190) NULL DEFAULT NULL AFTER name");
        try { $pdo->exec("ALTER TABLE customers ADD UNIQUE KEY uq_email (email)"); } catch (Throwable $e) { error_log('Vision Guard uq_email: ' . $e->getMessage()); }
    }
    $pc = $pdo->query("SHOW COLUMNS FROM customers LIKE 'phone'")->fetch(PDO::FETCH_ASSOC);
    if ($pc && strtoupper((string)$pc['Null']) === 'NO') {
        $pdo->exec("ALTER TABLE customers MODIFY phone VARCHAR(20) NULL DEFAULT NULL");
    }
    $c = $pdo->query("SHOW COLUMNS FROM leads LIKE 'customer_id'")->fetch();
    if (!$c) {
        $pdo->exec("ALTER TABLE leads ADD COLUMN customer_id INT UNSIGNED NULL DEFAULT NULL");
    }
    vg_schema_v2();
}

function vg_norm_phone($p) {
    $p = strtr((string)$p, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
    $p = preg_replace('/\D+/', '', $p);
    if (strpos($p, '0020') === 0) $p = substr($p, 4);
    elseif (strpos($p, '20') === 0 && strlen($p) === 12) $p = substr($p, 2);
    if (strlen($p) === 10 && $p[0] === '1') $p = '0' . $p;
    return $p;
}

function vg_current_customer() {
    vg_session();
    if (empty($_SESSION['cid'])) return null;
    $s = db()->prepare('SELECT id, name, phone, email FROM customers WHERE id = ?');
    $s->execute([(int)$_SESSION['cid']]);
    $row = $s->fetch(PDO::FETCH_ASSOC);
    return $row ? vg_cust_public($row) : null;
}

/** Customer fields that are safe to send to the browser. */
function vg_cust_public($row) {
    $email = isset($row['email']) ? trim((string)$row['email']) : '';
    return [
        'id' => (int)$row['id'],
        'name' => (string)$row['name'],
        'phone' => isset($row['phone']) ? (string)$row['phone'] : '',
        'email' => $email,
        'needs_email' => $email === '',
    ];
}

function vg_norm_email($e) {
    return mb_strtolower(trim((string)$e), 'UTF-8');
}

function vg_email_ok($e) {
    return $e !== '' && strlen($e) <= 190 && filter_var($e, FILTER_VALIDATE_EMAIL) !== false;
}
