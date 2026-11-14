<?php
require_once __DIR__ . '/admin/includes/db.php';
header('Content-Type: text/csv; charset=utf-8');
header('Cache-Control: public, max-age=900');
$base = 'https://visionguardeg.com';
$cats = [
  'cat_hd' => 'كاميرات مراقبة HD (سلكية)', 'cat_ip' => 'كاميرات IP', 'cat_wf' => 'كاميرات واي فاي',
  'cat_dvr' => 'أجهزة تسجيل DVR/NVR', 'cat_hdd' => 'هارد ديسك مراقبة', 'cat_mem' => 'كروت ذاكرة',
  'cat_psu' => 'مصادر طاقة', 'cat_cab' => 'كابلات', 'cat_rack' => 'راكات ومستلزمات', 'cat_acc' => 'ملحقات وشبكات',
];
$catDesc = [
  'cat_hd' => 'كاميرا مراقبة سلكية بتتوصل بمسجل DVR عن طريق كابل كواكس، وبتحتاج مصدر طاقة.',
  'cat_ip' => 'كاميرا IP بتتوصل بالشبكة عن طريق كابل شبكة.',
  'cat_wf' => 'كاميرا واي فاي بتتوصل بشبكة الواي فاي عندك وبتتشاف من الموبايل عن طريق تطبيق الماركة، وبتحتاج مصدر طاقة.',
  'cat_dvr' => 'مسجل DVR بيسجل صورة الكاميرات السلكية، ولازم يتركّب معاه هارد ديسك للتسجيل.',
  'cat_hdd' => 'هارد ديسك لتخزين تسجيلات الكاميرات.',
  'cat_mem' => 'كارت ميموري لتخزين التسجيلات في الكاميرات اللي بتدعم الكروت.',
  'cat_psu' => 'باور سبلاي بيغذي الكاميرات بالطاقة، والأمبير بيحدد قدرته على تشغيل أكتر من كاميرا.',
  'cat_cab' => 'سلك كواكس لتوصيل الكاميرات السلكية بالمسجل.',
];
$pd = [
  'p42' => 'هارد ديسك من سلسلة WD Purple المخصصة لتسجيل المراقبة.',
  'p43' => 'هارد ديسك من سلسلة WD Purple المخصصة لتسجيل المراقبة.',
  'p44' => 'هارد ديسك من سلسلة WD Purple المخصصة لتسجيل المراقبة.',
  'p59' => 'سلك شبكة Cat6 لتوصيل كاميرات IP وأجهزة الشبكة.',
  'p60' => 'راك لتنظيم المسجلات وأجهزة الشبكة.',
  'p61' => 'ادابتور لتغذية الكاميرا بالطاقة.',
  'p62' => 'بوكس لتجميع وحماية توصيلات الكاميرا.',
  'p63' => 'جاك BNC لتوصيل كابل الفيديو بالكاميرا أو المسجل.',
  'p64' => 'جاك DC لتوصيل الطاقة بالكاميرا.',
  'p65' => 'نقطة وصول واي فاي من TP-Link لتقوية وتوسيع تغطية الشبكة.',
  'p66' => 'راوتر واي فاي من TP-Link بسرعة 300Mbps لشبكة المنزل أو المكتب.',
  'p67' => 'راوتر واي فاي من Netis بسرعة 300Mbps لشبكة المنزل أو المكتب.',
  'p74' => 'جهاز حضور وانصراف من ZKTeco.',
];
$rows = db()->query('SELECT id,slug,category,brand,name_ar,name_en,price,in_stock FROM products ORDER BY id')->fetchAll();
$out = fopen('php://output', 'w');
fputcsv($out, ['id','title','description','availability','condition','price','link','image_link','brand','mpn','product_type'], ',', '"', '');
foreach ($rows as $r) {
  $id = 'p' . $r['id'];
  $title = trim((string)$r['name_ar']);
  $extra = $pd[$id] ?? ($catDesc[$r['category']] ?? 'منتج لأنظمة المراقبة والأمان.');
  fputcsv($out, [
    $id, $title, $title . ' - ' . $extra,
    ((int)$r['in_stock'] === 1) ? 'in stock' : 'out of stock',
    'new',
    number_format((float)$r['price'], 2, '.', '') . ' EGP',
    $base . '/p/' . rawurlencode($r['slug']),
    $base . '/images/p' . (int)$r['id'] . '.jpg' . (($__m = @filemtime(__DIR__ . '/images/p' . (int)$r['id'] . '.jpg')) ? '?v=' . $__m : ''),
    $r['brand'] !== '' ? $r['brand'] : 'Vision Guard',
    $r['slug'],
    $cats[$r['category']] ?? 'منتجات مراقبة',
  ], ',', '"', '');
}
