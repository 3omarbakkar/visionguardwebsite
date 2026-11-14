<?php
// Admin page: send offer notifications to subscribed customers (installed app / browser).
require_once __DIR__ . '/includes/auth.php';
require_admin();
require_once __DIR__ . '/includes/webpush.php';

$subsFile = dirname(__DIR__) . '/push-data/subscriptions.json';
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// VAPID keys: created once, stored in the settings table.
function vapid_keys() {
    $out = [];
    $st = db()->query("SELECT meta_key, meta_value FROM settings WHERE meta_key IN ('vapid_public','vapid_private')");
    foreach ($st as $r) $out[$r['meta_key']] = $r['meta_value'];
    if (empty($out['vapid_public']) || empty($out['vapid_private'])) {
        $kp = wp_new_keypair();
        $ins = db()->prepare('INSERT INTO settings (meta_key, meta_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)');
        $ins->execute(['vapid_public', $kp['public']]);
        $ins->execute(['vapid_private', $kp['pem']]);
        $out = ['vapid_public' => $kp['public'], 'vapid_private' => $kp['pem']];
    }
    return $out;
}
$keys = vapid_keys();
$subs = is_file($subsFile) ? (json_decode(file_get_contents($subsFile), true) ?: []) : [];

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title = mb_substr(trim($_POST['title'] ?? ''), 0, 60);
    $body  = mb_substr(trim($_POST['body'] ?? ''), 0, 160);
    $url   = trim($_POST['url'] ?? '/');
    if ($url === '' || $url[0] !== '/') $url = '/';
    if ($title === '' || $body === '') {
        $result = ['err' => 'اكتب العنوان والنص'];
    } else {
        @set_time_limit(120);
        $payload = json_encode(['title' => $title, 'body' => $body, 'url' => $url], JSON_UNESCAPED_UNICODE);
        $sent = 0; $failed = 0; $removed = 0;
        foreach ($subs as $k => $row) {
            $code = wp_send($row['sub'], $payload, $keys['vapid_private'], $keys['vapid_public'], 'https://visionguardeg.com');
            if ($code >= 200 && $code < 300) $sent++;
            elseif ($code === 404 || $code === 410) { unset($subs[$k]); $removed++; }
            else $failed++;
        }
        if ($removed) file_put_contents($subsFile, json_encode($subs), LOCK_EX);
        $result = ['sent' => $sent, 'failed' => $failed, 'removed' => $removed];
    }
}

$page_title = 'إشعارات العروض';
$active = 'notify';
require __DIR__ . '/includes/layout_top.php';
?>
<div class="topbar"><h2>إشعارات العروض</h2></div>
<div class="card" style="max-width:720px">
  <p>عدد المشتركين الحاليين: <b><?= count($subs) ?></b></p>
  <?php if ($result): ?>
    <?php if (isset($result['err'])): ?>
      <p style="color:#c0392b"><?= h($result['err']) ?></p>
    <?php else: ?>
      <p style="color:#1e8449">تم الإرسال إلى <?= (int)$result['sent'] ?> | فشل <?= (int)$result['failed'] ?> | اشتراكات منتهية حُذفت <?= (int)$result['removed'] ?></p>
    <?php endif; ?>
  <?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label>العنوان (حتى 60 حرف)</label>
    <input type="text" name="title" maxlength="60" required>
    <label>النص (حتى 160 حرف)</label>
    <textarea name="body" rows="3" maxlength="160" required></textarea>
    <label>الرابط عند الضغط (يبدأ بـ / مثل /c/wifi-cameras)</label>
    <input type="text" name="url" value="/" dir="ltr">
    <div style="margin-top:18px"><button class="btn" type="submit" onclick="return confirm('إرسال الإشعار لكل المشتركين؟')">إرسال</button></div>
  </form>
  <p style="margin-top:16px;font-size:.9rem;opacity:.8">لا ترسل أكثر من إشعار أو اثنين أسبوعياً حتى لا يلغي العملاء الاشتراك.</p>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
