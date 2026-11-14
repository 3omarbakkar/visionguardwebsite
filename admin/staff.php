<?php
require_once __DIR__ . '/includes/perms.php';
require_perm('staff_manage');
$me = current_admin();
$roles = vg_roles();
$subs = vg_sub_perms();

/* one-time safe migrations */
try {
    $c = db()->query("SHOW COLUMNS FROM admin_users LIKE 'role'")->fetch();
    if ($c && stripos((string)($c['Type'] ?? ''), 'enum') === 0) {
        db()->exec("ALTER TABLE admin_users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'admin'");
    }
} catch (Throwable $e) {}
try {
    if (!db()->query("SHOW COLUMNS FROM admin_users LIKE 'perm_deny'")->fetch()) {
        db()->exec("ALTER TABLE admin_users ADD perm_deny VARCHAR(255) NOT NULL DEFAULT ''");
    }
} catch (Throwable $e) {}
try {
    if (!db()->query("SHOW COLUMNS FROM leads LIKE 'assigned_to'")->fetch()) {
        db()->exec("ALTER TABLE leads ADD assigned_to INT NULL");
    }
} catch (Throwable $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['action'] ?? '';

    if ($act === 'create') {
        $u  = trim($_POST['username'] ?? '');
        $fn = trim($_POST['full_name'] ?? '');
        $rl = $_POST['role'] ?? '';
        $pw = $_POST['password'] ?? '';
        if (!preg_match('/^[A-Za-z0-9._-]{3,40}$/', $u)) {
            flash_set('اسم المستخدم لازم يكون 3 حروف إنجليزي أو أرقام على الأقل.', 'err');
        } elseif (!isset($roles[$rl])) {
            flash_set('الدور غير صحيح.', 'err');
        } elseif (strlen($pw) < 8) {
            flash_set('كلمة المرور لازم تكون 8 حروف على الأقل.', 'err');
        } else {
            $ex = db()->prepare('SELECT id FROM admin_users WHERE username = ?');
            $ex->execute([$u]);
            if ($ex->fetch()) {
                flash_set('اسم المستخدم مستخدم بالفعل.', 'err');
            } else {
                db()->prepare('INSERT INTO admin_users (username, password_hash, full_name, role, active) VALUES (?,?,?,?,1)')
                    ->execute([$u, password_hash($pw, PASSWORD_DEFAULT), $fn !== '' ? $fn : $u, $rl]);
                flash_set('تم إضافة الموظف.');
            }
        }
    } elseif ($act === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $rl = $_POST['role'] ?? '';
        $fn = trim($_POST['full_name'] ?? '');
        $active = !empty($_POST['active']) ? 1 : 0;
        $deny = [];
        foreach ($subs as $k => $lab) { if (!empty($_POST['deny_' . $k])) $deny[] = $k; }
        if ($id === (int)$me['id']) { $rl = 'admin'; $active = 1; $deny = []; }
        if ($id > 0 && isset($roles[$rl])) {
            db()->prepare('UPDATE admin_users SET role = ?, active = ?, full_name = ?, perm_deny = ? WHERE id = ?')
                ->execute([$rl, $active, $fn, implode(',', $deny), $id]);
            flash_set('تم حفظ التعديلات.');
        }
    } elseif ($act === 'reset') {
        $id = (int)($_POST['id'] ?? 0);
        $pw = $_POST['new_password'] ?? '';
        if ($id > 0 && strlen($pw) >= 8) {
            db()->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($pw, PASSWORD_DEFAULT), $id]);
            flash_set('تم تغيير كلمة المرور.');
        } else {
            flash_set('كلمة المرور لازم تكون 8 حروف على الأقل.', 'err');
        }
    }
    header('Location: staff.php');
    exit;
}

$users = db()->query('SELECT id, username, full_name, role, active, perm_deny FROM admin_users ORDER BY (role = \'admin\') DESC, id ASC')->fetchAll();

$page_title = 'الموظفين';
$active = 'staff';
require __DIR__ . '/includes/layout_top.php';
?>
<div class="topbar"><h2>الموظفين</h2>
  <div>
    <a class="btn sm" href="staff.php">الموظفين والصلاحيات</a>
    <a class="btn sm secondary" href="attendance.php">الحضور والانصراف</a>
  </div>
</div>

<div class="card">
  <h3 style="margin-top:0">الأدوار</h3>
  <table>
    <tr><th>الدور</th><th>يقدر يعمل إيه</th></tr>
    <tr><td><strong>مدير</strong></td><td class="muted">كل حاجة.</td></tr>
    <tr><td><strong>مبيعات / خدمة عملاء</strong></td><td class="muted">الطلبات والرسائل والعملاء وتغيير حالة الطلب. بدون حذف وبدون تعديل أسعار.</td></tr>
    <tr><td><strong>مخزن / منتجات</strong></td><td class="muted">المنتجات والمخزون والأسعار. بدون بيانات العملاء.</td></tr>
    <tr><td><strong>فني / تركيب</strong></td><td class="muted">الطلبات المسندة له فقط، يحدّث حالة التركيب، وتسجيل الحضور.</td></tr>
    <tr><td><strong>موظف (حضور فقط)</strong></td><td class="muted">الدور القديم: تسجيل الحضور ومتابعة حالة الطلبات.</td></tr>
  </table>
</div>

<div class="card">
  <h3 style="margin-top:0">إضافة موظف</h3>
  <form method="post" autocomplete="off">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <div class="grid2">
      <div><label>الاسم</label><input type="text" name="full_name"></div>
      <div><label>اسم المستخدم (إنجليزي)</label><input type="text" name="username" required pattern="[A-Za-z0-9._-]{3,40}"></div>
      <div><label>الدور</label>
        <select name="role">
          <?php foreach ($roles as $k => $r): if ($k === 'admin') continue; ?>
            <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($r['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label>كلمة المرور (8 حروف على الأقل)</label><input type="password" name="password" required minlength="8" autocomplete="new-password"></div>
    </div>
    <div style="margin-top:14px"><button class="btn" type="submit">إضافة</button></div>
  </form>
</div>

<?php foreach ($users as $u):
    $isMe = ((int)$u['id'] === (int)$me['id']);
    $deny = array_filter(array_map('trim', explode(',', (string)($u['perm_deny'] ?? ''))));
?>
<div class="card">
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
    <div class="grid2">
      <div><label>الاسم (<?= htmlspecialchars($u['username']) ?>)</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars((string)($u['full_name'] ?? '')) ?>"></div>
      <div><label>الدور</label>
        <select name="role" <?= $isMe ? 'disabled' : '' ?>>
          <?php foreach ($roles as $k => $r): ?>
            <option value="<?= htmlspecialchars($k) ?>" <?= $u['role'] === $k ? 'selected' : '' ?>><?= htmlspecialchars($r['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div style="margin-top:10px;display:flex;gap:18px;flex-wrap:wrap;align-items:center">
      <?php if (!$isMe): ?>
        <label style="margin:0"><input type="checkbox" name="active" value="1" <?= !empty($u['active']) ? 'checked' : '' ?>> الحساب مفعّل</label>
        <?php foreach ($subs as $k => $lab): ?>
          <label style="margin:0"><input type="checkbox" name="deny_<?= htmlspecialchars($k) ?>" value="1" <?= in_array($k, $deny, true) ? 'checked' : '' ?>> <?= htmlspecialchars($lab) ?></label>
        <?php endforeach; ?>
      <?php else: ?>
        <span class="muted">ده حسابك (مدير) ولا يمكن تغيير دوره.</span>
      <?php endif; ?>
      <button class="btn sm" type="submit">حفظ</button>
    </div>
  </form>
  <form method="post" style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;align-items:center" autocomplete="off">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="reset">
    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
    <input type="password" name="new_password" placeholder="كلمة مرور جديدة (8+)" minlength="8" autocomplete="new-password" style="max-width:240px">
    <button class="btn sm secondary" type="submit">تغيير كلمة المرور</button>
  </form>
</div>
<?php endforeach; ?>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
