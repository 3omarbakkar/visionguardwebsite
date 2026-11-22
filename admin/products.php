<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('products');
$opt = require __DIR__ . '/includes/options.php';

$q = trim($_GET['q'] ?? '');
$cat = $_GET['cat'] ?? '';

$sql = 'SELECT * FROM products WHERE 1=1';
$params = [];
if ($q !== '') {
    $sql .= ' AND (name_ar LIKE ? OR name_en LIKE ? OR brand LIKE ?)';
    $like = "%$q%";
    $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($cat !== '') {
    $sql .= ' AND category = ?';
    $params[] = $cat;
}
$sql .= ' ORDER BY sort_order ASC, id ASC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$page_title = 'المنتجات';
$active = 'products';
require __DIR__ . '/includes/layout_top.php';
?>
<div class="topbar">
  <h2>المنتجات (<?= count($products) ?>)</h2>
  <a class="btn" href="product_form.php">+ إضافة منتج</a>
</div>

<div class="card">
  <form method="get" style="display:flex;gap:10px;flex-wrap:wrap">
    <input type="text" name="q" placeholder="بحث بالاسم أو الماركة" value="<?= htmlspecialchars($q) ?>" style="max-width:260px">
    <select name="cat" style="max-width:220px">
      <option value="">كل الأقسام</option>
      <?php foreach ($opt['categories'] as $k => $label): ?>
        <option value="<?= $k ?>" <?= $cat === $k ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn secondary" type="submit">بحث</button>
    <?php if ($q || $cat): ?><a class="btn secondary" href="products.php">مسح الفلتر</a><?php endif; ?>
  </form>
</div>

<div class="card">
<table>
  <tr><th>صورة</th><th>الاسم</th><th>القسم</th><th>الماركة</th><th>السعر</th><th>الحالة</th><th>الكمية</th><th></th></tr>
  <?php foreach ($products as $p): ?>
  <tr>
    <td>
      <?php if (product_image_exists((int)$p['id'])): ?>
        <img class="thumb" src="<?= htmlspecialchars(IMAGES_URL_FROM_ADMIN . '/p' . (int)$p['id'] . '.jpg') ?>" alt="">
      <?php else: ?>
        <div class="thumb" style="display:flex;align-items:center;justify-content:center;font-size:11px;color:#9aa5b5"><?= htmlspecialchars($p['icon']) ?></div>
      <?php endif; ?>
    </td>
    <td>
      <div><?= htmlspecialchars($p['name_ar']) ?></div>
      <div class="muted"><?= htmlspecialchars($p['name_en']) ?></div>
    </td>
    <td class="muted"><?= htmlspecialchars($opt['categories'][$p['category']] ?? $p['category']) ?></td>
    <td class="muted"><?= htmlspecialchars($p['brand']) ?></td>
    <td><?= number_format((float)$p['price'], 2) ?> ج.م<?php if ($p['discount_percent']): ?><br><span class="muted">خصم <?= (int)$p['discount_percent'] ?>%</span><?php endif; ?></td>
    <?php
      if ($p['stock_qty'] === null) { $vgS = $p['in_stock'] ? ['in', 'متاح'] : ['out', 'غير متاح']; $vgQt = 'غير متتبع'; }
      else { $vgN = (int)$p['stock_qty']; $vgQt = (string)$vgN; $vgS = $vgN <= 0 ? ['out', 'نفد'] : ($vgN <= 3 ? ['low', 'منخفض'] : ['in', 'متاح']); }
    ?>
    <td><span class="badge <?= $vgS[0] ?>"<?= $vgS[0] === 'low' ? ' style="background:#fef3c7;color:#92400e"' : '' ?>><?= $vgS[1] ?></span></td>
    <td class="muted" style="white-space:nowrap"><a href="inventory.php?f=&q=<?= urlencode((string)$p['name_ar']) ?>" title="تعديل الكمية من المخزون"><?= htmlspecialchars($vgQt) ?></a></td>
    <td style="white-space:nowrap">
      <a class="btn secondary sm" href="product_form.php?id=<?= (int)$p['id'] ?>">تعديل</a>
      <form method="post" action="product_delete.php" style="display:inline" onsubmit="return confirm('تأكيد حذف المنتج؟');">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <button class="btn danger sm" type="submit">حذف</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$products): ?><tr><td colspan="8" class="muted">مفيش منتجات مطابقة.</td></tr><?php endif; ?>
</table>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
