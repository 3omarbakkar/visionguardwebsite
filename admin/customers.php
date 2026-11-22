<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('customers');

$q  = trim((string)($_GET['q'] ?? ''));
$id = (int)($_GET['id'] ?? 0);
$page_title = 'العملاء';
$active = 'customers';

$ready = false;
try {
    $t = db()->query("SHOW TABLES LIKE 'customers'")->fetch();
    $c = db()->query("SHOW COLUMNS FROM leads LIKE 'customer_id'")->fetch();
    $ready = ($t && $c);
} catch (Throwable $e) { $ready = false; }

function wa_link($phone) {
    $p = preg_replace('/\D+/', '', (string)$phone);
    if ($p !== '' && $p[0] === '0') $p = substr($p, 1);
    return 'https://wa.me/20' . $p;
}
$statusLabels = ['new' => 'جديد', 'contacted' => 'تم التواصل', 'delivered' => 'تم التوصيل', 'closed' => 'مغلق'];

require __DIR__ . '/includes/layout_top.php';

if (!$ready) {
    echo '<div class="card"><p class="muted">لسه ما فيش عملاء مسجلين. هيظهروا هنا أول ما أول عميل يعمل حساب من الموقع.</p></div>';
    require __DIR__ . '/includes/layout_bottom.php';
    exit;
}

if ($id > 0) {
    $s = db()->prepare('SELECT * FROM customers WHERE id = ?');
    $s->execute([$id]);
    $cust = $s->fetch(); if ($cust && !can('customer_contact')) { $cust['phone'] = ''; }
    if (!$cust) {
        echo '<div class="card"><p>العميل غير موجود.</p><a class="btn sm secondary" href="customers.php">رجوع</a></div>';
        require __DIR__ . '/includes/layout_bottom.php';
        exit;
    }
    $s = db()->prepare("SELECT * FROM leads WHERE customer_id = ? ORDER BY created_at DESC LIMIT 200");
    $s->execute([$id]);
    $orders = $s->fetchAll(); if (!can('customer_contact')) { foreach ($orders as &$vgo) { $vgo['message'] = ''; } unset($vgo); }
    $sum = 0; foreach ($orders as $o) { if ($o['type'] === 'order') $sum += (float)$o['total']; }
    ?>
<div class="topbar">
  <h2><?= htmlspecialchars($cust['name']) ?></h2>
  <div><a class="btn sm secondary" href="customers.php">← كل العملاء</a></div>
</div>
<div class="card">
  <p><b>الموبايل:</b> <span dir="ltr"><?= htmlspecialchars($cust['phone']) ?></span>
     &nbsp; <?php if ($cust['phone'] !== ''): ?><a class="btn sm" target="_blank" rel="noopener" href="<?= htmlspecialchars(wa_link($cust['phone'])) ?>">واتساب</a><?php endif; ?></p>
  <p class="muted">سجّل في: <?= htmlspecialchars($cust['created_at']) ?> &nbsp;|&nbsp; آخر دخول: <?= htmlspecialchars($cust['last_login'] ?: '—') ?></p>
  <p><b>عدد الطلبات:</b> <?= count($orders) ?> &nbsp;|&nbsp; <b>إجمالي المشتريات:</b> <?= number_format($sum) ?> ج.م</p>
</div>
<?php if (can('customers_manage')): ?>
<div class="card">
<h3 style="margin-top:0">إدارة العميل</h3>
<form method="post" action="customer_update.php" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
<?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$cust['id'] ?>"><input type="hidden" name="action" value="update">
<label style="display:flex;flex-direction:column;gap:4px;font-size:13px">الاسم<input name="name" value="<?= htmlspecialchars($cust['name']) ?>" maxlength="120" required></label>
<label style="display:flex;flex-direction:column;gap:4px;font-size:13px">الموبايل<input name="phone" dir="ltr" value="<?= htmlspecialchars($cust['phone']) ?>" maxlength="20"></label>
<button class="btn sm" type="submit">حفظ التعديل</button>
</form>
<form method="post" action="customer_update.php" style="margin-top:14px" onsubmit="return confirm('حذف حساب العميل ده؟ طلباته هتفضل محفوظة بدون حساب.');">
<?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$cust['id'] ?>"><input type="hidden" name="action" value="delete">
<button class="btn sm danger" type="submit">حذف الحساب</button>
</form>
</div>
<?php endif; ?>
<div class="card">
<table>
<tr><th>التاريخ</th><th>التفاصيل</th><th>الإجمالي</th><th>الحالة</th></tr>
  <?php foreach ($orders as $o): ?>
  <tr>
    <td class="muted"><?= htmlspecialchars($o['created_at']) ?></td>
    <td class="muted" style="max-width:360px">
      <?php if ($o['message']): ?><?= nl2br(htmlspecialchars(mb_substr($o['message'], 0, 200))) ?><?php endif; ?>
      <?php if ($o['cart_json']): $items = json_decode($o['cart_json'], true); if (is_array($items)): ?>
        <div style="margin-top:4px">
        <?php foreach ($items as $it): ?>
          <div>• <?= htmlspecialchars((string)($it['name'] ?? '')) ?> × <?= (int)($it['qty'] ?? 1) ?></div>
        <?php endforeach; ?>
        </div>
      <?php endif; endif; ?>
    </td>
    <td><?= $o['total'] !== null ? number_format((float)$o['total']) . ' ج.م' : '—' ?></td>
    <td><?= htmlspecialchars($statusLabels[$o['status']] ?? (string)$o['status']) ?></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$orders): ?><tr><td colspan="4" class="muted">لا توجد طلبات بعد.</td></tr><?php endif; ?>
</table>
</div>
<?php
    require __DIR__ . '/includes/layout_bottom.php';
    exit;
}

$sql = "SELECT c.id, c.name, c.phone, c.created_at, c.last_login,
               COUNT(l.id) AS orders, COALESCE(SUM(l.total), 0) AS total
        FROM customers c
        LEFT JOIN leads l ON l.customer_id = c.id AND l.type = 'order'";
$params = [];
if ($q !== '') { $sql .= ' WHERE c.name LIKE ? OR c.phone LIKE ?'; $params = ['%' . $q . '%', '%' . $q . '%']; }
$sql .= ' GROUP BY c.id ORDER BY c.created_at DESC LIMIT 500';
$s = db()->prepare($sql);
$s->execute($params);
$rows = $s->fetchAll(); if (!can('customer_contact')) { foreach ($rows as &$vgr) { $vgr['phone'] = ''; } unset($vgr); }
?>
<div class="topbar">
  <h2>العملاء (<?= count($rows) ?>)</h2>
  <form method="get" style="display:flex;gap:8px">
    <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="بحث بالاسم أو الموبايل">
    <button class="btn sm" type="submit">بحث</button>
  </form>
</div>
<div class="card">
<table>
  <tr><th>الاسم</th><th>الموبايل</th><th>الطلبات</th><th>إجمالي المشتريات</th><th>تاريخ التسجيل</th><th>آخر دخول</th><th></th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><a href="customers.php?id=<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['name']) ?></a></td>
    <td class="muted" dir="ltr"><?= htmlspecialchars($r['phone']) ?></td>
    <td><?= (int)$r['orders'] ?></td>
    <td><?= number_format((float)$r['total']) ?> ج.م</td>
    <td class="muted"><?= htmlspecialchars($r['created_at']) ?></td>
    <td class="muted"><?= htmlspecialchars($r['last_login'] ?: '—') ?></td>
    <td><?php if ($r['phone'] !== ''): ?><a class="btn sm secondary" target="_blank" rel="noopener" href="<?= htmlspecialchars(wa_link($r['phone'])) ?>">واتساب</a><?php endif; ?></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7" class="muted">لا يوجد عملاء<?= $q !== '' ? ' مطابقين للبحث' : ' بعد' ?>.</td></tr><?php endif; ?>
</table>
</div>
<?php
$guests = [];
try {
    $gs = "SELECT MAX(name) AS name, phone, COUNT(*) AS orders, COALESCE(SUM(total),0) AS total, MIN(created_at) AS first_at, MAX(created_at) AS last_at
           FROM leads WHERE type = 'order' AND (customer_id IS NULL OR customer_id = 0) AND phone <> ''";
    $gp = [];
    if ($q !== '') { $gs .= ' AND (name LIKE ? OR phone LIKE ?)'; $gp = ['%' . $q . '%', '%' . $q . '%']; }
    $gs .= ' GROUP BY phone ORDER BY last_at DESC LIMIT 300';
    $gq = db()->prepare($gs); $gq->execute($gp);
    $guests = $gq->fetchAll();
} catch (Throwable $e) { $guests = []; }
if ($guests): ?>
<div class="card">
<h3 style="margin-top:0">عملاء طلبوا بدون حساب (<?= count($guests) ?>)</h3>
<p class="muted" style="margin-top:0">طلبات قديمة أو طلبات واتساب اتعملت من غير تسجيل حساب. دول عملاء مش موظفين ولا فنيين.</p>
<table>
<tr><th>الاسم</th><th>الموبايل</th><th>الطلبات</th><th>إجمالي المشتريات</th><th>أول طلب</th><th>آخر طلب</th><th></th></tr>
<?php foreach ($guests as $g): $gph = can('customer_contact') ? $g['phone'] : ''; ?>
<tr>
  <td><?= htmlspecialchars($g['name']) ?> <span class="muted">(بدون حساب)</span></td>
  <td class="muted" dir="ltr"><?= htmlspecialchars($gph) ?></td>
  <td><?= (int)$g['orders'] ?></td>
  <td><?= number_format((float)$g['total']) ?> ج.م</td>
  <td class="muted"><?= htmlspecialchars($g['first_at']) ?></td>
  <td class="muted"><?= htmlspecialchars($g['last_at']) ?></td>
  <td><?php if ($gph !== ''): ?><a class="btn sm secondary" target="_blank" rel="noopener" href="<?= htmlspecialchars(wa_link($gph)) ?>">واتساب</a><?php endif; ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
