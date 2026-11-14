<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('dashboard');
require_once __DIR__ . '/../api/_stock.php';
vg_schema_v2();
function vgd_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function vgd_one($sql) { try { return db()->query($sql)->fetch(); } catch (Throwable $e) { return []; } }
function vgd_all($sql) { try { return db()->query($sql)->fetchAll(); } catch (Throwable $e) { return []; } }
function vgd_ic($d, $cls = 'kic') { return '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>'; }
$P = [
  'cash' => 'M3 7h18v10H3zM12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4M6.5 10v.01M17.5 14v.01',
  'cal' => 'M4 6h16v14H4zM4 10h16M8 3v4M16 3v4',
  'inbox' => 'M4 13l2-8h12l2 8M4 13v6h16v-6M4 13h4l1 2h6l1-2h4',
  'truck' => 'M2 7h11v9H2zM13 10h4l3 3v3h-7zM6.5 18.5h.01M17.5 18.5h.01',
  'alert' => 'M12 4l9 16H3zM12 10v4M12 17v.01',
  'receipt' => 'M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6M9 12h6',
  'users' => 'M3 6h18v12H3zM8 12h.01M7 15c.5-1.5 3.5-1.5 4 0M14 10h4M14 14h3',
];
$ok = "type = 'order' AND status <> 'cancelled'";
$today = vgd_one("SELECT COUNT(*) c, COALESCE(SUM(total),0) s FROM leads WHERE $ok AND DATE(created_at) = CURDATE()");
$month = vgd_one("SELECT COUNT(*) c, COALESCE(SUM(total),0) s FROM leads WHERE $ok AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
$allt  = vgd_one("SELECT COUNT(*) c, COALESCE(SUM(total),0) s FROM leads WHERE $ok");
$avg = ($allt['c'] ?? 0) > 0 ? ($allt['s'] / $allt['c']) : 0;
$pending = vgd_one("SELECT COUNT(*) c FROM leads WHERE type = 'order' AND status = 'new'");
$byStatus = [];
foreach (vgd_all("SELECT status, COUNT(*) c FROM leads WHERE type = 'order' GROUP BY status") as $r) { $byStatus[$r['status']] = (int)$r['c']; }
$low = vgd_one("SELECT COUNT(*) c FROM products WHERE stock_qty IS NOT NULL AND stock_qty <= 3");
$lowList = vgd_all("SELECT id, name_ar, stock_qty FROM products WHERE stock_qty IS NOT NULL AND stock_qty <= 3 ORDER BY stock_qty ASC LIMIT 8");
$cust = vgd_one("SELECT COUNT(*) c FROM customers");

$days = [];
foreach (vgd_all("SELECT DATE(created_at) d, COALESCE(SUM(total),0) s, COUNT(*) c FROM leads WHERE $ok AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at)") as $r) { $days[$r['d']] = $r; }
$series = [];
$max = 1;
$weekTotal = 0;
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $s = isset($days[$d]) ? (float)$days[$d]['s'] : 0;
    $series[] = [$d, $s, isset($days[$d]) ? (int)$days[$d]['c'] : 0];
    $weekTotal += $s;
    if ($s > $max) $max = $s;
}
$top = vgd_all("SELECT p.id, p.name_ar, SUM(r.qty) q, SUM(r.qty * p.price) v
    FROM vg_stock_reservations r JOIN products p ON p.id = r.product_id
    WHERE r.released_at IS NULL AND r.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY p.id, p.name_ar ORDER BY q DESC LIMIT 8");
$recent = vgd_all("SELECT id, name, phone, total, status, created_at FROM leads WHERE type = 'order' ORDER BY created_at DESC LIMIT 8");

$labels = ['new' => 'جديد', 'contacted' => 'تم التواصل', 'delivered' => 'تم التوصيل', 'closed' => 'مغلق', 'cancelled' => 'ملغي'];
$page_title = 'لوحة التقارير';
$active = 'reports';
require __DIR__ . '/includes/layout_top.php';
?>
<style id="vg-unslop">
.kpis{gap:16px;margin-bottom:24px}
.kpis .kpi{border-radius:12px;padding:20px !important;box-shadow:0 1px 2px rgba(15,27,45,.05) !important;border:1px solid #e6ecf3 !important}
.kpis .kpi::after{display:none !important}
.kpis .kpi.hero{background:#0667a5 !important;border-color:transparent !important}
.kpis .kchip{background:#eef3f9 !important;color:#51607a !important;border-radius:10px}
.kpis .kpi.hero .kchip{background:rgba(255,255,255,.16) !important;color:#fff !important}
.kpis .kpi.on .kchip{background:#fdecec !important;color:#b91c1c !important}
.kpis .kpi.on .kchip svg,.kpis .kchip svg{color:inherit;stroke:currentColor}
.kpis .kval{font-weight:600 !important;font-variant-numeric:tabular-nums;letter-spacing:0}
.kpis .kpi small{font-weight:500 !important}
.kpis .kpi-label{font-weight:500 !important}
.card table th,.vg-box table th{font-weight:600 !important}
.oc{border-radius:12px}
</style>
<?php
$nToday = (int)($today['c'] ?? 0);
$nMonth = (int)($month['c'] ?? 0);
$nLow = (int)($low['c'] ?? 0);
$nNew = (int)($pending['c'] ?? 0);
?>
<style>
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin-bottom:18px}
.kpi{display:flex;flex-direction:column;gap:12px;padding:16px 18px;text-decoration:none;color:inherit}
.kpi-top{display:flex;align-items:center;justify-content:space-between;gap:10px}
.kpi-label{font-size:13.5px;font-weight:600;color:var(--muted)}
.kchip{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;flex:0 0 40px;background:#e8f3fb;color:#0667a5}
.kchip .kic{width:21px;height:21px}
.kchip.green{background:#e3f6ea;color:#15803d}
.kchip.amber{background:#fdf1dc;color:#b45309}
.kchip.red{background:#fde8e8;color:#b91c1c}
.kchip.violet{background:#ece9fd;color:#5b46d6}
.kchip.cyan{background:#e0f6fd;color:#0a8fb8}
.kval{display:flex;align-items:baseline;gap:6px;font-size:30px;font-weight:800;line-height:1.1;color:#0f1b2d}
.kval small{font-size:13px;font-weight:700;color:var(--muted)}
.ksub{font-size:12.5px;color:var(--muted);min-height:18px}
.ksub b{color:#0f1b2d}
.kpi::before{display:none}
.kpi.on-alert{box-shadow:0 0 0 1px rgba(185,28,28,.25),0 6px 16px -6px rgba(185,28,28,.25) !important}
.grid2{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;margin-bottom:16px}
.ch{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px}
.ch h3{margin:0;font-size:16px}
.ch span{font-size:12.5px;color:var(--muted)}
.bars{display:flex;align-items:flex-end;gap:10px;height:160px;padding-top:8px}
.bar{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;font-size:11px;color:var(--muted);gap:4px}
.bar i{display:block;width:100%;max-width:40px;background:linear-gradient(180deg,#05b3e9,#0667a5);border-radius:8px 8px 3px 3px;min-height:3px}
.bar.zero i{background:#e2e8ef}
.badge{display:inline-block;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600;background:#e5e7eb;color:#374151}
.badge.new{background:#dbeafe;color:#1e40af}.badge.contacted{background:#fef3c7;color:#92400e}
.badge.delivered{background:#dcfce7;color:#166534}.badge.closed{background:#e0e7ff;color:#3730a3}.badge.cancelled{background:#fee2e2;color:#991b1b}
.srow{display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid #eef1f5}
.srow:last-child{border-bottom:0}
.srow b{font-variant-numeric:tabular-nums}
.empty{display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center;color:var(--muted);padding:18px 8px}
.empty .kic{width:34px;height:34px;color:#94a3b8}
.empty p{margin:0;max-width:300px;line-height:1.7;font-size:13.5px}
.tw{overflow-x:auto}
.kpis{grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.kpi.card{margin:0;position:relative;overflow:hidden;border-radius:16px;padding:16px 18px 18px}
.kpi.card::after{content:"";position:absolute;inset-inline:0;bottom:0;height:3px;background:var(--tone,#0667a5);opacity:.9}
.kpi.hero{grid-column:span 2;background:linear-gradient(135deg,#0667a5 0%,#0a8fd0 55%,#05b3e9 100%);color:#fff;--tone:#7fe3ff}
.kpi.hero .kpi-label,.kpi.hero .kval small,.kpi.hero .ksub{color:rgba(255,255,255,.85)}
.kpi.hero .kval{color:#fff;font-size:40px}
.kpi.hero .ksub b{color:#fff}
.kpi.hero .kchip{background:rgba(255,255,255,.18);color:#fff}
.kpi.t-cyan{--tone:#05b3e9}.kpi.t-violet{--tone:#5b46d6}.kpi.t-green{--tone:#15803d}.kpi.t-amber{--tone:#d97706}.kpi.t-red{--tone:#b91c1c}
@media(max-width:1000px){.kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:480px){.kpis{grid-template-columns:1fr}.kpi.hero{grid-column:auto}.kpi.hero .kval{font-size:34px}}
</style>
<div class="kpis">
  <div class="kpi card hero">
    <div class="kpi-top"><span class="kpi-label">مبيعات اليوم</span><span class="kchip"><?= vgd_ic($P['cash']) ?></span></div>
    <div class="kval"><?= number_format((float)($today['s'] ?? 0), 0) ?><small>ج.م</small></div>
    <div class="ksub"><?= $nToday ? '<b>' . $nToday . '</b> ' . ($nToday === 1 ? 'طلب' : 'طلبات') . ' النهارده' : 'لسه مفيش طلبات النهارده' ?></div>
  </div>
  <div class="kpi card t-cyan">
    <div class="kpi-top"><span class="kpi-label">مبيعات الشهر</span><span class="kchip cyan"><?= vgd_ic($P['cal']) ?></span></div>
    <div class="kval"><?= number_format((float)($month['s'] ?? 0), 0) ?><small>ج.م</small></div>
    <div class="ksub"><?= $nMonth ? '<b>' . $nMonth . '</b> ' . ($nMonth === 1 ? 'طلب' : 'طلبات') . ' الشهر ده' : 'مفيش طلبات الشهر ده لحد دلوقتي' ?></div>
  </div>
  <a class="kpi card t-violet" href="leads.php?status=new">
    <div class="kpi-top"><span class="kpi-label">طلبات جديدة</span><span class="kchip violet"><?= vgd_ic($P['inbox']) ?></span></div>
    <div class="kval"><?= $nNew ?></div>
    <div class="ksub"><?= $nNew ? 'محتاجة متابعة منك' : 'مفيش طلبات في الانتظار' ?></div>
  </a>
  <a class="kpi card t-green" href="leads.php?status=delivered">
    <div class="kpi-top"><span class="kpi-label">طلبات تم توصيلها</span><span class="kchip green"><?= vgd_ic($P['truck']) ?></span></div>
    <div class="kval"><?= (int)($byStatus['delivered'] ?? 0) ?></div>
    <div class="ksub">إجمالي الطلبات المتوصّلة</div>
  </a>
  <a class="kpi card <?= $nLow ? 'on-alert t-red' : 't-amber' ?>" href="inventory.php?f=low">
    <div class="kpi-top"><span class="kpi-label">مخزون منخفض</span><span class="kchip <?= $nLow ? 'red' : 'amber' ?>"><?= vgd_ic($P['alert']) ?></span></div>
    <div class="kval"><?= $nLow ?></div>
    <div class="ksub"><?= $nLow ? 'منتجات قربت تخلص، راجعها' : 'مفيش منتجات قربت تخلص' ?></div>
  </a>
  <div class="kpi card t-amber">
    <div class="kpi-top"><span class="kpi-label">متوسط قيمة الطلب</span><span class="kchip amber"><?= vgd_ic($P['receipt']) ?></span></div>
    <div class="kval"><?= number_format($avg, 0) ?><small>ج.م</small></div>
    <div class="ksub">من <b><?= (int)($allt['c'] ?? 0) ?></b> طلب غير ملغي</div>
  </div>
  <a class="kpi card t-cyan" href="customers.php">
    <div class="kpi-top"><span class="kpi-label">العملاء المسجّلون</span><span class="kchip cyan"><?= vgd_ic($P['users']) ?></span></div>
    <div class="kval"><?= (int)($cust['c'] ?? 0) ?></div>
    <div class="ksub">أصحاب حسابات على الموقع</div>
  </a>
</div>
<div class="grid2">
  <div class="card">
    <div class="ch"><h3>مبيعات آخر 7 أيام</h3><span><?= number_format($weekTotal, 0) ?> ج.م</span></div>
    <div class="bars">
      <?php foreach ($series as $s): ?>
        <div class="bar <?= $s[1] > 0 ? '' : 'zero' ?>" title="<?= vgd_h($s[0]) ?>: <?= number_format($s[1], 0) ?> ج.م (<?= $s[2] ?> طلب)">
          <span><?= $s[1] > 0 ? number_format($s[1], 0) : '' ?></span>
          <i style="height:<?= max(3, round($s[1] / $max * 100)) ?>%"></i>
          <span><?= vgd_h(substr($s[0], 5)) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card">
    <div class="ch"><h3>حالة الطلبات</h3></div>
    <?php foreach ($labels as $k => $lb): ?>
      <div class="srow"><span class="badge <?= $k ?>"><?= $lb ?></span><b><?= (int)($byStatus[$k] ?? 0) ?></b></div>
    <?php endforeach; ?>
  </div>
  <div class="card tw">
    <div class="ch"><h3>الأكثر مبيعاً</h3><span>آخر 30 يوم</span></div>
    <?php if ($top): ?>
    <table><thead><tr><th>المنتج</th><th>الكمية</th><th>القيمة</th></tr></thead><tbody>
      <?php foreach ($top as $t): ?>
        <tr><td><?= vgd_h($t['name_ar']) ?></td><td><?= (int)$t['q'] ?></td><td><?= number_format((float)$t['v'], 0) ?> ج.م</td></tr>
      <?php endforeach; ?>
    </tbody></table>
    <?php else: ?>
      <div class="empty"><?= vgd_ic($P['receipt']) ?><p>لسه مفيش بيانات مبيعات للمنتجات. هتظهر هنا لما تفعّل تتبع الكمية لمنتجاتك وتوصلك طلبات عليها.</p><a class="btn sm secondary" href="inventory.php">فتح المخزون</a></div>
    <?php endif; ?>
  </div>
  <div class="card tw">
    <div class="ch"><h3>تنبيهات المخزون</h3><a class="btn sm secondary" href="inventory.php">فتح المخزون</a></div>
    <?php if ($lowList): ?>
      <?php foreach ($lowList as $p): ?>
        <div class="srow"><span><?= vgd_h($p['name_ar']) ?></span><span class="badge <?= (int)$p['stock_qty'] <= 0 ? 'cancelled' : 'contacted' ?>"><?= (int)$p['stock_qty'] <= 0 ? 'نفد' : (int)$p['stock_qty'] . ' متبقي' ?></span></div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty"><?= vgd_ic($P['truck']) ?><p>المخزون في وضع كويس، ومفيش منتجات قربت تخلص.</p></div>
    <?php endif; ?>
  </div>
</div>
<div class="card tw">
  <div class="ch"><h3>آخر الطلبات</h3><a class="btn sm secondary" href="leads.php?status=all">عرض الكل</a></div>
  <table><thead><tr><th>رقم الطلب</th><th>العميل</th><th>الإجمالي</th><th>الحالة</th><th>التاريخ</th></tr></thead><tbody>
    <?php foreach ($recent as $r): ?>
      <tr>
        <td><a href="order.php?id=<?= (int)$r['id'] ?>">#<?= (int)$r['id'] ?></a></td>
        <td><?= vgd_h($r['name']) ?></td>
        <td><?= number_format((float)$r['total'], 0) ?> ج.م</td>
        <td><span class="badge <?= vgd_h($r['status']) ?>"><?= vgd_h($labels[$r['status']] ?? $r['status']) ?></span></td>
        <td><?= vgd_h($r['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$recent): ?><tr><td colspan="5" style="color:var(--muted)">لسه مفيش طلبات. أول طلب هيظهر هنا تلقائياً.</td></tr><?php endif; ?>
  </tbody></table>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
