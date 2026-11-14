<?php
// Admin-only SMS diagnostic page. Not linked in the menu; open /admin/sms_test.php.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../api/_sms.php';
require_admin();

$msg = ''; $ok = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $phone = vg_norm_phone_sms_test($_POST['phone'] ?? '');
    if (!preg_match('/^01[0125][0-9]{8}$/', $phone)) {
        $msg = 'رقم غير صحيح. اكتب 01xxxxxxxxx.';
        $ok = false;
    } elseif (!vg_sms_enabled()) {
        $msg = 'مفيش إعدادات SMS. انسخ api/sms-config.sample.php إلى api/sms-config.php واملأ بيانات المزود.';
        $ok = false;
    } else {
        $ok = vg_sms_send($phone, 'Vision Guard: رسالة اختبار من لوحة التحكم. لو وصلتك، إعدادات SMS شغالة.');
        $msg = $ok ? 'تم إرسال الرسالة للمزود. راجع الموبايل.' : 'فشل الإرسال. راجع error_log وإعدادات المزود.';
    }
}

function vg_norm_phone_sms_test($p): string {
    $p = strtr((string)$p, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
    $p = preg_replace('/\D+/', '', $p);
    if (strpos($p, '0020') === 0) $p = substr($p, 4);
    elseif (strpos($p, '20') === 0 && strlen($p) === 12) $p = substr($p, 2);
    if (strlen($p) === 10 && $p[0] === '1') $p = '0' . $p;
    return $p;
}

$page_title = 'اختبار SMS';
$active = 'sms_test';
require __DIR__ . '/includes/layout_top.php';
?>
<div class="topbar"><h2>اختبار إرسال SMS</h2><a class="btn secondary" href="index.php">رجوع</a></div>
<div class="card" style="max-width:520px">
  <?php if ($msg): ?><div class="flash <?= $ok ? 'ok' : 'err' ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <p class="muted">الحالة: <?= vg_sms_enabled() ? 'الإعدادات موجودة' : 'غير مُعدّ' ?> — المزود: <?= htmlspecialchars((string)(vg_sms_config()['driver'] ?: '—')) ?></p>
  <form method="post">
    <?= csrf_field() ?>
    <label>رقم الموبايل</label>
    <input type="tel" name="phone" inputmode="tel" placeholder="01xxxxxxxxx" required>
    <div style="margin-top:14px"><button class="btn" type="submit">إرسال رسالة تجريبية</button></div>
  </form>
  <p class="muted" style="margin-top:14px">التكلفة: كل اختبار بيستهلك رسالة مدفوعة عند المزود.</p>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
