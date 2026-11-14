<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('settings');

$fields = [
    'whatsapp_number'  => 'رقم واتساب (بدون + أو مسافات، مثال 201105006854)',
    'call_number'      => 'رقم الاتصال',
    'hero_title_ar'    => 'عنوان الصفحة الرئيسية (عربي)',
    'hero_title_en'    => 'عنوان الصفحة الرئيسية (إنجليزي)',
    'hero_subtitle_ar' => 'الوصف تحت العنوان (عربي)',
    'hero_subtitle_en' => 'الوصف تحت العنوان (إنجليزي)',
    'top_banner_ar'    => 'شريط الإعلان العلوي (عربي)',
    'top_banner_en'    => 'شريط الإعلان العلوي (إنجليزي)',
    'about_ar'         => 'نبذة "من نحن" (عربي)',
    'about_en'         => 'نبذة "من نحن" (إنجليزي)',
    'facebook_url'     => 'رابط فيسبوك',
    'instagram_url'    => 'رابط إنستجرام',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $stmt = db()->prepare('INSERT INTO settings (meta_key, meta_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)');
    foreach ($fields as $key => $label) {
        $val = trim($_POST[$key] ?? '');
        $stmt->execute([$key, $val]);
    }
    flash_set('اتحفظت الإعدادات بنجاح.');
    header('Location: settings.php');
    exit;
}

$current = [];
foreach (db()->query('SELECT meta_key, meta_value FROM settings') as $row) {
    $current[$row['meta_key']] = $row['meta_value'];
}

$page_title = 'إعدادات الصفحة الرئيسية';
$active = 'settings';
require __DIR__ . '/includes/layout_top.php';
?>
<div class="topbar"><h2>إعدادات الصفحة الرئيسية</h2></div>
<form method="post">
<?= csrf_field() ?>
<div class="card" style="max-width:720px">
<?php foreach ($fields as $key => $label): ?>
  <label><?= htmlspecialchars($label) ?></label>
  <?php if (str_contains($key, 'subtitle') || str_contains($key, 'banner') || str_contains($key, 'about')): ?>
    <textarea name="<?= $key ?>" rows="3"><?= htmlspecialchars($current[$key] ?? '') ?></textarea>
  <?php else: ?>
    <input type="text" name="<?= $key ?>" value="<?= htmlspecialchars($current[$key] ?? '') ?>">
  <?php endif; ?>
<?php endforeach; ?>
  <div style="margin-top:18px"><button class="btn" type="submit">حفظ الإعدادات</button></div>
</div>
</form>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
