<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('dashboard');

$productCount = db()->query('SELECT COUNT(*) c FROM products')->fetch()['c'];
$outOfStock   = db()->query("SELECT COUNT(*) c FROM products WHERE in_stock = 0")->fetch()['c'];
$newLeads     = db()->query("SELECT COUNT(*) c FROM leads WHERE status = 'new'")->fetch()['c'];
$recentLeads  = db()->query('SELECT * FROM leads ORDER BY created_at DESC LIMIT 6')->fetchAll();

date_default_timezone_set('Africa/Cairo');
function vgh_one(string $sql): array { try { return db()->query($sql)->fetch() ?: []; } catch (Throwable $e) { return []; } }
function vgh_all(string $sql): array { try { return db()->query($sql)->fetchAll() ?: []; } catch (Throwable $e) { return []; } }
function vgh_e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function vgh_money($n): string { return number_format((float)$n, 0); }
function vgh_short($n): string { $n = (float)$n; return $n >= 1000 ? rtrim(rtrim(number_format($n / 1000, 1), '0'), '.') . 'ك' : number_format($n, 0); }
$vghOk  = "type = 'order' AND status <> 'cancelled'";
$vghTod = vgh_one("SELECT COUNT(*) c, COALESCE(SUM(total),0) s FROM leads WHERE $vghOk AND DATE(created_at) = CURDATE()");
$vghMon = vgh_one("SELECT COUNT(*) c, COALESCE(SUM(total),0) s FROM leads WHERE $vghOk AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
$vghWk  = vgh_all("SELECT DATE(created_at) d, COUNT(*) c, COALESCE(SUM(total),0) s FROM leads WHERE $vghOk AND created_at >= CURDATE() - INTERVAL 6 DAY GROUP BY DATE(created_at)");
$vghLow = vgh_all("SELECT id, name_ar, stock_qty, in_stock FROM products WHERE in_stock = 0 OR (stock_qty IS NOT NULL AND stock_qty <= 3) ORDER BY (in_stock = 1 AND stock_qty > 0) DESC, stock_qty ASC, name_ar ASC LIMIT 6");
$vghCnt = vgh_one("SELECT SUM(in_stock = 0 OR (stock_qty IS NOT NULL AND stock_qty <= 0)) o, SUM(in_stock = 1 AND stock_qty IS NOT NULL AND stock_qty BETWEEN 1 AND 3) l FROM products");
$vghOutN = (int)($vghCnt['o'] ?? 0); $vghLowOnly = (int)($vghCnt['l'] ?? 0); $vghLowN = $vghOutN + $vghLowOnly;
$vghDayAr = ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
$vghMonAr = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
$vghBy = []; foreach ($vghWk as $r) { $vghBy[$r['d']] = $r; }
$vghDays = [];
for ($i = 6; $i >= 0; $i--) {
    $ts = strtotime("-$i day"); $d = date('Y-m-d', $ts); $r = $vghBy[$d] ?? ['c' => 0, 's' => 0];
    $vghDays[] = ['n' => $vghDayAr[(int)date('w', $ts)], 'c' => (int)$r['c'], 's' => (float)$r['s'], 'today' => $i === 0];
}
$vghMax = max(1, max(array_column($vghDays, 's')));
$vghWkSum = array_sum(array_column($vghDays, 's')); $vghWkCnt = array_sum(array_column($vghDays, 'c'));
$vghMe = function_exists('current_admin') ? (current_admin() ?: []) : [];
$vghFirst = trim(explode(' ', trim((string)(($vghMe['full_name'] ?? '') ?: ($vghMe['username'] ?? ''))))[0] ?? '');
$vghFirst = $vghFirst !== '' ? mb_convert_case($vghFirst, MB_CASE_TITLE) : '';
$vghHello = ((int)date('G') < 12 ? 'صباح الخير' : 'مساء الخير') . ($vghFirst !== '' ? ' يا ' . $vghFirst : '');
$vghDate = $vghDayAr[(int)date('w')] . ' ' . (int)date('j') . ' ' . $vghMonAr[(int)date('n') - 1] . ' ' . date('Y');
$vghTypeAr = ['order' => 'طلب شراء', 'contact' => 'رسالة تواصل', 'quote' => 'طلب عرض سعر'];
$vghStAr = ['new' => ['جديد', 'st-new'], 'contacted' => ['تم التواصل', 'st-amber'], 'paid' => ['مدفوع', 'st-green'], 'delivered' => ['تم التسليم', 'st-green'], 'closed' => ['مغلق', 'st-gray'], 'cancelled' => ['ملغي', 'st-red']];
function vgh_when(string $t): string { $ts = strtotime($t); if (!$ts) return ''; return date('Y-m-d', $ts) === date('Y-m-d') ? 'النهارده ' . date('H:i', $ts) : date('d/m H:i', $ts); }
function vgh_detail(array $l): string {
    $m = trim((string)($l['message'] ?? ''));
    if ($m !== '') return mb_substr($m, 0, 80);
    $c = json_decode((string)($l['cart_json'] ?? ''), true);
    if (is_array($c) && count($c)) return count($c) . ' صنف في الطلب';
    return '';
}
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
<style id="vgh-css">
.vgh-hd{margin:0 0 18px}
.vgh-hd h2{margin:0;font-size:22px;line-height:1.3}
.vgh-hd p{margin:4px 0 0;color:#64748b;font-size:13.5px}
.vgh-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:16px}
.vgh-kpi{display:grid;grid-template-columns:36px minmax(0,1fr);column-gap:10px;row-gap:4px;align-items:center;align-content:start;padding:14px 16px;background:#fff;border:1px solid #e3e9f1;border-radius:14px;color:inherit;text-decoration:none;box-shadow:0 1px 2px rgba(15,27,45,.05);transition:border-color .15s ease-out,box-shadow .15s ease-out}
.vgh-kpi:hover{border-color:#c5d3e4;box-shadow:0 6px 16px -8px rgba(15,27,45,.22)}
.vgh-kpi:focus-visible,.vgh-row:focus-visible,.vgh-li:focus-visible{outline:2px solid #1d4ed8;outline-offset:2px}
.vgh-kpi .ic{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;grid-row:1}
.vgh-kpi .ic svg{width:19px;height:19px}
.vgh-kpi .tx{display:contents}
.vgh-kpi .lb{grid-row:1;grid-column:2;font-size:12.5px;line-height:1.35;color:#64748b}
.vgh-kpi .vl{grid-row:2;grid-column:1 / -1;margin-top:6px;font-size:24px;font-weight:700;line-height:1.25;color:#0f1b2d;font-variant-numeric:tabular-nums;white-space:nowrap}
.vgh-kpi .vl small{font-size:12px;font-weight:600;color:#64748b;margin-inline-start:4px}
.vgh-kpi .sb{grid-row:3;grid-column:1 / -1;font-size:12px;line-height:1.4;color:#64748b}
.vgh-kpi.hot{border-color:#bfd0f7;background:#f7f9ff}
.t-green{background:#e3f6ea;color:#15803d}.t-violet{background:#ece9fd;color:#5b46d6}.t-blue{background:#e8f3fb;color:#0667a5}.t-amber{background:#fdf1dc;color:#b45309}.t-red{background:#fee2e2;color:#b91c1c}
.vgh-grid{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(0,1fr);gap:14px;margin-bottom:16px}
.vgh-card{background:#fff;border:1px solid #e3e9f1;border-radius:14px;padding:16px;box-shadow:0 1px 2px rgba(15,27,45,.05)}
.vgh-ct{display:flex;justify-content:space-between;align-items:baseline;gap:10px;margin-bottom:14px}
.vgh-ct h3{margin:0;font-size:15px}
.vgh-ct small,.vgh-ct a{color:#64748b;font-size:12.5px}
.vgh-ct a{text-decoration:none;font-weight:600;color:#0667a5}
.vgh-bars{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:8px;align-items:end;height:150px;padding-bottom:2px;border-bottom:1px solid #e3e9f1}
.vgh-bar{display:flex;flex-direction:column;align-items:center;justify-content:flex-end;gap:5px;height:100%;min-width:0}
.vgh-bar b{font-size:11px;font-weight:600;color:#475569;font-variant-numeric:tabular-nums;white-space:nowrap}
.vgh-bar i{display:block;width:100%;max-width:36px;background:#9fc3de;border-radius:8px 8px 3px 3px}
.vgh-bar.today i{background:#0667a5}.vgh-bar.today b{color:#0667a5}
.vgh-days{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:8px;margin-top:8px;text-align:center;font-size:11.5px;color:#64748b}
.vgh-days span{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.vgh-days em{display:block;font-style:normal;font-size:11px;color:#94a3b8}
.vgh-li{display:flex;justify-content:space-between;align-items:center;gap:10px;min-height:46px;padding:8px 0;border-top:1px solid #eef2f7;color:inherit;text-decoration:none}
.vgh-li:first-of-type{border-top:0}
.vgh-li span.n{min-width:0;font-size:13.5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.vgh-bd{display:inline-flex;align-items:center;height:22px;padding:0 9px;border-radius:999px;font-size:11.5px;font-weight:700;white-space:nowrap;flex:0 0 auto}
.st-new{background:#dbe7ff;color:#1d4ed8}.st-amber{background:#fdf1dc;color:#b45309}.st-green{background:#e3f6ea;color:#15803d}.st-gray{background:#eef2f7;color:#475569}.st-red{background:#fee2e2;color:#b91c1c}
.vgh-empty{margin:0;padding:18px 0;color:#64748b;font-size:13.5px;text-align:center}
.vgh-row{display:flex;justify-content:space-between;align-items:center;gap:12px;min-height:60px;padding:12px 4px;border-top:1px solid #eef2f7;color:inherit;text-decoration:none;border-radius:8px}
.vgh-row:hover{background:#f6f9fc}
.vgh-list .vgh-row:first-child{border-top:0}
.vgh-rm{min-width:0;flex:1}
.vgh-rt{font-size:14px}.vgh-rt strong{font-weight:700}.vgh-rt span{color:#64748b;font-size:12px;margin-inline-start:6px}
.vgh-rs{margin-top:2px;color:#64748b;font-size:12.5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.vgh-rr{display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex:0 0 auto;font-size:12px;color:#64748b}
.vgh-rr .am{font-weight:700;color:#0f1b2d;font-size:13px;font-variant-numeric:tabular-nums}
.vgh-sec{margin:18px 0 10px;font-size:15px}
@media (max-width:860px){.vgh-grid{grid-template-columns:minmax(0,1fr)}}
@media (max-width:560px){.vgh-kpis{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.vgh-kpi{padding:12px}.vgh-kpi .vl{font-size:20px}.vgh-hd h2{font-size:19px}.vgh-row{flex-wrap:wrap}.vgh-rr{width:100%;flex-direction:row;justify-content:space-between;align-items:center}}
</style>
<?php
$vghNewN = (int)$newLeads;
?>
<div class="vgh-hd">
  <h2><?= vgh_e($vghHello) ?></h2>
  <p><?= vgh_e($vghDate) ?><?php if ($vghNewN > 0): ?> · عندك <?= $vghNewN ?> <?= $vghNewN === 1 ? 'طلب أو رسالة جديدة' : 'طلبات ورسائل جديدة' ?> محتاجة رد<?php else: ?> · مفيش حاجة جديدة محتاجة رد<?php endif; ?></p>
</div>

<div class="vgh-kpis">
  <a class="vgh-kpi" href="dashboard.php"><span class="ic t-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M3 4h2.2l2.1 10.2a1.6 1.6 0 0 0 1.6 1.3h8.1a1.6 1.6 0 0 0 1.6-1.2L20.4 8H6.1"/></svg></span><span class="tx"><span class="lb">مبيعات النهارده</span><span class="vl"><?= vgh_money($vghTod['s'] ?? 0) ?><small>ج.م</small></span><span class="sb"><?= (int)($vghTod['c'] ?? 0) ?> طلب</span></span></a>
  <a class="vgh-kpi<?= $vghNewN > 0 ? ' hot' : '' ?>" href="leads.php"><span class="ic t-violet"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9a6 6 0 1 1 12 0c0 5 2 6.5 2 6.5H4S6 14 6 9"/><path d="M10 19a2 2 0 0 0 4 0"/></svg></span><span class="tx"><span class="lb">جديد محتاج رد</span><span class="vl"><?= $vghNewN ?></span><span class="sb">طلبات ورسائل</span></span></a>
  <a class="vgh-kpi" href="dashboard.php"><span class="ic t-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg></span><span class="tx"><span class="lb">مبيعات الشهر</span><span class="vl"><?= vgh_money($vghMon['s'] ?? 0) ?><small>ج.م</small></span><span class="sb"><?= (int)($vghMon['c'] ?? 0) ?> طلب</span></span></a>
  <a class="vgh-kpi" href="inventory.php"><span class="ic <?= $vghLowN > 0 ? 't-amber' : 't-green' ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg></span><span class="tx"><span class="lb">مخزون يحتاج انتباه</span><span class="vl"><?= $vghLowN ?></span><span class="sb"><?= $vghLowN > 0 ? 'نفد ' . $vghOutN . ' · قرب يخلص ' . $vghLowOnly : 'من ' . (int)$productCount . ' منتج' ?></span></span></a>
</div>

<div class="vgh-grid">
  <section class="vgh-card" aria-label="مبيعات آخر ٧ أيام">
    <div class="vgh-ct"><h3>مبيعات آخر ٧ أيام</h3><small><?= vgh_money($vghWkSum) ?> ج.م · <?= (int)$vghWkCnt ?> طلب</small></div>
    <?php if ($vghWkSum <= 0): ?><p class="vgh-empty" style="padding:44px 0">لسه مفيش مبيعات في آخر ٧ أيام.</p><?php else: ?>
    <div class="vgh-bars" role="img" aria-label="<?php foreach ($vghDays as $d) { echo vgh_e($d['n'] . ': ' . vgh_money($d['s']) . ' جنيه، ' . $d['c'] . ' طلب. '); } ?>">
      <?php foreach ($vghDays as $d): $h = $d['s'] > 0 ? max(6, (int)round($d['s'] / $vghMax * 104)) : 3; ?>
      <div class="vgh-bar<?= $d['today'] ? ' today' : '' ?>" title="<?= vgh_e($d['n'] . ' · ' . vgh_money($d['s']) . ' ج.م · ' . $d['c'] . ' طلب') ?>"><b><?= $d['s'] > 0 ? vgh_short($d['s']) : '—' ?></b><i style="height:<?= $h ?>px"></i></div>
      <?php endforeach; ?>
    </div>
    <div class="vgh-days" aria-hidden="true"><?php foreach ($vghDays as $d): ?><span><?= vgh_e($d['n']) ?><em><?= $d['c'] ?> طلب</em></span><?php endforeach; ?></div>
    <?php endif; ?>
  </section>
  <section class="vgh-card" aria-label="مخزون يحتاج انتباه">
    <div class="vgh-ct"><h3>مخزون يحتاج انتباه</h3><a href="inventory.php">المخزون</a></div>
    <?php if (!$vghLow): ?>
      <p class="vgh-empty">مخزون كل المنتجات كويس.</p>
    <?php else: foreach ($vghLow as $p): $q = (int)$p['stock_qty']; $o = (int)($p['in_stock'] ?? 1) !== 1 || $q <= 0; ?>
      <a class="vgh-li" href="inventory.php?q=<?= urlencode((string)$p['name_ar']) ?>"><span class="n"><?= vgh_e($p['name_ar']) ?></span><span class="vgh-bd <?= $o ? 'st-red' : 'st-amber' ?>"><?= $o ? 'نفد' : 'باقي ' . $q ?></span></a>
    <?php endforeach; endif; ?>
  </section>
</div>

<style>.kchip{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;background:#e8f3fb;color:#0667a5}.kchip.green{background:#e3f6ea;color:#15803d}.kchip.amber{background:#fdf1dc;color:#b45309}.kchip.violet{background:#ece9fd;color:#5b46d6}</style>
<div class="quick">
<h3>الإجراءات السريعة</h3>
<a class="card" href="leads.php"><span class="kchip violet"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" aria-hidden="true"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6M9 12h6"/></svg></span>الطلبات الحالية</a>
<a class="card" href="inventory.php"><span class="kchip green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" aria-hidden="true"><path d="M12 3l9 4.5-9 4.5-9-4.5zM3 12l9 4.5 9-4.5M3 16.5L12 21l9-4.5"/></svg></span>إدارة المخزون</a>
<a class="card" href="products.php"><span class="kchip amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" aria-hidden="true"><path d="M12 3l8 4.5v9L12 21l-8-4.5v-9zM12 12l8-4.5M12 12v9M12 12L4 7.5"/></svg></span>المنتجات</a>
<a class="card" href="dashboard.php"><span class="kchip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" aria-hidden="true"><path d="M12 3v9h9a9 9 0 1 1-9-9zM15 3.5A9 9 0 0 1 20.5 9H15z"/></svg></span>لوحة التقارير</a>
</div>

<section class="vgh-card" style="margin-top:16px" aria-label="أحدث الطلبات والرسائل">
  <div class="vgh-ct"><h3>أحدث الطلبات والرسائل</h3><a href="leads.php">عرض الكل</a></div>
  <?php if (!$recentLeads): ?>
    <p class="vgh-empty">لسه مفيش رسائل أو طلبات.</p>
  <?php else: ?>
  <div class="vgh-list">
    <?php foreach ($recentLeads as $l): $st = $vghStAr[$l['status'] ?? ''] ?? [(string)($l['status'] ?? ''), 'st-gray']; $det = vgh_detail($l); ?>
    <a class="vgh-row" href="order.php?id=<?= (int)$l['id'] ?>">
      <div class="vgh-rm">
        <div class="vgh-rt"><strong><?= vgh_e(($l['name'] ?? '') ?: 'بدون اسم') ?></strong><span><?= vgh_e($vghTypeAr[$l['type'] ?? ''] ?? ($l['type'] ?? '')) ?><?= !empty($l['phone']) ? ' · ' . vgh_e($l['phone']) : '' ?></span></div>
        <?php if ($det !== ''): ?><div class="vgh-rs"><?= vgh_e($det) ?></div><?php endif; ?>
      </div>
      <div class="vgh-rr">
        <span class="vgh-bd <?= vgh_e($st[1]) ?>"><?= vgh_e($st[0]) ?></span>
        <?php if (($l['type'] ?? '') === 'order' && (float)($l['total'] ?? 0) > 0): ?><span class="am"><?= vgh_money($l['total']) ?> ج.م</span><?php endif; ?>
        <span><?= vgh_e(vgh_when((string)$l['created_at'])) ?></span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
