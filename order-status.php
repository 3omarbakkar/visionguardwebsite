<?php
require_once __DIR__ . '/admin/includes/db.php';
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function vg_order_phone($p) {
    $p = strtr((string)$p, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
    $p = preg_replace('/\D+/', '', $p);
    if (strpos($p, '0020') === 0) $p = substr($p, 4);
    elseif (strpos($p, '20') === 0 && strlen($p) === 12) $p = substr($p, 2);
    if (strlen($p) === 10 && $p[0] === '1') $p = '0' . $p;
    return $p;
}
function vg_order_status_limited() {
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_status_attempts (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        ip_hash CHAR(64) NOT NULL,
        attempted_at DATETIME NOT NULL,
        KEY idx_order_status_ip_time (ip_hash, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $ip = hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $s = $pdo->prepare('SELECT COUNT(*) FROM order_status_attempts WHERE ip_hash = ? AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
    $s->execute([$ip]);
    $blocked = (int)$s->fetchColumn() >= 8;
    if (!$blocked) $pdo->prepare('INSERT INTO order_status_attempts (ip_hash, attempted_at) VALUES (?, NOW())')->execute([$ip]);
    if (random_int(1,100) === 1) $pdo->exec('DELETE FROM order_status_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 2 DAY) LIMIT 1000');
    return $blocked;
}

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0; $phone = ''; $order = null; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (vg_order_status_limited()) {
        $err = 'محاولات كثيرة. انتظر 15 دقيقة ثم حاول مرة أخرى.';
    } else {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        $phone = vg_order_phone($_POST['phone'] ?? '');
        if ($id && preg_match('/^01[0125][0-9]{8}$/', $phone)) {
            $st = db()->prepare("SELECT id, phone, status, total, created_at FROM leads WHERE id = ? AND type = 'order' LIMIT 1");
            $st->execute([$id]);
            $row = $st->fetch();
            $dbPhone = $row ? vg_order_phone($row['phone']) : '';
            if ($row && $dbPhone !== '' && hash_equals($dbPhone, $phone)) $order = $row;
            else $err = 'لم نجد طلبًا بهذه البيانات. تأكد من رقم الطلب ورقم الهاتف.';
        } else $err = 'اكتب رقم طلب صحيح ورقم الهاتف المسجل به.';
        if ($err) usleep(250000);
    }
}
$labels = [
    'new' => ['تم استلام طلبك', 'استلمنا طلبك وهنتواصل معاك قريبًا لتأكيده.', 1],
    'contacted' => ['جاري التجهيز', 'تم التواصل معاك وطلبك قيد التجهيز والتوصيل.', 2],
    'delivered' => ['تم توصيل طلبك', 'وصل طلبك. شكرًا لثقتك في Vision Guard، ولو عندك أي استفسار كلمنا على واتساب.', 3],
    'closed' => ['مكتمل', 'تم إغلاق الطلب. لو عندك أي استفسار كلمنا على واتساب.', 3],
    'cancelled' => ['ملغي', 'تم إلغاء الطلب ورجعت الكمية للمخزون. لو محتاج مساعدة كلمنا على واتساب.', 0],
];
?><!doctype html>
<html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>تتبّع طلبك | Vision Guard</title>
<style>
:root{--brand:#0667a5;--bg:#f4f7fb;--card:#fff;--ink:#14213d;--muted:#5b6b82;--line:#dbe4ef}
@media (prefers-color-scheme:dark){:root{--bg:#0a1424;--card:#101d33;--ink:#eaf1fb;--muted:#9fb2cc;--line:#24344f}}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font-family:Tahoma,Arial,sans-serif;line-height:1.6}
.w{max-width:520px;margin:0 auto;padding:24px 16px}.c{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:20px}
h1{font-size:22px;margin:0 0 6px}p{margin:0 0 12px;color:var(--muted)}
label{display:block;font-weight:700;margin:12px 0 4px}input{width:100%;font-size:16px;padding:12px;border:1px solid var(--line);border-radius:10px;background:var(--bg);color:var(--ink)}
button{margin-top:16px;width:100%;padding:13px;border:0;border-radius:10px;background:var(--brand);color:#fff;font-size:16px;font-weight:700;cursor:pointer}
.err{color:#b3261e;font-weight:700}.steps{display:flex;gap:6px;margin:16px 0}.s{flex:1;height:8px;border-radius:4px;background:var(--line)}.s.on{background:var(--brand)}a{color:var(--brand)}
</style></head><body><div class="w"><div class="c">
<h1>تتبّع طلبك</h1>
<?php if ($order): $L = $labels[$order['status']] ?? $labels['new']; ?>
<p>طلب رقم <b>#<?= (int)$order['id'] ?></b> · <?= h(substr((string)$order['created_at'], 0, 10)) ?></p>
<div class="steps"><?php for ($i = 1; $i <= 3; $i++) echo '<div class="s' . ($i <= $L[2] ? ' on' : '') . '"></div>'; ?></div>
<h2 style="margin:0 0 6px"><?= h($L[0]) ?></h2><p><?= h($L[1]) ?></p>
<?php if ($order['total'] !== null): ?><p>الإجمالي: <b><?= h(number_format((float)$order['total'], 2)) ?> ج.م</b></p><?php endif; ?>
<p><a href="/">العودة للمتجر</a></p>
<?php else: ?>
<p>اكتب رقم الطلب ورقم الهاتف كاملًا كما سجلته عند الطلب.</p>
<?php if ($err) echo '<p class="err">' . h($err) . '</p>'; ?>
<form method="post" action="/order-status.php"><label for="id">رقم الطلب</label><input id="id" name="id" inputmode="numeric" value="<?= $id ? (int)$id : '' ?>" required>
<label for="phone">رقم الهاتف</label><input id="phone" name="phone" inputmode="tel" autocomplete="tel" required><button type="submit">تتبّع</button></form>
<?php endif; ?>
</div></div></body></html>
