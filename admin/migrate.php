<?php
/**
 * Vision Guard - one-click database update (admin only).
 * Applies migration-followups.sql and migration-projects.sql: only ADDs missing, empty, nullable
 * columns and indexes to `leads`. Nothing existing is changed or removed, and running it twice is safe.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('db_update');   // not in any role's list, so admin only
require_once __DIR__ . '/includes/crm.php';

$steps = [
    'followups' => ['title' => 'المتابعات (تاريخ ومتابعة وملاحظة ووقت الرد)', 'cols' => [
        'followup_at' => 'DATETIME NULL DEFAULT NULL',
        'followup_note' => 'VARCHAR(500) NULL DEFAULT NULL',
        'contacted_at' => 'DATETIME NULL DEFAULT NULL',
    ], 'index' => ['idx_leads_followup', 'followup_at']],
    'projects' => ['title' => 'مراحل المشروع (معاينة، عرض سعر، تركيب)', 'cols' => [
        'project_stage' => 'VARCHAR(12) NULL DEFAULT NULL',
        'quote_value' => 'DECIMAL(12,2) NULL DEFAULT NULL',
    ], 'index' => ['idx_leads_project_stage', 'project_stage']],
];

function vgm_has_col(string $c): bool {
    return (bool)db()->query("SHOW COLUMNS FROM leads LIKE " . db()->quote($c))->fetch();
}
function vgm_has_idx(string $i): bool {
    return (bool)db()->query("SHOW INDEX FROM leads WHERE Key_name = " . db()->quote($i))->fetch();
}
/** Missing columns and index of a step. */
function vgm_pending(array $s): array {
    $miss = [];
    try {
        foreach ($s['cols'] as $c => $def) if (!vgm_has_col($c)) $miss[] = $c;
        if (!vgm_has_idx($s['index'][0])) $miss[] = 'index:' . $s['index'][0];
    } catch (Throwable $e) { return ['?']; }
    return $miss;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $done = []; $err = null;
    try {
        foreach ($steps as $s) {
            foreach ($s['cols'] as $c => $def) {
                if (!vgm_has_col($c)) { db()->exec("ALTER TABLE leads ADD COLUMN `$c` $def"); $done[] = $c; }
            }
            if (!vgm_has_idx($s['index'][0])) { db()->exec("ALTER TABLE leads ADD INDEX `{$s['index'][0]}` (`{$s['index'][1]}`)"); $done[] = $s['index'][0]; }
        }
    } catch (Throwable $e) { $err = $e->getMessage(); error_log('migrate.php: ' . $err); }
    if ($err !== null) flash_set('فشل التحديث: ' . $err . (empty($done) ? '' : ' (اتضاف قبلها: ' . implode(', ', $done) . ')'), 'err');
    else flash_set($done ? 'تم التحديث. اتضافت: ' . implode('، ', $done) : 'قاعدة البيانات محدّثة بالفعل، مفيش حاجة اتغيرت.');
    header('Location: migrate.php');
    exit;
}

$page_title = 'تحديث قاعدة البيانات';
$active = 'settings';
require __DIR__ . '/includes/layout_top.php';
$total = 0;
?>
<div class="card">
  <h3 style="margin-top:0">تحديث قاعدة البيانات</h3>
  <p style="color:var(--muted);line-height:1.8">الزر ده بيضيف أعمدة جديدة فاضية بس لجدول الطلبات والرسائل. مفيش حاجة موجودة بتتغير أو بتتمسح، وتقدر تدوس عليه أكتر من مرة بأمان. الأفضل تاخد نسخة احتياطية من phpMyAdmin قبل ما تدوس.</p>
  <?php foreach ($steps as $s): $p = vgm_pending($s); $total += count($p); ?>
    <div class="srow"><span><?= htmlspecialchars($s['title'], ENT_QUOTES, 'UTF-8') ?></span>
      <span class="badge <?= $p ? 'contacted' : 'delivered' ?>"><?= $p ? 'محتاج تحديث' : 'جاهز' ?></span></div>
  <?php endforeach; ?>
  <form method="post" style="margin-top:16px">
    <?= csrf_field() ?>
    <button class="btn" type="submit"<?= $total ? '' : ' disabled' ?>><?= $total ? 'حدّث قاعدة البيانات' : 'كل حاجة محدّثة' ?></button>
  </form>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
