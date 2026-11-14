<?php
// Public VAPID key for the browser to subscribe with. Returns {"key":null} until the admin opens /admin/notify.php once.
require_once __DIR__ . '/admin/includes/db.php';
header('Content-Type: application/json');
header('Cache-Control: public, max-age=3600');
header('X-Robots-Tag: noindex');
try {
    $st = db()->prepare("SELECT meta_value FROM settings WHERE meta_key = 'vapid_public'");
    $st->execute();
    $k = $st->fetchColumn();
} catch (Throwable $e) { $k = false; }
echo json_encode(['key' => $k ?: null]);
