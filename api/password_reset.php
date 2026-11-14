<?php
// Customer password reset with a 6-digit one-time code sent by email.
// POST JSON: {action:"request", email}  or  {action:"verify", email, code, password}
require_once __DIR__ . '/_customers.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');

function vg_pr_out(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function vg_pr_origin_ok(): bool {
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
    return false;
}
function vg_pr_hash(string $value): string { return hash('sha256', $value); }
function vg_pr_rate(PDO $pdo, string $action, string $phone, string $ip, int $window, int $phoneLimit, int $ipLimit): bool {
    $since = date('Y-m-d H:i:s', time() - $window);
    $s = $pdo->prepare('SELECT SUM(phone_hash = ?) AS by_phone, SUM(ip_hash = ?) AS by_ip FROM vg_auth_attempts WHERE action_name = ? AND attempted_at >= ?');
    $s->execute([vg_pr_hash($phone), vg_pr_hash($ip), $action, $since]);
    $r = $s->fetch(PDO::FETCH_ASSOC) ?: [];
    return ((int)($r['by_phone'] ?? 0) >= $phoneLimit) || ((int)($r['by_ip'] ?? 0) >= $ipLimit);
}
function vg_pr_record(PDO $pdo, string $action, string $phone, string $ip): void {
    $pdo->prepare('INSERT INTO vg_auth_attempts (action_name, phone_hash, ip_hash, attempted_at) VALUES (?,?,?,NOW())')
        ->execute([$action, vg_pr_hash($phone), vg_pr_hash($ip)]);
    if (random_int(1, 100) === 1) {
        $pdo->exec('DELETE FROM vg_auth_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 2 DAY) LIMIT 1000');
        $pdo->exec('DELETE FROM vg_password_resets WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 DAY) LIMIT 1000');
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') vg_pr_out(['ok' => false, 'error' => 'method_not_allowed'], 405);
if (!vg_pr_origin_ok()) vg_pr_out(['ok' => false, 'error' => 'bad_origin'], 403);
$contentType = strtolower(trim(explode(';', (string)($_SERVER['CONTENT_TYPE'] ?? ''))[0]));
if ($contentType !== 'application/json') vg_pr_out(['ok' => false, 'error' => 'json_required'], 415);
$raw = file_get_contents('php://input', false, null, 0, 4097);
$data = $raw ? json_decode($raw, true) : null;
if (!is_array($data)) vg_pr_out(['ok' => false, 'error' => 'bad_json'], 400);
if (!empty($data['website'])) vg_pr_out(['ok' => true]);

$action = (string)($data['action'] ?? '');
if (!in_array($action, ['request', 'verify'], true)) vg_pr_out(['ok' => false, 'error' => 'unknown_action'], 400);
$phone = vg_norm_email($data['email'] ?? '');  // identifier hashed into the phone_hash columns
if (!vg_email_ok($phone)) vg_pr_out(['ok' => false, 'error' => 'bad_email'], 422);
$ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');

vg_session();
vg_boot();
$pdo = db();

if ($action === 'request') {
    if (vg_pr_rate($pdo, 'reset_req', $phone, $ip, 3600, 3, 10)) vg_pr_out(['ok' => false, 'error' => 'too_many'], 429);
    vg_pr_record($pdo, 'reset_req', $phone, $ip);

    // Always do the same work so response timing does not reveal whether the phone exists.
    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $codeHash = password_hash($code, PASSWORD_DEFAULT);

    $c = $pdo->prepare('SELECT id FROM customers WHERE email = ? LIMIT 1');
    $c->execute([$phone]);
    $customerId = (int)($c->fetchColumn() ?: 0);

    if ($customerId > 0) {
        // Cooldown: never send more than one email per minute per account.
        $last = $pdo->prepare('SELECT (TIMESTAMPDIFF(SECOND, created_at, NOW()) < 60) AS cooling FROM vg_password_resets WHERE customer_id = ? ORDER BY id DESC LIMIT 1');
        $last->execute([$customerId]);
        $cooling = (int)$last->fetchColumn() === 1;

        if (!$cooling) {
            $pdo->prepare('UPDATE vg_password_resets SET used_at = NOW() WHERE customer_id = ? AND used_at IS NULL')->execute([$customerId]);
            $pdo->prepare('INSERT INTO vg_password_resets (customer_id, phone_hash, code_hash, ip_hash, created_at, expires_at) VALUES (?,?,?,?,NOW(),DATE_ADD(NOW(), INTERVAL 10 MINUTE))')
                ->execute([$customerId, vg_pr_hash($phone), $codeHash, vg_pr_hash($ip)]);
            $subject = '=?UTF-8?B?' . base64_encode('Vision Guard - كود استعادة كلمة المرور') . '?=';
            $body = "كود استعادة كلمة المرور: $code\nالكود صالح 10 دقائق ويُستخدم مرة واحدة. لو ما طلبتش ده، تجاهل الرسالة.\n\nYour Vision Guard password reset code: $code\nValid for 10 minutes. If you did not request it, ignore this email.\n";
            $sent = @mail($phone, $subject, $body, "Content-Type: text/plain; charset=UTF-8\r\nFrom: Vision Guard <noreply@visionguardeg.com>");
            if (!$sent) {
                $pdo->prepare('UPDATE vg_password_resets SET used_at = NOW() WHERE customer_id = ? AND used_at IS NULL')->execute([$customerId]);
                vg_pr_out(['ok' => false, 'error' => 'email_failed'], 502);
            }
        }
    }
    // Same answer for existing and unknown emails.
    vg_pr_out(['ok' => true]);
}

// ----- verify -----
if (vg_pr_rate($pdo, 'reset_verify', $phone, $ip, 3600, 10, 25)) vg_pr_out(['ok' => false, 'error' => 'too_many'], 429);
vg_pr_record($pdo, 'reset_verify', $phone, $ip);

$code = trim((string)($data['code'] ?? ''));
$code = strtr($code, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
$code = preg_replace('/\s+/', '', $code);
$pass = (string)($data['password'] ?? '');
if (!preg_match('/^[0-9]{6}$/', $code)) vg_pr_out(['ok' => false, 'error' => 'bad_code'], 422);
if (strlen($pass) < 10 || strlen($pass) > 100) vg_pr_out(['ok' => false, 'error' => 'bad_password'], 422);

$s = $pdo->prepare('SELECT id, customer_id, code_hash, attempts FROM vg_password_resets WHERE phone_hash = ? AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1');
$s->execute([vg_pr_hash($phone)]);
$row = $s->fetch(PDO::FETCH_ASSOC);
if (!$row) vg_pr_out(['ok' => false, 'error' => 'bad_code'], 400);
if ((int)$row['attempts'] >= 5) vg_pr_out(['ok' => false, 'error' => 'too_many'], 429);

// Count the attempt before checking the code so concurrent guesses cannot bypass the limit.
$pdo->prepare('UPDATE vg_password_resets SET attempts = attempts + 1 WHERE id = ? AND used_at IS NULL')->execute([(int)$row['id']]);
if (!password_verify($code, (string)$row['code_hash'])) vg_pr_out(['ok' => false, 'error' => 'bad_code'], 400);

$pdo->beginTransaction();
try {
    $use = $pdo->prepare('UPDATE vg_password_resets SET used_at = NOW() WHERE id = ? AND used_at IS NULL');
    $use->execute([(int)$row['id']]);
    if ($use->rowCount() !== 1) {
        $pdo->rollBack();
        vg_pr_out(['ok' => false, 'error' => 'bad_code'], 400);
    }
    $pdo->prepare('UPDATE customers SET pass_hash = ? WHERE id = ?')
        ->execute([password_hash($pass, PASSWORD_DEFAULT), (int)$row['customer_id']]);
    $pdo->prepare('UPDATE vg_password_resets SET used_at = NOW() WHERE customer_id = ? AND used_at IS NULL')->execute([(int)$row['customer_id']]);
    $pdo->prepare("DELETE FROM vg_auth_attempts WHERE action_name IN ('login','reset_verify') AND phone_hash = ?")->execute([vg_pr_hash($phone)]);
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Vision Guard password reset failed: ' . $e->getMessage());
    vg_pr_out(['ok' => false, 'error' => 'server_error'], 500);
}

$cust = $pdo->prepare('SELECT id, name, phone, email FROM customers WHERE id = ?');
$cust->execute([(int)$row['customer_id']]);
$me = $cust->fetch(PDO::FETCH_ASSOC);
if (!$me) vg_pr_out(['ok' => false, 'error' => 'server_error'], 500);

session_regenerate_id(true);
$_SESSION = ['cid' => (int)$me['id']];
vg_pr_out(['ok' => true, 'customer' => vg_cust_public($me)]);
