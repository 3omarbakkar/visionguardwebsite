<?php
require_once __DIR__ . '/admin/includes/db.php';
require_once __DIR__ . '/admin/includes/images.php';
require_once __DIR__ . '/api/_stock.php';
vg_schema_v2();
if (!function_exists('h')) { function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); } }

$CATS = require __DIR__ . '/seo_categories.php';
$slug = isset($_GET['c']) ? strtolower(trim($_GET['c'])) : '';
if (!isset($CATS[$slug])) {
    http_response_code(404);
    echo '<!doctype html><html lang="ar" dir="rtl"><title>الصفحة غير موجودة</title><body style="font-family:sans-serif;text-align:center;padding:60px"><h1>الصفحة غير موجودة</h1><p><a href="/">رجوع للمتجر</a></p></body></html>';
    exit;
}
$cat = $CATS[$slug];
if (isset($cat['db_categories'])) {
    $ph = implode(',', array_fill(0, count($cat['db_categories']), '?'));
    $where = "category IN ($ph)"; $args = $cat['db_categories'];
} elseif (isset($cat['db_brand'])) {
    $where = "brand = ?"; $args = [$cat['db_brand']];
} else {
    $where = "category = ?"; $args = [$cat['db_category']];
}
$stmt = db()->prepare("SELECT id, name_ar, name_en, price, discount_percent, in_stock, stock_qty, slug, brand FROM products WHERE $where AND slug <> '' ORDER BY in_stock DESC, price ASC");
$stmt->execute($args);
$rows = $stmt->fetchAll();

$pageUrl = 'https://visionguardeg.com/c/' . $slug;
$items = [];
foreach ($rows as $r) {
    $price = (float)$r['price'];
    $disc = (int)($r['discount_percent'] ?? 0);
    $eff = $disc > 0 ? round($price * (1 - $disc / 100), 2) : $price;
    $slots = product_image_slots((int)$r['id']);
    $img = $slots ? vg_img('/images/p' . $r['id'] . ($slots[0] > 1 ? ('_' . $slots[0]) : '') . '.jpg') : '';
    $items[] = ['r' => $r, 'eff' => $eff, 'price' => $price, 'disc' => $disc, 'img' => $img, 'url' => 'https://visionguardeg.com/p/' . rawurlencode($r['slug'])];
}

$itemList = [];
foreach ($items as $i => $it) {
    $itemList[] = ['@type' => 'ListItem', 'position' => $i + 1, 'url' => $it['url'], 'name' => $it['r']['name_ar']];
}
$faqLd = [];
foreach ($cat['faq'] as $f) {
    $faqLd[] = ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]];
}
$ld = [
  ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => $cat['h1'], 'description' => $cat['desc'], 'url' => $pageUrl, 'inLanguage' => 'ar', 'isPartOf' => ['@type' => 'WebSite', 'name' => 'Vision Guard', 'url' => 'https://visionguardeg.com/'], 'mainEntity' => ['@type' => 'ItemList', 'numberOfItems' => count($itemList), 'itemListElement' => $itemList]],
  ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => 'الرئيسية', 'item' => 'https://visionguardeg.com/'],
      ['@type' => 'ListItem', 'position' => 2, 'name' => $cat['name'], 'item' => $pageUrl]]],
  ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqLd],
];
$wa = 'https://wa.me/201105006854?text=' . rawurlencode('مرحبًا Vision Guard، عايز أسأل عن ' . $cat['name']);
?><!doctype html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8">

<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= h($cat['title']) ?></title>
<meta name="description" content="<?= h($cat['desc']) ?>">
<link rel="canonical" href="<?= h($pageUrl) ?>">
<meta name="theme-color" content="#202020">
<meta property="og:type" content="website"><meta property="og:url" content="<?= h($pageUrl) ?>"><meta property="og:site_name" content="Vision Guard"><meta property="og:title" content="<?= h($cat['title']) ?>"><meta property="og:description" content="<?= h($cat['desc']) ?>"><meta property="og:locale" content="ar_EG">
<meta property="og:image" content="https://visionguardeg.com/og-image.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;800&family=Plus+Jakarta+Sans:wght@400..800&family=Inter:wght@400..600&family=Sometype+Mono:wght@400..500&display=swap">
<?php foreach ($ld as $block): ?>
<script type="application/ld+json"><?= json_encode($block, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
<?php endforeach; ?>
<style>
:root{--navy:#1f3154;--cyan:#05b3e9;--blue:#0667a5;--bg:#f4f7fb;--card:#fff;--tx:#202224;--mut:#5b6573;--bd:#e3e8ef}
@media(prefers-color-scheme:dark){:root{--bg:#2e4160;--card:#3d5279;--tx:#ffffff;--mut:#cdd6e4;--bd:#5d7399}}
*{box-sizing:border-box}body{margin:0;font:16px/1.7 Cairo,system-ui,sans-serif;background:var(--bg);color:var(--tx)}
a{color:var(--blue);text-decoration:none}
header{background:var(--navy);color:#fff}header .w{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px}
header a{color:#fff}.logo{font-weight:800;font-size:20px}.cta{background:var(--cyan);color:#06243a!important;padding:8px 14px;border-radius:999px;font-weight:700}
.w{max-width:1100px;margin:0 auto;padding:0 16px}
nav.bc{padding:14px 0;font-size:14px;color:var(--mut)}
h1{font-size:28px;line-height:1.35;margin:6px 0 10px;color:var(--navy)}@media(prefers-color-scheme:dark){h1{color:#fff}}
.intro p{color:var(--mut);margin:0 0 10px;max-width:820px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px;margin:22px 0}
.card{background:var(--card);border:1px solid var(--bd);border-radius:14px;overflow:hidden;display:flex;flex-direction:column;color:var(--tx)}
.card img{width:100%;aspect-ratio:1/1;object-fit:contain;background:#fff}
.card .b{padding:12px;display:flex;flex-direction:column;gap:6px;flex:1}
.card .n{font-weight:600;font-size:15px;line-height:1.45}
.pr{margin-top:auto;font-weight:800;color:var(--blue);font-size:18px}.pr s{color:var(--mut);font-weight:400;font-size:13px;margin-inline-start:6px}
.oos{color:#b42318;font-size:13px}
section.faq{margin:30px 0}section.faq h2{font-size:22px;color:var(--navy)}@media(prefers-color-scheme:dark){section.faq h2{color:#fff}}
details{background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:12px 14px;margin:8px 0}summary{cursor:pointer;font-weight:600}details p{margin:8px 0 0;color:var(--mut)}
footer{padding:26px 0 40px;color:var(--mut);font-size:14px;text-align:center}
</style>
<link rel="manifest" href="/manifest.webmanifest">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Vision Guard">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link rel="stylesheet" href="/pwa.css">
<link rel="stylesheet" href="/vg-design.css?v=<?php echo (int)@filemtime(__DIR__ . '/vg-design.css'); ?>">
</head>
<body>
<header><div class="w"><a class="logo" href="/">Vision Guard</a><a class="cta" href="<?= h($wa) ?>" rel="noopener">اطلب عبر واتساب</a></div></header>
<main class="w">
<nav class="bc" aria-label="breadcrumb"><a href="/">الرئيسية</a> › <?= h($cat['name']) ?></nav>
<h1><?= h($cat['h1']) ?></h1>
<div class="intro"><?php foreach ($cat['intro'] as $p): ?><p><?= h($p) ?></p><?php endforeach; ?></div>
<div class="grid">
<?php foreach ($items as $it): $r = $it['r']; ?>
  <a class="card" href="/p/<?= h(rawurlencode($r['slug'])) ?>">
    <?php if ($it['img']): ?><img src="<?= h($it['img']) ?>" alt="<?= h($r['name_ar']) ?>" width="400" height="400" loading="lazy"><?php endif; ?>
    <div class="b">
      <span class="n"><?= h($r['name_ar']) ?></span>
      <span class="pr"><?= h(number_format($it['eff'])) ?> ج.م<?php if ($it['disc'] > 0): ?><s><?= h(number_format($it['price'])) ?></s><?php endif; ?></span>
      <?php if (!vg_stock_available($r)): ?><span class="oos">غير متوفر حاليًا</span><?php elseif (vg_stock_left($r) !== null && vg_stock_left($r) <= 3): ?><span class="oos" style="color:#b45309"><?= h(vg_stock_label($r)) ?></span><?php endif; ?>
    </div>
  </a>
<?php endforeach; ?>
</div>
<section class="faq"><h2>أسئلة شائعة</h2>
<?php foreach ($cat['faq'] as $f): ?><details><summary><?= h($f[0]) ?></summary><p><?= h($f[1]) ?></p></details><?php endforeach; ?>
</section>
</main>
<footer><div class="w">Vision Guard — كاميرات مراقبة وأنظمة أمان · توصيل القاهرة والجيزة · <a href="/">كل المنتجات</a></div></footer>
<script src="/pwa.js" defer></script>
</body>
</html>
