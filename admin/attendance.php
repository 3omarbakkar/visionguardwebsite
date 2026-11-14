<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
date_default_timezone_set('Africa/Cairo');
try { db()->exec("SET time_zone = '" . date('P') . "'"); } catch (Throwable $e) {}

$me = current_admin();
$role = $me['role'] ?? 'admin'; if ($role !== 'admin') { $role = 'employee'; }
$today = date('Y-m-d');
function att_hours($in, $out) {
    if (!$in || !$out) return null;
    $diff = strtotime($out) - strtotime($in);
    if ($diff <= 0) return null;
    $h = intdiv($diff, 3600);
    $m = intdiv($diff % 3600, 60);
    return sprintf('%d:%02d', $h, $m);
    }
    $SHIFTS = array(
    'ahmed rayan' => array('10:00', '16:00'),
    'dina rahal' => array('17:00', '23:00'),
    );
    function att_shift($name) {
    global $SHIFTS;
    $key = strtolower(trim($name));
    return isset($SHIFTS[$key]) ? $SHIFTS[$key] : null;
    }
    function att_status($in, $out, $shift) {
    if (!$shift) return null;
    $late = false; $early = false;
    if ($in && substr($in, 11, 5) > $shift[0]) $late = true;
    if ($out && substr($out, 11, 5) < $shift[1]) $early = true;
    if ($late && $early) return 'متأخر + انصراف مبكر';
    if ($late) return 'متأخر';
    if ($early) return 'انصراف مبكر';
    if ($in) return 'فى الميعاد';
    return null;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role !== 'employee' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $delId = (int)($_POST['id'] ?? 0);
    if ($delId) {
    db()->prepare('DELETE FROM attendance WHERE id = ?')->execute([$delId]);
    flash_set('تم حذف السجل.');
    }
    header('Location: attendance.php');
    exit;
    }
    
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'employee') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if (in_array($action, array('check_in', 'check_out'), true) && ($geoErr = geo_error())) {
        flash_set($geoErr, 'err');
        $action = '';
    }

    if ($action === 'check_in') {
        $stmt = db()->prepare('SELECT id, check_in FROM attendance WHERE user_id = ? AND work_date = ?');
        $stmt->execute([$me['id'], $today]);
        $row = $stmt->fetch();
        if ($row && $row['check_in']) {
            flash_set('تم تسجيل حضورك اليوم بالفعل.', 'err');
        } else {
            db()->prepare('INSERT INTO attendance (user_id, work_date, check_in) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE check_in = NOW()')->execute([$me['id'], $today]);
            flash_set('تم تسجيل الحضور بنجاح.');
        }
    } elseif ($action === 'check_out') {
        $stmt = db()->prepare('SELECT id, check_in, check_out FROM attendance WHERE user_id = ? AND work_date = ?');
        $stmt->execute([$me['id'], $today]);
        $row = $stmt->fetch();
        if (!$row || !$row['check_in']) {
            flash_set('لازم تسجل الحضور الأول.', 'err');
        } elseif ($row['check_out']) {
            flash_set('تم تسجيل انصرافك اليوم بالفعل.', 'err');
        } else {
            db()->prepare('UPDATE attendance SET check_out = NOW() WHERE id = ?')->execute([$row['id']]);
            flash_set('تم تسجيل الانصراف بنجاح.');
        }
    }
    header('Location: attendance.php');
    exit;
}

function geo_cfg() {
    static $c = null;
    if ($c !== null) return $c;
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS attendance_geo (id TINYINT PRIMARY KEY, lat DOUBLE NOT NULL, lng DOUBLE NOT NULL, radius INT NOT NULL DEFAULT 200) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $r = db()->query('SELECT lat, lng, radius FROM attendance_geo WHERE id = 1')->fetch();
        $c = $r ? $r : false;
    } catch (Throwable $e) { $c = false; }
    return $c;
}
function geo_dist($lat1, $lng1, $lat2, $lng2) {
    $R = 6371000;
    $p1 = deg2rad($lat1); $p2 = deg2rad($lat2);
    $dp = deg2rad($lat2 - $lat1); $dl = deg2rad($lng2 - $lng1);
    $x = sin($dp / 2) * sin($dp / 2) + cos($p1) * cos($p2) * sin($dl / 2) * sin($dl / 2);
    return 2 * $R * asin(min(1, sqrt($x)));
}
function geo_error() {
    $g = geo_cfg();
    if (!$g) return null;
    $lat = isset($_POST['geo_lat']) ? (float)$_POST['geo_lat'] : 0;
    $lng = isset($_POST['geo_lng']) ? (float)$_POST['geo_lng'] : 0;
    $acc = isset($_POST['geo_acc']) ? (float)$_POST['geo_acc'] : 0;
    if (!$lat || !$lng) return 'لازم تفعّل الموقع (GPS) وتسمح للمتصفح بالوصول لموقعك عشان تسجّل.';
    $d = geo_dist($lat, $lng, (float)$g['lat'], (float)$g['lng']);
    $allow = (int)$g['radius'] + min(max($acc, 0), 100);
    if ($d > $allow) return 'أنت بعيد عن مقر العمل (حوالي ' . round($d) . ' متر). لازم تكون في المحل عشان تسجّل.';
    return null;
}
if ($role !== 'employee' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $ga = $_POST['action'] ?? '';
    if ($ga === 'save_geo') {
        $glat = (float)($_POST['geo_lat'] ?? 0); $glng = (float)($_POST['geo_lng'] ?? 0);
        $grad = max(50, min(2000, (int)($_POST['radius'] ?? 200)));
        if ($glat && $glng) {
            geo_cfg();
            db()->prepare('REPLACE INTO attendance_geo (id, lat, lng, radius) VALUES (1, ?, ?, ?)')->execute([$glat, $glng, $grad]);
            flash_set('تم حفظ موقع المحل والتحقق بالموقع شغال.');
        } else {
            flash_set('تعذّر قراءة الموقع. فعّل GPS وجرب تاني.', 'err');
        }
    } elseif ($ga === 'clear_geo') {
        geo_cfg();
        db()->exec('DELETE FROM attendance_geo WHERE id = 1');
        flash_set('تم إيقاف التحقق بالموقع.');
    }
    header('Location: attendance.php');
    exit;
}

$todayRow = null;
if ($role === 'employee') {
    $stmt = db()->prepare('SELECT * FROM attendance WHERE user_id = ? AND work_date = ?');
    $stmt->execute([$me['id'], $today]);
    $todayRow = $stmt->fetch();
}

if ($role === 'employee') {
    $stmt = db()->prepare('SELECT * FROM attendance WHERE user_id = ? ORDER BY work_date DESC LIMIT 60');
    $stmt->execute([$me['id']]);
    $history = $stmt->fetchAll();
} else {
    $stmt = db()->prepare('SELECT a.*, u.username, u.full_name FROM attendance a JOIN admin_users u ON u.id = a.user_id ORDER BY a.work_date DESC, u.username ASC LIMIT 200');
    $stmt->execute();
    $history = $stmt->fetchAll();
}

$page_title = 'الحضور والانصراف';
$active = 'attendance';
require __DIR__ . '/includes/layout_top.php';
?>
<div class="topbar"><h2>الحضور والانصراف</h2><?php if ($role === 'admin'): ?><div><a class="btn sm secondary" href="staff.php">الموظفين والصلاحيات</a> <a class="btn sm" href="attendance.php">الحضور والانصراف</a></div><?php endif; ?></div>

<script>
function vgGeo(cb, err) {
  if (!navigator.geolocation) { err('المتصفح مش بيدعم تحديد الموقع.'); return; }
  navigator.geolocation.getCurrentPosition(function (p) { cb(p.coords); }, function () {
    err('مقدرناش نحدد موقعك. فعّل GPS واسمح للموقع بالوصول لمكانك وجرب تاني.');
  }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
}
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form').forEach(function (f) {
    if (!f.querySelector('[name=geo_lat]')) return;
    f.addEventListener('submit', function (e) {
      if (f.dataset.ready) return;
      e.preventDefault();
      var b = f.querySelector('button'); if (b) { b.disabled = true; b.textContent = 'جاري تحديد موقعك...'; }
      vgGeo(function (c) {
        f.querySelector('[name=geo_lat]').value = c.latitude;
        f.querySelector('[name=geo_lng]').value = c.longitude;
        f.querySelector('[name=geo_acc]').value = c.accuracy;
        f.dataset.ready = '1'; f.submit();
      }, function (m) { alert(m); location.reload(); });
    });
  });
});
</script>


<?php if ($role === 'employee'): ?>
<div class="card" style="max-width:420px">
  <p style="margin-top:0">تاريخ اليوم: <strong><?= htmlspecialchars($today) ?></strong></p>
  <p>
    الحضور: <strong><?= $todayRow && $todayRow['check_in'] ? htmlspecialchars(substr($todayRow['check_in'], 11, 5)) : '— لم يتم بعد —' ?></strong><br>
    الانصراف: <strong><?= $todayRow && $todayRow['check_out'] ? htmlspecialchars(substr($todayRow['check_out'], 11, 5)) : '— لم يتم بعد —' ?></strong>
  </p>
  <form method="post" style="display:flex;gap:10px;flex-wrap:wrap">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="check_in">
    <input type="hidden" name="geo_lat"><input type="hidden" name="geo_lng"><input type="hidden" name="geo_acc">
    <button class="btn" type="submit" <?= ($todayRow && $todayRow['check_in']) ? 'disabled' : '' ?>>تسجيل حضور</button>
  </form>
  <form method="post" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="check_out">
    <input type="hidden" name="geo_lat"><input type="hidden" name="geo_lng"><input type="hidden" name="geo_acc">
    <button class="btn" type="submit" <?= (!$todayRow || !$todayRow['check_in'] || $todayRow['check_out']) ? 'disabled' : '' ?>>تسجيل انصراف</button>
  </form>
</div>

<div class="card" style="margin-top:18px">
  <h3 style="margin-top:0">سجل حضورك</h3>
  <table style="width:100%;border-collapse:collapse">
    <tr style="text-align:right;border-bottom:1px solid var(--border)">
      <th style="padding:8px 4px">التاريخ</th>
      <th style="padding:8px 4px">الحضور</th>
      <th style="padding:8px 4px">الانصراف</th>
      <th style="padding:8px 4px">عدد الساعات</th>
    </tr>
    <?php foreach ($history as $h): ?>
    <tr style="border-bottom:1px solid var(--border)">
      <td style="padding:8px 4px"><?= htmlspecialchars($h['work_date']) ?></td>
      <td style="padding:8px 4px"><?= $h['check_in'] ? htmlspecialchars(substr($h['check_in'], 11, 5)) : '-' ?></td>
      <td style="padding:8px 4px"><?= $h['check_out'] ? htmlspecialchars(substr($h['check_out'], 11, 5)) : '-' ?></td>
      <td style="padding:8px 4px"><?= ($hh = att_hours($h['check_in'], $h['check_out'])) ? htmlspecialchars($hh) : '-' ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$history): ?>
    <tr><td colspan="4" style="padding:12px 4px;color:var(--muted)">لا يوجد سجل حضور بعد.</td></tr>
    <?php endif; ?>
  </table>
</div>

<?php else: ?>
<?php $__g = geo_cfg(); ?>
<div class="card" style="max-width:520px;margin-bottom:18px">
  <h3 style="margin-top:0">التحقق بالموقع الجغرافي</h3>
  <?php if ($__g): ?>
    <p>مفعّل. نطاق التسجيل: <strong><?= (int)$__g['radius'] ?></strong> متر حوالين موقع المحل المحفوظ.</p>
  <?php else: ?>
    <p style="color:var(--muted)">غير مفعّل. افتح الصفحة دي من موبايلك وانت في المحل واضغط الزر تحت.</p>
  <?php endif; ?>
  <form method="post" id="geoSave" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_geo">
    <input type="hidden" name="geo_lat"><input type="hidden" name="geo_lng">
    <label>النطاق (متر) <input type="number" name="radius" min="50" max="2000" value="<?= $__g ? (int)$__g['radius'] : 200 ?>" style="width:90px"></label>
    <button class="btn" type="submit">حفظ موقعي الحالي كموقع المحل</button>
  </form>
  <?php if ($__g): ?>
  <form method="post" style="margin-top:8px"><?= csrf_field() ?><input type="hidden" name="action" value="clear_geo"><button class="btn" type="submit">إيقاف التحقق بالموقع</button></form>
  <?php endif; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var f = document.getElementById('geoSave'); if (!f) return;
  f.addEventListener('submit', function (e) {
    if (f.dataset.ready) return; e.preventDefault();
    vgGeo(function (c) { f.querySelector('[name=geo_lat]').value = c.latitude; f.querySelector('[name=geo_lng]').value = c.longitude; f.dataset.ready = '1'; f.submit(); },
      function (m) { alert(m); });
  });
});
</script>
<div class="card">
  <h3 style="margin-top:0">سجل حضور وانصراف الموظفين</h3>
  <table style="width:100%;border-collapse:collapse">
    <tr style="text-align:right;border-bottom:1px solid var(--border)">
      <th style="padding:8px 4px">الموظف</th>
      <th style="padding:8px 4px">التاريخ</th>
      <th style="padding:8px 4px">الحضور</th>
      <th style="padding:8px 4px">الانصراف</th>
      <th style="padding:8px 4px">عدد الساعات</th>
      <th style="padding:8px 4px">الدوام المحدد</th>
      <th style="padding:8px 4px">الحالة</th>
      <th style="padding:8px 4px">حذف</th>
    </tr>
    <?php foreach ($history as $h): ?>
    <tr style="border-bottom:1px solid var(--border)">
      <td style="padding:8px 4px"><?= htmlspecialchars($h['full_name'] ?: $h['username']) ?></td>
      <td style="padding:8px 4px"><?= htmlspecialchars($h['work_date']) ?></td>
      <td style="padding:8px 4px"><?= $h['check_in'] ? htmlspecialchars(substr($h['check_in'], 11, 5)) : '-' ?></td>
      <td style="padding:8px 4px"><?= $h['check_out'] ? htmlspecialchars(substr($h['check_out'], 11, 5)) : '-' ?></td>
      <td style="padding:8px 4px"><?= ($hh = att_hours($h['check_in'], $h['check_out'])) ? htmlspecialchars($hh) : '-' ?></td>
      <td style="padding:8px 4px"><?php $sh = att_shift($h['full_name'] ?: $h['username']); ?><?= $sh ? htmlspecialchars($sh[0].' - '.$sh[1]) : '-' ?></td>
      <td style="padding:8px 4px"><?= ($st = att_status($h['check_in'], $h['check_out'], $sh)) ? htmlspecialchars($st) : '-' ?></td>
      <td style="padding:8px 4px"><form method="post" onsubmit="return confirm('متأكد من حذف السجل؟');" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$h['id'] ?>"><button class="btn" type="submit" style="background:#c0392b;padding:4px 10px;font-size:13px">حذف</button></form></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$history): ?>
    <tr><td colspan="8" style="padding:12px 4px;color:var(--muted)">لا يوجد سجل حضور بعد.</td></tr>
    <?php endif; ?>
  </table>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
