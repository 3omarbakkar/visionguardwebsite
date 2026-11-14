<?php
require_once __DIR__ . '/admin/includes/db.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function vg_push_out($ok, $status=200) { http_response_code($status); echo json_encode(['ok'=>$ok], JSON_UNESCAPED_UNICODE); exit; }
function vg_push_host_ok($host) {
    $host = strtolower(rtrim((string)$host, '.'));
    $exact = ['fcm.googleapis.com','updates.push.services.mozilla.com','push.services.mozilla.com','web.push.apple.com'];
    if (in_array($host, $exact, true)) return true;
    return (bool)preg_match('/^[a-z0-9.-]+\.notify\.windows\.com$/', $host);
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') vg_push_out(false, 405);
$host = strtolower(preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? '')));
$origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
if ($origin !== '') {
    $o = parse_url($origin);
    if (!is_array($o) || strtolower((string)($o['host'] ?? '')) !== $host) vg_push_out(false, 403);
} else vg_push_out(false, 403);
$contentType = strtolower(trim(explode(';', (string)($_SERVER['CONTENT_TYPE'] ?? ''))[0]));
if ($contentType !== 'application/json') vg_push_out(false, 415);
$raw = file_get_contents('php://input', false, null, 0, 8193);
if (!$raw || strlen($raw) > 8192) vg_push_out(false, 413);
$d = json_decode($raw, true);
if (!is_array($d) || empty($d['endpoint']) || !is_array($d['keys'] ?? null)) vg_push_out(false, 422);
$endpoint = (string)$d['endpoint'];
$u = parse_url($endpoint);
if (!is_array($u) || strtolower((string)($u['scheme'] ?? '')) !== 'https' || empty($u['host']) || !vg_push_host_ok($u['host']) || isset($u['user']) || isset($u['pass'])) vg_push_out(false, 422);
$p256 = (string)($d['keys']['p256dh'] ?? '');
$auth = (string)($d['keys']['auth'] ?? '');
$decode = function($v) { return base64_decode(strtr($v, '-_', '+/') . str_repeat('=', (4 - strlen($v) % 4) % 4), true); };
if (!preg_match('/^[A-Za-z0-9_-]{80,100}$/', $p256) || !preg_match('/^[A-Za-z0-9_-]{16,64}$/', $auth)) vg_push_out(false, 422);
if (strlen((string)$decode($p256)) !== 65 || strlen((string)$decode($auth)) < 8) vg_push_out(false, 422);

$pdo = db();
$pdo->exec("CREATE TABLE IF NOT EXISTS push_subscribe_attempts (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 ip_hash CHAR(64) NOT NULL,
 attempted_at DATETIME NOT NULL,
 KEY idx_push_sub_ip_time (ip_hash, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$ipHash = hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
$q = $pdo->prepare('SELECT COUNT(*) FROM push_subscribe_attempts WHERE ip_hash = ? AND attempted_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)');
$q->execute([$ipHash]);
if ((int)$q->fetchColumn() >= 30) vg_push_out(false, 429);
$pdo->prepare('INSERT INTO push_subscribe_attempts (ip_hash, attempted_at) VALUES (?, NOW())')->execute([$ipHash]);

$dir = __DIR__ . '/push-data';
if (!is_dir($dir) && !@mkdir($dir, 0750, true)) vg_push_out(false, 500);
$file = $dir . '/subscriptions.json';
$fp = @fopen($file, 'c+');
if (!$fp) vg_push_out(false, 500);
if (!flock($fp, LOCK_EX)) { fclose($fp); vg_push_out(false, 500); }
$contents = stream_get_contents($fp);
$rows = json_decode($contents ?: '[]', true);
if (!is_array($rows)) $rows = [];
$rows = array_values(array_filter($rows, function($row) use ($endpoint) { return is_array($row) && ($row['sub']['endpoint'] ?? '') !== $endpoint; }));
$rows[] = ['sub' => ['endpoint'=>$endpoint, 'keys'=>['p256dh'=>$p256, 'auth'=>$auth]], 'created_at'=>date('c')];
if (count($rows) > 5000) $rows = array_slice($rows, -5000);
rewind($fp); ftruncate($fp, 0);
$ok = fwrite($fp, json_encode($rows, JSON_UNESCAPED_SLASHES)) !== false;
fflush($fp); flock($fp, LOCK_UN); fclose($fp);
if (!$ok) vg_push_out(false, 500);
vg_push_out(true);
