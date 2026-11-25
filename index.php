<?php
require_once __DIR__ . '/admin/includes/db.php';
require_once __DIR__ . '/api/_stock.php';
vg_schema_v2();

function vgtop($k, $d) { $v = trim((string)setting($k, $d)); return strpos($v, '01105006854') === false ? $v . ' 01105006854' : $v; }
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function vg_img_versions() { $v = []; foreach (glob(__DIR__ . '/images/p*.jpg') ?: [] as $f) { if (preg_match('/^(p\d+)\.jpg$/', basename($f), $m)) { $v[$m[1]] = @filemtime($f); } } return (object)$v; }

function wa_local($intl) {
    $intl = preg_replace('/\\D/', '', (string)$intl);
    return (strpos($intl, '20') === 0) ? '0' . substr($intl, 2) : $intl;
}
function tel_href($local) {
    $local = preg_replace('/\\D/', '', (string)$local);
    return '+20' . ltrim($local, '0');
}

function wa_href($raw) {
	return ltrim(tel_href($raw), '+');
}

function products_max_price() {
    $v = (float) db()->query('SELECT COALESCE(MAX(price),0) m FROM products')->fetch()['m'];
    return (int) (ceil(($v > 0 ? $v : 8200) / 100) * 100);
}

function products_js_array() {
    $rows = db()->query('SELECT id,slug,category,placement,brand,name_ar,name_en,price,icon,in_stock,stock_qty FROM products ORDER BY sort_order ASC, id ASC')->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            $r['category'], $r['placement'], $r['brand'], $r['name_ar'], $r['name_en'],
            (float)$r['price'], $r['icon'], vg_stock_available($r) ? 1 : 0, (int)$r['id'], (string)($r['slug'] ?? ''),
            vg_stock_left($r),
        ];
    }
    return json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
}

function offers_js_array() {
    $rows = db()->query('SELECT id, discount_percent FROM products WHERE discount_percent IS NOT NULL AND discount_percent > 0')->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[] = ['on' => true, 'sc' => 'prod', 'tg' => 'p' . $r['id'], 'k' => 'pct', 'v' => (int)$r['discount_percent']];
    }
    return json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
}
?>
<?php $vgEn=(($_GET['lang']??'')==='en');
$vgPages=[
'home'=>['t_ar'=>'Vision Guard | كاميرات المراقبة','t_en'=>'Vision Guard | CCTV & Surveillance Cameras','d_ar'=>'Vision Guard | كاميرات مراقبة وأنظمة أمان: كاميرات IP وواي فاي وسلكية، مسجلات DVR، هارد ديسك وملحقات بأسعار واضحة.','d_en'=>'Vision Guard | CCTV and security systems: IP, Wi-Fi and wired cameras, DVR recorders, hard drives and accessories at clear prices.','h_ar'=>'Vision Guard | كاميرات مراقبة وأنظمة أمان في مصر: كاميرات IP وواي فاي، مسجلات DVR، هارد ديسك وملحقات','h_en'=>'Vision Guard | CCTV and surveillance cameras in Egypt: IP, Wi-Fi and wired cameras, DVR recorders, hard drives and accessories'],
'shop'=>['t_ar'=>'المتجر | كل منتجات كاميرات المراقبة | Vision Guard','t_en'=>'Shop | All CCTV Products | Vision Guard','d_ar'=>'تسوق كل منتجات Vision Guard: كاميرات واي فاي و IP و HD، مسجلات DVR، هارد ديسك، كابلات وملحقات، مع فلترة بالقسم والماركة والسعر.','d_en'=>'Shop all Vision Guard products: Wi-Fi, IP and HD cameras, DVR recorders, hard drives, cables and accessories. Filter by category, brand and price.','h_ar'=>'متجر Vision Guard: كاميرات مراقبة، مسجلات DVR وملحقات','h_en'=>'Vision Guard shop: CCTV cameras, DVR recorders and accessories'],
'planner'=>['t_ar'=>'مخطط تغطية الكاميرات | Vision Guard','t_en'=>'Camera Coverage Planner | Vision Guard','d_ar'=>'ارسم مخطط المكان وحدد أماكن الكاميرات وشوف نسبة التغطية، واحصل على ترشيح بنظام المراقبة المناسب لبيتك أو شغلك.','d_en'=>'Draw your floor plan, place cameras and see the coverage percentage, then get a recommended surveillance system for your home or business.','h_ar'=>'مخطط تغطية كاميرات المراقبة','h_en'=>'CCTV camera coverage planner'],
'about'=>['t_ar'=>'من نحن | Vision Guard','t_en'=>'About Us | Vision Guard','d_ar'=>'تعرف على Vision Guard: متجر كاميرات مراقبة وأنظمة أمان في حدائق أكتوبر بالجيزة، بنساعدك تختار النظام المناسب.','d_en'=>'About Vision Guard: a CCTV and security systems store in October Gardens, Giza, helping you pick the right system.','h_ar'=>'من نحن - Vision Guard','h_en'=>'About Vision Guard'],
'faq'=>['t_ar'=>'الأسئلة الشائعة | Vision Guard','t_en'=>'FAQ | Vision Guard','d_ar'=>'إجابات الأسئلة الشائعة عن الطلب والشحن والدفع والضمان واختيار كاميرات المراقبة من Vision Guard.','d_en'=>'Answers to common questions about ordering, shipping, payment, warranty and choosing CCTV cameras from Vision Guard.','h_ar'=>'الأسئلة الشائعة - Vision Guard','h_en'=>'Frequently asked questions - Vision Guard'],
'contact'=>['t_ar'=>'اتصل بنا | Vision Guard','t_en'=>'Contact Us | Vision Guard','d_ar'=>'تواصل مع Vision Guard بالتليفون أو واتساب أو ابعت رسالة. مواعيد العمل من السبت للخميس 10 صباحًا إلى 8 مساءً.','d_en'=>'Contact Vision Guard by phone, WhatsApp or message. Open Saturday to Thursday, 12:00 to 20:00.','h_ar'=>'اتصل بنا - Vision Guard','h_en'=>'Contact Vision Guard'],
'cart'=>['t_ar'=>'سلة الطلب | Vision Guard','t_en'=>'Your Cart | Vision Guard','d_ar'=>'راجع منتجات سلتك وأكمل طلبك من Vision Guard.','d_en'=>'Review your cart and complete your Vision Guard order.','h_ar'=>'سلة الطلب','h_en'=>'Your cart'],
'account'=>['t_ar'=>'حسابي | Vision Guard','t_en'=>'My Account | Vision Guard','d_ar'=>'حسابك وطلباتك في Vision Guard.','d_en'=>'Your Vision Guard account and orders.','h_ar'=>'حسابي وطلباتي','h_en'=>'My account and orders'],
];
$vgPage=(string)($_GET['page']??'home'); if(!isset($vgPages[$vgPage])) $vgPage='home';
$vgP=$vgPages[$vgPage]; $vgUrl='https://visionguardeg.com'.($vgPage==='home'?'/':'/'.$vgPage);
if(in_array($vgPage,['cart','account'],true)) header('X-Robots-Tag: noindex, follow');
$vgAll=['vgHero','vgtrust','vgspaces','bestsellers','homeprices','homecats','hometiles','shop','planner','about','faq','contact','cartpage','acctpage'];
$vgShow=['home'=>['vgHero','vgtrust','vgspaces','bestsellers','shop','planner'],'shop'=>['shop'],'planner'=>['planner'],'about'=>['about'],'faq'=>['faq'],'contact'=>['contact'],'cart'=>['cartpage'],'account'=>['acctpage']];
$vgHide=array_diff($vgAll,$vgShow[$vgPage]);
?>
<!doctype html>
<html lang="<?php echo $vgEn?'en':'ar'; ?>" dir="<?php echo $vgEn?'ltr':'rtl'; ?>">
<head><meta charset="utf-8"><script>(function(){var r=document.documentElement,s=localStorage.getItem('vg-theme'),t=s||((window.matchMedia&&matchMedia('(prefers-color-scheme: dark)').matches)?'dark':'light');r.setAttribute('data-theme',t);document.addEventListener('DOMContentLoaded',function(){var b=document.getElementById('themeToggle');if(b){b.addEventListener('click',function(){var c=r.getAttribute('data-theme')==='dark'?'light':'dark';r.setAttribute('data-theme',c);try{localStorage.setItem('vg-theme',c)}catch(e){}})}});})();</script>

<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?php echo h($vgEn?$vgP['t_en']:$vgP['t_ar']); ?></title>
<meta name="description" content="<?php echo h($vgEn?$vgP['d_en']:$vgP['d_ar']); ?>">
<link rel="canonical" href="<?php echo $vgUrl.($vgEn?'?lang=en':''); ?>"><link rel="alternate" hreflang="ar" href="<?php echo $vgUrl; ?>"><link rel="alternate" hreflang="en" href="<?php echo $vgUrl; ?>?lang=en"><link rel="alternate" hreflang="x-default" href="<?php echo $vgUrl; ?>">
<meta name="theme-color" content="#0667a5">
<meta property="og:type" content="website"><meta property="og:url" content="<?php echo $vgUrl.($vgEn?'?lang=en':''); ?>"><meta property="og:site_name" content="Vision Guard">
<meta property="og:title" content="<?php echo h($vgEn?$vgP['t_en']:$vgP['t_ar']); ?>"><meta property="og:description" content="<?php echo h($vgEn?$vgP['d_en']:$vgP['d_ar']); ?>">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="apple-touch-icon" href="/apple-touch-icon.png">
<meta property="og:image" content="https://visionguardeg.com/og-image.png?v=<?= (int)@filemtime(__DIR__ . '/og-image.png') ?>"><meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image"><meta name="twitter:image" content="https://visionguardeg.com/og-image.png?v=<?= (int)@filemtime(__DIR__ . '/og-image.png') ?>">
<meta property="og:locale" content="<?php echo $vgEn?'en_US':'ar_EG'; ?>"><meta property="og:locale:alternate" content="en_US">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;800&display=swap">
<script type="application/ld+json">{"@context":"https://schema.org","@type":"ElectronicsStore","name":"Vision Guard","url":"https://visionguardeg.com/","image":"https://visionguardeg.com/og-image.png","telephone":"+201260087815","@id":"https://visionguardeg.com/#store","openingHoursSpecification":[{"@type":"OpeningHoursSpecification","dayOfWeek":["Saturday","Sunday","Monday","Tuesday","Wednesday","Thursday"],"opens":"12:00","closes":"20:00"}],"description":"Vision Guard sells CCTV cameras (IP, WiFi, wired HD), DVR/NVR recorders, hard disks and security accessories in Egypt, with delivery in Cairo and Giza.","priceRange":"EGP","currenciesAccepted":"EGP","paymentAccepted":"Cash on Delivery, InstaPay, Mobile Wallet","areaServed":[{"@type":"City","name":"Cairo"},{"@type":"City","name":"Giza"}],"contactPoint":[{"@type":"ContactPoint","telephone":"+201260087815","contactType":"customer service","availableLanguage":["ar","en"]},{"@type":"ContactPoint","telephone":"+201105006854","contactType":"sales","availableLanguage":["ar","en"]}],"address":{"@type":"PostalAddress","addressLocality":"October Gardens","addressRegion":"Giza","addressCountry":"EG"},"sameAs":["https://www.facebook.com/visionguardegypt","https://www.instagram.com/visionguardegypt"]}</script><script type="application/ld+json">{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"إزاي أطلب؟","acceptedAnswer":{"@type":"Answer","text":"ضيف المنتجات للسلة واضغط «اطلب عبر واتساب»، هتتفتح محادثة معانا فيها طلبك جاهز، ونأكد معاك التوفر والسعر النهائي."}},{"@type":"Question","name":"إزاي أعرف أي كاميرا تناسبني؟","acceptedAnswer":{"@type":"Answer","text":"جرّب مخطط التغطية: ارفع صورة المكان وحدد أماكن الكاميرات وشوف نسبة التغطية. وابعتلنا المخطط على واتساب نساعدك في الاختيار."}},{"@type":"Question","name":"الأسعار بالجنيه المصري؟","acceptedAnswer":{"@type":"Answer","text":"أيوه، كل الأسعار بالجنيه المصري. الأسعار والتوفر ممكن يتغيروا، فبنأكد السعر النهائي معاك على واتساب قبل الطلب."}},{"@type":"Question","name":"إزاي الشحن والتوصيل؟","acceptedAnswer":{"@type":"Answer","text":"شحن سريع لكل محافظة. اكتبلنا محافظتك على واتساب ونرد عليك بتفاصيل التوصيل والمدة."}},{"@type":"Question","name":"إيه ضمان المنتجات؟","acceptedAnswer":{"@type":"Answer","text":"الضمان بيختلف من منتج لتاني، فاسألنا عن المنتج اللي يهمك على واتساب قبل الطلب ونوضحلك."}}]}</script>
<style>
<?php readfile(__DIR__ . '/inc/index.css'); ?>
</style>
<style>
header .cart{position:relative;display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;padding:0;border-radius:10px;font-size:0}
header .cart .cbadge{position:absolute;top:-7px;inset-inline-end:-7px;min-width:19px;height:19px;padding:0 5px;border-radius:10px;background:#05b3e9;color:#0b1b3a;font-size:11px;font-weight:800;line-height:19px;text-align:center;box-sizing:border-box;border:2px solid var(--bg,#fff)}
header .acct{position:relative;display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;padding:0;border-radius:10px;border:1px solid var(--border);background:var(--bg);color:var(--text);cursor:pointer}
header .acct .adot{display:none;position:absolute;top:5px;inset-inline-end:5px;width:9px;height:9px;border-radius:50%;background:#22c55e;border:2px solid var(--bg,#fff)}
header .acct.on .adot{display:block}
header .hbg{width:40px;height:40px;border-radius:10px;border:1px solid var(--border);background:var(--bg);color:var(--text);display:inline-flex;align-items:center;justify-content:center;padding:0;cursor:pointer}
header .qcat{flex:0 0 auto;max-width:150px;border:0;border-inline-end:1px solid var(--border);background:var(--bg);color:var(--text);font:inherit;font-weight:700;font-size:14px;padding:0 10px;cursor:pointer;border-radius:0;outline:0;text-overflow:ellipsis}
@media(max-width:640px){header .qcat{max-width:98px;font-size:12px;padding:0 4px}}
@media(max-width:640px){.bsrow{gap:12px;scroll-padding-inline:0}.bsrow .card{flex:0 0 100%;width:100%;min-width:0;box-sizing:border-box;scroll-snap-align:center;scroll-snap-stop:always}.bsrow .pic img{max-height:210px;width:100%;object-fit:contain}}
.bsnav{display:none}
@media(max-width:640px){.bsnav{display:flex;align-items:center;justify-content:center;gap:14px;margin-top:6px}
.bestsellers{position:relative}.bsarr{position:absolute;top:150px;z-index:3;width:36px;height:36px;border-radius:50%;border:1px solid var(--border);background:var(--bg);color:var(--text);display:inline-flex;align-items:center;justify-content:center;padding:0;cursor:pointer;box-shadow:0 3px 10px rgba(0,0,0,.2);opacity:.95}.bsarr:first-child{inset-inline-start:4px}.bsarr:last-child{inset-inline-end:4px}
.bsarr:disabled{opacity:.35;cursor:default}
@media(max-width:600px){.bsarr{width:30px;height:30px;opacity:.8}.bsarr:first-child{inset-inline-start:0}.bsarr:last-child{inset-inline-end:0}}
.bsdots{display:flex;gap:7px;align-items:center}
.bsdots button{width:8px;height:8px;padding:0;border:0;border-radius:99px;background:var(--border);cursor:pointer;transition:width .2s,background .2s}
.bsdots button.on{width:22px;background:#0667a5}}
</style><style>:root:not([data-theme="dark"]) .pchip{background:rgba(0,40,80,.35)!important;color:#fff!important;border:1px solid rgba(255,255,255,.35)}</style>
<style>footer .fcols>div>a{padding:4px 0;box-sizing:border-box}:root:not([data-theme="dark"]) #pv{color:#00698f}</style>
<link rel="manifest" href="/manifest.webmanifest">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Vision Guard">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link rel="stylesheet" href="/pwa.css">
<style id="vgpages"><?php echo '#'.implode(',#',$vgHide).'{display:none!important}'; ?>
/* burger drawer must sit above the sticky header/search */
.dr{z-index:100050}
/* burger: products group */
.drl details:not([open])>summary::after{content:'+'}.drl details[open]>summary::after{content:'\2212'}:root[data-theme="dark"] .drl summary{color:var(--text)}@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) .drl summary{color:var(--text)}}
.drl details.nvprod>a{padding-inline-start:28px;font-weight:700;color:var(--text)}.drl details details>summary{padding-inline-start:28px;font-weight:600}.drl details details a{padding-inline-start:48px}


/* add-to-cart + pressed state */
:root[data-theme="dark"] .card button[data-n]:not([disabled]){background:#1a37b5;color:#fff;border-color:#1a37b5}:root[data-theme="dark"] #cart{background:var(--bg)}:root[data-theme="dark"] .card button[data-n]:not([disabled]):hover{background:#2745cf}:root[data-theme="dark"] .card button[data-n]:not([disabled]):active{background:#11297f;transform:scale(.97)}:root[data-theme="dark"] :is(#acct,#bg,#lang,#themeToggle,#cart,#vg-nav a):active{background:#1a37b5;color:#fff}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) .card button[data-n]:not([disabled]){background:#1a37b5;color:#fff;border-color:#1a37b5}:root:not([data-theme="light"]) #cart{background:var(--bg)}:root:not([data-theme="light"]) .card button[data-n]:not([disabled]):hover{background:#2745cf}:root:not([data-theme="light"]) .card button[data-n]:not([disabled]):active{background:#11297f;transform:scale(.97)}:root:not([data-theme="light"]) :is(#acct,#bg,#lang,#themeToggle,#cart,#vg-nav a):active{background:#1a37b5;color:#fff}}
/* contrast fixes */
:root[data-theme="dark"] #pv{color:#8fe0ff!important}@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) #pv{color:#8fe0ff!important}}
.catrow .cr.on,.catrow button.cr.on{color:#04202e!important}.stock{color:#0b7a3b}
:root[data-theme="dark"] .stock{color:#6ef0ac}:root[data-theme="dark"] .pf-sum,:root[data-theme="dark"] .fmore,:root[data-theme="dark"] .pf-sum,:root[data-theme="dark"] #resetf,:root[data-theme="dark"] summary,:root[data-theme="dark"] #vg-nav a.on{color:#8fe0ff}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) .stock{color:#6ef0ac}:root:not([data-theme="light"]) .fmore,:root:not([data-theme="light"]) .pf-sum,:root:not([data-theme="light"]) #resetf,:root:not([data-theme="light"]) summary,:root:not([data-theme="light"]) #vg-nav a.on{color:#8fe0ff}}

footer .fbar{padding-bottom:20px}footer .paym{gap:10px;font-size:13px}footer .pchip{padding:6px 14px}

:root[data-theme="dark"] footer{background:var(--bg)!important;border-top:1px solid var(--border)}@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) footer{background:var(--bg)!important;border-top:1px solid var(--border)}}

#shop>*{min-width:0}.catrow{width:100%;max-width:100%;box-sizing:border-box;display:flex;gap:8px;overflow-x:auto;padding:2px 0 12px;margin:0 0 4px;scrollbar-width:none;-webkit-overflow-scrolling:touch}.catrow::-webkit-scrollbar{display:none}.catrow .cr{flex:0 0 auto;border:1px solid var(--border);background:var(--surface);color:var(--text);border-radius:999px;padding:9px 16px;font:inherit;font-weight:700;cursor:pointer;white-space:nowrap}.catrow .cr small{opacity:.7;font-weight:400}.catrow .cr.on{background:var(--accent);border-color:var(--accent);color:#fff}
/* cleaner cards */
.card .meta{display:none}.card h3{margin-top:10px}
.card{min-width:0;overflow:hidden}.card .meta,.card h3{min-width:0;max-width:100%}#grid,#bsgrid{min-width:0}
.card .meta{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:12px}.card h3{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.45;min-height:2.9em;font-size:14.5px}.card .stock{font-size:12px;margin:2px 0 8px}.card .price{margin:4px 0 2px}
.hpbox{max-width:560px;margin:0 auto 18px;background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:14px 16px}
.hpbar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:0 0 14px}.hpc{border:1px solid var(--border);background:var(--surface);color:var(--text);border-radius:999px;padding:8px 14px;font:inherit;cursor:pointer}.hpc.on{background:var(--accent);color:#fff;border-color:var(--accent)}.hpin{display:flex;gap:6px;align-items:center;margin-inline-start:auto}.hpin input{width:84px;padding:8px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font:inherit}.hpin button{background:var(--accent);color:#fff;border:0;border-radius:8px;padding:8px 14px;font:inherit;cursor:pointer}.hpall{display:inline-block;padding:10px 22px;border-radius:999px;border:1px solid var(--accent);color:var(--accent);text-decoration:none;font-weight:700}.hpgrid{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px;overflow:visible!important}

/* lighter dark theme */
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]){--brand:#3d5279;--bg:#2e4160;--surface:#3d5279;--text:#ffffff;--muted:#cdd6e4;--border:#5d7399}}
:root[data-theme="dark"]{--brand:#3d5279;--bg:#2e4160;--surface:#3d5279;--text:#ffffff;--muted:#cdd6e4;--border:#5d7399}
/* white logo in dark mode */
.logo .wmd{display:none}
:root[data-theme="dark"] .logo .wm{display:none}
:root[data-theme="dark"] .logo .wmd{display:block!important;background:none!important;padding:0!important;border-radius:0!important;width:auto;height:52px}
main.wrap{min-height:40vh}
body:not([data-page="home"]) main.wrap{padding-top:18px}
body:not([data-page="home"]):not([data-page="shop"]):not([data-page="planner"]) main.wrap{min-height:0;padding-top:0}
body:not([data-page="home"]):not([data-page="shop"]):not([data-page="planner"]) main.wrap+.wrap{padding-top:22px;min-height:46vh}
.vgpg h2{font-size:22px;margin:0 0 12px}
.vgpg .vgsub{color:var(--muted);margin:0 0 16px}
#hometiles{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:0 0 22px}
#hometiles a{display:block;background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:18px;transition:transform .2s,border-color .2s}
#hometiles a:hover{transform:translateY(-2px);border-color:var(--accent)}
#hometiles b{display:block;font-size:17px;margin-bottom:4px}
#hometiles span{color:var(--muted);font-size:14px}
@media(max-width:760px){#hometiles{grid-template-columns:1fr}}
.vgcgrid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
@media(max-width:760px){.vgcgrid{grid-template-columns:1fr}}
.vgcl a,.vgcl div{display:flex;align-items:center;gap:10px;padding:12px 14px;border:1px solid var(--border);border-radius:12px;margin-bottom:10px;background:var(--bg);font-weight:700}
.vgcl small{display:block;font-weight:400;color:var(--muted)}
.vgform input,.vgform textarea{width:100%;box-sizing:border-box;padding:12px;margin:0 0 10px;border:1px solid var(--border);border-radius:10px;background:var(--bg);color:var(--text);font:inherit;font-size:16px}
.vgform textarea{min-height:120px;resize:vertical}
#cartpage #cdc{max-width:640px;margin:0 auto}
#cartpage .ci{font-size:15px;padding:12px 0}
.vgord{border:1px solid var(--border);border-radius:12px;padding:12px 14px;margin-bottom:10px;background:var(--bg)}
.vgord .vgoh{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;font-weight:800}
.vgord ul{margin:8px 0 0;padding-inline-start:18px;color:var(--muted);font-size:14px}
.vgtag{display:inline-block;padding:2px 10px;border-radius:999px;background:var(--accent);color:var(--on-accent);font-size:12px;font-weight:800}
</style>
<style>/*vgfoot*/@media(max-width:700px){footer{padding-top:28px}.fgrid{grid-template-columns:1fr;gap:16px}.fc:has(.fl){display:grid;grid-template-columns:1fr 1fr;column-gap:14px}.fc:has(.fl) h3{grid-column:1/-1}.fb p{margin-bottom:12px;max-width:none}.fgrid h3{margin-bottom:6px}.fc a.fl{padding:5px 0}.fc a.fct{padding:5px 0;gap:8px}.fc a.fct span{white-space:normal}}</style><style>/*vgtrust*/
.vgtrust{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:18px 0}
.vgtrust .tri{display:flex;align-items:center;gap:12px;padding:14px;border:1px solid var(--border);border-radius:14px;background:var(--surface);color:var(--text)}
.vgtrust .tri svg{flex:none;color:var(--accent,#05b3e9)}
.vgtrust b{display:block;font-size:14px}.vgtrust small{display:block;font-size:12px;color:var(--muted);margin-top:2px}
.vgspaces{margin:26px 0}.vgspaces h2{margin:0 0 4px}.vgspaces .spsub{margin:0 0 14px;color:var(--muted);font-size:14px}
.sprow{display:grid;grid-template-columns:repeat(5,1fr);gap:12px}
.sprow .spc{display:flex;flex-direction:column;align-items:center;gap:10px;padding:18px 8px;border:1px solid var(--border);border-radius:14px;background:var(--surface);color:var(--text);font-weight:700;font-size:14px;text-align:center;transition:transform .2s,border-color .2s}
.sprow .spc svg{width:30px;height:30px;color:var(--accent,#05b3e9)}
.sprow .spc:hover{transform:translateY(-2px);border-color:var(--accent,#05b3e9)}
@media(max-width:800px){.vgtrust{grid-template-columns:1fr 1fr;gap:10px}.vgtrust .tri{padding:11px;gap:9px}.sprow{display:flex;overflow-x:auto;gap:10px;padding-bottom:6px;scroll-snap-type:x proximity}.sprow .spc{flex:none;width:112px;scroll-snap-align:start}}
</style><style>/*vgaudit*/html[lang="ar"] h1,html[lang="ar"] h2,html[lang="ar"] h3,html[lang="ar"] h4,html[lang="ar"] small,html[lang="ar"] .tools h2{letter-spacing:normal!important}.card h3,.card h3 a{line-height:1.6}.mnav button{font-size:12px}.search button{cursor:pointer}#plock{width:24px;height:24px;margin:0 4px;cursor:pointer}.bsdots button{box-sizing:content-box;padding:8px;background-clip:content-box;margin:0}label[for],label:has(input[type="checkbox"]){min-height:24px;display:inline-flex;align-items:center;cursor:pointer}#planner input[type="range"],.pf input[type="range"]{height:24px}#planner input[type="range"]::-webkit-slider-thumb,.pf input[type="range"]::-webkit-slider-thumb{width:24px;height:24px}</style><style id="vg-unslop-site">#wab,.wab{animation:none !important}.price,.cbadge,.pf-sum{font-weight:700 !important;font-variant-numeric:tabular-nums}.lang,.fmore{font-weight:600 !important}aside h3,.filters h3,#pft{font-weight:700 !important;text-transform:none !important;letter-spacing:0 !important}</style>
<style id="vg-cart-has">.search button:not(#mnSearch){background:#0667a5 !important;color:#fff !important;border-color:transparent !important}.cart{transition:background-color .2s,color .2s}.cart:not(.has){background:#eef1f5 !important;color:#51607a !important;border:1px solid #e1e7ef !important}.cart:not(.has) .cbadge{background:#dfe5ee !important;color:#51607a !important}.cart.has{background:#0667a5 !important;color:#fff !important}</style>
<script>(function(){function s(){var c=document.getElementById("cart");if(!c)return;var b=c.querySelector(".cbadge");c.classList.toggle("has",!!b&&(parseInt(b.textContent,10)||0)>0)}document.addEventListener("DOMContentLoaded",function(){var c=document.getElementById("cart");if(!c)return;s();new MutationObserver(s).observe(c,{childList:true,characterData:true,subtree:true});setInterval(s,500)})})()</script>
</head>
<body data-page="<?php echo h($vgPage); ?>"><h1 id="h1" style="position:absolute;width:1px;height:1px;margin:-1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0"><?php echo h($vgEn?$vgP['h_en']:$vgP['h_ar']); ?></h1>
<svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs><linearGradient id="vgGrad" x1="0" y1="0" x2="64" y2="64" gradientUnits="userSpaceOnUse"><stop stop-color="#06b6d4"/><stop offset="1" stop-color="#0667a5"/></linearGradient></defs><symbol id="vg" viewBox="0 0 64 64"><rect width="64" height="64" rx="14" fill="#0667a5/><path d="M32 8 L52 16 V32 C52 44 43 52 32 56 C21 52 12 44 12 32 V16 Z" stroke="url(#vgGrad)" stroke-width="3" fill="none"/><circle cx="32" cy="30" r="9" stroke="url(#vgGrad)" stroke-width="3" fill="none"/><circle cx="32" cy="30" r="3.5" fill="url(#vgGrad)"/><path d="M20 44 H44" stroke="url(#vgGrad)" stroke-width="2.5" stroke-linecap="round" opacity="0.7"/><path d="M24 48 H40" stroke="url(#vgGrad)" stroke-width="2.5" stroke-linecap="round" opacity="0.4"/></symbol><defs><linearGradient id="wmv" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#22aef2"/><stop offset="1" stop-color="#0a8bd3"/></linearGradient><linearGradient id="wmsw" x1="0" y1="0" x2="1" y2="0"><stop stop-color="#444546"/><stop offset=".72" stop-color="#5a5b5c"/><stop offset="1" stop-color="#a4a5a6"/></linearGradient><linearGradient id="wmln" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#0d7fc2"/><stop offset="1" stop-color="#0a5f9c"/></linearGradient></defs><symbol id="wm" viewBox="0 -11.7 392 251.4"><image href="/images/vg-logo.png?v=1" width="392" height="228"/></symbol></svg>
<div class="top" id="topbar"><div class="wrap top-in"><div class="top-msgs"><span class="tm on"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l8 3v6c0 4.5-3.2 8-8 9-4.8-1-8-4.5-8-9V6z"/><path d="M9 12l2 2 4-4"/></svg><span class="t-ar">أنظمة مراقبة للمنازل والأعمال</span><span class="t-en">Security systems for homes &amp; businesses</span></span><span class="tm"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.7 2.7L16 9.5"/></svg><span class="t-ar">استشارة مجانية قبل الشراء</span><span class="t-en">Free consultation before you buy</span></span><span class="tm"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.6 2.6 0 115 .8c0 1.7-2.5 2-2.5 3.7M12 17h.01"/></svg><span class="t-ar">نساعدك تختار النظام المناسب</span><span class="t-en">We help you choose the right system</span></span><span class="tm"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7.5V12l3 2"/></svg><span class="t-ar">السبت – الخميس، 10 ص – 8 م</span><span class="t-en">Open Sat – Thu, 10 AM – 8 PM</span></span></div><div class="top-cta"><a class="tc tc-call" href="tel:+201260087815"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/></svg><bdi class="tn" dir="ltr">01260087815</bdi></a><a class="tc tc-wa" href="https://wa.me/201105006854" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20l1.3-4A8 8 0 1112 20a8 8 0 01-3.9-1z"/><path d="M9 9.5c.3 2 2.5 4.2 5 5"/></svg><span class="t-ar">واتساب</span><span class="t-en">WhatsApp</span> <bdi class="tn" dir="ltr">01105006854</bdi></a></div></div></div>
<header><div class="wrap bar">
  
  <a class="logo" href="/" aria-label="Vision Guard"><svg class="wm" width="81" height="52" role="img" aria-label="Vision Guard"><use href="#wm"/></svg><img class="wmd" src="/images/vg-logo-footer.png" width="88" height="52" alt="Vision Guard"></a><button type="button" class="cart" id="cart"></button><button type="button" class="acct" id="acct" aria-label="Account"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg><span class="adot"></span></button>
  <div class="search"><button type="button" class="burger" id="bg" aria-label="Menu" aria-expanded="false"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button><input id="q" data-p="ph" aria-label="Search"><button type="button" data-t="search"></button></div>
  <button type="button" class="lang" id="lang">EN</button><button type="button" class="theme" id="themeToggle" aria-label="Toggle theme"><svg class="i-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg><svg class="i-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg></button>
</div></header>
<nav><div class="wrap"><a href="/" data-t="mnHome"<?php echo $vgPage==='home'?' class="on"':''; ?>></a><a href="/shop" data-t="n1"<?php echo $vgPage==='shop'?' class="on"':''; ?>></a><details class="nvcam"><summary data-t="ncam"></summary><a href="/shop?c=cat_hd" data-t="cat_hd"></a><a href="/shop?c=cat_ip" data-t="cat_ip"></a><a href="/shop?c=cat_wf" data-t="cat_wf"></a></details><details class="nvcam"><summary data-t="cat_dvr"></summary><a href="/shop?c=cat_dvr&amp;b=Dahua">Dahua</a><a href="/shop?c=cat_dvr&amp;b=Uniview">Uniview</a></details><details class="nvcam"><summary data-t="cat_acc"></summary><a href="/shop?c=cat_hdd" data-t="cat_hdd"></a><a href="/shop?c=cat_mem" data-t="cat_mem"></a><a href="/shop?c=cat_psu" data-t="cat_psu"></a><a href="/shop?c=cat_cab" data-t="cat_cab"></a><a href="/shop?c=cat_rack" data-t="cat_rack"></a><a href="/shop?c=cat_acc" data-t="cat_acc"></a><a href="/shop?c=cat_net" data-t="cat_net"></a><a href="/shop?c=cat_att" data-t="cat_att"></a></details><a href="/planner" data-t="n7"<?php echo $vgPage==='planner'?' class="on"':''; ?>></a><a href="/shop?offers=1" data-t="n5"></a><a href="/about" data-t="n8"<?php echo $vgPage==='about'?' class="on"':''; ?>></a><a href="/faq" data-t="n9"<?php echo $vgPage==='faq'?' class="on"':''; ?>></a><a href="/contact" data-t="n6"<?php echo $vgPage==='contact'?' class="on"':''; ?>></a></div></nav>

<main class="wrap">
<?php include __DIR__ . '/hero_block.php'; ?>
  <section class="vgtrust reveal" id="vgtrust">
    <div class="tri"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><div><b data-t="tr1"></b><small data-t="tr1s"></small></div></div>
    <div class="tri"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg><div><b data-t="tr2"></b><small data-t="tr2s"></small></div></div>
    <div class="tri"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11v2a2 2 0 0 0 2 2h1v-6H5a2 2 0 0 0-2 2z"/><path d="M21 11v2a2 2 0 0 1-2 2h-1v-6h1a2 2 0 0 1 2 2z"/><path d="M6 9a6 6 0 0 1 12 0"/></svg><div><b data-t="tr3"></b><small data-t="tr3s"></small></div></div>
    <div class="tri"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 6-9 12-9 12S3 16 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg><div><b data-t="tr4"></b><small data-t="tr4s"></small></div></div>
  </section>
  <section class="vgspaces reveal" id="vgspaces">
    <h2 data-t="sp0"></h2><p class="spsub" data-t="sp0s"></p>
    <div class="sprow">
      <a class="spc" href="/planner?t=apt"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21V9l9-6 9 6v12z"/><path d="M9 21v-6h6v6"/></svg><span data-t="sp1"></span></a>
      <a class="spc" href="/planner?t=villa"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-5h6v5"/><path d="M9 11h6"/></svg><span data-t="sp2"></span></a>
      <a class="spc" href="/planner?t=shop"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l1-5h16l1 5"/><path d="M4 9v11h16V9"/><path d="M9 20v-6h6v6"/></svg><span data-t="sp3"></span></a>
      <a class="spc" href="/planner?t=office"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="1"/><path d="M9 8h2M13 8h2M9 12h2M13 12h2M9 16h6"/></svg><span data-t="sp4"></span></a>
      <a class="spc" href="/planner?t=factory"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21V9l9-5 9 5v12"/><path d="M7 21v-8h10v8"/><path d="M7 17h10"/></svg><span data-t="sp5"></span></a>
    </div>
  </section>

  <section class="bestsellers reveal" id="bestsellers"><h2 data-t="bsh"></h2><div class="bsrow" id="bsgrid"></div></section>
  <div class="layout" id="shop">
    <aside>
      <div class="filter-head"><h3 data-t="flt"></h3><button type="button" id="resetf" data-t="rst"></button></div>
      <h3 data-t="fa"></h3>
      <label><input type="checkbox" id="stock"> <span data-t="fs"></span></label>
      <label><input type="checkbox" id="onsale"> <span data-t="of"></span></label>
      <h3 data-t="fc"></h3><div id="cats"></div>
      <h3 data-t="ft"></h3><div id="types"></div>
      <h3 data-t="fb"></h3><div id="brands"></div>
      <h3 id="pft"></h3>
<div class="pf" id="pf">
 <div class="pf-row">
  <div class="pf-f"><span id="pla"></span><input type="text" id="pmin" autocomplete="off" inputmode="numeric" min="0" step="1" placeholder="0"></div>
  <span class="pf-dash" aria-hidden="true">–</span>
  <div class="pf-f"><span id="plb"></span><input type="text" id="pmaxi" autocomplete="off" inputmode="numeric" min="0" step="1"></div>
 </div>
 <div class="pf-slider"><div class="pf-track"><div class="pf-fill" id="pffill"></div></div>
<?php $pm_max = products_max_price(); ?>
  <input type="range" id="pr1" min="0" max="<?php echo (int)ceil($pm_max/100)*100; ?>" step="25" value="0">
  <input type="range" id="pr2" min="0" max="<?php echo (int)ceil($pm_max/100)*100; ?>" step="25" value="<?php echo (int)ceil($pm_max/100)*100; ?>">
 </div>
 <div class="pf-chips" id="pfchips"></div>
 <div class="pf-sum" id="pv" aria-live="polite"></div>
</div>
    </aside>
    <section>
      <div class="catrow" id="catrow"></div><div class="tools"><h2><span data-t="n1"></span> <small id="count" class="meta"></small></h2>
        <select id="sort" aria-label="Sort"><option value="f" data-t="s1"></option><option value="pa" data-t="s2"></option><option value="pd" data-t="s3"></option><option value="az" data-t="s4"></option></select></div>
      <div class="grid" id="grid"></div>
      <div id="loadmore" style="text-align:center;margin:18px 0"></div>
    </section>
  </div>

  <div id="bchips" hidden></div>
  <section class="brands reveal" id="homecats"><h2 data-t="ch"></h2><div class="bchips" id="cchips"></div></section>
<section class="reveal" id="homeprices"></section><div id="hometiles"></div>
    <section class="plan reveal" id="planner"><h2 data-t="ph1"></h2><p data-t="ph2"></p>
<h3 class="ps"><i>1</i><span data-t="p_s1"></span></h3>
<div class="pgen">
<div class="pgf"><label for="pptype" data-t="ptyp"></label><select id="pptype" aria-label="Property type"><option value="apt" data-t="popt_apt"></option><option value="villa" data-t="popt_villa"></option><option value="office" data-t="popt_office"></option><option value="shop" data-t="popt_shop"></option><option value="factory" data-t="popt_factory"></option><option value="farm" data-t="popt_farm"></option></select></div>
<div class="pgf"><label for="ppsize" data-t="psz"></label><select id="ppsize" aria-label="Size"></select></div>
<div class="pgf" id="ppcwrap" style="display:none"><label for="ppcustom" data-t="pcustom"></label><input type="number" id="ppcustom" min="5" step="any" inputmode="decimal"></div>
<div class="pgf"><label for="pdW" data-t="p_w"></label><input type="number" id="pdW" min="2" step="any" inputmode="decimal"></div>
<div class="pgf"><label for="pdH" data-t="p_l"></label><input type="number" id="pdH" min="2" step="any" inputmode="decimal"></div>
</div>
<label class="plock"><input type="checkbox" id="plock" checked> <span data-t="p_lock"></span></label>
<div class="pgf" style="margin-top:8px"><label data-t="p_rooms"></label><div class="rcnt" id="rcnt"></div></div>
<details class="rdet"><summary data-t="p_details"></summary><div class="rlist" id="rlist"></div></details>
<div class="ptools"><button type="button" class="btn" id="pshuf" data-t="p_shuffle"></button><button type="button" class="btn" id="preset" data-t="p_reset"></button></div>
<h3 class="ps"><i>2</i><span data-t="p_s2"></span></h3>
<div class="ptools"><div class="seg" id="ptech" role="group" aria-label="Camera technology"><button type="button" data-k="ip" data-t="p_t_ip"></button><button type="button" data-k="hd" data-t="p_t_hd"></button><button type="button" data-k="wf" data-t="p_t_wf"></button></div><select id="pty" aria-label="Camera type"></select><select id="pcnt" aria-label="Camera count"><option value="0">0</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="8">8</option><option value="10">10</option><option value="12">12</option><option value="16">16</option></select><button type="button" class="btn pri" id="prec" data-t="prec"></button><label class="btn"><input type="file" id="pf" accept="image/*" hidden><span data-t="pup"></span></label><button type="button" class="btn" id="pcl" data-t="pcl"></button></div>
<div class="meta" data-t="phint"></div>
<div class="pcvwrap"><canvas id="cv" tabindex="0" aria-label="Coverage plan"></canvas></div>
<div class="pce off" id="pce"><div class="pceh" id="pceh"></div><label><span data-t="pfo"></span> <b id="sfv"></b><input type="range" id="sf" min="20" max="180"></label><label><span data-t="prn"></span> <b id="srv"></b><input type="range" id="sr" min="2" max="80" step="0.5"></label><label><span data-t="p_rot"></span> <b id="sav"></b><input type="range" id="sa" min="0" max="359"></label><button type="button" class="btn" id="pdl" data-t="pdl"></button></div>
<div class="pstats"><span><span data-t="pcn"></span> <b id="pn">0</b></span><span><span data-t="pcv"></span> <b id="pp">0%</b></span><span id="pb"></span><span id="pdim" class="pdimm"></span></div>
<div class="prstats" id="proomstats"></div>
<div class="ptools"><button type="button" class="btn pri" id="pfin" data-t="p_finish"></button><a class="btn" id="pcart" target="_blank" rel="noopener" data-t="pcart"></a><a class="btn" href="/shop" data-t="pshop"></a></div>
<div class="psys" id="psys" hidden></div>
<p class="meta" data-t="pnote"></p>
</section>
  
</main>

<div class="wrap"><section class="info vgpg" id="contact"></section>
<section class="info vgpg" id="cartpage"><div id="cartmount"></div></section>
<section class="info vgpg" id="acctpage"><div id="acctmount"></div></section>
<section class="info reveal" id="about"><h2 data-t="ab1"></h2><p data-t="ab2"></p></section>
<section class="info reveal" id="faq"><h2 data-t="fq"></h2><details><summary data-t="q1"></summary><p data-t="a1"></p></details><details><summary data-t="q2"></summary><p data-t="a2"></p></details><details><summary data-t="q3"></summary><p data-t="a3"></p></details><details><summary data-t="q4"></summary><p data-t="a4"></p></details><details><summary data-t="q5"></summary><p data-t="a5"></p></details><details><summary data-t="q6"></summary><p data-t="a6"></p></details><details><summary data-t="q7"></summary><p data-t="a7"></p></details><details><summary data-t="q8"></summary><p data-t="a8"></p></details><details><summary data-t="q9"></summary><p data-t="a9"></p></details></section></div>
<footer><div class="wrap">
  <div class="fgrid">
    <div class="fb"><a class="flogo" href="/" aria-label="Vision Guard"><img src="/images/vg-logo-footer.png" width="150" height="89" alt="Vision Guard"></a><p data-t="f1p"></p><div class="fsoc"><a href="<?php echo h(setting('instagram_url','https://www.instagram.com/visionguardegypt')); ?>" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2c2.72 0 3.06.01 4.12.06 1.06.05 1.79.22 2.43.47.66.26 1.21.6 1.76 1.15.55.55.89 1.1 1.15 1.76.25.64.42 1.37.47 2.43.05 1.06.06 1.4.06 4.12s-.01 3.06-.06 4.12c-.05 1.06-.22 1.79-.47 2.43a4.9 4.9 0 0 1-1.15 1.76 4.9 4.9 0 0 1-1.76 1.15c-.64.25-1.37.42-2.43.47-1.06.05-1.4.06-4.12.06s-3.06-.01-4.12-.06c-1.06-.05-1.79-.22-2.43-.47a4.9 4.9 0 0 1-1.76-1.15 4.9 4.9 0 0 1-1.15-1.76c-.25-.64-.42-1.37-.47-2.43C2.01 15.06 2 14.72 2 12s.01-3.06.06-4.12c.05-1.06.22-1.79.47-2.43.26-.66.6-1.21 1.15-1.76A4.9 4.9 0 0 1 5.44 2.54c.64-.25 1.37-.42 2.43-.47C8.94 2.01 9.28 2 12 2m0 1.8c-2.67 0-2.99.01-4.04.06-.87.04-1.34.18-1.65.3-.42.16-.71.35-1.02.66-.31.31-.5.6-.66 1.02-.12.31-.26.78-.3 1.65C4.28 8.74 4.27 9.06 4.27 12s.01 3.26.06 4.31c.04.87.18 1.34.3 1.65.16.42.35.71.66 1.02.31.31.6.5 1.02.66.31.12.78.26 1.65.3 1.05.05 1.37.06 4.04.06s2.99-.01 4.04-.06c.87-.04 1.34-.18 1.65-.3.42-.16.71-.35 1.02-.66.31-.31.5-.6.66-1.02.12-.31.26-.78.3-1.65.05-1.05.06-1.37.06-4.31s-.01-3.26-.06-4.31c-.04-.87-.18-1.34-.3-1.65a2.75 2.75 0 0 0-.66-1.02 2.75 2.75 0 0 0-1.02-.66c-.31-.12-.78-.26-1.65-.3C14.99 3.81 14.67 3.8 12 3.8m0 3.06a5.14 5.14 0 1 1 0 10.28 5.14 5.14 0 0 1 0-10.28m0 1.8a3.34 3.34 0 1 0 0 6.68 3.34 3.34 0 0 0 0-6.68m5.34-2a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0"/></svg></a><a href="<?php echo h(setting('facebook_url','https://www.facebook.com/visionguardegypt')); ?>" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5 3.66 9.15 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.91h-2.33V22c4.78-.79 8.44-4.94 8.44-9.94"/></svg></a></div></div>
    <div class="fc"><h3 data-t="f2"></h3><a class="fl" href="/about" data-t="l1"></a><a class="fl" href="/faq" data-t="n9"></a><a class="fl" href="/contact" data-t="n6"></a><a class="fl" href="/shipping.html" data-t="l3"></a><a class="fl" href="/terms.html" data-t="l4"></a><a class="fl" href="/privacy.html" data-t="l5"></a></div>
    <div class="fc"><h3 data-t="f4"></h3><a class="fct" href="tel:+201260087815"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg><span><span data-t="fcl"></span> <bdi dir="ltr">01260087815</bdi></span></a><a class="fct" href="https://wa.me/201105006854" target="_blank" rel="noopener"><svg width="18" height="18" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.634-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.336-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/></svg><span><span data-t="fwa"></span> <bdi dir="ltr">01105006854</bdi></span></a></div>
    <div class="fc fn"><h3 data-t="f3"></h3><p data-t="f3p"></p><form id="nlf" onsubmit="return nlSubmit(this)"><input data-p="em" id="nlEmail" type="email" required aria-label="Email"><button data-t="join"></button></form></div>
  </div>
  <div class="fbar"><div class="paym"><span data-t="payh"></span><span class="pchip" data-t="paycod"></span><span class="pchip" data-t="payip"></span><span class="pchip" data-t="payew"></span></div><div class="copy"><span data-t="copy"></span><span data-t="fpr"></span></div></div>
</div></footer>
<div class="dr" id="dr"><div class="drb" id="drb"></div><aside class="drp" role="dialog" aria-label="Menu"><div class="drh"><span class="logo"><svg class="wm" width="81" height="52" role="img" aria-label="Vision Guard"><use href="#wm"/></svg><img class="wmd" src="/images/vg-logo-footer.png" width="88" height="52" alt="Vision Guard"></span><button type="button" class="drx" id="drx" aria-label="Close">×</button></div><div class="drl" id="drl"></div></aside></div>
<dialog id="pdg"><div id="pdc"></div></dialog>
<div id="cartlive" aria-live="polite" style="position:absolute;width:1px;height:1px;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap"></div><dialog id="cd" aria-labelledby="cdt"><div id="cdc"></div></dialog>
<a class="wab" id="wab" target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 16 16" width="26" height="26" fill="#fff"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.634-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.336-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/></svg></a><nav class="mnav"><button type="button" id="mnHome"><svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg><span data-t="mnHome"></span></button><button type="button" id="mnSearch"><svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg><span data-t="mnSearch"></span></button><button type="button" id="mnCat"><svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg><span data-t="mnCat"></span></button><button type="button" id="mnCart"><svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2.5 3h2l2.4 12.2a2 2 0 0 0 2 1.6h8a2 2 0 0 0 2-1.6L21 7H6"/></svg><span data-t="mnCart"></span></button></nav>
<div id="toast"></div>

<script>
function esc(s){return String(s).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})}
var VGPAGE=<?php echo json_encode($vgPage); ?>,VGSEO=<?php echo json_encode(['ar'=>['t'=>$vgP['t_ar'],'d'=>$vgP['d_ar'],'h'=>$vgP['h_ar']],'en'=>['t'=>$vgP['t_en'],'d'=>$vgP['d_en'],'h'=>$vgP['h_en']]], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;
var IMGV=<?php echo json_encode(vg_img_versions()); ?>;
var WA='https://wa.me/<?php echo h(wa_href(setting('whatsapp_number','201105006854'))); ?>';
var IPN='<?php echo h(setting('instapay_number','omar_bakkar2@instapay')); ?>';
var EWN='<?php echo h(setting('ewallet_number','01105006854')); ?>';
  var EPD=<?php try{$__e=db()->query('SELECT active,percent FROM vg_discount_state WHERE id=1')->fetch();echo ($__e&&(int)$__e['active']===1)?max(0,min(90,(int)$__e['percent'])):0;}catch(Throwable $__x){echo 0;} ?>;
var D={ar:{top:<?php echo json_encode(vgtop('top_banner_ar', "عروض لفترة محدودة · توصيل داخل القاهرة والجيزة · استرجاع واستبدال سهل · ضمان رسمي"), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>,brand:"Vision Guard",search:"بحث",ph:"ابحث عن كاميرات، DVR، NVR...",n1:"المنتجات",n4:"الملحقات",n5:"عروض ساخنة",n6:"اتصل بنا",h1:<?php echo json_encode(setting('hero_title_ar', "شوف كل حاجة. احمِ اللي يهمك."), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>,hp:<?php echo json_encode(setting('hero_subtitle_ar', "كاميرات IP وواي فاي وأنالوج من أفضل الماركات، مع نصيحة صادقة وضمان رسمي."), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>,cta:"تسوق الكاميرات",t1:"شحن سريع",t1s:"لكل أنحاء مصر",t2:"دفع آمن",t2s:"حماية ١٠٠٪",t3:"استرجاع سهل",t3s:"استبدال سريع",t4:"ضمان رسمي",t4s:"وكيل معتمد",t5:"فريق الدعم",t5s:"معاك دايمًا",flt:"فلاتر",rst:"إعادة ضبط",fa:"التوفر",fs:"المتوفر فقط",ft:"مكان الاستخدام",in:"داخلية",out:"خارجية",fb:"الماركة",fp:"أقصى سعر:",any:"أي سعر",egp:"ج.م",s1:"الأكثر صلة",s2:"السعر: من الأقل للأعلى",s3:"السعر: من الأعلى للأقل",s4:"أبجديًا",f1:"عن Vision Guard",f1p:"كاميرات مراقبة وأنظمة أمان وكل ما يلزم للتركيب.",mnHome:"الرئيسية",mnSearch:"بحث",mnCat:"الأقسام",mnCart:"السلة",bsh:"الأكثر مبيعاً",f2:"روابط مهمة",l1:"من نحن",l2:"اتصل بنا",l3:"سياسة الشحن",l4:"الشروط والأحكام",l5:"سياسة الخصوصية",tr1:"استشارة مجانية",tr1s:"قبل ما تشتري",tr2:"الدفع عند الاستلام",tr2s:"أو إنستاباي ومحفظة",tr3:"دعم واتساب",tr3s:"رد سريع على طلبك",tr4:"تتبّع طلبك",tr4s:"من حسابك بسهولة",sp0:"حلول لكل مساحة",sp0s:"اختار مكانك، ظبّط الكاميرات على المخطط، وابعت الخطة على واتساب.",sp1:"شقة",sp2:"فيلا",sp3:"محل",sp4:"مكتب",sp5:"مخزن / مصنع",f3:"النشرة البريدية",f3p:"عروض ونصائح أمان على بريدك.",em:"البريد الإلكتروني",join:"اشترك",payh:"طرق الدفع:",paycod:"الدفع عند الاستلام",payip:"إنستاباي",payew:"محفظة إلكترونية",copy:"© Vision Guard. جميع الحقوق محفوظة.",fpr:"جميع الأسعار بالجنيه المصري.",ph1:"مخطط تغطية الكاميرات",ph2:"اختار نوع ومساحة العقار أو ارفع صورة الموقع، حط الكاميرات بنفسك أو خلي المخطط يقترحها لك، وشوف نسبة التغطية قبل ما تشتري.",n7:"مخطط التغطية",pup:"ارفع صورة الموقع",psm:"مخطط تجريبي",pt1:"خارجية Bullet",pt2:"داخلية Dome",pt3:"زاوية واسعة",pcl:"مسح الكل",pdl:"حذف المحددة",phint:"اضغط على الصورة لإضافة كاميرا · اسحب الكاميرا لتحريكها · اسحب النقطة البيضاء لتدويرها",pcn:"عدد الكاميرات:",pcv:"التغطية التقريبية:",pfo:"زاوية الرؤية",prn:"المدى",pcart:"أرسل المخطط على واتساب",pshop:"تسوق الكاميرات",pnote:"التغطية تقريبية ولا تحسب الجدران والعوائق. للتصميم الدقيق تواصل مع فريقنا.",fc:"القسم",both:"داخلية وخارجية",ncam:"كاميرات",cat_hd:"كاميرات سلكية HD",cat_ip:"كاميرات IP",cat_wf:"كاميرات واي فاي",cat_dvr:"مسجلات DVR",cat_hdd:"هارد ديسك",cat_mem:"كروت الميموري",cat_psu:"باور سبلاي",cat_cab:"الكابلات",cat_rack:"الراكات",cat_acc:"ملحقات",cat_net:"شبكات",cat_att:"أجهزة حضور وانصراف",ch:"تسوق حسب القسم",loadmore:"عرض المزيد",bh:"الماركات المتوفرة",bh:"الماركات المتوفرة",wt:"سلة الطلب",we:"السلة فاضية",wn:"الاسم",wad:"العنوان",wph:"رقم الهاتف",wtot:"الإجمالي",wgo:"اطلب عبر واتساب",wclr:"تفريغ السلة",wx:"إغلاق",cobtn:"متابعة للدفع",cot:"بيانات الطلب",pmt:"اختار طريقة الدفع",pmip:"إنستاباي",pmcod:"الدفع عند الاستلام",pmcard:"الدفع بالكارت (أونلاين)",coconf:"تأكيد الطلب",coback:"رجوع",cerr:"من فضلك املا الاسم ورقم الهاتف والعنوان واختر طريقة الدفع",odone:"تم إرسال طلبك ✅",odthank:"هيتم التواصل معاك قريبًا لتأكيد الطلب.",ipmsg:"حوّل قيمة الطلب على إنستاباي على الرقم:",ipnote:"ابعت إيصال التحويل على واتساب. الطلب هيفضل في انتظار المراجعة لحد ما نتأكد من وصول المبلغ.",codnote:"هتدفع نقدًا عند استلام الطلب.",pmew:"محفظة إلكترونية",ewmsg:"حوّل قيمة الطلب على محفظتك الإلكترونية (فودافون كاش / اتصالات كاش / أورنج كاش) على الرقم:",ewnote:"ابعت إيصال التحويل على واتساب. الطلب هيفضل في انتظار المراجعة لحد ما نتأكد من وصول المبلغ.",f4:"تواصل معنا",fwa:"واتساب:",fcl:"اتصال:",of:"عروض فقط",n8:"من نحن",n9:"الأسئلة الشائعة",ask:"اسأل عن المنتج على واتساب",rel:"منتجات مشابهة",ab1:"من نحن",ab2:<?php echo json_encode(setting('about_ar', "Vision Guard متجر متخصص في كاميرات المراقبة وأنظمة الأمان في مصر. بنوفر كاميرات سلكية وIP وواي فاي، ومسجلات DVR، وهارد ديسك للمراقبة، وكروت ميموري، وباور سبلاي، وكابلات وملحقات من ماركات زي Uniview وDahua وImou وTapo. تقدر تحدد أماكن الكاميرات بنفسك من مخطط التغطية، وتطلب مباشرة عبر واتساب."), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>,fq:"الأسئلة الشائعة",q1:"إزاي أطلب؟",a1:"ضيف المنتجات للسلة واضغط «متابعة للدفع»، اكتب بياناتك واختار طريقة الدفع، وهنتواصل معاك على واتساب أو بالتليفون لتأكيد المنتجات والسعر والعنوان قبل الشحن.",q2:"إزاي أعرف أي كاميرا تناسبني؟",a2:"جرّب مخطط التغطية: ارفع صورة المكان وحدد أماكن الكاميرات وشوف نسبة التغطية، وابعتلنا المخطط على واتساب نساعدك في الاختيار.",q3:"الأسعار بالجنيه المصري؟",a3:"أيوه، كل الأسعار بالجنيه المصري. الأسعار والتوفر ممكن يتغيروا، والسعر المعتمد هو اللي بيتأكد معاك وقت تأكيد الطلب.",q4:"بتوصلوا فين وبياخد قد إيه؟",a4:"بنوصّل الطلبات داخل القاهرة والجيزة، والمدة المتوقعة 3 أيام عمل من تأكيد الطلب. المدة تقديرية وممكن تختلف حسب المنطقة وتوفر المنتج، وتكلفة الشحن بتتأكد معاك على واتساب قبل تأكيد الطلب.",q5:"إيه ضمان المنتجات؟",a5:"الضمان بيختلف من منتج لتاني، فاسألنا عن المنتج اللي يهمك على واتساب قبل الطلب ونوضحلك.",q6:"طرق الدفع إيه؟",a6:"الدفع عند الاستلام أو التحويل عبر إنستاباي أو المحفظة الإلكترونية. التحويلات بتتأكد يدويًا قبل اعتماد الدفع.",q7:"أقدر أسترجع أو أستبدل المنتج؟",a7:"أيوه، خلال 14 يوم من الاستلام بشرط إن المنتج بحالته الأصلية وفي عبوته ومعاه ملحقاته. المنتجات المعيبة أو المخالفة للطلب بنستبدلها أو نرجع قيمتها بعد الفحص. غير مشمول: الهارد ديسك والمحولات الكهربائية وجميع الإكسسوارات البلاستيكية.",q8:"بتركّبوا الكاميرات؟",a8:"التركيب خدمة منفصلة، وبيتم الاتفاق على تكلفتها وموعدها معاك قبل التنفيذ. كلمنا على واتساب 01105006854.",q9:"إزاي أتابع طلبي؟",a9:"بعد الطلب هتاخد رقم طلب. ادخل على visionguardeg.com/order-status.php واكتب رقم الطلب ورقم الهاتف اللي سجلت بيه.",sp:"المواصفات",sbr:"الماركة",sres:"الدقة",spl:"مكان الاستخدام",scn:"طريقة التوصيل",sft:"الميزات",sch:"عدد القنوات",scap:"السعة",sam:"التيار",sty:"النوع",sln:"الطول",ssz:"المقاس",cn1:"كابل كواكس إلى مسجل DVR",cn2:"كابل شبكة",cn3:"واي فاي",fmic:"ميكروفون",fnv:"رؤية ليلية",fcol:"ملونة",ffix:"ثابتة",fpt:"بانوراما وإمالة",snote:"المواصفات مأخوذة من اسم المنتج. للمواصفات الكاملة والتأكد من التوافق اسألنا على واتساب.",dc_hd:"كاميرا مراقبة سلكية بتتوصل بمسجل DVR عن طريق كابل كواكس، وبتحتاج مصدر طاقة.",dc_ip:"كاميرا IP بتتوصل بالشبكة عن طريق كابل شبكة.",dc_wf:"كاميرا واي فاي بتتوصل بشبكة الواي فاي عندك وبتتشاف من الموبايل عن طريق تطبيق الماركة، وبتحتاج مصدر طاقة.",dc_dvr:"مسجل DVR بيسجل صورة الكاميرات السلكية، ولازم يتركّب معاه هارد ديسك للتسجيل.",dc_hdd:"هارد ديسك لتخزين تسجيلات الكاميرات.",dc_wd:"هارد ديسك من سلسلة WD Purple المخصصة لتسجيل المراقبة.",dc_mem:"كارت ميموري لتخزين التسجيلات في الكاميرات اللي بتدعم الكروت.",dc_psu:"باور سبلاي بيغذي الكاميرات بالطاقة، والأمبير بيحدد قدرته على تشغيل أكتر من كاميرا.",dc_coax:"سلك كواكس لتوصيل الكاميرات السلكية بالمسجل.",dc_utp:"سلك شبكة Cat6 لتوصيل كاميرات IP وأجهزة الشبكة.",dc_rack:"راك لتنظيم المسجلات وأجهزة الشبكة.",dc_ad:"ادابتور لتغذية الكاميرا بالطاقة.",dc_box:"بوكس لتجميع وحماية توصيلات الكاميرا.",dc_bnc:"جاك BNC لتوصيل كابل الفيديو بالكاميرا أو المسجل.",dc_dc:"جاك DC لتوصيل الطاقة بالكاميرا.",dc_x:"اسألنا على واتساب لأي تفاصيل عن المنتج.",sdt:"دقة الفيديو",sptr:"التحريك (Pan/Tilt)",snv:"الرؤية الليلية",sip:"مقاومة الماء والغبار",sst:"التخزين",sau:"الصوت",snw:"الشبكة",slens:"العدسة",sfn:"ميزات إضافية",lg:"English",cart:"السلة",added:"تمت الإضافة للسلة",subok:"تم الاشتراك، شكرًا لك",stock:"متوفر",oos:"غير متوفر",add:"أضف للسلة",un:"غير متاح",maxq:"الكمية المتاحة اتاخدت بالكامل",war:"ضمان",y1:"سنة",y2:"سنتان",prod:"منتج",ptyp:"نوع العقار",psz:"المساحة",pcustom:"مساحة مخصصة (م²)",pgen:"توليد المخطط",prec:"اقترح أماكن الكاميرات",popt_apt:"شقة",popt_villa:"فيلا",popt_office:"مكتب",popt_factory:"مصنع",popt_farm:"مزرعة",none:"لا توجد منتجات مطابقة."},
en:{top:<?php echo json_encode(vgtop('top_banner_en', "Limited-time offers · Delivery within Cairo and Giza · Easy returns · Authorized warranty"), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>,brand:"Vision Guard",search:"Search",ph:"Search cameras, DVR, NVR...",n1:"Products",n4:"Accessories",n5:"Hot Deals",n6:"Contact",h1:<?php echo json_encode(setting('hero_title_en', "See everything. Protect what matters."), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>,hp:<?php echo json_encode(setting('hero_subtitle_en', "IP, Wi-Fi and analog CCTV cameras from trusted brands, with honest advice and official warranty."), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>,cta:"Shop cameras",t1:"Fast Delivery",t1s:"All over Egypt",t2:"Safe Payment",t2s:"100% secure",t3:"Easy Returns",t3s:"Quick exchanges",t4:"Official Warranty",t4s:"Authorized",t5:"Support Team",t5s:"Always here for you",flt:"Filters",rst:"Reset",fa:"Availability",fs:"In stock only",ft:"Placement",in:"Indoor",out:"Outdoor",fb:"Brand",fp:"Max price:",any:"any",egp:"EGP",s1:"Featured",s2:"Price, low to high",s3:"Price, high to low",s4:"A–Z",f1:"About Vision Guard",f1p:"CCTV cameras, security systems and all you need to install them.",mnHome:"Home",mnSearch:"Search",mnCat:"Categories",mnCart:"Cart",bsh:"Best sellers",f2:"Important links",l1:"About us",l2:"Contact us",l3:"Shipping policy",l4:"Terms and conditions",l5:"Privacy policy",tr1:"Free consultation",tr1s:"Before you buy",tr2:"Cash on delivery",tr2s:"Or InstaPay and e-wallet",tr3:"WhatsApp support",tr3s:"Quick reply on your order",tr4:"Track your order",tr4s:"Easily from your account",sp0:"Solutions for every space",sp0s:"Pick your place, arrange cameras on the planner, then send the plan on WhatsApp.",sp1:"Apartment",sp2:"Villa",sp3:"Shop",sp4:"Office",sp5:"Warehouse / Factory",f3:"Newsletter",f3p:"Offers and security tips in your inbox.",em:"Email",join:"Join",payh:"Payment methods:",paycod:"Cash on delivery",payip:"InstaPay",payew:"E-wallet",copy:"© Vision Guard. All rights reserved.",fpr:"All prices are in EGP.",ph1:"Camera Coverage Planner",ph2:"Pick your property type and size or upload a site photo, place the cameras yourself or let the plan recommend them, and see the coverage before you buy.",n7:"Coverage Planner",pup:"Upload site photo",psm:"Sample plan",pt1:"Outdoor bullet",pt2:"Indoor dome",pt3:"Wide angle",pcl:"Clear all",pdl:"Delete selected",phint:"Tap the image to add a camera · drag a camera to move it · drag the white dot to rotate it",pcn:"Cameras:",pcv:"Approx. coverage:",pfo:"Field of view",prn:"Range",pcart:"Send plan via WhatsApp",pshop:"Shop cameras",pnote:"Coverage is approximate and ignores walls and obstacles. For an exact design, contact our team.",fc:"Category",both:"Indoor & outdoor",ncam:"Cameras",cat_hd:"HD Wired Cameras",cat_ip:"IP Cameras",cat_wf:"Wi-Fi Cameras",cat_dvr:"DVR Recorders",cat_hdd:"Hard Drives",cat_mem:"Memory Cards",cat_psu:"Power Supplies",cat_cab:"Cables",cat_rack:"Racks",cat_acc:"Accessories",cat_net:"Networking",cat_att:"Attendance Devices",ch:"Shop by Category",loadmore:"Show more",bh:"Available brands",bh:"Brands we carry",wt:"Your order",we:"Your cart is empty",wn:"Name",wad:"Address",wph:"Phone number",wtot:"Total",wgo:"Order via WhatsApp",wclr:"Clear cart",wx:"Close",cobtn:"Proceed to checkout",cot:"Checkout details",pmt:"Choose payment method",pmip:"InstaPay",pmcod:"Cash on Delivery",pmcard:"Pay by Card (Online)",coconf:"Confirm order",coback:"Back",cerr:"Please fill in name, phone, address and choose a payment method",odone:"Your order has been placed",odthank:"We will contact you soon to confirm your order.",ipmsg:"Transfer the order total via InstaPay to:",ipnote:"Send the transfer receipt on WhatsApp. The order stays pending until we verify the funds.",codnote:"You will pay in cash upon delivery.",pmew:"E-Wallet",ewmsg:"Transfer the order total to this mobile wallet number (Vodafone Cash / Etisalat Cash / Orange Cash):",ewnote:"Send the transfer receipt on WhatsApp. The order stays pending until we verify the funds.",f4:"Contact us",fwa:"WhatsApp:",fcl:"Call:",of:"Offers only",n8:"About us",n9:"FAQ",ask:"Ask about this product on WhatsApp",rel:"Related products",ab1:"About us",ab2:<?php echo json_encode(setting('about_en', "Vision Guard is a store specialized in CCTV cameras and security systems in Egypt. We offer wired, IP and Wi-Fi cameras, DVR recorders, surveillance hard drives, memory cards, power supplies, cables and accessories from brands like Uniview, Dahua, Imou and Tapo. You can plan your camera positions yourself with the coverage planner and order directly via WhatsApp."), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>,fq:"Frequently asked questions",q1:"How do I order?",a1:"Add products to the cart and tap Continue to checkout, enter your details and choose a payment method. We then contact you on WhatsApp or by phone to confirm the products, price and address before shipping.",q2:"How do I know which camera suits me?",a2:"Try the coverage planner: upload a photo of the place, mark where the cameras go and see the coverage, then send us the plan on WhatsApp and we will help you choose.",q3:"Are prices in Egyptian pounds?",a3:"Yes, all prices are in EGP. Prices and availability may change, and the approved price is the one confirmed with you when the order is confirmed.",q4:"Where do you deliver and how long does it take?",a4:"We deliver within Cairo and Giza, usually 3 working days after the order is confirmed. This is an estimate and may vary by area and product availability. Shipping cost is confirmed with you on WhatsApp before the order is confirmed.",q5:"What about warranty?",a5:"Warranty differs from product to product, so ask us about the product you are interested in on WhatsApp before ordering.",q6:"What payment methods do you accept?",a6:"Cash on delivery, InstaPay or mobile wallet transfer. Transfers are manually verified before payment is marked as paid.",q7:"Can I return or exchange a product?",a7:"Yes, within 14 days of receiving it, as long as it is in its original condition and packaging with all accessories. Defective or wrong items are exchanged or refunded after inspection. Not covered: hard drives, power adapters and all plastic accessories.",q8:"Do you install the cameras?",a8:"Installation is a separate service, and its cost and date are agreed with you before the work. Message us on WhatsApp at 01105006854.",q9:"How do I track my order?",a9:"After ordering you get an order number. Go to visionguardeg.com/order-status.php and enter the order number and the phone number you used.",sp:"Specifications",sbr:"Brand",sres:"Resolution",spl:"Placement",scn:"Connection",sft:"Features",sch:"Channels",scap:"Capacity",sam:"Current",sty:"Type",sln:"Length",ssz:"Size",cn1:"Coaxial cable to a DVR",cn2:"Network cable",cn3:"Wi-Fi",fmic:"Microphone",fnv:"Night vision",fcol:"Color",ffix:"Fixed",fpt:"Pan & tilt",snote:"Specifications are taken from the product name. For full specs and compatibility, ask us on WhatsApp.",dc_hd:"Wired surveillance camera that connects to a DVR through a coaxial cable and needs a power source.",dc_ip:"IP camera that connects to your network through a network cable.",dc_wf:"Wi-Fi camera that connects to your Wi-Fi network and is viewed from your phone through the brand app. It needs a power source.",dc_dvr:"DVR recorder for wired cameras. A hard drive must be installed in it for recording.",dc_hdd:"Hard drive for storing camera recordings.",dc_wd:"WD Purple hard drive, a series made for surveillance recording.",dc_mem:"Memory card for storing recordings in cameras that support cards.",dc_psu:"Power supply that powers your cameras. The amperage sets how many cameras it can run.",dc_coax:"Coaxial cable for connecting wired cameras to the recorder.",dc_utp:"Cat6 network cable for IP cameras and network devices.",dc_rack:"Rack for organizing recorders and network devices.",dc_ad:"Adaptor for powering a camera.",dc_box:"Box for gathering and protecting camera connections.",dc_bnc:"BNC connector for linking the video cable to the camera or recorder.",dc_dc:"DC connector for supplying power to the camera.",dc_x:"Ask us on WhatsApp for any product details.",sdt:"Video resolution",sptr:"Pan / Tilt",snv:"Night vision",sip:"Weather rating",sst:"Storage",sau:"Audio",snw:"Network",slens:"Lens",sfn:"More features",lg:"العربية",cart:"Cart",added:"Added to cart",subok:"Subscribed, thank you",stock:"In stock",oos:"Out of stock",add:"Add to cart",un:"Unavailable",maxq:"All available units are already in your cart",war:"warranty",y1:"1 Year",y2:"2 Years",prod:"products",ptyp:"Property type",psz:"Size",pcustom:"Custom area (sqm)",pgen:"Generate plan",prec:"Recommend camera placement",popt_apt:"Apartment",popt_villa:"Villa",popt_office:"Office",popt_factory:"Factory",popt_farm:"Farm",none:"No products match your filters."}};
// [category, place, brand, Arabic name, English name, sell price (EGP), icon]
<?php $pm_max = products_max_price(); ?>var PMX=Math.max(100,Math.ceil((<?php echo (float)$pm_max; ?>)/100)*100),PLO=0,PHI=PMX,PM=PMX,P=<?php echo products_js_array(); ?>.map(function(a,i){return{i:i,c:a[0],pl:a[1],b:a[2],a:a[3],e:a[4],p:a[5],ic:a[6],s:a[7],id:'p'+a[8],sl:a[9],q:a[10]}});
<?php readfile(__DIR__ . '/inc/index-app-1.js'); ?>
var OFF=<?php echo offers_js_array(); ?>,PI=0,PQ=1,DCAT={cat_hd:'bu',cat_ip:'pt',cat_wf:'cu',cat_dvr:'dv',cat_hdd:'hd',cat_mem:'mc',cat_psu:'ps',cat_cab:'ca',cat_rack:'rk',cat_acc:'ad',cat_net:'bx',cat_att:'bx'};
<?php readfile(__DIR__ . '/inc/index-app-2.js'); ?>
</script>
<script>
/* live search suggestions */
(function(){
var q=document.getElementById('q'),box=document.querySelector('header .search');
if(!q||!box||typeof P==='undefined')return;
box.style.position='relative';
var d=document.createElement('div');d.id='qsug';d.hidden=true;box.appendChild(d);
var st=document.createElement('style');
st.textContent='header{z-index:60}header #acct,header #bg,header #lang,header #themeToggle,header #cart{box-sizing:border-box!important;width:42px!important;height:42px!important;min-width:42px!important;padding:0!important;margin:0!important;border-radius:12px!important;display:inline-flex!important;align-items:center;justify-content:center;border:1px solid var(--border,#dde3ea)!important;flex:none}header #cart{border-color:transparent!important}header #lang{font-size:13px;font-weight:800}header .wrap.bar>#bg{order:0}header .wrap.bar>#lang{order:1}header .wrap.bar>#themeToggle{order:2}header .wrap.bar>.search{order:3}header .wrap.bar>#cart{order:4}header .wrap.bar>#acct{order:5}header .wrap.bar>.logo{order:9}@media(max-width:760px){header .wrap.bar>.search{order:10}header .wrap.bar{gap:8px}}#qsug{position:absolute;top:calc(100% + 6px);inset-inline:0;z-index:80;background:var(--surface,#fff);color:var(--text,#202224);border:1px solid var(--border,#dde3ea);border-radius:12px;box-shadow:0 18px 40px rgba(0,0,0,.2);max-height:min(70vh,440px);overflow:auto;padding:6px}#qsug[hidden]{display:none}#qsug a{display:flex;align-items:center;gap:10px;padding:8px;border-radius:8px;color:inherit;text-decoration:none}#qsug a:hover,#qsug a:focus{background:var(--bg,#f4f7fb);outline:none}#qsug .si{width:48px;height:48px;flex:none;border-radius:8px;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden}#qsug .si img,#qsug .si svg{max-width:100%;max-height:100%;object-fit:contain}#qsug .sn{flex:1;min-width:0;font-weight:600;font-size:14px;line-height:1.4}#qsug .sp{font-weight:800;font-size:14px;white-space:nowrap}#qsug .sp s{display:block;font-weight:400;font-size:11px;opacity:.6}#qsug .sa{display:block;text-align:center;padding:10px;font-weight:700;color:var(--accent,#05b3e9);cursor:pointer;border-radius:8px}#qsug .sa:hover{background:var(--bg,#f4f7fb)}#qsug .se{padding:14px;text-align:center;opacity:.7;font-size:14px}';
document.head.appendChild(st);
function url(p){return p.sl?'/p/'+encodeURIComponent(p.sl):'product.php?id='+p.id.slice(1)}
function find(v){var ts=v.toLowerCase().split(/\s+/).filter(Boolean);return P.filter(function(p){if(p.hide)return false;var h=(p.a+' '+p.e+' '+p.b).toLowerCase();return ts.every(function(t){return h.indexOf(t)>-1})})}
function toShop(){d.hidden=true;if(VGPAGE!=='shop'){location.href='/shop?q='+encodeURIComponent(q.value.trim());return}var s=document.getElementById('shop');if(s)s.scrollIntoView({behavior:'smooth',block:'start'})}
function draw(){
 var v=q.value.trim();
 if(!v){d.hidden=true;d.innerHTML='';return}
 var R=find(v);d.innerHTML='';
 var en=(typeof L!=='undefined'&&L==='en');
 if(!R.length){var e=document.createElement('div');e.className='se';e.textContent=en?'No matching products':'مفيش منتجات مطابقة';d.appendChild(e)}
 R.slice(0,6).forEach(function(p){ var qc=(window.__qc=window.__qc||{}); var ck=p.id+'|'+(typeof L!=='undefined'?L:''); var a=qc[ck]; if(!a){ a=document.createElement('a');a.href=url(p); var si=document.createElement('span');si.className='si';si.innerHTML=pimg(p).replace('loading="lazy"','loading="eager" fetchpriority="high"'); var sn=document.createElement('span');sn.className='sn';sn.textContent=nmp(p); var sp=document.createElement('span');sp.className='sp';sp.innerHTML=prc(p); a.appendChild(si);a.appendChild(sn);a.appendChild(sp); qc[ck]=a; } d.appendChild(a) });
 if(R.length>6){var m=document.createElement('div');m.className='sa';m.textContent=(en?'View all results (':'عرض كل النتائج (')+R.length+')';m.onclick=toShop;d.appendChild(m)}
 d.hidden=false
}
q.addEventListener('input',draw);
q.addEventListener('focus',draw); var __pre=false; function preloadImgs(){ if(__pre||typeof P==='undefined')return; try{ if(navigator.connection&&navigator.connection.saveData)return; }catch(e){} __pre=true; var list=P.filter(function(p){return !p.hide}).slice(0,30); var i=0,act=0,MAX=8; function next(){ while(act<MAX&&i<list.length){ var m=pimg(list[i++]).match(/src="([^"]+)"/); if(!m)continue; act++; var im=new Image(); im.onload=im.onerror=function(){act--;next()}; im.src=m[1]; } } next(); } q.addEventListener('focus',preloadImgs); q.addEventListener('pointerdown',preloadImgs,{passive:true}); q.addEventListener('touchstart',preloadImgs,{passive:true});
q.addEventListener('keydown',function(e){
 if(e.key==='Escape'){d.hidden=true}
 if(e.key==='Enter'){e.preventDefault();toShop()}
 if(e.key==='ArrowDown'){var f=d.querySelector('a');if(f){e.preventDefault();f.focus()}}
});
d.addEventListener('keydown',function(e){
 var t=e.target;
 if(e.key==='ArrowDown'&&t.nextElementSibling&&t.nextElementSibling.tagName==='A'){e.preventDefault();t.nextElementSibling.focus()}
 if(e.key==='ArrowUp'){e.preventDefault();if(t.previousElementSibling)t.previousElementSibling.focus();else q.focus()}
 if(e.key==='Escape'){d.hidden=true;q.focus()}
});
document.addEventListener('click',function(e){if(!box.contains(e.target))d.hidden=true});
})();
</script>
<script>
(function(){
try{if(localStorage.getItem('vg_notrack')==='1')return;}catch(e){}
var API='/api/track.php';
function send(t,o){try{var b=Object.assign({t:t,p:location.pathname},o||{});var s=JSON.stringify(b);
if(navigator.sendBeacon){navigator.sendBeacon(API,new Blob([s],{type:'application/json'}));}
else{fetch(API,{method:'POST',body:s,keepalive:true,headers:{'Content-Type':'application/json'}});}}catch(e){}}
var host='';try{host=document.referrer?new URL(document.referrer).hostname:'';}catch(e){}
var sp=new URLSearchParams(location.search);
send('pv',{ref:host,utm:sp.get('utm_source')||'',fb:sp.has('fbclid')});
var seen={};
document.addEventListener('click',function(e){
var el=e.target;if(!el||!el.closest)return;
var card=el.closest('article.card');var h3=card?card.querySelector('h3'):null;var nm=h3?h3.textContent.trim():'';
var a=el.closest('a');
if(a){var h=a.getAttribute('href')||'';
if(/wa\.me|whatsapp\.com/i.test(h)){send('wa',{label:nm});return;}
if(/^tel:/i.test(h)){send('call',{label:nm});return;}}
if(card){if(el.closest('button')){send('cart',{label:nm});}else if(!seen[nm]){seen[nm]=1;send('product',{label:nm});}}
},true);
var st;
document.addEventListener('input',function(e){
var i=e.target;if(!i||!i.closest||i.tagName!=='INPUT'||!i.closest('.search'))return;
clearTimeout(st);
st=setTimeout(function(){var q=(i.value||'').trim();if(q.length>=2)send('search',{label:q,n:document.querySelectorAll('article.card').length});},1200);
});
})();
</script>
<script src="/pwa.js" defer></script>
<script>
(function(){function run(){try{
if(typeof P==='undefined'||typeof CART==='undefined')return;
var a=JSON.parse(localStorage.getItem('vg-pending')||'[]'),ch=false;
if(a.length){localStorage.removeItem('vg-pending');a.forEach(function(e){var p=P.find(function(x){return e.k==='sl:'+x.sl||e.k==='id:'+String(x.id).slice(1)});if(p&&p.s){CART[p.i]=(CART[p.i]||0)+Math.max(1,Math.min(10,+e.q||1));ch=true}});if(ch){csave();cupd();if(VGPAGE==='cart')cartUI()}}
if(new URLSearchParams(location.search).get('cart')==='1'){history.replaceState(null,'',location.pathname);setTimeout(function(){var b=document.getElementById('cart');if(b)b.click()},250)}
}catch(e){}}
run();window.addEventListener('pageshow',function(ev){if(ev.persisted)run()})})();
</script>
<style>/*vgcmp*/
.cmpb{width:100%;margin:0 0 6px;min-height:40px;border-radius:10px;border:1px solid var(--border,#d5dde8);background:transparent;color:var(--text,#202224);font:600 13px Cairo,system-ui,sans-serif;cursor:pointer}
.cmpb[aria-pressed=true]{border-color:#05b3e9;background:rgba(5,179,233,.14)}
#cmpbar{position:fixed;inset-inline:12px;bottom:16px;z-index:9000;display:none;align-items:center;gap:10px;padding:10px 12px;background:var(--surface,#fff);color:var(--text,#202224);border:1px solid var(--border,#d5dde8);border-radius:14px;box-shadow:0 8px 28px rgba(0,0,0,.2);max-width:640px;margin-inline:auto}
#cmpbar.on{display:flex}
#cmpbar .ct{flex:1;min-width:0;font-weight:700;font-size:14px}
#cmpbar .ct small{display:block;font-weight:500;color:var(--muted,#555);font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#cmpbar button{min-height:44px;border-radius:10px;border:0;padding:0 14px;font:700 14px Cairo,system-ui,sans-serif;cursor:pointer}
#cmpbar .go{background:#05b3e9;color:#04202e}
#cmpbar .go:disabled{opacity:.5;cursor:not-allowed}
#cmpbar .cl{background:transparent;color:var(--text,#202224);border:1px solid var(--border,#d5dde8)}
#cmpm{position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,.6);display:none;align-items:flex-end;justify-content:center}
#cmpm.on{display:flex}
#cmpm .bx{background:var(--surface,#fff);color:var(--text,#202224);width:100%;max-width:980px;max-height:92vh;overflow:auto;border-radius:16px 16px 0 0;padding:16px}
#cmpm h2{margin:0 0 12px;font-size:18px}
#cmpm .x{position:sticky;top:0;float:inline-end;width:44px;height:44px;border-radius:50%;border:1px solid var(--border,#d5dde8);background:var(--surface,#fff);color:inherit;font-size:22px;cursor:pointer}
#cmpm table{border-collapse:collapse;width:100%;min-width:520px;table-layout:fixed}
#cmpm th,#cmpm td{padding:10px 8px;border-top:1px solid var(--border,#e1e1e1);text-align:start;vertical-align:top;font-size:14px}
#cmpm th{width:92px;color:var(--muted,#555);font-weight:600}
#cmpm td img{width:100%;max-height:110px;object-fit:contain;background:#fff;border-radius:8px}
#cmpm .nm{font-weight:700;line-height:1.4}
#cmpm .tag{display:inline-block;margin-top:4px;padding:1px 8px;border-radius:99px;background:rgba(5,179,233,.18);font-size:11px;font-weight:700}
#cmpm .ac a,#cmpm .ac button{display:flex;align-items:center;justify-content:center;min-height:42px;margin-top:6px;border-radius:10px;font:700 13px Cairo,system-ui,sans-serif;text-decoration:none;cursor:pointer}
#cmpm .ac a{border:1px solid var(--border,#d5dde8);color:inherit}
#cmpm .ac button{border:0;background:#05b3e9;color:#04202e}
#cmpm .nt{margin:10px 0 0;font-size:12px;color:var(--muted,#555)}
@media(min-width:761px){#cmpm{align-items:center}#cmpm .bx{border-radius:16px}}
@media(max-width:760px){#cmpbar{bottom:calc(62px + env(safe-area-inset-bottom))}}
</style>
<div id="cmpbar" role="region" aria-label="مقارنة المنتجات"><div class="ct"></div><button type="button" class="cl"></button><button type="button" class="go"></button></div>
<div id="cmpm" role="dialog" aria-modal="true"><div class="bx"><button type="button" class="x" aria-label="إغلاق">&times;</button><h2></h2><div style="overflow-x:auto"><table></table></div><p class="nt"></p></div></div>
<script>/*vgcmp*/
(function(){
var g=document.getElementById('grid');if(!g||typeof P==='undefined')return;
var ar=(typeof L!=='undefined'?L:'ar')==='ar',MAX=3,SEL=[];
var X=ar?{cmp:'قارن',on:'تمت الإضافة ✓',bar:'مقارنة المنتجات',pick:'اختر منتجين على الأقل',n:' منتج مختار',go:'قارن الآن',clr:'مسح',max:'الحد الأقصى 3 منتجات',title:'مقارنة المنتجات',price:'السعر',brand:'الماركة',cat:'القسم',stock:'التوفر',inS:'متوفر',outS:'غير متوفر',cheap:'الأقل سعرًا',view:'عرض المنتج',add:'أضف للسلة',note:'الخصائص مستخرجة من اسم المنتج. للمواصفات الكاملة اسألنا على واتساب.',res:'الدقة',con:'النوع',night:'الرؤية الليلية',env:'الاستخدام',aud:'الصوت',mov:'الحركة',cap:'السعة',spd:'السرعة',close:'إغلاق',cur:'ج.م'}:{cmp:'Compare',on:'Added ✓',bar:'Compare products',pick:'Pick at least 2 products',n:' selected',go:'Compare now',clr:'Clear',max:'Maximum 3 products',title:'Compare products',price:'Price',brand:'Brand',cat:'Category',stock:'Availability',inS:'In stock',outS:'Out of stock',cheap:'Lowest price',view:'View product',add:'Add to cart',note:'Features are taken from the product name. Ask us on WhatsApp for full specs.',res:'Resolution',con:'Type',night:'Night vision',env:'Use',aud:'Audio',mov:'Movement',cap:'Capacity',spd:'Speed',close:'Close',cur:'EGP'};
try{SEL=JSON.parse(sessionStorage.getItem('vgcmp')||'[]').filter(function(i){return P[i]})}catch(e){}
function sv(){try{sessionStorage.setItem('vgcmp',JSON.stringify(SEL))}catch(e){}}
var bar=document.getElementById('cmpbar'),m=document.getElementById('cmpm'),ct=bar.querySelector('.ct'),go=bar.querySelector('.go'),cl=bar.querySelector('.cl'),msgT;
cl.textContent=X.clr;go.textContent=X.go;m.querySelector('.x').setAttribute('aria-label',X.close);m.setAttribute('aria-label',X.title);
function paint(b){var on=SEL.indexOf(+b.dataset.cmp)>=0;b.setAttribute('aria-pressed',on);b.textContent=on?X.on:'⇄ '+X.cmp}
function refresh(msg){g.querySelectorAll('.cmpb').forEach(paint);bar.classList.toggle('on',SEL.length>0);go.disabled=SEL.length<2;
ct.innerHTML='<span></span><small></small>';ct.firstChild.textContent=X.bar+' ('+SEL.length+'/'+MAX+')';ct.lastChild.textContent=msg||(SEL.length<2?X.pick:SEL.map(function(i){return nmp(P[i])}).join(' · '))}
function inject(){g.querySelectorAll('article.card').forEach(function(c){if(c.querySelector('.cmpb'))return;var a=c.querySelector('button[data-n]');if(!a)return;var b=document.createElement('button');b.type='button';b.className='cmpb';b.dataset.cmp=a.dataset.n;c.insertBefore(b,a);paint(b)})}
new MutationObserver(inject).observe(g,{childList:true});inject();refresh();
g.addEventListener('click',function(e){var b=e.target.closest('.cmpb');if(!b)return;var n=+b.dataset.cmp,i=SEL.indexOf(n);if(i>=0)SEL.splice(i,1);else if(SEL.length>=MAX){refresh(X.max);clearTimeout(msgT);msgT=setTimeout(function(){refresh()},2000);return}else SEL.push(n);sv();refresh()});
cl.onclick=function(){SEL=[];sv();refresh()};
function sp(p){var s=p.e+' '+p.a,o={},r;
if(r=s.match(/(\d+(?:\.\d+)?)\s?MP/i))o.res=r[1]+'MP';else if(/\b4K\b/i.test(s))o.res='4K';
if(/wi-?fi/i.test(s))o.con='Wi-Fi';else if(/\bIP\b/.test(s))o.con='IP';else if(/\bHD\b/.test(s))o.con='HD';
if(/full-?colou?r/i.test(s))o.night=ar?'ملونة ليلًا':'Full-colour';else if(/night/i.test(s))o.night=ar?'رؤية ليلية':'Night vision';
if(/outdoor/i.test(s))o.env=ar?'خارجي':'Outdoor';
if(/\bMic\b|audio/i.test(s))o.aud=ar?'ميكروفون':'Mic';
if(/pan\s*&\s*tilt|ptz/i.test(s))o.mov=ar?'تحريك وإمالة':'Pan & Tilt';
if(r=s.match(/(\d+(?:\.\d+)?)\s?(TB|GB)\b/i))o.cap=r[1]+' '+r[2].toUpperCase();
if(r=s.match(/(\d+)\s?Mbps/i))o.spd=r[1]+' Mbps';
return o}
function open(){var ps=SEL.map(function(i){return P[i]}),S=ps.map(sp),rows=[];
var min=Math.min.apply(null,ps.filter(function(p){return p.s}).map(ep).concat([Infinity]));
m.querySelector('h2').textContent=X.title;
function row(l,f){var c=ps.map(f);if(c.some(function(v){return v&&v!=='—'}))rows.push('<tr><th scope="row">'+l+'</th>'+c.map(function(v){return '<td>'+(v||'—')+'</td>'}).join('')+'</tr>')}
var h='<tr><th></th>'+ps.map(function(p){var u=p.sl?'/p/'+encodeURIComponent(p.sl):'product.php?id='+p.id.slice(1);return '<td><a href="'+u+'">'+pimg(p)+'</a><div class="nm">'+esc(nmp(p))+'</div></td>'}).join('')+'</tr>';
row(X.price,function(p){var e=ep(p);return '<b>'+N(e)+' '+X.cur+'</b>'+(p.s&&e===min&&ps.length>1?'<br><span class="tag">'+X.cheap+'</span>':'')});
row(X.brand,function(p){return esc(BN(p.b))});
row(X.cat,function(p){return T(p.c)});
row(X.stock,function(p){return p.s?X.inS:X.outS});
['res','con','night','env','aud','mov','cap','spd'].forEach(function(k){row(X[k],function(p){return S[ps.indexOf(p)][k]})});
rows.push('<tr><th></th>'+ps.map(function(p,j){var u=p.sl?'/p/'+encodeURIComponent(p.sl):'product.php?id='+p.id.slice(1);return '<td class="ac"><a href="'+u+'">'+X.view+'</a>'+(p.s?'<button type="button" data-add="'+SEL[j]+'">'+X.add+'</button>':'')+'</td>'}).join('')+'</tr>');
m.querySelector('table').innerHTML=h+rows.join('');m.querySelector('.nt').textContent=X.note;m.classList.add('on');m.querySelector('.x').focus()}
go.onclick=function(){if(SEL.length>=2)open()};
m.addEventListener('click',function(e){if(e.target===m||e.target.closest('.x')){m.classList.remove('on');return}var b=e.target.closest('button[data-add]');if(!b)return;var n=+b.dataset.add;if((CART[n]||0)>=avail(n)){tst('maxq');return}CART[n]=(CART[n]||0)+1;csave();cupd();tst('added')});
document.addEventListener('keydown',function(e){if(e.key==='Escape')m.classList.remove('on')});
})();
</script>
<script>/*vgft*/(function(){try{var sp=new URLSearchParams(location.search),h='';try{h=document.referrer?new URL(document.referrer).hostname:''}catch(e){}var me=location.hostname,ext=h&&h.indexOf(me)<0&&me.indexOf(h)<0,u=sp.get('utm_source')||'',fb=sp.has('fbclid'),old=null;try{old=JSON.parse(localStorage.getItem('vgft')||'null')}catch(e){}if(u||fb||ext||!old||Date.now()-old.t>2592000000)localStorage.setItem('vgft',JSON.stringify({ref:ext?h:'',utm:u,fb:fb,t:Date.now()}))}catch(e){}})();</script>
</body>
</html>
