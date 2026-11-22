<?php require_once __DIR__ . '/includes/ratelimit.php';
require_once __DIR__ . '/includes/perms.php';

$cur = current_admin();
if ($cur && current_role() !== 'admin') {
    header('Location: ' . role_home(current_role()));
    exit;
}

$error = '';
$notice = isset($_GET['e']) ? 'حسابات الموظفين تدخل من هذه الصفحة.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $rate = vg_admin_login_rate_state($username);
    if ($rate['blocked']) { $error = 'محاولات دخول كثيرة. انتظر 15 دقيقة ثم حاول مرة أخرى.'; }

    $stmt = db()->prepare('SELECT * FROM admin_users WHERE username = ? AND active = 1 LIMIT 1');
    if (!$rate['blocked']) { $stmt->execute([$username]); $user = $stmt->fetch(); } else { $user = false; }

    if ($user && password_verify($password, $user['password_hash'])) {
        if (($user['role'] ?? 'admin') === 'admin') {
            $error = 'هذا الحساب ليس حساب موظف. استخدم صفحة دخول الإدارة.';
        } else {
            vg_admin_login_succeeded($rate);
        session_regenerate_id(true);
            $_SESSION['admin'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'full_name' => $user['full_name'] ?? $user['username'],
            ];
            header('Location: ' . role_home(current_role()));
            exit;
        }
    } else {
        if (!$rate['blocked']) { vg_admin_login_failed($rate); $error = 'بيانات الدخول غير صحيحة.'; }
    }
    usleep(400000);
}
?><!doctype html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8">

<meta name="viewport" content="width=device-width,initial-scale=1">
<title>دخول الموظفين · Vision Guard</title>
<meta name="robots" content="noindex,nofollow">
<style>
:root{--brand:#0f766e;--brand-dark:#115e59;--bg:#ecf6f5;--text:#10231f;--muted:#5a6f6a;--border:#cfe3e0;--danger:#dc2626}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px;font:15px/1.5 -apple-system,Segoe UI,Tahoma,Arial,sans-serif;background:var(--bg);color:var(--text)}
.box{width:100%;max-width:380px;background:#fff;border:1px solid var(--border);border-radius:16px;padding:28px 24px;box-shadow:0 10px 30px rgba(15,118,110,.12)}
.tag{display:inline-block;background:var(--brand);color:#fff;border-radius:999px;padding:3px 12px;font-size:12px;font-weight:700;margin-bottom:10px}
h1{margin:0 0 4px;font-size:22px}
p.sub{margin:0 0 18px;color:var(--muted);font-size:14px}
label{display:block;margin:12px 0 4px;font-weight:600;font-size:14px}
input[type=text],input[type=password]{width:100%;padding:11px 12px;border:1px solid var(--border);border-radius:10px;font:inherit;background:#fff}
input:focus{outline:2px solid var(--brand);outline-offset:1px}
button{width:100%;margin-top:18px;padding:12px;border:0;border-radius:10px;background:var(--brand);color:#fff;font:inherit;font-weight:700;cursor:pointer}
button:hover{background:var(--brand-dark)}
.err{background:#fee2e2;color:#991b1b;border-radius:10px;padding:9px 12px;margin-bottom:10px;font-size:14px}
.note{background:#ccfbf1;color:#115e59;border-radius:10px;padding:9px 12px;margin-bottom:10px;font-size:14px}
.foot{margin-top:16px;text-align:center;font-size:13px;color:var(--muted)}
.foot a{color:var(--brand)}
</style>
<script src="assets/i18n.js?v=20261120"></script>
</head>
<body>
<form class="box" method="post" autocomplete="off">
  <span class="tag">دخول الموظفين</span>
  <h1>Vision Guard</h1>
  <p class="sub">سجّل الدخول لمتابعة الطلبات وتسجيل الحضور.</p>
  <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($notice && !$error): ?><div class="note"><?= htmlspecialchars($notice) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label for="u">اسم المستخدم</label>
  <input id="u" type="text" name="username" required autofocus>
  <label for="p">كلمة المرور</label>
  <input id="p" type="password" name="password" required>
  <button type="submit">دخول</button>
  <div class="foot"><a href="login.php">دخول الإدارة</a></div>
</form>
</body>
</html>
