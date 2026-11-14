<?php
// Customer account API: register / login / logout / me.
require_once __DIR__ . '/_customers.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');

function vg_auth_out($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function vg_auth_origin_ok() {
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') return false;
    $origin = trim((string)($_SERVER['HTTP_ORIGIN'] ?? ''));
    if ($origin !== '') {
        $parts = parse_url($origin);
        return is_array($parts) && strtolower((string)($parts['host'] ?? '')) === preg_replace('/:\d+$/', '', $host);
    }
    $referer = trim((string)($_SERVER['HTTP_REFERER'] ?? ''));
    if ($referer !== '') {
        $parts = parse_url($referer);
        return is_array($parts) && strtolower((string)($parts['host'] ?? '')) === preg_replace('/:\d+$/', '', $host);
    }
    // SameSite=Lax remains a second layer; requests without origin metadata are rejected.
    return false;
}
function vg_auth_hash($value) { return hash('sha256', (string)$value); }
function vg_auth_rate($pdo, $action, $phone, $ip, $windowSeconds, $phoneLimit, $ipLimit) {
    $since = date('Y-m-d H:i:s', time() - $windowSeconds);
    $ph = vg_auth_hash($phone);
    $ih = vg_auth_hash($ip);
    $s = $pdo->prepare('SELECT SUM(phone_hash = ?) AS by_phone, SUM(ip_hash = ?) AS by_ip FROM vg_auth_attempts WHERE action_name = ? AND attempted_at >= ?');
    $s->execute([$ph, $ih, $action, $since]);
    $r = $s->fetch(PDO::FETCH_ASSOC) ?: [];
    return ((int)($r['by_phone'] ?? 0) >= $phoneLimit) || ((int)($r['by_ip'] ?? 0) >= $ipLimit);
}
function vg_auth_record($pdo, $action, $phone, $ip) {
    $s = $pdo->prepare('INSERT INTO vg_auth_attempts (action_name, phone_hash, ip_hash, attempted_at) VALUES (?,?,?,NOW())');
    $s->execute([$action, vg_auth_hash($phone), vg_auth_hash($ip)]);
    // Small bounded cleanup; old rows are not useful for throttling.
    if (random_int(1, 100) === 1) $pdo->exec('DELETE FROM vg_auth_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 2 DAY) LIMIT 1000');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') vg_auth_out(['ok' => false, 'error' => 'method_not_allowed'], 405);
if (!vg_auth_origin_ok()) vg_auth_out(['ok' => false, 'error' => 'bad_origin'], 403);
$contentType = strtolower(trim(explode(';', (string)($_SERVER['CONTENT_TYPE'] ?? ''))[0]));
if ($contentType !== 'application/json') vg_auth_out(['ok' => false, 'error' => 'json_required'], 415);
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) vg_auth_out(['ok' => false, 'error' => 'bad_json'], 400);
if (!empty($data['website'])) vg_auth_out(['ok' => true]);

vg_session();
vg_boot();
$pdo = db();
$action = (string)($data['action'] ?? '');
if ($action === 'me') vg_auth_out(['ok' => true, 'customer' => vg_current_customer()]);
if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $p['path'], 'domain' => $p['domain'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
    }
    session_destroy();
    vg_auth_out(['ok' => true]);
}
if ($action === 'orders') {
    $me = vg_current_customer();
    if (!$me) vg_auth_out(['ok' => false, 'error' => 'login_required'], 401);
    $q = $pdo->prepare("SELECT id, status, total, payment_method, cart_json, created_at FROM leads WHERE customer_id = ? AND type = 'order' ORDER BY id DESC LIMIT 50");
    $q->execute([(int)$me['id']]);
    $orders = [];
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $items = json_decode((string)$r['cart_json'], true);
        $list = [];
        if (is_array($items)) {
            foreach ($items as $it) {
                if (!is_array($it)) continue;
                $list[] = ['name' => (string)($it['name'] ?? ''), 'name_en' => (string)($it['name_en'] ?? ''), 'qty' => (int)($it['qty'] ?? 1)];
            }
        }
        $orders[] = [
            'id' => (int)$r['id'],
            'status' => (string)$r['status'],
            'total' => (float)$r['total'],
            'payment_method' => (string)$r['payment_method'],
            'date' => substr((string)$r['created_at'], 0, 10),
            'items' => $list,
        ];
    }
    vg_auth_out(['ok' => true, 'orders' => $orders]);
}
if ($action === 'google') {
    $gClient = '249338136868-dq98bt7kr5b5m552h69tgjeu2vj6u8ir.apps.googleusercontent.com';
    $gip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $tok = (string)($data['credential'] ?? '');
    if ($tok === '' || strlen($tok) > 4096) vg_auth_out(['ok' => false, 'error' => 'bad_token'], 422);
    if (vg_auth_rate($pdo, 'google', $gip, $gip, 900, 20, 20)) vg_auth_out(['ok' => false, 'error' => 'too_many'], 429);
    vg_auth_record($pdo, 'google', $gip, $gip);
    $ctx = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
    $raw = @file_get_contents('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($tok), false, $ctx);
    $info = json_decode((string)$raw, true);
    $okTok = is_array($info)
        && (($info['aud'] ?? '') === $gClient)
        && in_array(($info['iss'] ?? ''), ['accounts.google.com', 'https://accounts.google.com'], true)
        && ((int)($info['exp'] ?? 0) > time())
        && (($info['email_verified'] ?? '') === true || ($info['email_verified'] ?? '') === 'true');
    $gEmail = $okTok ? vg_norm_email($info['email'] ?? '') : '';
    if (!$okTok || !vg_email_ok($gEmail)) vg_auth_out(['ok' => false, 'error' => 'bad_token'], 401);
    $gs = $pdo->prepare('SELECT id, name, phone, email FROM customers WHERE email = ?');
    $gs->execute([$gEmail]);
    $grow = $gs->fetch(PDO::FETCH_ASSOC);
    if (!$grow) {
        $gName = trim((string)($info['name'] ?? ''));
        if ($gName === '') $gName = strstr($gEmail, '@', true);
        $gName = mb_substr($gName, 0, 120);
        try {
            $gi = $pdo->prepare('INSERT INTO customers (name, email, pass_hash, last_login) VALUES (?, ?, ?, NOW())');
            $gi->execute([$gName, $gEmail, password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT)]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) != 1062) throw $e;
        }
        $gs->execute([$gEmail]);
        $grow = $gs->fetch(PDO::FETCH_ASSOC);
    }
    if (!$grow) vg_auth_out(['ok' => false, 'error' => 'server'], 500);
    session_regenerate_id(true);
    $_SESSION = ['cid' => (int)$grow['id']];
    $pdo->prepare('UPDATE customers SET last_login = NOW() WHERE id = ?')->execute([$grow['id']]);
    vg_auth_out(['ok' => true, 'customer' => vg_cust_public($grow)]);
}
if (!in_array($action, ['register', 'login', 'set_email'], true)) vg_auth_out(['ok' => false, 'error' => 'unknown_action'], 400);
$ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');

// Existing (phone-only) customers can add an email after signing in.
if ($action === 'set_email') {
    $me = vg_current_customer();
    if (!$me) vg_auth_out(['ok' => false, 'error' => 'login_required'], 401);
    $email = vg_norm_email($data['email'] ?? '');
    if (!vg_email_ok($email)) vg_auth_out(['ok' => false, 'error' => 'bad_email'], 422);
    if (vg_auth_rate($pdo, 'set_email', 'cid' . $me['id'], $ip, 3600, 5, 10)) vg_auth_out(['ok' => false, 'error' => 'too_many'], 429);
    vg_auth_record($pdo, 'set_email', 'cid' . $me['id'], $ip);
    try {
        $pdo->prepare('UPDATE customers SET email = ? WHERE id = ? AND (email IS NULL OR email = \'\')')->execute([$email, (int)$me['id']]);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? null) == 1062) vg_auth_out(['ok' => false, 'error' => 'email_exists'], 409);
        throw $e;
    }
    vg_auth_out(['ok' => true, 'customer' => vg_current_customer()]);
}

$pass = (string)($data['password'] ?? '');
if (mb_strlen($pass, 'UTF-8') < 10 || mb_strlen($pass, 'UTF-8') > 100) vg_auth_out(['ok' => false, 'error' => 'bad_password'], 422);

if ($action === 'register') {
    $email = vg_norm_email($data['email'] ?? '');
    if (!vg_email_ok($email)) vg_auth_out(['ok' => false, 'error' => 'bad_email'], 422);
    if (vg_auth_rate($pdo, 'register', $email, $ip, 3600, 3, 5)) vg_auth_out(['ok' => false, 'error' => 'too_many'], 429);
    $name = trim((string)($data['name'] ?? ''));
    if ($name === '') vg_auth_out(['ok' => false, 'error' => 'name_required'], 422);
    $name = mb_substr($name, 0, 120);
    vg_auth_record($pdo, 'register', $email, $ip);
    try {
        $s = $pdo->prepare('INSERT INTO customers (name, email, pass_hash, last_login) VALUES (?, ?, ?, NOW())');
        $s->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? null) == 1062) vg_auth_out(['ok' => false, 'error' => 'email_exists'], 409);
        throw $e;
    }
    session_regenerate_id(true);
    $_SESSION = ['cid' => (int)$pdo->lastInsertId()];
    vg_auth_out(['ok' => true, 'customer' => vg_current_customer()]);
}

// Login: email (new accounts) or mobile number (accounts created before email sign-up).
$ident = trim((string)($data['email'] ?? ($data['identifier'] ?? '')));
if (strpos($ident, '@') !== false) {
    $key = vg_norm_email($ident);
    if (!vg_email_ok($key)) vg_auth_out(['ok' => false, 'error' => 'bad_email'], 422);
    $lookup = 'SELECT id, name, phone, email, pass_hash FROM customers WHERE email = ?';
} else {
    $key = vg_norm_phone($ident);
    if (!preg_match('/^01[0125][0-9]{8}$/', $key)) vg_auth_out(['ok' => false, 'error' => 'bad_login_id'], 422);
    $lookup = 'SELECT id, name, phone, email, pass_hash FROM customers WHERE phone = ?';
}
if (vg_auth_rate($pdo, 'login', $key, $ip, 900, 8, 30)) vg_auth_out(['ok' => false, 'error' => 'too_many'], 429);
$s = $pdo->prepare($lookup);
$s->execute([$key]);
$row = $s->fetch(PDO::FETCH_ASSOC);
if (!$row || !password_verify($pass, $row['pass_hash'])) {
    vg_auth_record($pdo, 'login', $key, $ip);
    vg_auth_out(['ok' => false, 'error' => 'bad_login'], 401);
}
// Successful login clears failed attempts for this identifier and rotates the session ID.
$del = $pdo->prepare("DELETE FROM vg_auth_attempts WHERE action_name = 'login' AND phone_hash = ?");
$del->execute([vg_auth_hash($key)]);
session_regenerate_id(true);
$_SESSION = ['cid' => (int)$row['id']];
$pdo->prepare('UPDATE customers SET last_login = NOW() WHERE id = ?')->execute([$row['id']]);
vg_auth_out(['ok' => true, 'customer' => vg_cust_public($row)]);
