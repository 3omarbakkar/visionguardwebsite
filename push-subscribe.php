<?php
// Stores push subscriptions (only from real browser push services). Sending is done from /admin/notify.php.
header('Content-Type: application/json');
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('{"ok":false}'); }
$body = file_get_contents('php://input');
if (strlen($body) > 4096) { http_response_code(413); exit('{"ok":false}'); }
$sub = json_decode($body, true);
$ep = is_array($sub) ? ($sub['endpoint'] ?? '') : '';
$host = is_string($ep) ? strtolower((string)parse_url($ep, PHP_URL_HOST)) : '';
$scheme = is_string($ep) ? parse_url($ep, PHP_URL_SCHEME) : '';
$allowed = ['fcm.googleapis.com', 'updates.push.services.mozilla.com', 'web.push.apple.com'];
$okHost = in_array($host, $allowed, true)
  || preg_match('/\.push\.apple\.com$/', $host) || preg_match('/\.notify\.windows\.com$/', $host)
  || preg_match('/\.push\.services\.mozilla\.com$/', $host);
if ($scheme !== 'https' || !$okHost || empty($sub['keys']['p256dh']) || empty($sub['keys']['auth'])) {
  http_response_code(400); exit('{"ok":false}');
}
$dir = __DIR__ . '/push-data';
if (!is_dir($dir)) { mkdir($dir, 0750, true); file_put_contents($dir . '/.htaccess', "Require all denied\n"); }

// Rate limit: 10 registrations / hour / IP
$rf = $dir . '/sub-rate-' . md5($_SERVER['REMOTE_ADDR'] ?? '0') . '.json';
$now = time(); $log = is_file($rf) ? (json_decode(@file_get_contents($rf), true) ?: []) : [];
$log = array_values(array_filter($log, fn($t) => $t > $now - 3600));
if (count($log) >= 10) { http_response_code(429); exit('{"ok":false}'); }
$log[] = $now; @file_put_contents($rf, json_encode($log));

$file = $dir . '/subscriptions.json';
$all = is_file($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
if (count($all) >= 20000 && !isset($all[md5($ep)])) { http_response_code(507); exit('{"ok":false}'); }
$all[md5($ep)] = ['sub' => ['endpoint' => $ep, 'keys' => ['p256dh' => $sub['keys']['p256dh'], 'auth' => $sub['keys']['auth']]], 'at' => date('c')];
file_put_contents($file, json_encode($all), LOCK_EX);
echo '{"ok":true}';
