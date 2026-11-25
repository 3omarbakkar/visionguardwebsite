<?php
// Checkout preview: is this promo code usable? The order itself is re-checked in lead.php.
require_once __DIR__ . '/../admin/includes/db.php';
require_once __DIR__ . '/_promo.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

$code = strtoupper(trim((string)($_GET['code'] ?? '')));
if ($code === '' || strlen($code) > 32 || !preg_match('/^[A-Z0-9_-]+$/', $code)) {
    echo json_encode(['ok' => false, 'error' => 'invalid_promo'], JSON_UNESCAPED_UNICODE);
    exit;
}
try {
    $pct = vg_promo_percent(db(), $code);
} catch (Throwable $e) {
    error_log('Vision Guard promo check failed: ' . $e->getMessage());
    $pct = 0;
}
if ($pct > 0) {
    echo json_encode(['ok' => true, 'code' => $code, 'percent' => $pct], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['ok' => false, 'error' => 'invalid_promo'], JSON_UNESCAPED_UNICODE);
}
