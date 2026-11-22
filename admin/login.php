<?php require_once __DIR__ . '/includes/ratelimit.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $rate = vg_admin_login_rate_state($username);
    if ($rate['blocked']) { $error = 'محاولات دخول كثيرة. انتظر 15 دقيقة ثم حاول مرة أخرى.'; }

    $stmt = db()->prepare('SELECT * FROM admin_users WHERE username = ? AND active = 1 LIMIT 1');
    if (!$rate['blocked']) { $stmt->execute([$username]); $user = $stmt->fetch(); } else { $user = false; }

    if ($user && password_verify($password, $user['password_hash'])) {
        if (($user['role'] ?? 'admin') !== 'admin') { header('Location: staff_login.php?e=1'); exit; }
        vg_admin_login_succeeded($rate);
        session_regenerate_id(true);
        $_SESSION['admin'] = ['id' => $user['id'], 'username' => $user['username'], 'role' => $user['role'] ?? 'admin', 'full_name' => $user['full_name'] ?? $user['username']];
        header('Location: ' . (($user['role'] ?? 'admin') !== 'admin' ? 'attendance.php' : 'index.php'));
        exit;
    }
    if (!$rate['blocked']) { vg_admin_login_failed($rate); $error = 'بيانات الدخول غير صحيحة.'; }
    usleep(400000); // slow down brute-force attempts a bit
}

$page_title = 'تسجيل الدخول';
require __DIR__ . '/includes/layout_top.php';
?>
<div style="max-width:380px;margin:60px auto 0">
  <div class="card">
    <h2 style="margin-top:0">دخول لوحة التحكم</h2>
    <?php if ($error): ?><div class="flash err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <label>اسم المستخدم</label>
      <input type="text" name="username" required autofocus>
      <label>كلمة المرور</label>
      <input type="password" name="password" required>
      <div style="margin-top:16px"><button class="btn" type="submit" style="width:100%">دخول</button></div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
