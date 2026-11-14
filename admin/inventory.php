<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('products');
require_once __DIR__ . '/../api/_stock.php';
vg_schema_v2();
$LOW = 3;
function vgi_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
try {
    db()->exec("CREATE TABLE IF NOT EXISTS vg_stock_log (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        product_id INT UNSIGNED NOT NULL,
        old_qty INT NULL, new_qty INT NULL,
        by_user VARCHAR(80) NOT NULL DEFAULT '',
        created_at DATETIME NOT NULL,
        KEY idx_log_product (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $raw = trim((string)($_POST['stock_qty'] ?? ''));
    $who = '';
    $cur = current_admin();
    if (is_array($cur)) { $who = (string)($cur['username'] ?? ''); }
    if ($id) {
        $s = db()->prepare('SELECT stock_qty FROM products WHERE id = ?');
        $s->execute([$id]);
        $row = $s->fetch();
        if ($row) {
            $old = $row['stock_qty'] === null ? null : (int)$row['stock_qty'];
            if ($raw === '') { $new = null; }
            elseif (ctype_digit($raw)) { $new = (int)$raw; }
            else { $new = false; }
            if ($new === false) {
                flash_set('اكتب رقم صحيح للكمية.', 'error');
            } else {
                db()->prepare('UPDATE products SET stock_qty = ? WHERE id = ?')->execute([$new, $id]);
                try {
                    db()->prepare('INSERT INTO vg_stock_log (product_id, old_qty, new_qty, by_user, created_at) VALUES (?,?,?,?,NOW())')
                        ->execute([$id, $old, $new, $who]);
                } catch (Throwable $e) {}
                flash_set($new === null ? 'تم إيقاف تتبع الكمية لهذا المنتج.' : 'تم تحديث الكمية.');
            }
        }
    }
    $back = 'inventory.php?f=' . urlencode((string)($_POST['f'] ?? '')) . '&q=' . urlencode((string)($_POST['q'] ?? ''));
    header('Location: ' . $back);
    exit;
}

$f = (string)($_GET['f'] ?? '');
$q = trim((string)($_GET['q'] ?? ''));
$all = db()->query('SELECT id, name_ar, name_en, brand, category, price, in_stock, stock_qty FROM products ORDER BY sort_order ASC, id ASC')->fetchAll();

function vgi_state($r, $LOW) {
    if ($r['stock_qty'] === null) {
        return ((int)$r['in_stock'] === 1) ? ['untracked', 'غير متتبع'] : ['out', 'غير متاح'];
    }
    $n = (int)$r['stock_qty'];
    if ($n <= 0) return ['out', 'نفد'];
    if ($n <= $LOW) return ['low', 'منخفض'];
    return ['ok', 'متاح'];
}
$cnt = ['ok' => 0, 'low' => 0, 'out' => 0, 'untracked' => 0];
$value = 0;
foreach ($all as $r) {
    $st = vgi_state($r, $LOW);
    $cnt[$st[0]]++;
    if ($r['stock_qty'] !== null) { $value += max(0, (int)$r['stock_qty']) * (float)$r['price']; }
}
$rows = [];
foreach ($all as $r) {
    $st = vgi_state($r, $LOW);
    if ($f !== '' && $st[0] !== $f) continue;
    if ($q !== '') {
        $hay = mb_strtolower($r['name_ar'] . ' ' . $r['name_en'] . ' ' . $r['brand']);
        if (mb_strpos($hay, mb_strtolower($q)) === false) continue;
    }
    $rows[] = [$r, $st];
}
$log = [];
try {
    $log = db()->query('SELECT l.*, p.name_ar FROM vg_stock_log l LEFT JOIN products p ON p.id = l.product_id ORDER BY l.id DESC LIMIT 15')->fetchAll();
} catch (Throwable $e) {}

$page_title = 'المخزون';
$active = 'inventory';
require __DIR__ . '/includes/layout_top.php';
?>
<style>
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px}
.kpi{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:14px 16px;text-decoration:none;display:block}
.kpi b{display:block;font-size:24px;line-height:1.2}
.kpi span{color:var(--muted);font-size:13px}
.kpi.on{border-color:var(--accent);box-shadow:0 0 0 2px rgba(29,78,216,.12)}
.badge{display:inline-block;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600}
.badge.ok{background:#dcfce7;color:#166534}.badge.low{background:#fef3c7;color:#92400e}
.badge.out{background:#fee2e2;color:#991b1b}.badge.untracked{background:#e5e7eb;color:#374151}
.qtyform{display:flex;gap:6px;align-items:center}
.qtyform input[type=number]{width:90px;padding:6px 8px}
.tw{overflow-x:auto}
</style>
<div class="topbar">
  <h2>المخزون (<?= count($rows) ?> من <?= count($all) ?>)</h2>
  <form method="get" style="display:flex;gap:8px">
    <input type="hidden" name="f" value="<?= vgi_h($f) ?>">
    <input type="text" name="q" value="<?= vgi_h($q) ?>" placeholder="ابحث باسم المنتج أو الماركة" style="min-width:220px">
    <button class="btn" type="submit">بحث</button>
  </form>
</div>
<div class="kpis">
  <a class="kpi <?= $f===''?'on':'' ?>" href="inventory.php"><b><?= count($all) ?></b><span>كل المنتجات</span></a>
  <a class="kpi <?= $f==='ok'?'on':'' ?>" href="inventory.php?f=ok"><b><?= $cnt['ok'] ?></b><span>متاح</span></a>
  <a class="kpi <?= $f==='low'?'on':'' ?>" href="inventory.php?f=low"><b style="color:#b45309"><?= $cnt['low'] ?></b><span>منخفض (<?= $LOW ?> أو أقل)</span></a>
  <a class="kpi <?= $f==='out'?'on':'' ?>" href="inventory.php?f=out"><b style="color:var(--danger)"><?= $cnt['out'] ?></b><span>نفد / غير متاح</span></a>
  <a class="kpi <?= $f==='untracked'?'on':'' ?>" href="inventory.php?f=untracked"><b><?= $cnt['untracked'] ?></b><span>غير متتبع بالكمية</span></a>
  <div class="kpi"><b><?= number_format($value, 0) ?></b><span>قيمة المخزون المتتبع (ج.م)</span></div>
</div>
<div class="card tw">
  <p class="muted" style="margin-top:0;color:var(--muted);font-size:13px">الكمية بتتخصم تلقائياً عند الطلب وبترجع لو الطلب اتلغى. سيب الخانة فاضية لو مش عايز تتبع كمية المنتج ده.</p>
  <table>
    <thead><tr><th>المنتج</th><th>القسم</th><th>الماركة</th><th>السعر</th><th>الحالة</th><th>الكمية الحالية</th></tr></thead>
    <tbody>
    <?php foreach ($rows as [$r, $st]): ?>
      <tr>
        <td><?= vgi_h($r['name_ar'] ?: $r['name_en']) ?></td>
        <td><?= vgi_h($r['category']) ?></td>
        <td><?= vgi_h($r['brand']) ?></td>
        <td><?= number_format((float)$r['price'], 0) ?> ج.م</td>
        <td><span class="badge <?= $st[0] ?>"><?= $st[1] ?></span></td>
        <td>
          <form method="post" class="qtyform">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <input type="hidden" name="f" value="<?= vgi_h($f) ?>">
            <input type="hidden" name="q" value="<?= vgi_h($q) ?>">
            <input type="number" min="0" name="stock_qty" value="<?= $r['stock_qty'] === null ? '' : (int)$r['stock_qty'] ?>" placeholder="—">
            <button class="btn sm" type="submit">حفظ</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="6" style="color:var(--muted)">مفيش منتجات مطابقة.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php if ($log): ?>
<div class="card tw">
  <h3 style="margin-top:0">آخر تعديلات الكمية</h3>
  <table>
    <thead><tr><th>المنتج</th><th>قبل</th><th>بعد</th><th>بواسطة</th><th>التاريخ</th></tr></thead>
    <tbody>
    <?php foreach ($log as $l): ?>
      <tr>
        <td><?= vgi_h($l['name_ar']) ?></td>
        <td><?= $l['old_qty'] === null ? '—' : (int)$l['old_qty'] ?></td>
        <td><?= $l['new_qty'] === null ? '—' : (int)$l['new_qty'] ?></td>
        <td><?= vgi_h($l['by_user']) ?></td>
        <td><?= vgi_h($l['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<style id="vgbulk-css">
#vgbulk{position:static;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:16px 0 0;padding:12px 16px;background:#fff;border:1px solid #e1e8f1;border-radius:12px;box-shadow:0 4px 16px -8px rgba(15,27,45,.2);font-family:inherit}
#vgbulk.on{display:flex}
#vgbulk .vgb-n{font-size:14px;color:#51607a}
#vgbulk button{font:inherit;font-size:14px;font-weight:600;border-radius:10px;padding:10px 20px;cursor:pointer;border:1px solid #e1e8f1;background:#fff;color:#0f1b2d}
#vgbulk button.vgb-save{background:#0667a5;border-color:#0667a5;color:#fff}
#vgbulk button:disabled{opacity:.6;cursor:default}

</style>
<div id="vgbulk" dir="rtl"><span class="vgb-n" id="vgbN"></span><button type="button" class="vgb-save" id="vgbSave">حفظ</button><button type="button" id="vgbReset">تراجع</button></div>
<script id="vgbulk-js">(function(){
var forms=[].slice.call(document.querySelectorAll("form.qtyform"));if(!forms.length)return;
var bar=document.getElementById("vgbulk"),n=document.getElementById("vgbN"),sv=document.getElementById("vgbSave"),rs=document.getElementById("vgbReset");
var items=forms.map(function(f){var i=f.querySelector("input[name=stock_qty]");return{f:f,i:i,o:i.value}});
function dirty(){return items.filter(function(x){return x.i.value!==x.o&&x.i.value!==""})}
function upd(){var d=dirty();sv.disabled=!d.length;rs.disabled=!d.length;n.textContent=d.length?(d.length+" تعديل لم يتم حفظه"):"اكتب الأرقام في الخانات ثم اضغط حفظ"}
items.forEach(function(x){x.i.addEventListener("input",upd)});upd();
rs.onclick=function(){items.forEach(function(x){x.i.value=x.o});upd()};
sv.onclick=async function(){var d=dirty();if(!d.length)return;sv.disabled=rs.disabled=true;var ok=0,bad=0;
for(var k=0;k<d.length;k++){n.textContent="جاري الحفظ "+(k+1)+" من "+d.length;
try{var r=await fetch(location.href.split("#")[0],{method:"POST",body:new FormData(d[k].f),credentials:"same-origin"});if(r.ok)ok++;else bad++}catch(e){bad++}}
n.textContent=bad?("تم حفظ "+ok+" وفشل "+bad):("تم حفظ "+ok+" بنجاح");setTimeout(function(){location.reload()},900)};
})();</script>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
