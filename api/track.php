<?php
// Public analytics beacon. Only INSERTs anonymous events; stores no IP, no cookies.
date_default_timezone_set('Africa/Cairo');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit; }

$ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
if ($ua === '' || preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|headless|lighthouse|pingdom|uptime|curl|python|wget/i', $ua)) {
    http_response_code(204); exit;
}

$raw = file_get_contents('php://input', false, null, 0, 4096);
$d = json_decode((string)$raw, true);
if (!is_array($d)) { http_response_code(204); exit; }

$types = ['pv', 'product', 'cart', 'wa', 'call', 'search'];
$t = (string)($d['t'] ?? '');
if (!in_array($t, $types, true)) { http_response_code(204); exit; }

function vg_cut($v, $n) { return mb_substr(trim((string)$v), 0, $n); }

$path  = vg_cut($d['p'] ?? '/', 120);
$label = vg_cut($d['label'] ?? '', 160);
$results = isset($d['n']) && is_numeric($d['n']) ? max(0, min(100000, (int)$d['n'])) : null;
if ($t === 'search') {
    if (mb_strlen($label) < 2) { http_response_code(204); exit; }
    $label = mb_strtolower($label);
}

// source (page views only)
$src = '';
if ($t === 'pv') {
    $ref  = strtolower((string)($d['ref'] ?? ''));
    $utm  = strtolower(vg_cut($d['utm'] ?? '', 40));
    $mine = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    $hay  = $utm . ' ' . $ref;
    if ($d['fb'] ?? false) { $hay .= ' facebook'; }
    if (preg_match('/facebook|fb\.|instagram|meta/', $hay))       $src = 'facebook';
    elseif (preg_match('/tiktok/', $hay))                          $src = 'tiktok';
    elseif (preg_match('/whatsapp|wa\.me/', $hay))                 $src = 'whatsapp';
    elseif (preg_match('/google|gclid/', $hay))                    $src = 'google';
    elseif ($ref === '' )                                          $src = 'direct';
    elseif ($mine !== '' && strpos($ref, $mine) !== false)         $src = 'direct';
    else                                                           $src = 'other';
}

$dev = preg_match('/mobile|android|iphone|ipad/i', $ua) ? 'mobile' : 'desktop';
$ip  = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$vid = substr(sha1($ip . '|' . $ua . '|' . date('Y-m-d') . '|vg-insights'), 0, 16);

require_once __DIR__ . '/../admin/includes/db.php';

$ins = 'INSERT INTO site_events (ts, d, t, vid, path, src, label, results, dev) VALUES (?,?,?,?,?,?,?,?,?)';
$args = [date('Y-m-d H:i:s'), date('Y-m-d'), $t, $vid, $path, $src, $label, $results, $dev];

try {
    try {
        // light flood guard: 120 events per visitor per 10 minutes
        $c = db()->prepare('SELECT COUNT(*) FROM site_events WHERE vid = ? AND ts > ?');
        $c->execute([$vid, date('Y-m-d H:i:s', time() - 600)]);
        if ((int)$c->fetchColumn() > 120) { http_response_code(204); exit; }
        db()->prepare($ins)->execute($args);
    } catch (PDOException $e) {
        db()->exec("CREATE TABLE IF NOT EXISTS site_events (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            ts DATETIME NOT NULL,
            d DATE NOT NULL,
            t VARCHAR(12) NOT NULL,
            vid CHAR(16) NOT NULL,
            path VARCHAR(120) NULL,
            src VARCHAR(20) NULL,
            label VARCHAR(160) NULL,
            results INT NULL,
            dev VARCHAR(8) NULL,
            KEY idx_dt (d, t),
            KEY idx_tl (t, label),
            KEY idx_vid (vid, ts)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->prepare($ins)->execute($args);
    }
} catch (Throwable $e) { /* never break the storefront */ }

http_response_code(204);
