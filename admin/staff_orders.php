<?php
require_once __DIR__ . '/includes/perms.php';
require_login();
if (!can('staff_orders')) {
    http_response_code(403);
    die('Access denied.');
}

$statusLabels = ['new' => 'جديد', 'contacted' => 'تم التواصل', 'delivered' => 'تم التوصيل', 'closed' => 'مغلق', 'cancelled' => 'ملغي'];
$staffStatuses = ['new', 'contacted', 'delivered', 'closed'];
$labels = ['order' => 'طلب', 'coverage_plan' => 'مخطط تغطية', 'contact' => 'رسالة تواصل'];
$paymentLabels = ['cod_due' => 'الدفع عند الاستلام', 'pending' => 'تحويل بانتظار المراجعة', 'paid' => 'تم تأكيد الدفع', 'unverified' => 'غير محدد'];

// تحديث حالة الطلب (الموظف يقدر يغيّر الحالة فقط)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $st = $_POST['status'] ?? '';
    if ($id > 0 && isset($statusLabels[$st]) && in_array($st, $staffStatuses, true)) {
        $u = db()->prepare("UPDATE leads SET status = ? WHERE id = ? AND type IN ('order','coverage_plan','contact')");
        if (in_array(current_role(), ['tech', 'employee'], true)) {
            $u = db()->prepare("UPDATE leads SET status = ? WHERE id = ? AND assigned_to = ? AND type IN ('order','coverage_plan','contact')");
            $u->execute([$st, $id, (int)current_admin()['id']]);
        } else {
            $u->execute([$st, $id]);
        }
        flash_set('تم تحديث حالة الطلب.');
    }
    $back = $_POST['back'] ?? '';
    header('Location: staff_orders.php' . (isset($statusLabels[$back]) ? '?status=' . $back : ''));
    exit;
}

$status = $_GET['status'] ?? '';
$sql = "SELECT * FROM leads WHERE type IN ('order','coverage_plan','contact')"; if (in_array(current_role(), ['tech', 'employee'], true)) { $sql .= ' AND assigned_to = ' . (int)current_admin()['id']; }
$params = [];
if (isset($statusLabels[$status])) { $sql .= ' AND status = ?'; $params[] = $status; }
$sql .= ' ORDER BY created_at DESC LIMIT 300';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll(); if (!can('customer_contact')) { foreach ($leads as &$vgl) { $vgl['phone'] = ''; $vgl['email'] = ''; $vgl['message'] = ''; } unset($vgl); }

$page_title = 'متابعة الطلبات';
$active = 'staff_orders';
require __DIR__ . '/includes/layout_top.php';
?>
<div class="topbar">
  <h2>متابعة الطلبات (<?= count($leads) ?>)</h2>
  <div>
    <a class="btn sm <?= $status === '' ? '' : 'secondary' ?>" href="staff_orders.php">الكل</a>
    <?php foreach ($statusLabels as $k => $lab): ?>
      <a class="btn sm <?= $status === $k ? '' : 'secondary' ?>" href="staff_orders.php?status=<?= $k ?>"><?= htmlspecialchars($lab) ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="card">
<table>
  <tr><th>النوع</th><th>الاسم</th><th>الهاتف/الإيميل</th><th>التفاصيل</th><th>الإجمالي</th><th>طريقة الدفع</th><th>حالة الدفع</th><th>التاريخ</th><th>الحالة</th></tr>
  <?php foreach ($leads as $l): ?>
  <tr>
    <td><?= htmlspecialchars($labels[$l['type']] ?? $l['type']) ?></td>
    <td><?= htmlspecialchars($l['name'] ?: '—') ?></td>
    <td class="muted"><?= htmlspecialchars($l['phone'] ?: ($l['email'] ?: '—')) ?></td>
    <td class="muted" style="max-width:260px">
      <?php if ($l['message']): ?><?= nl2br(htmlspecialchars(mb_substr($l['message'], 0, 160))) ?><?php endif; ?>
      <?php if ($l['cart_json']): $items = json_decode($l['cart_json'], true); if (is_array($items)): ?>
        <div style="margin-top:4px">
        <?php foreach (array_slice($items, 0, 5) as $it): ?>
          <div>• <?= htmlspecialchars($it['name'] ?? '') ?> × <?= (int)($it['qty'] ?? 1) ?></div>
        <?php endforeach; ?>
        </div>
      <?php endif; endif; ?>
    </td>
    <td><?= $l['total'] !== null ? number_format((float)$l['total'], 2) . ' ج.م' : '—' ?></td>
    <td><?= htmlspecialchars(['cod'=>'عند الاستلام','instapay'=>'إنستاباي','ewallet'=>'محفظة إلكترونية'][$l['payment_method'] ?? ''] ?? '—') ?></td>
    <td><?= htmlspecialchars($paymentLabels[$l['payment_status'] ?? 'unverified'] ?? ($l['payment_status'] ?? '—')) ?></td>
    <td class="muted"><?= htmlspecialchars((string)$l['created_at']) ?></td>
    <td>
      <?php if (($l['status'] ?? '') === 'cancelled'): ?>
        <span class="muted">ملغي</span>
      <?php else: ?>
      <form method="post" style="display:flex;gap:4px">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
        <input type="hidden" name="back" value="<?= htmlspecialchars($status) ?>">
        <select name="status" onchange="this.form.submit()" style="font-size:12.5px;padding:4px 6px">
          <?php foreach ($staffStatuses as $k): ?>
            <option value="<?= $k ?>" <?= $l['status'] === $k ? 'selected' : '' ?>><?= htmlspecialchars($statusLabels[$k]) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$leads): ?><tr><td colspan="9" class="muted">لا توجد طلبات.</td></tr><?php endif; ?>
</table>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
