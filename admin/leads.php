<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('leads');
function vgl_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$status = (string)($_GET['status'] ?? '');
$q = trim((string)($_GET['q'] ?? ''));
$sql = 'SELECT * FROM leads WHERE 1=1';
$params = [];
if ($status === 'all') { /* every order */ }
elseif ($status !== '') { $sql .= ' AND status = ?'; $params[] = $status; }
else { $sql .= " AND status <> 'delivered'"; }
if ($q !== '') { $sql .= ' AND (name LIKE ? OR phone LIKE ? OR message LIKE ?)'; $like = '%' . $q . '%'; array_push($params, $like, $like, $like); }
$sql .= ' ORDER BY created_at DESC LIMIT 300';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();
$techs = [];
try { $techs = db()->query("SELECT id, COALESCE(NULLIF(full_name,''),username) AS n FROM admin_users WHERE role='tech' AND active=1")->fetchAll(); } catch (Throwable $e) {}

$labels = ['order' => 'طلب', 'coverage_plan' => 'مخطط تغطية', 'newsletter' => 'نشرة بريدية', 'contact' => 'رسالة تواصل'];
$statusLabels = ['new' => 'جديد', 'contacted' => 'تم التواصل', 'delivered' => 'تم التوصيل', 'closed' => 'مغلق', 'cancelled' => 'ملغي'];
$paymentLabels = ['cod_due' => 'الدفع عند الاستلام', 'pending' => 'تحويل بانتظار المراجعة', 'paid' => 'تم تأكيد الدفع', 'unverified' => 'غير محدد'];
$methodLabels = ['cod' => 'الدفع عند الاستلام', 'card' => 'بطاقة', 'instapay' => 'إنستاباي', 'ewallet' => 'محفظة إلكترونية'];
$canContact = can('customer_contact');

$counts = ['all' => 0];
try { foreach (db()->query('SELECT status, COUNT(*) AS c FROM leads GROUP BY status')->fetchAll() as $vgr) { $counts[$vgr['status']] = (int)$vgr['c']; $counts['all'] += (int)$vgr['c']; } } catch (Throwable $e) {}
$counts['current'] = $counts['all'] - ($counts['delivered'] ?? 0);
$tabs = [['', 'الطلبات الحالية', 'current'], ['new', 'جديد', 'new'], ['contacted', 'تم التواصل', 'contacted'], ['delivered', 'تم التوصيل', 'delivered'], ['closed', 'مغلق', 'closed'], ['cancelled', 'ملغي', 'cancelled'], ['all', 'الكل', 'all']];

$page_title = 'الطلبات والرسائل';
$active = 'leads';
require __DIR__ . '/includes/layout_top.php';
?>
<style>
.vg-tabs{display:flex;gap:8px;overflow-x:auto;padding:2px 0 14px;-webkit-overflow-scrolling:touch}
.vg-tabs a{display:inline-flex;align-items:center;gap:8px;white-space:nowrap;padding:8px 14px;border-radius:99px;background:#fff;border:1px solid var(--border);color:var(--text);text-decoration:none;font-size:14px;font-weight:600}
.vg-tabs a b{background:#eef2f7;color:var(--muted);border-radius:99px;padding:0 8px;font-size:12px;line-height:20px;min-width:22px;text-align:center}
.vg-tabs a.on{background:var(--accent);border-color:var(--accent);color:#fff}
.vg-tabs a.on b{background:rgba(255,255,255,.25);color:#fff}
.sbar{display:flex;gap:8px}
.sbar input{min-width:220px}
.oc{background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(15,30,60,.12);margin-bottom:14px;overflow:hidden;border-right:5px solid #94a3b8}
.oc.s-new{border-right-color:#2563eb}.oc.s-contacted{border-right-color:#d97706}.oc.s-delivered{border-right-color:#16a34a}.oc.s-closed{border-right-color:#6366f1}.oc.s-cancelled{border-right-color:#dc2626}
.oc-h{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:14px 18px;border-bottom:1px solid #eef1f5}
.oc-t{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.oc-id{background:#eef2f7;color:#475569;border-radius:6px;padding:2px 8px;font-size:12.5px;font-weight:700}
.oc-name{font-size:16px;font-weight:700;text-decoration:none;color:#0f1b2d}
.oc-sub{color:var(--muted);font-size:13px;display:flex;gap:14px;flex-wrap:wrap}
.tag{display:inline-block;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600;background:#e5e7eb;color:#374151}
.tag.new{background:#dbeafe;color:#1e40af}.tag.contacted{background:#fef3c7;color:#92400e}.tag.delivered{background:#dcfce7;color:#166534}.tag.closed{background:#e0e7ff;color:#3730a3}.tag.cancelled{background:#fee2e2;color:#991b1b}
.oc-b{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;padding:14px 18px}
.vg-box{background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:10px 12px;font-size:13.5px}
.vg-h{font-size:12px;font-weight:700;color:var(--muted);margin-bottom:6px}
.vg-item{display:flex;justify-content:space-between;gap:10px;padding:4px 0;border-bottom:1px dashed var(--border)}
.vg-item:last-of-type{border-bottom:0}
.vg-item b{white-space:nowrap;color:var(--accent)}
.vg-kv{display:grid;grid-template-columns:90px 1fr;gap:8px;padding:3px 0;line-height:1.55}
.vg-kv span{color:var(--muted)}
.vg-more{color:var(--muted);font-size:12px;padding-top:4px}
.oc-f{display:flex;align-items:center;flex-wrap:wrap;gap:16px;padding:12px 18px;background:#f8fafc;border-top:1px solid #eef1f5}
.oc-f .tot{font-size:18px;font-weight:800;color:#0f1b2d}
.oc-f .grow{flex:1}
.oc-f label{display:flex;flex-direction:column;gap:3px;font-size:11.5px;color:var(--muted)}
.oc-f select{font-size:13px;padding:6px 8px;min-width:130px}
.oc-f form{margin:0}
.pay{font-size:13px}
.pay b{display:block}
</style>
<div class="topbar">
  <h2>الطلبات والرسائل (<?= count($leads) ?>)</h2>
  <form method="get" class="sbar">
    <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= vgl_h($status) ?>"><?php endif; ?>
    <input type="text" name="q" value="<?= vgl_h($q) ?>" placeholder="ابحث بالاسم أو الموبايل أو العنوان">
    <button class="btn sm" type="submit">بحث</button>
  </form>
</div>
<div class="vg-tabs">
<?php foreach ($tabs as $tb): $on = ($status === $tb[0]); $href = $tb[0] === '' ? 'leads.php' : 'leads.php?status=' . $tb[0]; ?>
  <a class="<?= $on ? 'on' : '' ?>" href="<?= $href ?>"><?= vgl_h($tb[1]) ?> <b><?= (int)($counts[$tb[2]] ?? 0) ?></b></a>
<?php endforeach; ?>
</div>
<?php if (!$leads): ?>
<div class="card"><p style="color:var(--muted);margin:0"><?= $status === '' ? 'مفيش طلبات حالية. الطلبات المتسلّمة بتظهر في "عرض الكل".' : 'مفيش طلبات في القسم ده.' ?></p></div>
<?php endif; ?>
<style>.vgqs{display:inline-flex;margin:0}.vgqs select{padding:6px 10px;border-radius:10px;border:1px solid #e1e8f1;background:#fff;font:inherit;font-size:13px;color:#0f1b2d;cursor:pointer}</style>
<?php foreach ($leads as $l):
    $st = (string)($l['status'] ?? 'new');
    $isOrder = ($l['type'] === 'order');
    $items = json_decode((string)($l['cart_json'] ?? ''), true);
    if (!is_array($items)) $items = [];
    $lines = [];
    if (!empty($l['message'])) { foreach (preg_split('/\R/u', (string)$l['message']) as $ln) { $ln = trim($ln); if ($ln !== '') $lines[] = $ln; } }
    $phone = (string)($l['phone'] ?: ($l['email'] ?? ''));
    $wa = preg_replace('/\D+/', '', (string)$l['phone']);
    if ($wa !== '' && $wa[0] === '0') $wa = substr($wa, 1);
    $payState = (string)($l['payment_status'] ?? 'unverified');
?>
<div class="oc s-<?= vgl_h($st) ?>">
  <div class="oc-h">
    <div>
      <div class="oc-t">
        <span class="oc-id">#<?= (int)$l['id'] ?></span>
        <?php if ($isOrder): ?><a class="oc-name" href="order.php?id=<?= (int)$l['id'] ?>"><?= vgl_h($l['name'] ?: '—') ?></a>
        <?php else: ?><span class="oc-name"><?= vgl_h($l['name'] ?: '—') ?></span><?php endif; ?>
        <span class="tag"><?= vgl_h($labels[$l['type']] ?? $l['type']) ?></span>
        <span class="tag <?= vgl_h($st) ?>"><?= vgl_h($statusLabels[$st] ?? $st) ?></span>
        <?php if (!empty($l['customer_id'])): ?><span class="tag" title="طلب من حساب عميل مسجّل">عميل مسجّل</span><?php endif; ?>
        <?php if (!empty($l['traffic_src'])): $tsl=['facebook'=>'فيسبوك/إنستغرام','tiktok'=>'تيك توك','whatsapp'=>'واتساب','google'=>'جوجل','direct'=>'مباشر','other'=>'موقع آخر']; ?><span class="tag" title="مصدر الزيارة">المصدر: <?= vgl_h($tsl[$l['traffic_src']] ?? $l['traffic_src']) ?></span><?php endif; ?>
      </div>
      <div class="oc-sub">
        <?php if ($canContact && $phone !== ''): ?><span dir="ltr"><?= vgl_h($phone) ?></span><?php endif; ?>
        <span><?= vgl_h($l['created_at']) ?></span>
      </div>
    </div>
    <div style="display:flex;gap:8px">
      <?php if ($canContact && $wa !== ''): ?><a class="btn sm secondary" target="_blank" rel="noopener" href="https://wa.me/20<?= vgl_h($wa) ?>">واتساب</a><?php endif; ?>
      <?php if ($isOrder): ?><a class="btn sm" href="order.php?id=<?= (int)$l['id'] ?>">التفاصيل والفاتورة</a><?php endif; ?>
<?php if ($isOrder): ?><a class="btn sm secondary" target="_blank" rel="noopener" href="order.php?id=<?= (int)$l['id'] ?>&amp;print=1">طباعة</a><?php endif; ?>
<?php if ($isOrder && can('leads_status')): ?><form method="post" action="lead_update.php" class="vgqs"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$l['id'] ?>"><select name="status" data-cur="<?= vgl_h($st) ?>" aria-label="تغيير الحالة" onchange="if(this.value!=='cancelled'||confirm('إلغاء الطلب هيرجّع المخزون المخصوم. متأكد؟')){this.form.submit()}else{this.value=this.dataset.cur}"><?php foreach ($statusLabels as $sk => $sv): ?><option value="<?= vgl_h($sk) ?>"<?= $sk === $st ? ' selected' : '' ?>><?= vgl_h($sv) ?></option><?php endforeach; ?></select></form><?php endif; ?>
    </div>
  </div>
  <?php if ($items || $lines): ?>
  <div class="oc-b">
    <?php if ($items): ?>
    <div class="vg-box"><div class="vg-h">المنتجات</div>
      <?php foreach (array_slice($items, 0, 6) as $it): ?>
        <div class="vg-item"><span><?= vgl_h($it['name'] ?? '') ?></span><b>× <?= (int)($it['qty'] ?? 1) ?></b></div>
      <?php endforeach; ?>
      <?php if (count($items) > 6): ?><div class="vg-more">+ <?= count($items) - 6 ?> منتجات أخرى</div><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if ($lines): ?>
    <div class="vg-box"><div class="vg-h"><?= $isOrder ? 'بيانات الطلب' : 'الرسالة' ?></div>
      <?php foreach ($lines as $ln): $p = mb_strpos($ln, ':'); ?>
        <?php if ($p !== false && $p < 30): ?>
          <?php $k = trim(mb_substr($ln, 0, $p)); $val = trim(mb_substr($ln, $p + 1)); ?>
          <?php if ($canContact || mb_strpos($k, 'العنوان') === false): ?>
            <div class="vg-kv"><span><?= vgl_h($k) ?></span><b><?= vgl_h($val) ?></b></div>
          <?php endif; ?>
        <?php else: ?>
          <div class="vg-kv"><b><?= vgl_h($ln) ?></b></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <div class="oc-f">
    <?php if ($isOrder): ?>
    <div><div style="font-size:11.5px;color:var(--muted)">الإجمالي</div><div class="tot"><?= $l['total'] !== null ? number_format((float)$l['total'], 0) . ' ج.م' : '—' ?></div></div>
    <div class="pay"><span style="font-size:11.5px;color:var(--muted)">الدفع</span>
      <b><?= vgl_h($methodLabels[$l['payment_method'] ?? ''] ?? ($l['payment_method'] ?: '—')) ?></b>
      <span><?= vgl_h($paymentLabels[$payState] ?? $payState) ?></span>
      <?php if (($l['payment_method'] ?? '') !== 'cod' && $payState === 'pending' && can('leads')): ?>
        <form method="post" action="lead_update.php" style="margin-top:6px" onsubmit="return confirm('هل تحققت من وصول المبلغ فعلاً؟');">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$l['id'] ?>"><input type="hidden" name="payment_status" value="paid">
          <button class="btn sm" type="submit">تأكيد استلام التحويل</button>
        </form>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="grow"></div>
    <form method="post" action="lead_update.php">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
      <label>حالة الطلب
        <select name="status" onchange="this.form.submit()">
          <?php foreach ($statusLabels as $k => $lab): ?>
            <option value="<?= $k ?>" <?= $st === $k ? 'selected' : '' ?>><?= vgl_h($lab) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </form>
    <?php if ($isOrder): ?>
    <form method="post" action="lead_update.php">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
      <label>فني التركيب
        <select name="assigned_to" onchange="this.form.submit()">
          <option value="0">— بدون فني —</option>
          <?php foreach ($techs as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= (int)($l['assigned_to'] ?? 0) === (int)$t['id'] ? 'selected' : '' ?>><?= vgl_h($t['n']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </form>
    <?php endif; ?>
    <?php if (can('orders_delete')): ?>
    <form method="post" action="lead_update.php" onsubmit="return confirm('حذف هذا الطلب؟ لا يمكن التراجع.');">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$l['id'] ?>"><input type="hidden" name="delete" value="1">
      <button class="btn sm danger" type="submit">حذف</button>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
