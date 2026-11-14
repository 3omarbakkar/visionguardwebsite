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
      <dt>نوع الطلب</dt><dd><?= vgo_h($o['type']) ?></dd>
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
    <thead><tr><th>المنتج</th><th>الكمية</th><th>السعر</th></tr></thead>
    <tbody>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?= vgo_h($it['name'] ?? '') ?></td>
        <td><?= (int)($it['qty'] ?? 1) ?></td>
        <td><?= isset($it['price']) ? number_format((float)$it['price'], 0) . ' ج.م' : '—' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
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
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
