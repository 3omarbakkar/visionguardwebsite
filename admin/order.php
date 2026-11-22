<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('leads');
function vgo_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$id = (int)($_GET['id'] ?? 0);
$s = db()->prepare('SELECT * FROM leads WHERE id = ?');
$s->execute([$id]);
$o = $s->fetch();
$page_title = 'تفاصيل الطلب';
$active = 'leads';
require __DIR__ . '/includes/layout_top.php';
if (!$o) {
    echo '<div class="card"><p>الطلب غير موجود.</p><a class="btn sm secondary" href="leads.php">رجوع</a></div>';
    require __DIR__ . '/includes/layout_bottom.php';
    exit;
}
$statusLabels = ['new' => 'جديد', 'contacted' => 'تم التواصل', 'delivered' => 'تم التوصيل', 'closed' => 'مغلق', 'cancelled' => 'ملغي'];
$payLabels = ['cod' => 'الدفع عند الاستلام', 'card' => 'بطاقة', 'instapay' => 'إنستاباي', 'ewallet' => 'محفظة إلكترونية'];
$payStates = ['cod_due' => 'الدفع عند الاستلام', 'pending' => 'تحويل بانتظار المراجعة', 'paid' => 'تم تأكيد الدفع', 'unverified' => 'غير محدد'];
$items = json_decode((string)($o['cart_json'] ?? ''), true);
if (!is_array($items)) $items = [];
$techs = [];
try { $techs = db()->query("SELECT id, COALESCE(NULLIF(full_name,''),username) AS n FROM admin_users WHERE role='tech' AND active=1")->fetchAll(); } catch (Throwable $e) {}
$phone = (string)($o['phone'] ?? '');
$wa = preg_replace('/\D+/', '', $phone);
if ($wa !== '' && $wa[0] === '0') $wa = substr($wa, 1);
$st = (string)($o['status'] ?? 'new');
?>
<style>
.two{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px}
.kv{display:grid;grid-template-columns:130px 1fr;gap:8px 12px;margin:0}
.kv dt{color:var(--muted)} .kv dd{margin:0;font-weight:600}
.badge{display:inline-block;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600;background:#e5e7eb;color:#374151}
.badge.new{background:#dbeafe;color:#1e40af}.badge.contacted{background:#fef3c7;color:#92400e}
.badge.delivered{background:#dcfce7;color:#166534}.badge.closed{background:#e0e7ff;color:#3730a3}.badge.cancelled{background:#fee2e2;color:#991b1b}
.tw{overflow-x:auto}
.num{text-align:left !important;white-space:nowrap;font-variant-numeric:tabular-nums}
.vgit{display:inline-flex;align-items:center;gap:10px}
.vgit img{width:44px;height:44px;flex:0 0 44px;object-fit:cover;border-radius:8px;border:1px solid #e1e8f1;background:#f5f7fa}
tfoot td{font-weight:700;border-top:2px solid #e1e8f1;background:#f8fafc}
.kvbox{margin-top:18px;padding-top:6px;border-top:1px solid #e1e8f1}
@media print{
  nav.side,.topbar .actions,.noprint,.flash{display:none !important}
  body{background:#fff} main{padding:0;max-width:none}
  .card{border:0;padding:0}
}
</style>
<div class="topbar">
  <h2>طلب رقم <?= (int)$o['id'] ?> <span class="badge <?= vgo_h($st) ?>"><?= vgo_h($statusLabels[$st] ?? $st) ?></span></h2>
  <div class="actions">
    <a class="btn sm secondary" href="leads.php">رجوع للطلبات</a>
    <button class="btn sm" type="button" onclick="window.print()">طباعة الفاتورة</button>
  </div>
</div>
<div class="two">
  <div class="card">
    <h3 style="margin-top:0">بيانات العميل</h3>
    <dl class="kv">
      <dt>الاسم</dt><dd><?= vgo_h($o['name']) ?></dd>
      <dt>الموبايل</dt><dd dir="ltr" style="text-align:right"><?= vgo_h($phone) ?>
        <?php if ($wa !== ''): ?><a class="btn sm secondary noprint" target="_blank" rel="noopener" href="https://wa.me/20<?= vgo_h($wa) ?>">واتساب</a><?php endif; ?></dd>
      <?php if (!empty($o['email'])): ?><dt>الإيميل</dt><dd><?= vgo_h($o['email']) ?></dd><?php endif; ?>
      <dt>تاريخ الطلب</dt><dd><?= vgo_h($o['created_at']) ?></dd>
      <dt>نوع الطلب</dt><dd><?= vgo_h(['order' => 'طلب شراء', 'contact' => 'رسالة تواصل', 'quote' => 'طلب عرض سعر'][$o['type']] ?? $o['type']) ?></dd>
      <?php $vgLc = null; if (!empty($o['customer_id'])) { $vgq = db()->prepare('SELECT id, name, phone FROM customers WHERE id = ?'); $vgq->execute([(int)$o['customer_id']]); $vgLc = $vgq->fetch(); } ?>
      <dt>حساب العميل</dt><dd><?php if ($vgLc): ?><a href="customers.php?id=<?= (int)$vgLc['id'] ?>"><?= vgo_h($vgLc['name']) ?></a><?php else: ?><span style="color:var(--muted);font-weight:400">غير مرتبط بحساب</span><?php endif; ?></dd>
    </dl>
  </div>
  <div class="card">
    <h3 style="margin-top:0">الدفع</h3>
    <dl class="kv">
      <dt>طريقة الدفع</dt><dd><?= vgo_h($payLabels[$o['payment_method'] ?? ''] ?? ($o['payment_method'] ?: '—')) ?></dd>
      <dt>حالة الدفع</dt><dd><?= vgo_h($payStates[$o['payment_status'] ?? 'unverified'] ?? $o['payment_status']) ?></dd>
      <dt>الإجمالي</dt><dd><?= $o['total'] !== null ? number_format((float)$o['total'], 2) . ' ج.م' : '—' ?></dd>
    </dl>
  </div>
</div>
<div class="card tw">
  <h3 style="margin-top:0">المنتجات</h3>
  <?php if ($items): ?>
  <table>
    <thead><tr><th>المنتج</th><th class="num">سعر الوحدة</th><th class="num">الكمية</th><th class="num">الإجمالي</th></tr></thead>
    <tbody>
    <?php $vgSum = 0.0; $vgPm = [];
    try { foreach (db()->query('SELECT id, name_ar, price, discount_percent FROM products')->fetchAll() as $vgP) { $vgPm[trim((string)$vgP['name_ar'])] = $vgP; } } catch (Throwable $e) {}
    foreach ($items as $it):
      $vgQ = max(1, (int)($it['qty'] ?? 1));
      $vgU = $it['unit_price'] ?? ($it['price'] ?? null);
      $vgPid = (int)($it['id'] ?? 0);
      $vgPr = $vgPm[trim((string)($it['name'] ?? ''))] ?? null;
      if ($vgPid <= 0 && $vgPr) $vgPid = (int)$vgPr['id'];
      if ($vgU === null && count($items) === 1 && $o['total'] !== null) $vgU = (float)$o['total'] / $vgQ;
      $vgLn = $it['line_total'] ?? ($vgU !== null ? (float)$vgU * $vgQ : null);
      if ($vgLn !== null) $vgSum += (float)$vgLn; ?>
      <tr>
        <td><span class="vgit"><?php if ($vgPid > 0 && file_exists(__DIR__ . '/../images/p' . $vgPid . '.jpg')): ?><img src="/images/p<?= $vgPid ?>.jpg" alt="" loading="lazy"><?php endif; ?><span><?= vgo_h($it['name'] ?? '') ?></span></span></td>
        <td class="num"><?= $vgU !== null ? number_format((float)$vgU, 0) . ' ج.م' : '—' ?></td>
        <td class="num"><?= $vgQ ?></td>
        <td class="num"><strong><?= $vgLn !== null ? number_format((float)$vgLn, 0) . ' ج.م' : '—' ?></strong></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><td colspan="3">إجمالي الطلب</td><td class="num"><?= number_format((float)($o['total'] ?? $vgSum), 0) ?> ج.م</td></tr></tfoot>
  </table>
  <?php else: ?><p style="color:var(--muted)">مفيش تفاصيل منتجات متسجلة للطلب ده.</p><?php endif; ?>
  <?php if (!empty($o['message'])): ?>
  <div class="kvbox"><h4 style="margin:14px 0 8px">بيانات التوصيل والملاحظات</h4>
  <dl class="kv">
  <?php foreach (preg_split('/\R/u', (string)$o['message']) as $vgln): $vgln = trim($vgln); if ($vgln === '') continue; $vgp = mb_strpos($vgln, ':'); ?>
    <?php if ($vgp !== false && $vgp < 30): ?>
      <dt><?= vgo_h(trim(mb_substr($vgln, 0, $vgp))) ?></dt><dd><?= vgo_h(trim(mb_substr($vgln, $vgp + 1))) ?></dd>
    <?php else: ?>
      <dt>ملاحظة</dt><dd><?= vgo_h($vgln) ?></dd>
    <?php endif; ?>
  <?php endforeach; ?>
  </dl></div>
  <?php endif; ?>
</div>
<div class="card noprint">
  <h3 style="margin-top:0">إدارة الطلب</h3>
  <form method="post" action="lead_update.php" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
    <label>الحالة
      <select name="status" onchange="this.form.submit()">
        <?php foreach ($statusLabels as $k => $lab): ?>
          <option value="<?= $k ?>" <?= $st === $k ? 'selected' : '' ?>><?= vgo_h($lab) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </form>
  <?php if (can('leads')): ?>
  <form method="post" action="lead_update.php" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:10px">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
    <label>فني التركيب
      <select name="assigned_to" onchange="this.form.submit()">
        <option value="0">— بدون فني —</option>
        <?php foreach ($techs as $t): ?>
          <option value="<?= (int)$t['id'] ?>" <?= (int)($o['assigned_to'] ?? 0) === (int)$t['id'] ? 'selected' : '' ?>><?= vgo_h($t['n']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <?php if (!$techs): ?><span style="color:var(--muted);font-size:13px">لسه مفيش فنيين. غيّر دور موظف لـ "فني / تركيب" من صفحة الموظفين.</span><?php endif; ?>
  </form>
  <?php endif; ?>
  <?php if (can('customers_manage')):
    $vgCs = db()->query('SELECT id, name, phone FROM customers ORDER BY name ASC')->fetchAll();
    $vgT = substr(preg_replace('/\D/', '', (string)$phone), -10);
  ?>
  <form method="post" action="order_link.php" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:10px">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
    <label>حساب العميل
      <select name="customer_id">
        <option value="0">بدون حساب</option>
        <?php foreach ($vgCs as $vgC): $vgSame = ($vgT !== '' && substr(preg_replace('/\D/', '', (string)$vgC['phone']), -10) === $vgT); ?>
          <option value="<?= (int)$vgC['id'] ?>" <?= (int)($o['customer_id'] ?? 0) === (int)$vgC['id'] ? 'selected' : '' ?>><?= vgo_h($vgC['name']) ?> - <?= vgo_h($vgC['phone']) ?><?= $vgSame ? ' (نفس رقم الطلب)' : '' ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button class="btn sm" type="submit">حفظ الربط</button>
    <span style="color:var(--muted);font-size:13px">الطلب المرتبط بيظهر للعميل في حسابه على الموقع.</span>
  </form>
  <?php endif; ?>
</div>
<script>if(/[?&]print=1\b/.test(location.search)){window.addEventListener('load',function(){setTimeout(function(){window.print()},500)})}</script>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
