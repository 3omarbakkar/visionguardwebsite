<?php
// Admin sign-in: Google by default (the button posts the ID token to google_login.php).
// TEMPORARY password fallback: shown and accepted ONLY when the server defines
//   define('VG_ADMIN_PASSWORD_LOGIN', true);
// in admin/includes/config.local.php (server-only, gitignored). Delete that line to switch it off.
require_once __DIR__ . '/includes/ratelimit.php';
require_once __DIR__ . '/includes/auth.php';

if (current_admin()) { header('Location: index.php'); exit; }

$vgPasswordLogin = defined('VG_ADMIN_PASSWORD_LOGIN') && VG_ADMIN_PASSWORD_LOGIN === true;

$vgGoogleClientId = '249338136868-dq98bt7kr5b5m552h69tgjeu2vj6u8ir.apps.googleusercontent.com';
$vgGoogleLoginUri = 'https://visionguardeg.com/admin/google_login.php';

// Error code -> message map; the raw ?e= value is never echoed.
$errors = [
    'csrf'   => 'تعذّر التحقق من الطلب. حاول مرة أخرى.',
    'token'  => 'تعذّر التحقق من حساب جوجل. حاول مرة أخرى.',
    'denied' => 'هذا الحساب غير مصرّح له بدخول لوحة التحكم.',
    'rate'   => 'محاولات دخول كثيرة. انتظر 15 دقيقة ثم حاول مرة أخرى.',
    'setup'  => 'إعداد تسجيل الدخول غير مكتمل. تواصل مع المسؤول.',
];
$e = (string)($_GET['e'] ?? '');
$error = $errors[$e] ?? '';

if ($vgPasswordLogin && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    // Rate limit per client IP (not per username) so an attacker cannot lock the owner out.
    $rate = vg_admin_login_rate_state('pw-login:' . (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    if ($rate['blocked']) {
        $error = $errors['rate'];
    } else {
        $stmt = db()->prepare("SELECT * FROM admin_users WHERE username = ? AND active = 1 AND role = 'admin' LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, (string)$user['password_hash'])) {
            vg_admin_login_succeeded($rate);
            session_regenerate_id(true);
            $_SESSION['admin'] = ['id' => $user['id'], 'username' => $user['username'], 'role' => 'admin', 'full_name' => $user['full_name'] ?? $user['username']];
            header('Location: index.php');
            exit;
        }
        vg_admin_login_failed($rate);
        $error = 'بيانات الدخول غير صحيحة.';
        usleep(400000); // slow down brute-force attempts a bit
    }
}

$page_title = 'تسجيل الدخول';
require __DIR__ . '/includes/layout_top.php';
?>
<div style="max-width:380px;margin:60px auto 0">
  <div class="card">
    <h2 style="margin-top:0">دخول لوحة التحكم</h2>
    <?php if ($error): ?><div class="flash err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <p style="color:#666;margin-bottom:16px">سجّل الدخول بحساب جوجل المصرّح له.</p>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <div id="g_id_onload"
         data-client_id="<?= htmlspecialchars($vgGoogleClientId) ?>"
         data-ux_mode="redirect"
         data-login_uri="<?= htmlspecialchars($vgGoogleLoginUri) ?>"
         data-auto_prompt="false"></div>
    <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline" data-text="signin_with" data-shape="rectangular" data-logo_alignment="left"></div>
    <?php if ($vgPasswordLogin): ?>
    <hr style="margin:20px 0;border:0;border-top:1px solid #ddd">
    <form method="post">
      <?= csrf_field() ?>
      <label>اسم المستخدم</label>
      <input type="text" name="username" required autocomplete="username">
      <label>كلمة المرور</label>
      <input type="password" name="password" required autocomplete="current-password">
      <div style="margin-top:16px"><button class="btn" type="submit" style="width:100%">دخول</button></div>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
