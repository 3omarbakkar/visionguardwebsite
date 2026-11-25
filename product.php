<?php
require_once __DIR__ . '/admin/includes/db.php';
require_once __DIR__ . '/admin/includes/images.php';
require_once __DIR__ . '/api/_stock.php';
vg_schema_v2();

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function wa_local($intl) {
	$intl = preg_replace('/\D/', '', (string)$intl);
	return (strpos($intl, '20') === 0) ? '0' . substr($intl, 2) : $intl;
}
function tel_href($local) {
	$local = preg_replace('/\D/', '', (string)$local);
	return '+20' . ltrim($local, '0');
}

function wa_href($raw) {
	return ltrim(tel_href($raw), '+');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = isset($_GET['s']) ? trim($_GET['s']) : '';
$product = null;
if ($slug !== '') {
    $stmt = db()->prepare('SELECT * FROM products WHERE slug = ?');
    $stmt->execute([$slug]);
    $product = $stmt->fetch();
    if ($product) $id = (int)$product['id'];
}
if (!$product && $id) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}

$CAT_NAMES = [
	'cat_hd' => 'كاميرات سلكية HD', 'cat_ip' => 'كاميرات IP', 'cat_wf' => 'كاميرات واي فاي', 'cat_dvr' => 'مسجلات DVR',
	'cat_hdd' => 'هارد ديسك', 'cat_mem' => 'كروت الميموري', 'cat_psu' => 'باور سبلاي', 'cat_cab' => 'الكابلات',
	'cat_rack' => 'الراكات', 'cat_acc' => 'ملحقات', 'cat_net' => 'شبكات', 'cat_att' => 'أجهزة حضور وانصراف',
];
$PLACE_NAMES = ['in' => 'داخلية', 'out' => 'خارجية', 'both' => 'داخلية وخارجية'];
$DESC_MAP = [
	'cat_hd' => 'كاميرا مراقبة سلكية بتتوصل بمسجل DVR عن طريق كابل كواكس، وبتحتاج مصدر طاقة.',
	'cat_ip' => 'كاميرا IP بتتوصل بالشبكة عن طريق كابل شبكة.',
	'cat_wf' => 'كاميرا واي فاي بتتوصل بشبكة الواي فاي عندك وبتتشاف من الموبايل عن طريق تطبيق الماركة، وبتحتاج مصدر طاقة.',
	'cat_dvr' => 'مسجل DVR بيسجل صورة الكاميرات السلكية، ولازم يتركّب معاه هارد ديسك للتسجيل.',
	'cat_hdd' => 'هارد ديسك لتخزين تسجيلات الكاميرات.',
	'cat_wd' => 'هارد ديسك من سلسلة WD Purple المخصصة لتسجيل المراقبة.',
	'cat_mem' => 'كارت ميموري لتخزين التسجيلات في الكاميرات اللي بتدعم الكروت.',
	'cat_psu' => 'باور سبلاي بيغذي الكاميرات بالطاقة، والأمبير بيحدد قدرته على تشغيل أكتر من كاميرا.',
	'cat_coax' => 'سلك كواكس لتوصيل الكاميرات السلكية بالمسجل.',
	'cat_utp' => 'سلك شبكة Cat6 لتوصيل كاميرات IP وأجهزة الشبكة.',
	'cat_rack' => 'راك لتنظيم المسجلات وأجهزة الشبكة.',
	'cat_ad' => 'ادابتور لتغذية الكاميرا بالطاقة.',
	'cat_box' => 'بوكس لتجميع وحماية توصيلات الكاميرا.',
	'cat_bnc' => 'جاك BNC لتوصيل كابل الفيديو بالكاميرا أو المسجل.',
	'cat_dc' => 'جاك DC لتوصيل الطاقة بالكاميرا.',
	'cat_x' => 'اسألنا على واتساب لأي تفاصيل عن المنتج.',
];

$XS_OVERRIDES = [
	26 => [["sdt", "1920×1080 (1080p)"], ["sptr", "تحريك أفقي 360° / رأسي 114° تقريبًا"], ["snv", "أشعة تحت حمراء حتى 9 متر"], ["slens", "4mm"], ["sau", "اتجاهين (ميكروفون وسماعة)"], ["sst", "فتحة كارت microSD"], ["snw", "Wi-Fi 2.4GHz"], ["sfn", "قد تختلف بعض التفاصيل حسب إصدار الجهاز"]],
	27 => [["sdt", "2304×1296 (3MP)"], ["snv", "حتى 30 متر"], ["sip", "IP66"], ["slens", "زاوية عرض حوالي 100°"], ["sau", "اتجاهين (ميكروفون وسماعة)"], ["sst", "microSD حتى 128GB"], ["snw", "Wi-Fi 2.4GHz أو كابل شبكة"], ["sfn", "كشف حركة بإشعارات، إنذار صوتي وضوئي"]],
	28 => [["sdt", "2560×1440 (2K QHD، 4MP)"], ["sptr", "تحريك أفقي 360° / رأسي 130° تقريبًا"], ["snv", "حتى 30 متر + رؤية ليلية ملونة (Starlight)"], ["sip", "IP66"], ["sau", "اتجاهين (ميكروفون وسماعة)"], ["sst", "microSD حتى 512GB"], ["snw", "Wi-Fi 2.4GHz أو كابل شبكة"], ["sfn", "كشف أشخاص وحيوانات ومركبات، إنذار صوتي وضوئي"]],
	25 => [["sdt", "1920×1080 (1080p)"], ["sptr", "تحريك أفقي 360° / رأسي 114° تقريبًا"], ["snv", "حتى 9 متر"], ["sau", "اتجاهين (ميكروفون وسماعة)"], ["sst", "microSD أو تخزين سحابي (باشتراك)"], ["snw", "Wi-Fi 2.4GHz"], ["sfn", "كشف حركة، إنذار صوتي وضوئي"]],
	29 => [["sdt", "2304×1296 (3MP) — إصدار V3.0"], ["sptr", "تحريك أفقي 0–355° / رأسي -15° إلى 55°"], ["sau", "اتجاهين (Full-duplex)"], ["snw", "Wi-Fi 6 (2.4GHz)"], ["sfn", "تتبع الحركة، تسجيل تلقائي عند كشف الحركة"]],
	13 => [["sdt", "2304×1296 (3MP)"], ["sptr", "تحريك أفقي 0–355° / رأسي -5° إلى 50°"], ["snv", "حتى 10 متر + رؤية ليلية ملونة"], ["sau", "ميكروفون وسماعة"], ["sst", "microSD حتى 512GB أو تخزين سحابي"], ["snw", "Wi-Fi 6 (2.4GHz)"], ["sfn", "كشف أشخاص وتتبع ذكي للحركة"]],
	17 => [["sdt", "2880×1620 (5MP)"], ["sptr", "تحريك أفقي 0–355° / رأسي 0–90°"], ["snv", "حتى 30 متر، 4 أوضاع رؤية ليلية"], ["sip", "IP66"], ["slens", "3.6mm"], ["sau", "اتجاهين (ميكروفون وسماعة)"], ["sst", "microSD حتى 512GB"], ["snw", "Wi-Fi"], ["sfn", "كشف أشخاص، أضواء تحذير حمراء وزرقاء"]],
	20 => [["sdt", "1920×1080 (2MP) — يوجد إصدار 4MP"], ["snv", "حتى 30 متر + كشاف ضوئي مدمج"], ["sip", "IP67"], ["slens", "2.8mm (متوفرة 3.6 و6mm)"], ["sau", "ميكروفون مدمج"], ["sst", "microSD حتى 256GB"], ["snw", "Wi-Fi 2.4GHz + منفذ شبكة 100Mbps"], ["sfn", "كشف أشخاص، ONVIF"]],
];
$XS_LABELS = ['sdt' => 'دقة الفيديو', 'sptr' => 'التحريك (Pan/Tilt)', 'snv' => 'الرؤية الليلية', 'sip' => 'مقاومة الماء والغبار', 'sst' => 'التخزين', 'sau' => 'الصوت', 'snw' => 'الشبكة', 'slens' => 'العدسة', 'sfn' => 'ميزات إضافية'];

function vg_generic_desc($p, $DESC_MAP) {
	$e = $p['name_en'] ?? '';
	$c = $p['category'];
	$map = ['cat_hd' => 'cat_hd', 'cat_ip' => 'cat_ip', 'cat_wf' => 'cat_wf', 'cat_dvr' => 'cat_dvr', 'cat_mem' => 'cat_mem', 'cat_psu' => 'cat_psu', 'cat_rack' => 'cat_rack'];
	$k = $map[$c] ?? null;
	if ($c === 'cat_hdd') $k = ($p['brand'] === 'WD') ? 'cat_wd' : 'cat_hdd';
	elseif ($c === 'cat_cab') $k = (strpos($e, 'Cat6') !== false) ? 'cat_utp' : 'cat_coax';
	elseif ($c === 'cat_acc') {
		if (strpos($e, 'Adaptor') !== false) $k = 'cat_ad';
		elseif (strpos($e, 'Box') !== false) $k = 'cat_box';
		elseif (strpos($e, 'BNC') === 0) $k = 'cat_bnc';
		elseif (strpos($e, 'DC') === 0) $k = 'cat_dc';
		else $k = 'cat_x';
	}
	return $DESC_MAP[$k ?? 'cat_x'] ?? $DESC_MAP['cat_x'];
}

function vg_specs($p, $PLACE_NAMES, $XS_OVERRIDES, $XS_LABELS) {
	$e = $p['name_en'] ?? '';
	$c = $p['category'];
	$r = [];
	if (!empty($p['brand'])) $r[] = ['الماركة', $p['brand']];
	if (preg_match('/^cat_(hd|ip|wf)$/', $c)) {
		if (preg_match('/(\d+)MP/', $e, $m)) $r[] = ['الدقة', $m[1] . 'MP'];
		if (!empty($p['placement']) && isset($PLACE_NAMES[$p['placement']])) $r[] = ['مكان الاستخدام', $PLACE_NAMES[$p['placement']]];
		$r[] = ['طريقة التوصيل', $c === 'cat_hd' ? 'كابل كواكس إلى مسجل DVR' : ($c === 'cat_ip' ? 'كابل شبكة' : 'واي فاي')];
		$FT = ['Mic' => 'ميكروفون', 'Night Vision' => 'رؤية ليلية', 'Color' => 'ملونة', 'Fixed' => 'ثابتة', 'Pan & Tilt' => 'بانوراما وإمالة'];
		$feats = [];
		foreach ($FT as $needle => $label) if (strpos($e, $needle) !== false) $feats[] = $label;
		if ($feats) $r[] = ['الميزات', implode(' · ', $feats)];
	} elseif ($c === 'cat_dvr') {
		if (preg_match('/(\d+)-Channel (\d+MP)/', $e, $m)) { $r[] = ['عدد القنوات', $m[1]]; $r[] = ['الدقة', $m[2]]; }
	} elseif ($c === 'cat_hdd' || $c === 'cat_mem') {
		if (preg_match('/(\d+(?:\.\d+)?)\s?(TB|GB)/i', $e, $m)) $r[] = ['السعة', $m[1] . strtoupper($m[2])];
	} elseif ($c === 'cat_psu') {
		if (preg_match('/^(\d+)A/', $e, $m)) $r[] = ['التيار', $m[1] . 'A'];
	} elseif ($c === 'cat_cab') {
		if (preg_match('/RG\d+|Cat6/', $e, $m)) $r[] = ['النوع', $m[0]];
		if (preg_match('/(\d+)m\b/', $e, $m)) $r[] = ['الطول', $m[1] . ' m'];
	} elseif ($c === 'cat_rack') {
		if (preg_match('/(\d+U)/', $e, $m)) $r[] = ['المقاس', $m[1]];
	} elseif ($c === 'cat_acc') {
		if (preg_match('/^(\d+)A/', $e, $m)) $r[] = ['التيار', $m[1] . 'A'];
		if (preg_match('/(\d+×\d+)/u', $e, $m)) $r[] = ['المقاس', $m[1]];
		if (preg_match('/^(BNC|DC)\b/', $e, $m)) $r[] = ['النوع', $m[1]];
	}
	if (isset($XS_OVERRIDES[(int)$p['id']])) {
		foreach ($XS_OVERRIDES[(int)$p['id']] as $row) {
			$r[] = [$XS_LABELS[$row[0]] ?? $row[0], $row[1]];
		}
	}
	return $r;
}

$SV = [
	'bu' => '<rect x="14" y="26" width="66" height="30" rx="13" fill="currentColor"/><rect x="78" y="22" width="14" height="38" rx="6" fill="currentColor"/><circle cx="90" cy="41" r="9" fill="var(--surface)"/><circle cx="90" cy="41" r="4" fill="var(--accent)"/><path d="M36 56v14h14" stroke="currentColor" stroke-width="7" fill="none" stroke-linecap="round"/>',
	'do' => '<ellipse cx="60" cy="72" rx="38" ry="9" fill="currentColor" opacity=".25"/><path d="M24 66a36 36 0 0 1 72 0z" fill="currentColor"/><circle cx="60" cy="52" r="13" fill="var(--surface)"/><circle cx="60" cy="52" r="6" fill="var(--accent)"/>',
	'cu' => '<rect x="34" y="24" width="52" height="42" rx="12" fill="currentColor"/><circle cx="60" cy="45" r="13" fill="var(--surface)"/><circle cx="60" cy="45" r="6" fill="var(--accent)"/><rect x="44" y="66" width="32" height="8" rx="4" fill="currentColor" opacity=".5"/><path d="M92 28a12 12 0 0 1 12 12M92 16a24 24 0 0 1 24 24" stroke="var(--accent)" stroke-width="4" fill="none" stroke-linecap="round"/>',
	'pt' => '<rect x="50" y="8" width="20" height="10" rx="3" fill="currentColor"/><circle cx="60" cy="46" r="26" fill="currentColor"/><circle cx="60" cy="46" r="15" fill="var(--surface)"/><circle cx="60" cy="46" r="7" fill="var(--accent)"/><path d="M96 28a40 40 0 0 1 0 36" stroke="var(--accent)" stroke-width="4" fill="none" stroke-linecap="round"/>',
	'dv' => '<rect x="12" y="30" width="96" height="32" rx="6" fill="currentColor"/><rect x="20" y="38" width="42" height="5" rx="2" fill="var(--surface)" opacity=".7"/><rect x="20" y="48" width="42" height="5" rx="2" fill="var(--surface)" opacity=".7"/><circle cx="82" cy="46" r="6" fill="var(--accent)"/><circle cx="97" cy="46" r="3" fill="var(--surface)"/><rect x="20" y="62" width="10" height="5" fill="currentColor"/><rect x="90" y="62" width="10" height="5" fill="currentColor"/>',
	'hd' => '<rect x="26" y="12" width="68" height="64" rx="8" fill="currentColor"/><circle cx="58" cy="44" r="20" fill="var(--surface)" opacity=".85"/><circle cx="58" cy="44" r="6" fill="currentColor"/><path d="M84 20 66 42" stroke="var(--accent)" stroke-width="5" stroke-linecap="round"/>',
	'mc' => '<path d="M38 12h34l16 16v50a4 4 0 0 1-4 4H38a4 4 0 0 1-4-4V16a4 4 0 0 1 4-4z" fill="currentColor"/><g fill="var(--accent)"><rect x="42" y="14" width="5" height="14"/><rect x="51" y="14" width="5" height="14"/><rect x="60" y="14" width="5" height="14"/></g><rect x="44" y="46" width="30" height="20" rx="3" fill="var(--surface)" opacity=".8"/>',
	'ps' => '<rect x="22" y="22" width="76" height="46" rx="6" fill="currentColor"/><circle cx="46" cy="45" r="14" fill="var(--surface)" opacity=".85"/><path d="M45 36l-6 11h7l-3 10 11-14h-7l4-7z" fill="var(--accent)"/><rect x="72" y="32" width="18" height="5" rx="2" fill="var(--surface)" opacity=".6"/><rect x="72" y="42" width="18" height="5" rx="2" fill="var(--surface)" opacity=".6"/>',
	'ca' => '<circle cx="60" cy="45" r="32" fill="currentColor"/><circle cx="60" cy="45" r="22" fill="var(--surface)" opacity=".9"/><circle cx="60" cy="45" r="14" fill="var(--accent)"/><circle cx="60" cy="45" r="6" fill="var(--surface)"/><path d="M92 58q14 6 18 20" stroke="currentColor" stroke-width="5" fill="none" stroke-linecap="round"/>',
	'rk' => '<rect x="34" y="8" width="52" height="76" rx="5" fill="currentColor"/><g fill="var(--surface)" opacity=".85"><rect x="40" y="16" width="40" height="10" rx="2"/><rect x="40" y="31" width="40" height="10" rx="2"/><rect x="40" y="46" width="40" height="10" rx="2"/><rect x="40" y="61" width="40" height="10" rx="2"/></g><circle cx="74" cy="21" r="2.5" fill="var(--accent)"/><circle cx="74" cy="36" r="2.5" fill="var(--accent)"/>',
	'ad' => '<rect x="38" y="26" width="38" height="38" rx="8" fill="currentColor"/><rect x="48" y="14" width="5" height="14" fill="var(--accent)"/><rect x="62" y="14" width="5" height="14" fill="var(--accent)"/><path d="M76 45h14q16 0 16 18" stroke="currentColor" stroke-width="5" fill="none" stroke-linecap="round"/>',
	'bx' => '<rect x="26" y="20" width="68" height="50" rx="6" fill="currentColor"/><rect x="34" y="28" width="52" height="34" rx="3" fill="var(--surface)" opacity=".85"/><g fill="var(--accent)"><circle cx="31" cy="25" r="2.5"/><circle cx="89" cy="25" r="2.5"/><circle cx="31" cy="65" r="2.5"/><circle cx="89" cy="65" r="2.5"/></g>',
	'jk' => '<rect x="34" y="34" width="44" height="22" rx="4" fill="currentColor"/><rect x="78" y="38" width="18" height="14" rx="3" fill="var(--accent)"/><rect x="22" y="40" width="12" height="10" rx="2" fill="currentColor" opacity=".6"/><path d="M50 34v22M60 34v22" stroke="var(--surface)" stroke-width="3"/>',
];

if (!$product) {
	http_response_code(404);
	?><!doctype html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>المنتج غير موجود | Vision Guard</title>
<style>body{font:16px/1.6 Cairo,system-ui,sans-serif;background:#f4f7fb;color:#202224;display:grid;place-items:center;min-height:100vh;margin:0;text-align:center}
a{color:#0667a5;font-weight:700}</style></head>
<body><div><h1>المنتج غير موجود</h1><p>المنتج اللي بتدور عليه مش موجود أو اتشال.</p><p><a href="/">رجوع للمتجر</a></p></div></body>
</html><?php
	exit;
}

$name_ar = $product['name_ar'];
$name_en = $product['name_en'];
$price = (float)$product['price'];
$discount = (int)($product['discount_percent'] ?? 0);
$effPrice = $discount > 0 ? round($price * (1 - $discount / 100), 2) : $price;
$inStock = vg_stock_available($product);
$stockLeft = vg_stock_left($product);
$details = trim((string)($product['details_ar'] ?? ''));
if ($details === '' && !empty($product['slug'])) { $__ov = @include __DIR__ . '/seo_descriptions.php'; if (is_array($__ov) && !empty($__ov[$product['slug']])) { $details = $__ov[$product['slug']]; } }
$hasRealDetails = $details !== '';
if (!$hasRealDetails) $details = vg_generic_desc($product, $DESC_MAP);
$specs = vg_specs($product, $PLACE_NAMES, $XS_OVERRIDES, $XS_LABELS);
	$manualSpecs = [];
	if (!empty($product['specs'])) {
		foreach (preg_split('/\r\n|\r|\n/', $product['specs']) as $__sl) {
			$__sl = trim($__sl);
			if ($__sl === '') continue;
			$__p = strpos($__sl, ':');
			if ($__p === false) continue;
			$manualSpecs[] = [trim(substr($__sl, 0, $__p)), trim(substr($__sl, $__p+1))];
		}
	}
	$hasManualSpecs = !empty($manualSpecs);
	if ($hasManualSpecs) $specs = $manualSpecs;
	if (!$hasRealDetails) {
		$__pairs = [];
		foreach ($specs as $__r) {
			$__l = trim((string)($__r[0] ?? ''));
			$__v = trim((string)($__r[1] ?? ''));
			if ($__l === '' || $__v === '' || mb_strpos($__v, 'غير محدد') !== false) continue;
			$__pairs[] = $__l . ': ' . $__v;
			if (count($__pairs) >= 6) break;
		}
		if ($__pairs) {
			$__nm = trim((string)($product['name_ar'] ?? ''));
			if ($__nm === '') $__nm = trim((string)($product['name_en'] ?? ''));
			$details = $__nm . ' - أبرز المواصفات: ' . implode('، ', $__pairs) . '. ' . $details;
		}
	}
$catLabel = $CAT_NAMES[$product['category']] ?? '';
$plLabel = !empty($product['placement']) ? ($PLACE_NAMES[$product['placement']] ?? '') : '';
$metaLine = implode(' · ', array_filter([$product['brand'], $catLabel, $plLabel]));

$imgSlots = product_image_slots((int)$product['id']);
$hasImage = !empty($imgSlots);
$imgRel = $hasImage ? vg_img('/images/p' . $product['id'] . ($imgSlots[0] > 1 ? ('_' . $imgSlots[0]) : '') . '.jpg') : '';

$waNumber = setting('whatsapp_number', '201105006854');
$callNumber = setting('call_number', '01260087815');
$pageUrl = !empty($product['slug']) ? ('https://visionguardeg.com/p/' . $product['slug']) : ('https://visionguardeg.com/product.php?id=' . $product['id']);
$waMsg = 'مرحبًا Vision Guard، عايز أسأل عن: ' . $name_ar . ' (' . number_format($effPrice) . ' ج.م)' . "\n" . $pageUrl;
$waHref = 'https://wa.me/' . h(wa_href($waNumber)) . '?text=' . rawurlencode($waMsg);

$related = [];
if (!empty($product['category'])) {
    $stmt2 = db()->prepare('SELECT id,name_ar,name_en,slug,price,icon,discount_percent FROM products WHERE category = ? AND id != ? ORDER BY sort_order ASC, id ASC LIMIT 4');
	$stmt2->execute([$product['category'], $product['id']]);
	$related = $stmt2->fetchAll();
}

$metaDesc = mb_substr(strip_tags($details), 0, 160);
?><!doctype html>
<html lang="ar" dir="rtl">
<head>
<script>(function(){var r=document.documentElement,s=localStorage.getItem('vg-theme'),t=s||((window.matchMedia&&matchMedia('(prefers-color-scheme: dark)').matches)?'dark':'light');r.setAttribute('data-theme',t);document.addEventListener('DOMContentLoaded',function(){var b=document.getElementById('themeToggle');if(b){b.addEventListener('click',function(){var c=r.getAttribute('data-theme')==='dark'?'light':'dark';r.setAttribute('data-theme',c);try{localStorage.setItem('vg-theme',c)}catch(e){}})}});})();</script>

<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= h($name_ar) ?> | Vision Guard</title>
<meta name="description" content="<?= h($metaDesc) ?>">
<link rel="canonical" href="<?= h($pageUrl) ?>">
<meta name="theme-color" content="#0667a5">
<meta property="og:type" content="product"><meta property="og:url" content="<?= h($pageUrl) ?>"><meta property="og:site_name" content="Vision Guard">
<meta property="og:title" content="<?= h($name_ar) ?> | Vision Guard"><meta property="og:description" content="<?= h($metaDesc) ?>">
<?php if ($hasImage): ?><meta property="og:image" content="https://visionguardeg.com<?= h($imgRel) ?>"><?php endif; ?>
<link rel="icon" type="image/png" sizes="32x32" href="favicon-32.png"><link rel="apple-touch-icon" href="/apple-touch-icon.png">
<script type="application/ld+json"><?php echo json_encode(['@context'=>'https://schema.org','@type'=>'Product','name'=>$name_ar,'description'=>mb_substr(trim(preg_replace('/\s+/u',' ',strip_tags($details))),0,500),'sku'=>(string)(!empty($product['slug'])?$product['slug']:$product['id']),'mpn'=>(string)(!empty($product['slug'])?$product['slug']:$product['id']),'image'=>($hasImage?('https://visionguardeg.com/'.ltrim($imgRel,'/')):null),'brand'=>['@type'=>'Brand','name'=>(string)($product['brand']??'Vision Guard')],'offers'=>['@type'=>'Offer','url'=>$pageUrl,'priceCurrency'=>'EGP','price'=>(string)$effPrice,'availability'=>($inStock?'https://schema.org/InStock':'https://schema.org/OutOfStock'),'priceValidUntil'=>date('Y').'-12-31','itemCondition'=>'https://schema.org/NewCondition','seller'=>['@type'=>'Organization','name'=>'Vision Guard'],'shippingDetails'=>['@type'=>'OfferShippingDetails','shippingDestination'=>['@type'=>'DefinedRegion','addressCountry'=>'EG','addressRegion'=>['Cairo','Giza']]]]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?></script><script type="application/ld+json"><?php echo json_encode(['@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>[['@type'=>'ListItem','position'=>1,'name'=>'الرئيسية','item'=>'https://visionguardeg.com/'],['@type'=>'ListItem','position'=>2,'name'=>$name_ar,'item'=>$pageUrl]]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?></script><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;800&display=swap">
<style>
:root{--brand:#0667a5;--on-brand:#fff;--accent:#05b3e9;--on-accent:#04202e;--sale:#ff5a1f;--bg:#f4f7fb;--surface:#fff;--text:#202224;--muted:#494b4a;--border:#e1e1e1}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]){--brand:#052e45;--accent:#05b3e9;--on-accent:#04202e;--bg:#2e4160;--surface:#3d5279;--text:#ffffff;--muted:#cdd6e4;--border:#5d7399}}
:root[data-theme="dark"]{--brand:#052e45;--accent:#05b3e9;--on-accent:#04202e;--bg:#2e4160;--surface:#3d5279;--text:#ffffff;--muted:#cdd6e4;--border:#5d7399}
*,*::before,*::after{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font:15px/1.6 Cairo,system-ui,-apple-system,"Segoe UI",sans-serif}
a{color:inherit;text-decoration:none}
.wrap{max-width:960px;margin:0 auto;padding:0 16px}
.top{background:var(--brand);color:var(--on-brand);font-size:13px;text-align:center;padding:6px 0}
header{background:var(--surface);border-bottom:1px solid var(--border);padding:12px 0}
.bar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}
.logo{display:flex;align-items:center;font-weight:800;font-size:20px;color:#4a4b4c}
:root[data-theme="dark"] .logo{color:#eef2f7}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) .logo{color:#eef2f7}}
.logo .wm{width:63px;height:40px;display:block}
.back{font-weight:700;color:var(--muted)}
.theme{border:1px solid var(--border);background:var(--bg);color:var(--text);display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;cursor:pointer}
.theme .i-moon{display:block}.theme .i-sun{display:none}:root[data-theme="dark"] .theme .i-moon{display:none}:root[data-theme="dark"] .theme .i-sun{display:block}
.crumb{font-size:13px;color:var(--muted);margin:16px 0}
.crumb a{color:var(--brand)}
.pdt{display:grid;grid-template-columns:minmax(220px,320px) 1fr;gap:26px;background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:22px;margin-bottom:22px}
@media(max-width:620px){.pdt{grid-template-columns:1fr}}
.pic{aspect-ratio:1/1;width:100%;border-radius:14px;background:radial-gradient(circle at 50% 40%,var(--surface),var(--bg));border:1px solid var(--border);display:grid;place-items:center;color:var(--brand);overflow:hidden}
.pic img{width:100%;height:100%;object-fit:contain;background:#fff}
.pic svg{width:70%;height:auto}
.meta{font-size:13px;color:var(--muted)}
h1{margin:6px 0;font-size:24px;line-height:1.3}
.price{font-weight:800;font-size:26px;margin:6px 0}
.price s{font-weight:400;font-size:15px;color:var(--muted);margin-inline-start:8px}
.badge{display:inline-block;background:var(--sale);color:#fff;font-size:12px;font-weight:800;padding:3px 10px;border-radius:99px;margin-bottom:8px}
.stock{font-size:13px;font-weight:700;color:#0b7a3b;margin:4px 0}.stock.out{color:#d93a3a}:root[data-theme="dark"] .stock{color:#6ef0ac}:root[data-theme="dark"] .stock.out{color:#ff8a8a}:root[data-theme="dark"] .buybox .now{color:#8fe0ff;border-color:#8fe0ff}:root[data-theme="dark"] .crumb a{color:#cfe8f7}:root[data-theme="dark"] p>a[href="/order-status.php"]{color:#8fe0ff}@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) p>a[href="/order-status.php"]{color:#8fe0ff}}@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) .stock{color:#6ef0ac}:root:not([data-theme="light"]) .stock.out{color:#ff8a8a}:root:not([data-theme="light"]) .buybox .now{color:#8fe0ff;border-color:#8fe0ff}:root:not([data-theme="light"]) .crumb a{color:#cfe8f7}}
.details{margin:14px 0;color:var(--text);line-height:1.8}
.spec-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;margin-top:10px}
.spec-card{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:10px 12px;display:flex;align-items:center;gap:9px}
.spec-card .ic{flex:0 0 auto;width:30px;height:30px;border-radius:8px;background:var(--surface);display:grid;place-items:center;color:var(--brand)}
.spec-card .ic svg{width:16px;height:16px}
.spec-card .sv{display:flex;flex-direction:column;gap:1px;min-width:0}
.spec-card .sl{font-size:11px;color:var(--muted)}
.spec-card .sval{font-size:13px;font-weight:600;color:var(--text);word-break:break-word}
.trustrow{display:flex;flex-direction:column;gap:8px;margin-top:14px;padding-top:14px;border-top:1px solid var(--border)}
.trust{display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted)}
.trust svg{width:17px;height:17px;flex:0 0 auto;color:var(--brand)}
.snote{font-size:12px;color:var(--muted);margin-top:8px}
.btns{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
.btn{display:inline-block;border:1px solid var(--border);background:var(--bg);color:var(--text);font:inherit;font-weight:700;padding:11px 20px;border-radius:12px;cursor:pointer}
.btn.pri{background:#25d366;color:#04200f;border-color:transparent}
h2{font-size:18px;margin:26px 0 12px}
.rel{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-bottom:26px}
.rcard{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:10px;display:block}
.rcard .pic{aspect-ratio:1/1;border-radius:10px;margin-bottom:6px}
.rcard h4{margin:0;font-size:13px;line-height:1.35;min-height:2.6em}
.rcard .price{font-size:14px;margin:4px 0 0}
footer{background:var(--brand);color:var(--on-brand);margin-top:30px;padding:22px 0;font-size:13px;text-align:center}
footer a{display:inline-block;margin:0 8px;opacity:.9}
</style>
<link rel="manifest" href="/manifest.webmanifest">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Vision Guard">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link rel="stylesheet" href="/pwa.css">
</head>
<body>
<div class="top">توصيل داخل القاهرة والجيزة · ضمان رسمي · اطلب عبر واتساب</div>
<header><div class="wrap bar">
<svg width="0" height="0" style="position:absolute" aria-hidden="true"><symbol id="wm" viewBox="-6 -128.28125 567.859375 364.15625">
  <defs>
    <linearGradient id="visionGrad" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#29b6f6"/>
      <stop offset="1" stop-color="#0a5f9c"/>
    </linearGradient>
    <linearGradient id="lensOuter" x1="0" y1="-1" x2="0.6" y2="1">
      <stop offset="0" stop-color="#3fc8f0"/>
      <stop offset="0.55" stop-color="#1683c4"/>
      <stop offset="1" stop-color="#0a5f9c"/>
    </linearGradient>
    <radialGradient id="lensInner" cx="0.35" cy="0.3" r="0.8">
      <stop offset="0" stop-color="#5fc3ec"/>
      <stop offset="1" stop-color="#1f6fa8"/>
    </radialGradient>
  </defs>
  <path d="M40.39 0L76.95 0L115.94-116.41L85.55-116.41L69.84-65.23C66.25-52.81 62.66-39.77 58.91-25.94C55-39.77 51.25-52.81 47.42-65.23L31.09-116.41L0.39-116.41M153.88-116.41L125.75-116.41L125.75 0L153.88 0M217.20 1.88C247.20 1.88 265.02-11.95 265.02-35.39C265.02-53.36 253.84-63.59 228.06-68.75L217.83-70.78C204.55-73.52 199-77.03 199-83.36C199-90.62 206.42-95.70 217.05-95.70C228.14-95.70 235.56-89.77 236.34-80.16L262.36-80.16C261.66-103.98 244.55-118.28 216.66-118.28C189.31-118.28 170.88-103.67 170.88-82.03C170.88-65.08 182.52-53.83 205.25-49.14L217.20-46.72C231.19-43.83 237.20-40 237.20-33.36C237.20-25.70 229.31-20.70 217.44-20.70C204-20.70 195.80-27.27 195.41-38.44L168.61-38.44C168.61-12.73 186.19 1.88 217.20 1.88M307.88-116.41L279.75-116.41L279.75 0L307.88 0M446.33 0L474.45 0L474.45-48.52C474.45-53.36 474.38-62.97 473.98-74.30C480-63.36 485.55-53.83 488.91-48.44L519.22 0L548.36 0L548.36-116.41L520.23-116.41L520.23-65.94C520.23-60.70 520.39-51.25 520.63-42.03C516.09-50.39 511.17-59.14 508.44-63.52L475.39-116.41L446.33-116.41" fill="url(#visionGrad)"/>
  <g transform="translate(377.1015625,-58.203125)">
  <circle r="55.6640625" fill="url(#lensOuter)"/>
  <circle r="48.6640625" fill="none" stroke="#ffffff" stroke-width="5"/>
  <circle r="28.9453125" fill="url(#lensInner)"/>
  <ellipse cx="-15.585937500000002" cy="-17.8125" rx="12.24609375" ry="7.792968750000001" fill="#eaf6ff" opacity="0.9" transform="rotate(-25)"/>
  <path d="M 8.349609375 -52.880859375 A 52.880859375 52.880859375 0 0 1 52.880859375 -5.56640625 C 30.615234375000004 -8.349609375 13.916015625 -30.615234375000004 8.349609375 -52.880859375 Z" fill="#8fb7cf" stroke="#eef6fb" stroke-width="5" stroke-linejoin="round" opacity="0.95"/>
</g>
  <g transform="translate(44.7607421875,0)">
    <path d="M60.31 187.88C91.48 187.88 112.97 167.95 112.97 138.42L112.97 122.64L62.19 122.64L62.19 143.27L85.47 143.27C85.23 155.30 75.94 163.34 61.17 163.34C42.81 163.34 32.34 150.22 32.34 127.88C32.34 105.45 42.73 92.25 60.86 92.25C73.44 92.25 81.41 98.81 83.52 109.13L112.11 109.13C109.53 84.36 87.27 67.72 59.84 67.72C28.20 67.72 4.06 89.83 4.06 127.88C4.06 164.20 26.48 187.88 60.31 187.88M162.56 187.43C185.13 187.43 199.91 174.38 199.91 154.79L199.91 97.24L179 97.24L179 153.06C179 162.47 172.45 169.20 162.56 169.20C152.73 169.20 146.06 162.41 146.06 153.06L146.06 97.24L125.15 97.24L125.15 154.79C125.15 174.38 139.86 187.43 162.56 187.43M207.30 186L230.60 186L236.02 168.90L267.53 168.90L272.77 186L296.72 186L266.04 97.24L237.80 97.24M241.38 152.16L242.99 146.98C246.08 137.09 248.94 127.38 251.98 116.48C254.96 127.38 257.82 137.09 260.74 146.98L262.35 152.16M304.24 186L325.68 186L325.68 156.51L339.50 156.51L353.98 186L376.61 186L359.58 152.16C368.87 147.34 374.29 138.64 374.29 127.26C374.29 108.86 361.36 97.24 340.81 97.24L304.24 97.24M325.68 139.65L325.68 114.81L339.32 114.81C347.96 114.81 353.32 119.70 353.32 127.26C353.32 134.77 347.96 139.65 339.38 139.65M418.92 186C446.20 186 463.24 168.72 463.24 141.62C463.24 114.52 445.96 97.24 418.92 97.24L385.98 97.24L385.98 186M407.42 168.13L407.42 115.11L417.61 115.11C433.93 115.11 441.68 124.11 441.68 141.62C441.68 159.07 434.11 168.13 417.85 168.13" fill="currentColor"/>
  </g>
  <path d="M 30.7607421875 205.875 Q 277.9296875 227.875 525.0986328125 199.875" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" opacity="0.9"/>
</symbol></svg>
<a class="logo" href="/" aria-label="Vision Guard"><svg class="wm" width="63" height="40" role="img" aria-label="Vision Guard"><use href="#wm"/></svg></a>
<div style="display:flex;align-items:center;gap:10px">
<a class="back" href="/shop">&#8592; رجوع للمتجر</a>
<button type="button" class="theme" id="langToggle" aria-label="Language" style="font-weight:700;font-size:12px;width:auto;padding:0 10px">EN</button><button type="button" class="theme" id="themeToggle" aria-label="تبديل المظهر"><svg class="i-sun" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg><svg class="i-moon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg></button>
</div>
</div></header>
<main class="wrap">
<div class="crumb"><a href="/">الرئيسية</a> / <?= h($catLabel) ?> / <?= h($name_ar) ?></div>
<div class="pdt">
<div>
<div class="pic"><?php if ($hasImage): ?><img id="mainImg" src="<?= h(vg_img('/images/p' . $product['id'] . ($imgSlots[0] > 1 ? ('_' . $imgSlots[0]) : '') . '.jpg')) ?>" alt="<?= h($name_ar) ?>"><?php else: ?><svg viewBox="0 0 120 90"><?= $SV[$product['icon']] ?? $SV['bu'] ?></svg><?php endif; ?></div>
<?php if (count($imgSlots) > 1): ?>
<div class="thumbs" style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap">
<?php foreach ($imgSlots as $__gi => $__gslot): ?>
<img src="<?= h(vg_img('/images/p' . $product['id'] . ($__gslot > 1 ? ('_' . $__gslot) : '') . '.jpg')) ?>" style="width:56px;height:56px;object-fit:cover;border-radius:8px;cursor:pointer;border:2px solid <?= $__gi===0?'var(--brand)':'transparent' ?>" onclick="document.getElementById('mainImg').src=this.src;this.parentNode.querySelectorAll('img').forEach(function(t){t.style.borderColor='transparent'});this.style.borderColor='var(--brand)'">
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
<div>
<div class="meta"><?= h($metaLine) ?></div>
<h1><?= h($name_ar) ?></h1>
<div class="meta"><?= h($name_en) ?></div>
<?php if ($discount > 0): ?><div class="badge">خصم <?= $discount ?>%</div><?php endif; ?>
<div class="price"><?= number_format($effPrice) ?> ج.م<?php if ($discount > 0): ?><s><?= number_format($price) ?> ج.م</s><?php endif; ?></div>
<div class="stock<?= $inStock ? '' : ' out' ?>"><?= h(vg_stock_label($product)) ?></div>
<div class="details"><?= nl2br(h($details)) ?></div>
<?php if ($specs): ?><h2 style="margin-top:0">المواصفات</h2><div class="spec-grid"><?php foreach ($specs as $row): ?><div class="spec-card"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg></span><span class="sv"><span class="sl"><?= h($row[0]) ?></span><span class="sval"><?= h($row[1]) ?></span></span></div><?php endforeach; ?></div>
<?php if (!$hasManualSpecs): ?><p class="snote">المواصفات مأخوذة من اسم المنتج. للمواصفات الكاملة والتأكد من التوافق اسألنا على واتساب.</p><?php endif; ?>
<?php endif; ?>
<?php if ($inStock): ?>
<div class="buybox">
  <div class="qty" role="group" aria-label="الكمية"><button type="button" data-q="-1" aria-label="أقل">&minus;</button><output id="qv" aria-live="polite">1</output><button type="button" data-q="1" aria-label="أكثر">+</button></div>
  <button type="button" class="buy" id="addcart">أضف للسلة</button>
  <button type="button" class="now" id="buynow">اشترِ الآن</button>
</div>
<?php endif; ?>
<div class="btns">
<a class="btn pri" target="_blank" rel="noopener" href="<?= h($waHref) ?>">اطلب / اسأل عن المنتج على واتساب</a>
<a class="btn" href="tel:<?= h(tel_href($callNumber)) ?>">اتصل بينا</a>
</div>
<div class="trustrow"><div class="trust"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg><span>توصيل داخل القاهرة والجيزة</span></div><div class="trust"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z"/><path d="M9 12l2 2 4-4"/></svg><span>ضمان رسمي وكيل معتمد</span></div><div class="trust"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 109-9"/><path d="M3 4v5h5"/></svg><span>استرجاع سهل واستبدال سريع</span></div></div>
</div>
</div>

<style>.pfaq{margin:28px 0 8px}.pfaq h2{margin:0 0 10px}.pfaq details{border-top:1px solid var(--line,#dbe4ef);padding:12px 0}.pfaq summary{cursor:pointer;font-weight:700}.pfaq p{margin:8px 0 0;color:var(--muted,#5b6b82);line-height:1.8}.pfaq a{color:var(--brand,#0667a5)}</style>
<section class="pfaq" id="faq"><h2>أسئلة شائعة</h2>
<details><summary>بتوصلوا فين وبياخد قد إيه؟</summary><p>بنوصّل الطلبات داخل القاهرة والجيزة، والمدة المتوقعة 3 أيام عمل من تأكيد الطلب. المدة تقديرية وممكن تختلف حسب المنطقة وتوفر المنتج، وتكلفة الشحن بتتأكد معاك على واتساب قبل تأكيد الطلب.</p></details>
<details><summary>طرق الدفع إيه؟</summary><p>الدفع عند الاستلام أو التحويل عبر إنستاباي أو المحفظة الإلكترونية. التحويلات بتتأكد يدويًا بعد مراجعة وصول المبلغ.</p></details>
<details><summary>أقدر أسترجع أو أستبدل المنتج؟</summary><p>أيوه، خلال 14 يوم من الاستلام بشرط إن المنتج بحالته الأصلية وفي عبوته ومعاه ملحقاته. المنتجات المعيبة أو المخالفة للطلب بنستبدلها أو نرجع قيمتها بعد الفحص. غير مشمول: الهارد ديسك والمحولات الكهربائية وجميع الإكسسوارات البلاستيكية.</p></details>
<details><summary>بتركّبوا الكاميرات؟</summary><p>التركيب خدمة منفصلة، وبيتم الاتفاق على تكلفتها وموعدها معاك قبل التنفيذ. كلمنا على واتساب 01105006854.</p></details>
<details><summary>إزاي أتابع طلبي؟</summary><p>بعد الطلب هتاخد رقم طلب. ادخل على صفحة تتبّع الطلب واكتب رقم الطلب ورقم الهاتف اللي سجلت بيه. <a href="/order-status.php">تتبّع طلبك</a></p></details>
</section>
<script type="application/ld+json">{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"بتوصلوا فين وبياخد قد إيه؟","acceptedAnswer":{"@type":"Answer","text":"بنوصّل الطلبات داخل القاهرة والجيزة، والمدة المتوقعة 3 أيام عمل من تأكيد الطلب. المدة تقديرية وممكن تختلف حسب المنطقة وتوفر المنتج، وتكلفة الشحن بتتأكد معاك على واتساب قبل تأكيد الطلب."}},{"@type":"Question","name":"طرق الدفع إيه؟","acceptedAnswer":{"@type":"Answer","text":"الدفع عند الاستلام أو التحويل عبر إنستاباي أو المحفظة الإلكترونية. التحويلات بتتأكد يدويًا بعد مراجعة وصول المبلغ."}},{"@type":"Question","name":"أقدر أسترجع أو أستبدل المنتج؟","acceptedAnswer":{"@type":"Answer","text":"أيوه، خلال 14 يوم من الاستلام بشرط إن المنتج بحالته الأصلية وفي عبوته ومعاه ملحقاته. المنتجات المعيبة أو المخالفة للطلب بنستبدلها أو نرجع قيمتها بعد الفحص. غير مشمول: الهارد ديسك والمحولات الكهربائية وجميع الإكسسوارات البلاستيكية."}},{"@type":"Question","name":"بتركّبوا الكاميرات؟","acceptedAnswer":{"@type":"Answer","text":"التركيب خدمة منفصلة، وبيتم الاتفاق على تكلفتها وموعدها معاك قبل التنفيذ. كلمنا على واتساب 01105006854."}},{"@type":"Question","name":"إزاي أتابع طلبي؟","acceptedAnswer":{"@type":"Answer","text":"بعد الطلب هتاخد رقم طلب. ادخل على صفحة تتبّع الطلب واكتب رقم الطلب ورقم الهاتف اللي سجلت بيه."}}]}</script>

<?php if ($related): ?>
<h2>منتجات مشابهة</h2>
<div class="rel">
<?php foreach ($related as $rp): $rEff = (!empty($rp['discount_percent'])) ? round($rp['price'] * (1 - (int)$rp['discount_percent'] / 100), 2) : (float)$rp['price'];
$rImgRel = vg_img('/images/p' . $rp['id'] . '.jpg'); $rHasImg = file_exists(__DIR__ . '/images/p' . $rp['id'] . '.jpg'); ?>
<a class="rcard" href="<?= !empty($rp['slug']) ? ('/p/' . h($rp['slug'])) : ('/product.php?id=' . (int)$rp['id']) ?>">
<div class="pic"><?php if ($rHasImg): ?><img src="<?= h($rImgRel) ?>" alt="<?= h($rp['name_ar']) ?>"><?php else: ?><svg viewBox="0 0 120 90"><?= $SV[$rp['icon']] ?? $SV['bu'] ?></svg><?php endif; ?></div>
<h4 data-en="<?= h($rp['name_en'] ?? '') ?>"><?= h($rp['name_ar']) ?></h4>
<div class="price"><?= number_format($rEff) ?> ج.م</div>
</a>
<?php endforeach; ?>
</div>
<?php endif; ?>
</main>
<footer><div class="wrap">
<a href="<?= h($waHref) ?>" target="_blank" rel="noopener">واتساب: <bdi dir="ltr"><?= h(wa_local($waNumber)) ?></bdi></a>
<a href="tel:<?= h(tel_href($callNumber)) ?>">اتصال: <bdi dir="ltr"><?= h($callNumber) ?></bdi></a>
<div style="margin-top:10px;opacity:.75">© Vision Guard. جميع الأسعار بالجنيه المصري.</div>
</div></footer>
<script src="/pwa.js" defer></script>
<style>
.buybox{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:18px 0 10px}
.qty{display:inline-flex;align-items:center;border:1px solid var(--border);border-radius:12px;overflow:hidden}
.qty button{width:44px;height:46px;border:0;background:transparent;color:inherit;font-size:1.25rem;cursor:pointer}
.qty output{min-width:36px;text-align:center;font-weight:700}
.buybox .buy,.buybox .now{flex:1;min-height:48px;padding:0 16px;border-radius:12px;font:inherit;font-weight:700;cursor:pointer;white-space:nowrap}
.buybox .buy{border:0;background:#05b3e9;color:#04202f}
.buybox .now{border:1.5px solid #0074a3;background:transparent;color:#0074a3}
.buybox button:focus-visible{outline:3px solid #05b3e9;outline-offset:2px}
#vgtoast{position:fixed;inset-inline:12px;bottom:90px;z-index:9985;max-width:420px;margin-inline:auto;padding:12px 14px;border-radius:12px;background:#0a5f9c;color:#fff;display:none;gap:12px;align-items:center;justify-content:space-between;box-shadow:0 6px 20px rgba(0,0,0,.35)}
#vgtoast.show{display:flex}
#vgtoast a{color:#fff;font-weight:700;text-decoration:underline;white-space:nowrap}
@media (max-width:700px){
 .buybox{position:fixed;inset-inline:0;bottom:0;z-index:9980;margin:0;flex-wrap:nowrap;padding:10px 12px calc(10px + env(safe-area-inset-bottom));background:var(--surface);border-top:1px solid var(--border)}
 .buybox .now{display:none}
 body:has(.buybox){padding-bottom:84px}
 html.vg-standalone .buybox{bottom:calc(56px + env(safe-area-inset-bottom))}
 body:has(.buybox) #vg-install{bottom:calc(86px + env(safe-area-inset-bottom))}
 html.vg-standalone body:has(.buybox) #vg-install{bottom:calc(142px + env(safe-area-inset-bottom))}
 #vgtoast{bottom:96px}
}
</style>
<script>
(function(){
var key=(function(){var m=location.pathname.match(/^\/p\/(.+)$/);if(m)return 'sl:'+decodeURIComponent(m[1]);var i=new URLSearchParams(location.search).get('id');return i?'id:'+i:''})();
var qv=document.getElementById('qv'),n=1;if(!qv)return;var MAXQ=<?= $stockLeft !== null ? max(1, min(10, $stockLeft)) : 10 ?>;
document.querySelectorAll('.qty button').forEach(function(b){b.onclick=function(){n=Math.max(1,Math.min(MAXQ,n+(+b.dataset.q)));qv.textContent=n}});
function add(){try{var a=JSON.parse(localStorage.getItem('vg-pending')||'[]');a.push({k:key,q:n});localStorage.setItem('vg-pending',JSON.stringify(a));return true}catch(e){return false}}
function toast(){var t=document.getElementById('vgtoast');if(!t){t=document.createElement('div');t.id='vgtoast';t.setAttribute('role','status');document.body.appendChild(t)}t.innerHTML='<span>تمت إضافة '+n+' للسلة</span><a href="/cart">عرض السلة</a>';t.classList.add('show');clearTimeout(t._h);t._h=setTimeout(function(){t.classList.remove('show')},4000)}
document.getElementById('addcart').onclick=function(){if(key&&add())toast()};
document.getElementById('buynow').onclick=function(){if(key&&add())location.href='/cart'};
})();
</script>
<script>/*vgProdEn*/(function(){
var en=/[?&]lang=en(?:&|$)/.test(location.search)||(function(){try{return localStorage.getItem('vg-lang')==='en'}catch(e){return false}})();
var lt=document.getElementById('langToggle');if(lt){lt.textContent=en?'عربي':'EN';lt.onclick=function(){try{localStorage.setItem('vg-lang',en?'ar':'en')}catch(e){}var u=new URL(location.href);if(en)u.searchParams.delete('lang');else u.searchParams.set('lang','en');location.href=u.toString()}}
if(!en)return;
var M={'← رجوع للمتجر':'← Back to shop','تبديل المظهر':'Toggle theme','الرئيسية':'Home','المواصفات':'Specifications','أضف للسلة':'Add to cart','اشترِ الآن':'Buy now','اطلب / اسأل عن المنتج على واتساب':'Order / ask on WhatsApp','اتصل بينا':'Call us','توصيل داخل القاهرة والجيزة':'Delivery in Cairo & Giza','ضمان رسمي وكيل معتمد':'Official dealer warranty','استرجاع سهل واستبدال سريع':'Easy returns & fast exchange','أسئلة شائعة':'FAQ','منتجات مشابهة':'Similar products','متوفر':'In stock','غير متوفر':'Out of stock','عرض السلة':'View cart','توصيل داخل القاهرة والجيزة · ضمان رسمي · اطلب عبر واتساب':'Delivery in Cairo & Giza · Official warranty · Order on WhatsApp','أقل':'Less','أكثر':'More','ملحقات':'Accessories','كاميرات سلكية HD':'HD wired cameras','كاميرات IP':'IP cameras','كاميرات واي فاي':'Wi-Fi cameras','مسجلات DVR':'DVR recorders','هارد ديسك':'Hard drives','كروت الميموري':'Memory cards','باور سبلاي':'Power supplies','الكابلات':'Cables','الراكات':'Racks','بتوصلوا فين وبياخد قد إيه؟':'Where do you deliver and how long does it take?','طرق الدفع إيه؟':'What are the payment methods?','أقدر أسترجع أو أستبدل المنتج؟':'Can I return or exchange the product?','بتركّبوا الكاميرات؟':'Do you install cameras?','إزاي أتابع طلبي؟':'How do I track my order?','واتساب:':'WhatsApp:','اتصال:':'Call:','© Vision Guard. جميع الأسعار بالجنيه المصري.':'© Vision Guard. All prices in EGP.'};
var h=document.documentElement;h.lang='en';h.dir='ltr';
var w=document.createTreeWalker(document.body,NodeFilter.SHOW_TEXT),n,list=[];while(n=w.nextNode())list.push(n);
list.forEach(function(t){var s=t.nodeValue,k=s.trim();if(k in M){t.nodeValue=s.replace(k,M[k]);return}if(/ج\.م/.test(s))t.nodeValue=s.replace(/ج\.م/g,'EGP')});
var C=['كاميرات سلكية HD','كاميرات IP','كاميرات واي فاي','مسجلات DVR','هارد ديسك','كروت الميموري','باور سبلاي','الكابلات','الراكات','ملحقات'];list.forEach(function(t){var s=t.nodeValue;if(!/[\u0600-\u06FF]/.test(s))return;C.forEach(function(c){if(s.indexOf(c)>=0)s=s.split(c).join(M[c])});t.nodeValue=s});
document.querySelectorAll('p,span,div,summary,li,h2,h3,h4,a').forEach(function(e){if(e.children.length===0&&/[\u0600-\u06FF]/.test(e.textContent)&&!/[A-Za-z]{4,}/.test(e.textContent.replace(/[A-Z0-9\-\/\.]+/g,''))){e.setAttribute('dir','rtl')}});
document.querySelectorAll('[aria-label],[title]').forEach(function(e){['aria-label','title'].forEach(function(a){var v=e.getAttribute(a);if(v&&(v in M))e.setAttribute(a,M[v])})});
document.querySelectorAll('.rcard h4[data-en]').forEach(function(e){if(e.getAttribute('data-en'))e.textContent=e.getAttribute('data-en')});
var h1=document.querySelector('h1'),me=h1&&h1.nextElementSibling;if(h1&&me&&me.classList.contains('meta')&&me.textContent.trim()){var ar=h1.textContent;h1.textContent=me.textContent.trim();me.textContent=ar}
document.title=document.title.replace(/^[^|]+\|/,(h1?h1.textContent.trim():'')+' |');
var d=document.querySelector('.pdt .desc, .desc');if(d){var p=document.createElement('p');p.className='meta';p.textContent='Full English details for this product are available on WhatsApp.';d.parentNode.insertBefore(p,d.nextSibling)}
})();</script><style>/*vgsticky*/
.pic #mainImg{cursor:zoom-in}
#vglb{position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,.88);display:none;align-items:center;justify-content:center;padding:16px}
#vglb.on{display:flex}
#vglb img{max-width:100%;max-height:100%;object-fit:contain;border-radius:8px;background:#fff}
#vglb button{position:absolute;top:12px;inset-inline-end:12px;width:44px;height:44px;border-radius:50%;border:0;background:#fff;color:#111;font-size:22px;cursor:pointer}
.buybox .vgwa{display:none}
@media(max-width:760px){.buybox .vgwa{display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:12px;background:#25d366;color:#04200f;flex:0 0 auto}}
</style>
<div id="vglb" role="dialog" aria-modal="true" aria-label="صورة المنتج"><button type="button" aria-label="إغلاق">&times;</button><img alt=""></div>
<script>/*vgsticky*/
(function(){
var bb=document.querySelector('.buybox'),wa=document.querySelector('.btns .btn.pri');
if(bb&&wa){var a=document.createElement('a');a.className='vgwa';a.target='_blank';a.rel='noopener';a.href=wa.href;a.setAttribute('aria-label','واتساب');a.innerHTML='<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.2 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .5l-.4.6c-.1.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.8-1c.2-.3.4-.2.6-.1l2 .9c.3.1.5.2.5.3.1.2.1.6-.1 1.2z"/></svg>';bb.appendChild(a);
function l(){a.setAttribute('aria-label',document.documentElement.lang==='en'?'WhatsApp':'واتساب')}l();new MutationObserver(l).observe(document.documentElement,{attributes:true,attributeFilter:['lang']})}
var lb=document.getElementById('vglb'),li=lb.querySelector('img');
document.addEventListener('click',function(e){var m=e.target.closest&&e.target.closest('#mainImg');if(!m)return;li.src=m.src;li.alt=m.alt;lb.classList.add('on');lb.querySelector('button').focus()});
lb.addEventListener('click',function(){lb.classList.remove('on')});
document.addEventListener('keydown',function(e){if(e.key==='Escape')lb.classList.remove('on')});
})();
</script>
<script>/*vgft*/(function(){try{var sp=new URLSearchParams(location.search),h='';try{h=document.referrer?new URL(document.referrer).hostname:''}catch(e){}var me=location.hostname,ext=h&&h.indexOf(me)<0&&me.indexOf(h)<0,u=sp.get('utm_source')||'',fb=sp.has('fbclid'),old=null;try{old=JSON.parse(localStorage.getItem('vgft')||'null')}catch(e){}if(u||fb||ext||!old||Date.now()-old.t>2592000000)localStorage.setItem('vgft',JSON.stringify({ref:ext?h:'',utm:u,fb:fb,t:Date.now()}))}catch(e){}})();</script>
</body>
</html>
