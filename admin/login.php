<?php
// Admin sign-in is Google-only (no password form). The Google button posts the ID token to google_login.php.
require_once __DIR__ . '/includes/auth.php';

if (current_admin()) { header('Location: index.php'); exit; }

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
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
