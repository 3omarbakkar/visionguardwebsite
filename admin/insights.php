<?php
require_once __DIR__ . '/includes/perms.php';
require_perm('insights');
date_default_timezone_set('Africa/Cairo');

$range = $_GET['r'] ?? '7';
$map = ['1' => 1, '7' => 7, '30' => 30, '90' => 90];
if (!is_string($range) || !isset($map[$range])) $range = '7';
$days = $map[$range];
$from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
$prevFrom = date('Y-m-d', strtotime('-' . (2 * $days - 1) . ' days'));
$prevTo = date('Y-m-d', strtotime('-' . $days . ' days'));
$today = date('Y-m-d');

$ready = true;
try { db()->query('SELECT 1 FROM site_events LIMIT 1'); } catch (Throwable $e) { $ready = false; }

function ins_q($sql, $args = []) {
    $s = db()->prepare($sql);
    $s->execute($args);
    return $s->fetchAll();
}
function ins_n($sql, $args = []) {
    $s = db()->prepare($sql);
    $s->execute($args);
    return (int)$s->fetchColumn();
}
function ins_delta($cur, $prev) {
    if ($prev <= 0) return $cur > 0 ? 'جديد' : '—';
    $p = round(($cur - $prev) / $prev * 100);
    return ($p >= 0 ? '+' : '') . $p . '%';
}

$data = [];
if ($ready) {
    $sets = [['from' => $from, 'to' => $today], ['from' => $prevFrom, 'to' => $prevTo]];
    foreach ($sets as $i => $r) {
        $a = [$r['from'], $r['to']];
        $data[$i] = [
            'visitors' => ins_n("SELECT COUNT(DISTINCT vid) FROM site_events WHERE t='pv' AND d BETWEEN ? AND ?", $a),
            'views'    => ins_n("SELECT COUNT(*) FROM site_events WHERE t='pv' AND d BETWEEN ? AND ?", $a),
            'wa'       => ins_n("SELECT COUNT(*) FROM site_events WHERE t='wa' AND d BETWEEN ? AND ?", $a),
            'call'     => ins_n("SELECT COUNT(*) FROM site_events WHERE t='call' AND d BETWEEN ? AND ?", $a),
            'cart'     => ins_n("SELECT COUNT(*) FROM site_events WHERE t='cart' AND d BETWEEN ? AND ?", $a),
        ];
    }
    $daily = [];
    foreach (ins_q("SELECT d, COUNT(DISTINCT vid) v FROM site_events WHERE t='pv' AND d BETWEEN ? AND ? GROUP BY d", [$from, $today]) as $r) {
        $daily[$r['d']] = (int)$r['v'];
    }
    $orderSrc = []; try { $orderSrc = ins_q("SELECT traffic_src, COUNT(*) c, COALESCE(SUM(total),0) tot FROM leads WHERE type='order' AND traffic_src IS NOT NULL AND DATE(created_at) BETWEEN ? AND ? GROUP BY traffic_src ORDER BY c DESC", [$from, $today]); } catch (Throwable $e) {}
    $sources = ins_q("SELECT src, COUNT(DISTINCT vid) v FROM site_events WHERE t='pv' AND d BETWEEN ? AND ? GROUP BY src ORDER BY v DESC", [$from, $today]);
    $devices = ins_q("SELECT dev, COUNT(DISTINCT vid) v FROM site_events WHERE t='pv' AND d BETWEEN ? AND ? GROUP BY dev ORDER BY v DESC", [$from, $today]);
    $products = ins_q("SELECT label,
            SUM(t='product') views, SUM(t='cart') carts
        FROM site_events WHERE t IN ('product','cart') AND label <> '' AND d BETWEEN ? AND ?
        GROUP BY label ORDER BY views DESC, carts DESC LIMIT 12", [$from, $today]);
    $contacts = ins_q("SELECT label, SUM(t='wa') wa, SUM(t='call') c
        FROM site_events WHERE t IN ('wa','call') AND d BETWEEN ? AND ?
        GROUP BY label ORDER BY (SUM(t='wa') + SUM(t='call')) DESC LIMIT 10", [$from, $today]);
    $terms = ins_q("SELECT label, COUNT(*) n, COUNT(DISTINCT vid) u, MIN(results) minr
        FROM site_events WHERE t='search' AND d BETWEEN ? AND ?
        GROUP BY label ORDER BY n DESC LIMIT 15", [$from, $today]);
    $zero = ins_q("SELECT label, COUNT(*) n FROM site_events WHERE t='search' AND results = 0 AND d BETWEEN ? AND ?
        GROUP BY label ORDER BY n DESC LIMIT 10", [$from, $today]);
}

$srcNames = ['facebook' => 'فيسبوك / إنستجرام', 'google' => 'جوجل', 'tiktok' => 'تيك توك', 'whatsapp' => 'واتساب', 'direct' => 'مباشر', 'other' => 'مواقع أخرى', '' => 'غير معروف'];
$devNames = ['mobile' => 'موبايل', 'desktop' => 'كمبيوتر'];

$page_title = 'الإحصائيات';
$active = 'insights';
require __DIR__ . '/includes/layout_top.php';
?>
<div class="topbar"><h2>الإحصائيات</h2>
  <div style="display:flex;gap:6px;flex-wrap:wrap">
    <?php foreach (['1' => 'اليوم', '7' => '7 أيام', '30' => '30 يوم', '90' => '90 يوم'] as $k => $lab): ?>
      <a class="btn sm <?= (string)$range === (string)$k ? '' : 'secondary' ?>" href="insights.php?r=<?= $k ?>"><?= $lab ?></a>
    <?php endforeach; ?>
  </div>
</div>

<?php if (!$ready): ?>
  <div class="card"><p class="muted" style="margin:0">لسه مفيش بيانات. التتبع شغال من دلوقتي، والأرقام هتبدأ تظهر بمجرد ما زوار يدخلوا الموقع. حدّث الصفحة بعد شوية.</p></div>
<?php else:
  $cards = [
    ['زوار (أشخاص مختلفين)', 'visitors'],
    ['مشاهدات الصفحة', 'views'],
    ['ضغطات واتساب', 'wa'],
    ['ضغطات اتصال', 'call'],
    ['إضافة للسلة', 'cart'],
  ];
?>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px">
  <?php foreach ($cards as $c): $cur = $data[0][$c[1]]; $prv = $data[1][$c[1]]; ?>
    <div class="card" style="margin:0;text-align:center">
      <div class="muted"><?= $c[0] ?></div>
      <div style="font-size:28px;font-weight:700"><?= number_format($cur) ?></div>
      <div class="muted" style="font-size:12px"><?= $range === '1' ? 'مقابل أمس' : 'مقابل الفترة السابقة' ?>: <?= ins_delta($cur, $prv) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<?php
  // daily chart (unique visitors per day)
  $dates = [];
  for ($i = $days - 1; $i >= 0; $i--) $dates[] = date('Y-m-d', strtotime("-$i days"));
  $max = max(1, max(array_map(function ($x) use ($daily) { return $daily[$x] ?? 0; }, $dates)));
  $W = 700; $H = 160; $n = count($dates); $bw = max(2, floor(($W - 20) / $n) - 3);
?>
<?php if ($days > 1): ?>
<div class="card">
  <h3 style="margin-top:0">الزوار يوميًا</h3>
  <svg viewBox="0 0 <?= $W ?> <?= $H + 28 ?>" style="width:100%;height:auto" role="img" aria-label="الزوار يوميًا">
    <?php foreach ($dates as $i => $dd):
      $v = $daily[$dd] ?? 0;
      $h = $v ? max(2, round($v / $max * $H)) : 0;
      $x = 10 + $i * (($W - 20) / $n);
    ?>
      <rect x="<?= round($x, 1) ?>" y="<?= $H - $h + 4 ?>" width="<?= $bw ?>" height="<?= $h ?>" rx="2" fill="#1d4ed8"><title><?= $dd ?>: <?= $v ?></title></rect>
      <?php if ($n <= 14 || $i % ceil($n / 10) === 0): ?>
        <text x="<?= round($x + $bw / 2, 1) ?>" y="<?= $H + 20 ?>" font-size="11" text-anchor="middle" fill="#5a6678"><?= substr($dd, 5) ?></text>
      <?php endif; ?>
    <?php endforeach; ?>
    <text x="4" y="12" font-size="11" fill="#5a6678"><?= $max ?></text>
  </svg>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px">
  <div class="card" style="margin:0">
    <h3 style="margin-top:0">الطلبات حسب المصدر</h3>
    <table>
      <tr><th>المصدر</th><th>طلبات</th><th>الإجمالي (ج.م)</th></tr>
      <?php $osn=['facebook'=>'فيسبوك / إنستغرام','tiktok'=>'تيك توك','whatsapp'=>'واتساب','google'=>'جوجل','direct'=>'مباشر','other'=>'موقع آخر']; foreach ($orderSrc as $r): ?>
        <tr><td><?= htmlspecialchars($osn[$r['traffic_src']] ?? $r['traffic_src']) ?></td><td><?= (int)$r['c'] ?></td><td><?= number_format((float)$r['tot']) ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$orderSrc): ?><tr><td colspan="3" class="muted">لا توجد طلبات بمصدر مسجّل بعد.</td></tr><?php endif; ?>
    </table>
  </div>
  <div class="card" style="margin:0">
    <h3 style="margin-top:0">جايين منين</h3>
    <table>
      <tr><th>المصدر</th><th>زوار</th></tr>
      <?php foreach ($sources as $r): ?>
        <tr><td><?= htmlspecialchars($srcNames[$r['src']] ?? $r['src']) ?></td><td><?= (int)$r['v'] ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$sources): ?><tr><td colspan="2" class="muted">لا توجد بيانات.</td></tr><?php endif; ?>
    </table>
    <h3>الأجهزة</h3>
    <table>
      <?php foreach ($devices as $r): ?>
        <tr><td><?= htmlspecialchars($devNames[$r['dev']] ?? $r['dev']) ?></td><td><?= (int)$r['v'] ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$devices): ?><tr><td class="muted">لا توجد بيانات.</td></tr><?php endif; ?>
    </table>
  </div>

  <div class="card" style="margin:0">
    <h3 style="margin-top:0">أكتر المنتجات مشاهدة</h3>
    <table>
      <tr><th>المنتج</th><th>مشاهدات</th><th>للسلة</th></tr>
      <?php foreach ($products as $r): ?>
        <tr><td><?= htmlspecialchars($r['label']) ?></td><td><?= (int)$r['views'] ?></td><td><?= (int)$r['carts'] ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$products): ?><tr><td colspan="3" class="muted">لا توجد بيانات.</td></tr><?php endif; ?>
    </table>
  </div>

  <div class="card" style="margin:0">
    <h3 style="margin-top:0">الضغط على واتساب والاتصال</h3>
    <table>
      <tr><th>من فين</th><th>واتساب</th><th>اتصال</th></tr>
      <?php foreach ($contacts as $r): ?>
        <tr><td><?= $r['label'] !== '' ? htmlspecialchars($r['label']) : 'عام (الهيدر / الفوتر)' ?></td><td><?= (int)$r['wa'] ?></td><td><?= (int)$r['c'] ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$contacts): ?><tr><td colspan="3" class="muted">لا توجد بيانات.</td></tr><?php endif; ?>
    </table>
  </div>

  <div class="card" style="margin:0">
    <h3 style="margin-top:0">كلمات البحث</h3>
    <table>
      <tr><th>الكلمة</th><th>مرات</th><th>نتائج</th></tr>
      <?php foreach ($terms as $r): ?>
        <tr><td><?= htmlspecialchars($r['label']) ?></td><td><?= (int)$r['n'] ?></td><td><?= $r['minr'] === null ? '—' : ((int)$r['minr'] === 0 ? '<span style="color:var(--danger)">لا شيء</span>' : 'موجود') ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$terms): ?><tr><td colspan="3" class="muted">لا توجد بيانات.</td></tr><?php endif; ?>
    </table>
    <?php if ($zero): ?>
      <h3>بحثوا عنها ومالقوش</h3>
      <p class="muted" style="margin-top:0">دي منتجات ممكن تفكر تضيفها:</p>
      <table>
        <?php foreach ($zero as $r): ?>
          <tr><td><?= htmlspecialchars($r['label']) ?></td><td><?= (int)$r['n'] ?></td></tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="card" style="margin-top:16px">
  <p class="muted" style="margin:0 0 8px">الإحصائيات مجهولة (من غير أي بيانات شخصية). لو مش عايز زياراتك انت تتحسب على الجهاز ده:</p>
  <button class="btn sm secondary" type="button" id="vgOpt"></button>
</div>
<script>
(function () {
  var b = document.getElementById('vgOpt');
  function on() { try { return localStorage.getItem('vg_notrack') === '1'; } catch (e) { return false; } }
  function draw() { b.textContent = on() ? 'زياراتي الآن لا تُحسب (اضغط للإلغاء)' : 'لا تحسب زياراتي على هذا الجهاز'; }
  b.onclick = function () { try { if (on()) localStorage.removeItem('vg_notrack'); else localStorage.setItem('vg_notrack', '1'); } catch (e) {} draw(); };
  draw();
})();
</script>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
