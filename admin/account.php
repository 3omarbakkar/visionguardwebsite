<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $current = $_POST['current_password'] ?? '';
    $new1 = $_POST['new_password'] ?? '';
    $new2 = $_POST['new_password2'] ?? '';

    $stmt = db()->prepare('SELECT * FROM admin_users WHERE id = ?');
    $stmt->execute([current_admin()['id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, $user['password_hash'])) {
        flash_set('كلمة المرور الحالية غير صحيحة.', 'err');
    } elseif (strlen($new1) < 8) {
        flash_set('كلمة المرور الجديدة لازم تكون 8 حروف على الأقل.', 'err');
    } elseif ($new1 !== $new2) {
        flash_set('كلمة المرور الجديدة غير متطابقة.', 'err');
    } else {
        $hash = password_hash($new1, PASSWORD_DEFAULT);
        db()->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')->execute([$hash, $user['id']]);
        flash_set('اتغيرت كلمة المرور بنجاح.');
    }
    header('Location: account.php');
    exit;
}

$page_title = 'الحساب';
$active = 'account';
require __DIR__ . '/includes/layout_top.php';
?>
<div class="topbar"><h2>تغيير كلمة المرور</h2></div>
<div class="card" style="max-width:420px">
  <form method="post">
    <?= csrf_field() ?>
    <label>كلمة المرور الحالية</label>
    <input type="password" name="current_password" required>
    <label>كلمة المرور الجديدة</label>
    <input type="password" name="new_password" required minlength="8">
    <label>تأكيد كلمة المرور الجديدة</label>
    <input type="password" name="new_password2" required minlength="8">
    <div style="margin-top:16px"><button class="btn" type="submit">حفظ</button></div>
  </form>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
