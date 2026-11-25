<?php
// Admin sign-in: username + password. Only active admin accounts can sign in.
// Brute-force protection: per-user and per-IP limits (see vg_admin_login_rate_state in auth.php).
require_once __DIR__ . '/includes/auth.php';

if (current_admin()) { header('Location: index.php'); exit; }

// Error code -> message map; the raw ?e= value is never echoed.
$errors = [
    'csrf' => 'تعذّر التحقق من الطلب. حاول مرة أخرى.',
    'rate' => 'محاولات دخول كثيرة. انتظر 15 دقيقة ثم حاول مرة أخرى.',
];
$e = (string)($_GET['e'] ?? '');
$error = $errors[$e] ?? '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $rate = vg_admin_login_rate_state($username);
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
        usleep(400000); // slow down guessing
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
    }
}

// The sign-in page is always light, whatever theme the admin is using.
$vgForceLight = true;
$page_title = 'تسجيل الدخول';
require __DIR__ . '/includes/layout_top.php';
?>
<div style="max-width:380px;margin:60px auto 0">
  <div class="card">
    <h2 style="margin-top:0">دخول لوحة التحكم</h2>
    <?php if ($error): ?><div class="flash err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" action="login.php">
      <?= csrf_field() ?>
      <label for="vg-user">اسم المستخدم</label>
      <input id="vg-user" type="text" name="username" required autocomplete="username" autofocus>
      <label for="vg-pass">كلمة المرور</label>
      <input id="vg-pass" type="password" name="password" required autocomplete="current-password">
      <div style="margin-top:16px"><button class="btn" type="submit" style="width:100%">دخول</button></div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
