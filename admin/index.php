<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('dashboard');

$productCount = db()->query('SELECT COUNT(*) c FROM products')->fetch()['c'];
$outOfStock   = db()->query("SELECT COUNT(*) c FROM products WHERE in_stock = 0")->fetch()['c'];
$newLeads     = db()->query("SELECT COUNT(*) c FROM leads WHERE status = 'new'")->fetch()['c'];
$recentLeads  = db()->query('SELECT * FROM leads ORDER BY created_at DESC LIMIT 6')->fetchAll();

$page_title = 'الرئيسية';
$active = 'dashboard';
require __DIR__ . '/includes/layout_top.php';
?>
<style id="vg-unslop">
.kchip,.quick .kchip,.kpis .kchip{background:#eef3f9 !important;color:#51607a !important}
.kchip svg{stroke:currentColor}
.kval,.kpi .kval,.quick b{font-weight:600 !important;font-variant-numeric:tabular-nums}
.quick a,.kpi{box-shadow:0 1px 2px rgba(15,27,45,.05) !important}
</style>
<?php
?>
<div class="topbar"><h2>نظرة عامة</h2></div>

<style>.kchip{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;background:#e8f3fb;color:#0667a5}.kchip.green{background:#e3f6ea;color:#15803d}.kchip.amber{background:#fdf1dc;color:#b45309}.kchip.violet{background:#ece9fd;color:#5b46d6}</style>
<div class="quick">
<h3>الإجراءات السريعة</h3>
<a class="card" href="leads.php"><span class="kchip violet"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" aria-hidden="true"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6M9 12h6"/></svg></span>الطلبات الحالية</a>
<a class="card" href="inventory.php"><span class="kchip green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" aria-hidden="true"><path d="M12 3l9 4.5-9 4.5-9-4.5zM3 12l9 4.5 9-4.5M3 16.5L12 21l9-4.5"/></svg></span>إدارة المخزون</a>
<a class="card" href="products.php"><span class="kchip amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" aria-hidden="true"><path d="M12 3l8 4.5v9L12 21l-8-4.5v-9zM12 12l8-4.5M12 12v9M12 12L4 7.5"/></svg></span>المنتجات</a>
<a class="card" href="dashboard.php"><span class="kchip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" aria-hidden="true"><path d="M12 3v9h9a9 9 0 1 1-9-9zM15 3.5A9 9 0 0 1 20.5 9H15z"/></svg></span>لوحة التقارير</a>
</div>
<div class="grid2" style="grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:16px">
  <div class="card" style="text-align:center">
    <div class="muted">عدد المنتجات</div>
    <div style="font-size:28px;font-weight:700"><?= (int)$productCount ?></div>
  </div>
  <div class="card" style="text-align:center">
    <div class="muted">منتجات غير متوفرة</div>
    <div style="font-size:28px;font-weight:700;color:#dc2626"><?= (int)$outOfStock ?></div>
  </div>
  <div class="card" style="text-align:center">
    <div class="muted">رسائل/طلبات جديدة</div>
    <div style="font-size:28px;font-weight:700;color:#1d4ed8"><?= (int)$newLeads ?></div>
  </div>
</div>

<div class="card">
  <div class="topbar" style="margin-bottom:10px"><h3 style="margin:0">أحدث الطلبات والرسائل</h3><a class="btn secondary sm" href="leads.php">عرض الكل</a></div>
  <?php if (!$recentLeads): ?>
    <p class="muted">لسه مفيش رسائل أو طلبات.</p>
  <?php else: ?>
  <table>
    <tr><th>النوع</th><th>الاسم</th><th>الهاتف</th><th>التفاصيل</th><th>التاريخ</th></tr>
    <?php foreach ($recentLeads as $l): ?>
    <tr>
      <td><?= htmlspecialchars($l['type']) ?></td>
      <td><?= htmlspecialchars($l['name'] ?: '—') ?></td>
      <td><?= htmlspecialchars($l['phone'] ?: '—') ?></td>
      <td class="muted"><?= htmlspecialchars(mb_substr((string)($l['message'] ?: $l['cart_json']), 0, 60)) ?>…</td>
      <td class="muted"><?= htmlspecialchars($l['created_at']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
