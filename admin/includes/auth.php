<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/images.php';

if (session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
        || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_name('vg_admin_sess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/admin/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function vg_admin_login_rate_state(string $username): array {
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_login_attempts (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        username_hash CHAR(64) NOT NULL,
        ip_hash CHAR(64) NOT NULL,
        attempted_at DATETIME NOT NULL,
        KEY idx_admin_login_user_time (username_hash, attempted_at),
        KEY idx_admin_login_ip_time (ip_hash, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $u = hash('sha256', strtolower(trim($username)));
    $ip = hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $s = $pdo->prepare('SELECT SUM(username_hash = ?) AS by_user, SUM(ip_hash = ?) AS by_ip FROM admin_login_attempts WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
    $s->execute([$u, $ip]);
    $r = $s->fetch(PDO::FETCH_ASSOC) ?: [];
    return ['blocked' => (int)($r['by_user'] ?? 0) >= 8 || (int)($r['by_ip'] ?? 0) >= 30, 'user_hash' => $u, 'ip_hash' => $ip];
}
function vg_admin_login_failed(array $state): void {
    db()->prepare('INSERT INTO admin_login_attempts (username_hash, ip_hash, attempted_at) VALUES (?,?,NOW())')->execute([$state['user_hash'], $state['ip_hash']]);
    if (random_int(1, 100) === 1) db()->exec('DELETE FROM admin_login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 2 DAY) LIMIT 1000');
}
function vg_admin_login_succeeded(array $state): void {
    db()->prepare('DELETE FROM admin_login_attempts WHERE username_hash = ?')->execute([$state['user_hash']]);
}

function current_admin(): ?array {
    if (empty($_SESSION['admin']['id'])) return null;
    // Re-check the account once per request so a deactivated or demoted staff member loses
    // access immediately instead of keeping the role stored in an old session.
    static $checked = false;
    if (!$checked) {
        $checked = true;
        try {
            $s = db()->prepare('SELECT * FROM admin_users WHERE id = ? LIMIT 1');
            $s->execute([(int)$_SESSION['admin']['id']]);
            $row = $s->fetch();
            if (!$row || (int)($row['active'] ?? 1) !== 1) {
                unset($_SESSION['admin']);
                return null;
            }
            if (isset($row['role']) && $row['role'] !== '') $_SESSION['admin']['role'] = (string)$row['role'];
        } catch (Throwable $e) {
            error_log('Vision Guard admin session re-check failed: ' . $e->getMessage());
        }
    }
    return $_SESSION['admin'] ?? null;
}

function require_login(): void {
    if (!current_admin()) {
        header('Location: login.php');
        exit;
    }
}

function current_role(): string {
    $a = current_admin();
    return $a['role'] ?? 'admin';
}

function require_admin(): void {
    require_login();
    if (current_role() !== 'admin') {
        http_response_code(403);
        die('Access denied. Admins only.');
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_check(): void {
    $token = $_POST['csrf'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(400);
        die('Invalid request (CSRF check failed). Go back and try again.');
    }
}

function flash_set(string $msg, string $type = 'ok'): void {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function flash_get(): ?array {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
