<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('hero');
$db = db();
$db->exec("CREATE TABLE IF NOT EXISTS hero_slides (
  id INT AUTO_INCREMENT PRIMARY KEY,
  image VARCHAR(255) NOT NULL,
  link VARCHAR(500) NOT NULL DEFAULT '',
  alt VARCHAR(255) NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8mb4");
try { $db->exec("ALTER TABLE hero_slides ADD COLUMN image_dark VARCHAR(255) NOT NULL DEFAULT ''"); } catch (Throwable $e) {}
try { $db->exec("ALTER TABLE hero_slides ADD COLUMN ends_at DATE NULL"); } catch (Throwable $e) {}

function hero_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function hero_link($l) {
    $l = trim((string)$l);
    if ($l === '') return '';
    if (strpos($l, '//') === 0 || strpos($l, '/\\') === 0) return '';
    if (preg_match('~^https?://~i', $l)) return mb_substr($l, 0, 500);
    if ($l[0] === '#' || ($l[0] === '/' && strpos($l, '//') !== 0)) return mb_substr($l, 0, 500);
    return '';
}
$dir = dirname(__DIR__) . '/images/hero';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $f = $_FILES['image'] ?? null;
        $info = ($f && $f['error'] === UPLOAD_ERR_OK) ? @getimagesize($f['tmp_name']) : false;
        $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2] ?? 0] ?? null;
        if (!$ext) {
            flash_set('اختر صورة JPG أو PNG أو WebP صحيحة.');
        } elseif ($f['size'] > 4 * 1024 * 1024) {
            flash_set('حجم الصورة أكبر من 4MB.');
        } elseif ($info[0] < 800) {
            flash_set('الصورة صغيرة، العرض لازم 800 بكسل على الأقل (المناسب 1600).');
        } else {
            if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
            $name = bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
                $max = (int)$db->query('SELECT COALESCE(MAX(sort_order),0) FROM hero_slides')->fetchColumn();
                $dn = '';
                $fd = $_FILES['image_dark'] ?? null;
                $di = ($fd && $fd['error'] === UPLOAD_ERR_OK) ? @getimagesize($fd['tmp_name']) : false;
                $dx = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$di[2] ?? 0] ?? null;
                if ($dx && $fd['size'] <= 4 * 1024 * 1024 && $di[0] >= 800) { $dn = bin2hex(random_bytes(8)) . '.' . $dx; if (!move_uploaded_file($fd['tmp_name'], $dir . '/' . $dn)) { $dn = ''; } }
                $st = $db->prepare('INSERT INTO hero_slides (image, image_dark, link, alt, sort_order, active) VALUES (?, ?, ?, ?, ?, 1)');
                $st->execute(['/images/hero/' . $name, $dn !== '' ? '/images/hero/' . $dn : '', hero_link($_POST['link'] ?? ''), mb_substr(trim($_POST['alt'] ?? ''), 0, 255), $max + 1]);
                $newId = (int)$db->lastInsertId();
                $ea = trim((string)($_POST['ends_at'] ?? ''));
                if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $ea)) { try { $db->prepare('UPDATE hero_slides SET ends_at = ? WHERE id = ?')->execute([$ea, $newId]); } catch (Throwable $e) {} }
                if (!empty($_POST['first'])) { $mn = (int)$db->query('SELECT COALESCE(MIN(sort_order),1) FROM hero_slides WHERE id <> ' . $newId)->fetchColumn(); $db->prepare('UPDATE hero_slides SET sort_order = ? WHERE id = ?')->execute([$mn - 1, $newId]); }
                flash_set('تمت إضافة البانر.');
            } else {
                flash_set('تعذر حفظ الصورة على السيرفر.');
            }
        }
    } elseif ($action === 'save') {
        $st = $db->prepare('UPDATE hero_slides SET link = ?, alt = ?, sort_order = ?, active = ? WHERE id = ?');
        foreach (($_POST['sort'] ?? []) as $id => $order) {
            $id = (int)$id;
            $st->execute([
                hero_link($_POST['link'][$id] ?? ''),
                mb_substr(trim($_POST['alt'][$id] ?? ''), 0, 255),
                (int)$order,
                isset($_POST['active'][$id]) ? 1 : 0,
                $id,
            ]);
        }
        flash_set('تم حفظ التعديلات.');
    } elseif ($action === 'delete_hidden') {
        $hs = $db->query('SELECT id, image, image_dark FROM hero_slides WHERE active = 0')->fetchAll();
        foreach ($hs as $h) {
            foreach ([$h['image'], $h['image_dark']] as $im) {
                if ($im && strpos($im, '/images/hero/') === 0) {
                    $p = $dir . '/' . basename($im);
                    if (is_file($p)) { @unlink($p); }
                }
            }
            $db->prepare('DELETE FROM hero_slides WHERE id = ?')->execute([(int)$h['id']]);
        }
        flash_set('تم حذف ' . count($hs) . ' بانر مخفي.');
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $st = $db->prepare('SELECT image FROM hero_slides WHERE id = ?');
        $st->execute([$id]);
        $img = $st->fetchColumn();
        if ($img && strpos($img, '/images/hero/') === 0) {
            $p = $dir . '/' . basename($img);
            if (is_file($p)) { @unlink($p); }
        }
        $db->prepare('DELETE FROM hero_slides WHERE id = ?')->execute([$id]);
        flash_set('تم حذف البانر.');
    }
    header('Location: hero.php');
    exit;
}

$rows = $db->query('SELECT * FROM hero_slides ORDER BY sort_order, id')->fetchAll();
$page_title = 'الهيرو (البانرات)';
$active = 'hero';
require __DIR__ . '/includes/layout_top.php';
?>
<div class="pbar"><h2>الهيرو (البانرات)</h2></div>

<div class="card" style="max-width:980px">
  <h3 style="margin-top:0">إضافة بانر جديد</h3>
  <p style="color:#666">المقاس المناسب 1600×700 بكسل. الصيغ المسموحة JPG وPNG وWebP حتى 4MB. البانر بيظهر في أول الصفحة الرئيسية.</p>
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <p><label>الصورة<br><input type="file" name="image" accept="image/jpeg,image/png,image/webp" required></label></p>
    <p><label>صورة الوضع الداكن (PNG شفاف، اختياري)<br><input type="file" name="image_dark" accept="image/png,image/webp,image/jpeg"></label></p>
    <p><label>الرابط عند الضغط (اختياري)<br><input type="text" name="link" dir="ltr" placeholder="/ar أو https://wa.me/201105006854" style="width:100%;max-width:560px"></label></p>
    <p><label>وصف الصورة (للقارئ الصوتي وجوجل)<br><input type="text" name="alt" placeholder="مثال: كاميرات واي فاي من Vision Guard" style="width:100%;max-width:560px"></label></p>
    <p><label>ينتهي في (اختياري، بعده البانر يختفي لوحده)<br><input type="date" name="ends_at"></label></p>
    <p><label><input type="checkbox" name="first" value="1"> يظهر أول بانر في الصفحة</label></p>
    <button type="submit" class="btn">رفع وإضافة</button>
  </form>
</div>

<?php if ($rows): ?>
<?php if (array_filter($rows, function ($r) { return !$r['active']; })): ?>
<form method="post" style="margin:12px 0" onsubmit="return confirm('حذف كل البانرات المخفية نهائياً؟ لا يمكن التراجع.')"><?= csrf_field() ?><input type="hidden" name="action" value="delete_hidden"><button type="submit" class="btn" style="background:#d93025;color:#fff">حذف كل البانرات المخفية</button></form>
<?php endif; ?>
<form method="post" id="saveForm">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="save">
  <?php foreach ($rows as $r): $id = (int)$r['id']; ?>
  <div class="card" style="max-width:980px;display:flex;gap:16px;flex-wrap:wrap;align-items:flex-start">
    <img src="<?= hero_h($r['image']) ?>" alt="" style="width:300px;max-width:100%;border-radius:10px;border:1px solid #ddd">
    <div style="flex:1;min-width:240px">
      <p><label>الترتيب (الأصغر يظهر أولاً)<br><input type="number" name="sort[<?= $id ?>]" value="<?= (int)$r['sort_order'] ?>" style="width:90px"></label></p>
      <p><label>الرابط<br><input type="text" name="link[<?= $id ?>]" dir="ltr" value="<?= hero_h($r['link']) ?>" style="width:100%"></label></p>
      <p><label>الوصف<br><input type="text" name="alt[<?= $id ?>]" value="<?= hero_h($r['alt']) ?>" style="width:100%"></label></p>
      <p><label><input type="checkbox" name="active[<?= $id ?>]" value="1" <?= $r['active'] ? 'checked' : '' ?>> ظاهر في الموقع</label></p>
      <button type="submit" form="del-<?= $id ?>" class="btn" onclick="return confirm('حذف هذا البانر نهائياً؟')">حذف</button>
    </div>
  </div>
  <?php endforeach; ?>
  <button type="submit" class="btn">حفظ التعديلات</button>
</form>
<?php foreach ($rows as $r): ?>
<form method="post" id="del-<?= (int)$r['id'] ?>"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"></form>
<?php endforeach; ?>
<?php else: ?>
<p style="color:#666">لا توجد بانرات بعد. ارفع أول بانر من الأعلى.</p>
<?php endif; ?>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
