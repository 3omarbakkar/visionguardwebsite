<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('products');
$opt = require __DIR__ . '/includes/options.php';
require_once __DIR__ . '/includes/images.php';
require_once __DIR__ . '/../api/_stock.php';
vg_schema_v2();

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
$product = ['category'=>'cat_hd','placement'=>'','brand'=>'','name_ar'=>'','name_en'=>'','slug'=>'','price'=>'','icon'=>'bu','in_stock'=>1,'stock_qty'=>'','discount_percent'=>'','sort_order'=>0,'details_ar'=>'','details_en'=>'','specs'=>''];
if ($id) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) $product = $row;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $product['category']         = $_POST['category'] ?? '';
    $product['placement']        = $_POST['placement'] ?? '';
    $product['brand']            = trim($_POST['brand'] ?? '');
    $product['name_ar']          = trim($_POST['name_ar'] ?? '');
    $product['name_en']          = trim($_POST['name_en'] ?? '');
    $product['price']            = $_POST['price'] ?? '';
    $product['icon']             = $_POST['icon'] ?? 'bu';
    $product['in_stock']         = isset($_POST['in_stock']) ? 1 : 0;
    $stockInput = trim((string)($_POST['stock_qty'] ?? ''));
    $product['stock_qty']        = $stockInput === '' ? null : (int)$stockInput;
    $discountInput = trim((string)($_POST['discount_percent'] ?? ''));
    $product['discount_percent'] = $discountInput !== '' ? (int)$discountInput : null;
    $product['sort_order']       = (int)($_POST['sort_order'] ?? 0);
    $product['details_ar'] = trim($_POST['details_ar'] ?? '');
    $product['details_en'] = trim($_POST['details_en'] ?? '');
    $product['specs'] = trim($_POST['specs'] ?? '');
    if (!can('products_price')) { $vgOld = null; if ($id) { $q0 = db()->prepare('SELECT price, discount_percent FROM products WHERE id = ?'); $q0->execute([$id]); $vgOld = $q0->fetch(); } $product['price'] = $vgOld ? $vgOld['price'] : 0; $product['discount_percent'] = $vgOld ? $vgOld['discount_percent'] : null; }

    if ($product['name_ar'] === '' || $product['name_en'] === '') $errors[] = 'لازم اسم المنتج بالعربي والإنجليزي.';
    if (!is_numeric($product['price']) || $product['price'] < 0) $errors[] = 'السعر لازم يكون رقم صحيح.';
    if (!array_key_exists($product['category'], $opt['categories'])) $errors[] = 'اختار قسم صحيح.';
    if (!array_key_exists($product['placement'], $opt['placements'])) $errors[] = 'اختار مكان استخدام صحيح.';
    if (!array_key_exists($product['icon'], $opt['icons'])) $errors[] = 'اختار أيقونة صحيحة.';
    if (mb_strlen($product['brand']) > 60) $errors[] = 'اسم الماركة أطول من الحد المسموح.';
    if (mb_strlen($product['name_ar']) > 190 || mb_strlen($product['name_en']) > 190) $errors[] = 'اسم المنتج أطول من الحد المسموح (190 حرفًا).';
    if (is_numeric($product['price']) && (float)$product['price'] > 99999999.99) $errors[] = 'السعر أكبر من الحد المسموح.';
    if ($product['discount_percent'] !== null && ($product['discount_percent'] < 0 || $product['discount_percent'] > 100)) $errors[] = 'نسبة الخصم لازم تكون من 0 إلى 100.';
    if ($stockInput !== '' && !preg_match('/^[0-9]{1,6}$/', $stockInput)) $errors[] = 'كمية المخزون لازم تكون رقمًا صحيحًا من 0 إلى 999999، أو اتركها فارغة.';

    // Slug (short link) - auto-generated from brand+model when left empty, editable by admin
    $slugInput = trim($_POST['slug'] ?? '');
    if ($slugInput !== '') {
        $slug = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $slugInput));
    } else {
        $base = trim($product['brand'] . ' ' . $product['name_en']);
        $slug = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $base));
    }
    $slug = trim($slug, '-');
    $slug = substr($slug, 0, 60);
    if ($slug === '') $slug = 'P' . time();
    $product['slug'] = $slug;
    if (!$errors) {
        $chk = db()->prepare('SELECT id FROM products WHERE slug = ? AND id <> ?');
        $chk->execute([$slug, (int)$id]);
        if ($chk->fetch()) $errors[] = 'الرابط المختصر مستخدم بالفعل، جرّب رابطًا آخر.';
    }

    // Images upload (optional) - up to 10 slots, validated now, saved after we know the id.
    $tmpImages = [];
    for ($__slot = 1; $__slot <= PRODUCT_IMAGE_MAX_SLOTS; $__slot++) {
        $fkey = 'image_' . $__slot;
        if (!empty($_FILES[$fkey]['name']) && $_FILES[$fkey]['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES[$fkey]['tmp_name'];
            $info = @getimagesize($tmp);
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!$info || !in_array($info['mime'], $allowed, true)) {
                $errors[] = 'صورة الخانة ' . $__slot . ': لازم تكون JPG أو PNG أو WEBP.';
            } elseif ($_FILES[$fkey]['size'] > 4 * 1024 * 1024) {
                $errors[] = 'صورة الخانة ' . $__slot . ': الحجم أكبر من 4MB.';
            } else {
                $tmpImages[$__slot] = $tmp;
            }
        }
    }
    $deleteImages = [];
    if (!empty($_POST['delete_image']) && is_array($_POST['delete_image'])) {
        foreach ($_POST['delete_image'] as $__ds => $__dv) {
            $deleteImages[] = (int)$__ds;
        }
    }

    if (!$errors) {
        if ($id) {
            $stmt = db()->prepare('UPDATE products SET category=?,placement=?,brand=?,name_ar=?,name_en=?,slug=?,price=?,icon=?,in_stock=?,stock_qty=?,discount_percent=?,sort_order=?,details_ar=?,details_en=?,specs=? WHERE id=?');
            $stmt->execute([$product['category'],$product['placement'],$product['brand'],$product['name_ar'],$product['name_en'],$product['slug'],$product['price'],$product['icon'],$product['in_stock'],$product['stock_qty'],$product['discount_percent'],$product['sort_order'],$product['details_ar'],$product['details_en'],$product['specs'],$id]);
            flash_set('تم التعديل بنجاح.');
        } else {
            $stmt = db()->prepare('INSERT INTO products (category,placement,brand,name_ar,name_en,slug,price,icon,in_stock,stock_qty,discount_percent,sort_order,details_ar,details_en,specs) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$product['category'],$product['placement'],$product['brand'],$product['name_ar'],$product['name_en'],$product['slug'],$product['price'],$product['icon'],$product['in_stock'],$product['stock_qty'],$product['discount_percent'],$product['sort_order'],$product['details_ar'],$product['details_en'],$product['specs']]);
            $id = (int)db()->lastInsertId();
            flash_set('تمت إضافة المنتج بنجاح.');
        }

        foreach ($deleteImages as $__delSlot) {
            delete_product_image($id, $__delSlot);
        }
        if ($deleteImages) product_images_repack($id);
        foreach ($tmpImages as $__slot => $__tmp) {
            save_product_image($__tmp, $id, $__slot);
        }

        header('Location: products.php');
        exit;
    }
}

$page_title = $id ? 'تعديل منتج' : 'إضافة منتج';
$active = 'products';
require __DIR__ . '/includes/layout_top.php';
?>
<div class="topbar"><h2><?= $id ? 'تعديل منتج' : 'إضافة منتج جديد' ?></h2><a class="btn secondary" href="products.php">رجوع للقائمة</a></div>

<?php foreach ($errors as $e): ?><div class="flash err"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>

<div class="card" style="max-width:620px">
<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$id ?>">

  <div class="grid2">
    <div>
      <label>اسم المنتج (عربي)</label>
      <input type="text" name="name_ar" required value="<?= htmlspecialchars($product['name_ar']) ?>">
    </div>
    <div>
      <label>اسم المنتج (إنجليزي)</label>
      <input type="text" name="name_en" required value="<?= htmlspecialchars($product['name_en']) ?>">
    </div>
  </div>

  <div class="grid2">
    <div>
      <label>القسم</label>
      <select name="category">
        <?php foreach ($opt['categories'] as $k => $label): ?>
          <option value="<?= $k ?>" <?= $product['category']===$k?'selected':'' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>الاستخدام</label>
      <select name="placement">
        <?php foreach ($opt['placements'] as $k => $label): ?>
          <option value="<?= $k ?>" <?= $product['placement']===$k?'selected':'' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="grid2">
    <div>
      <label>الماركة</label>
      <input type="text" name="brand" value="<?= htmlspecialchars($product['brand']) ?>">
    </div>
    <div>
      <label>السعر (ج.م)</label>
      <input type="number" step="0.01" min="0" name="price" required value="<?= htmlspecialchars((string)$product['price']) ?>">
    </div>
  </div>

  <div class="grid2">
    <div>
      <label>الرابط المختصر (اختياري — يُنشأ تلقائيًا من الماركة والموديل لو تُرك فارغًا، مثال: DH-HAC)</label>
      <input type="text" name="slug" value="<?= htmlspecialchars($product['slug'] ?? '') ?>" placeholder="مثال: DH-HAC">
    </div>
  </div>

  <div class="grid2">
    <div>
      <label>نسبة الخصم % (اختياري)</label>
      <input type="number" min="1" max="90" name="discount_percent" value="<?= htmlspecialchars((string)($product['discount_percent'] ?? '')) ?>">
    </div>
    <div>
      <label>ترتيب الظهور (رقم أصغر = أول)</label>
      <input type="number" name="sort_order" value="<?= (int)$product['sort_order'] ?>">
    </div>
  </div>

  <label>تفاصيل المنتج (عربي)</label>
  <textarea name="details_ar" rows="4"><?= htmlspecialchars($product['details_ar'] ?? '') ?></textarea>

  <label>تفاصيل المنتج (إنجليزي)</label>
  <textarea name="details_en" rows="4"><?= htmlspecialchars($product['details_en'] ?? '') ?></textarea>

<label>جدول المواصفات / الداتاشيت (اختياري)</label>
<textarea name="specs" rows="6" placeholder="اكتب كل مواصفة في سطر، بالصيغة: الاسم: القيمة
مثال:
الدقة: 5 ميجابكسل
الرؤية الليلية: 30 متر
العدسة: 2.8mm"><?= htmlspecialchars($product['specs'] ?? '') ?></textarea>

  <label>أيقونة افتراضية (تُستخدم لو مفيش صورة مرفوعة)</label>
  <select name="icon">
    <?php foreach ($opt['icons'] as $k => $label): ?>
      <option value="<?= $k ?>" <?= $product['icon']===$k?'selected':'' ?>><?= htmlspecialchars($label) ?></option>
    <?php endforeach; ?>
  </select>

      <label>صور المنتج (حتى 10 صور، JPG/PNG/WEBP حتى 4MB لكل صورة — الصورة الأولى هي الرئيسية)</label>
      <?php $__slots = $id ? product_image_slots($id) : []; for ($__s = 1; $__s <= PRODUCT_IMAGE_MAX_SLOTS; $__s++): $__has = in_array($__s, $__slots, true); ?>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;padding:8px;border:1px solid #eee;border-radius:8px">
          <?php if ($__has): ?>
            <img class="thumb" style="width:60px;height:60px;object-fit:cover;border-radius:6px" src="<?= htmlspecialchars(IMAGES_URL_FROM_ADMIN . '/p' . $id . ($__s>1?('_'.$__s):'') . '.jpg?v=' . time()) ?>">
            <label style="display:flex;align-items:center;gap:6px;font-size:13px;width:auto"><input type="checkbox" name="delete_image[<?= $__s ?>]" value="1" style="width:auto"> حذف</label>
          <?php else: ?>
            <span class="muted" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;border:1px dashed #ccc;border-radius:6px;font-size:11px">خانة <?= $__s ?></span>
          <?php endif; ?>
          <input type="file" name="image_<?= $__s ?>" accept="image/png,image/jpeg,image/webp" style="flex:1">
        </div>
      <?php endfor; ?>

  <label style="display:flex;align-items:center;gap:8px;margin-top:14px">
    <input type="checkbox" name="in_stock" style="width:auto" <?= $product['in_stock']?'checked':'' ?>> متوفر في المخزون
  </label>

  <label style="margin-top:14px">كمية المخزون (اختياري — اتركها فارغة لعدم تتبع الكمية)</label>
  <input type="number" min="0" max="999999" step="1" name="stock_qty" value="<?= htmlspecialchars((string)($product['stock_qty'] ?? '')) ?>" placeholder="مثال: 5">
  <p class="muted" style="font-size:12.5px;margin:6px 0 0">لو حددت كمية، هتتخصم تلقائيًا مع كل طلب وترجع لو الطلب اتلغى أو اتحذف. صفر = غير متوفر. المنتجات من غير كمية بتعتمد على خيار «متوفر في المخزون» فقط.</p>

  <div style="margin-top:18px">
    <button class="btn" type="submit">حفظ</button>
    <a class="btn secondary" href="products.php">إلغاء</a>
  </div>
</form>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
