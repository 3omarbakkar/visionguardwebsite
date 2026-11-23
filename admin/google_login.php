<?php
// Receives the Google Identity Services redirect (POST: credential + g_csrf_token), verifies the ID token
// with Google, and signs in the matching admin. Google-only: there is no password fallback.
require_once __DIR__ . '/includes/auth.php';

const VG_GOOGLE_CLIENT_ID = '249338136868-dq98bt7kr5b5m552h69tgjeu2vj6u8ir.apps.googleusercontent.com';
// Hard allow-list on top of the DB lookup (defence in depth): only these Google accounts may ever sign in.
const VG_ADMIN_GOOGLE_ALLOWED = ['3omarbakkar@gmail.com'];

function vg_gl_fail(string $code): void {
    usleep(400000); // slow down brute-force attempts a bit
    header('Location: login.php?e=' . $code);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Location: login.php'); exit; }

// Google's double-submit CSRF check: the g_csrf_token cookie must equal the g_csrf_token body field.
$cookieTok = (string)($_COOKIE['g_csrf_token'] ?? '');
$bodyTok   = (string)($_POST['g_csrf_token'] ?? '');
if ($cookieTok === '' || $bodyTok === '' || !hash_equals($cookieTok, $bodyTok)) vg_gl_fail('csrf');

$cred = (string)($_POST['credential'] ?? '');
if ($cred === '' || strlen($cred) > 4096) vg_gl_fail('token');

// Rate limit per client IP. The "username" slot is IP-scoped so one attacker cannot lock the owner out.
$rate = vg_admin_login_rate_state('google-login:' . (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
if ($rate['blocked']) { header('Location: login.php?e=rate'); exit; }

$ctx  = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
$raw  = @file_get_contents('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($cred), false, $ctx);
$info = json_decode((string)$raw, true);
$ok = is_array($info)
    && (($info['aud'] ?? '') === VG_GOOGLE_CLIENT_ID)
    && in_array(($info['iss'] ?? ''), ['accounts.google.com', 'https://accounts.google.com'], true)
    && ((int)($info['exp'] ?? 0) > time())
    && (($info['email_verified'] ?? '') === true || ($info['email_verified'] ?? '') === 'true');
if (!$ok) { vg_admin_login_failed($rate); vg_gl_fail('token'); }

$email = strtolower(trim((string)($info['email'] ?? '')));
if ($email === '' || !in_array($email, VG_ADMIN_GOOGLE_ALLOWED, true)) { vg_admin_login_failed($rate); vg_gl_fail('denied'); }

try {
    $stmt = db()->prepare("SELECT * FROM admin_users WHERE google_email = ? AND active = 1 AND role = 'admin' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('admin google login: lookup failed (is migration-admin-google.sql applied?)');
    vg_gl_fail('setup');
}
if (!$user) { vg_admin_login_failed($rate); vg_gl_fail('denied'); }

vg_admin_login_succeeded($rate);
session_regenerate_id(true);
$_SESSION['admin'] = [
    'id'        => $user['id'],
    'username'  => $user['username'],
    'role'      => $user['role'] ?? 'admin',
    'full_name' => $user['full_name'] ?? $user['username'],
];
header('Location: index.php');
exit;
