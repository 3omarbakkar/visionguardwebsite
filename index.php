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
<meta property="og:image" content="https://visionguardeg.com/og-image.png"><meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image"><meta name="twitter:image" content="https://visionguardeg.com/og-image.png">
<meta property="og:locale" content="<?php echo $vgEn?'en_US':'ar_EG'; ?>"><meta property="og:locale:alternate" content="en_US">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;800&display=swap">
<script type="application/ld+json">{"@context":"https://schema.org","@type":"ElectronicsStore","name":"Vision Guard","url":"https://visionguardeg.com/","image":"https://visionguardeg.com/og-image.png","telephone":"+201260087815","@id":"https://visionguardeg.com/#store","openingHoursSpecification":[{"@type":"OpeningHoursSpecification","dayOfWeek":["Saturday","Sunday","Monday","Tuesday","Wednesday","Thursday"],"opens":"12:00","closes":"20:00"}],"description":"Vision Guard sells CCTV cameras (IP, WiFi, wired HD), DVR/NVR recorders, hard disks and security accessories in Egypt, with delivery in Cairo and Giza.","priceRange":"EGP","currenciesAccepted":"EGP","paymentAccepted":"Cash on Delivery, InstaPay, Mobile Wallet","areaServed":[{"@type":"City","name":"Cairo"},{"@type":"City","name":"Giza"}],"contactPoint":[{"@type":"ContactPoint","telephone":"+201260087815","contactType":"customer service","availableLanguage":["ar","en"]},{"@type":"ContactPoint","telephone":"+201105006854","contactType":"sales","availableLanguage":["ar","en"]}],"address":{"@type":"PostalAddress","addressLocality":"October Gardens","addressRegion":"Giza","addressCountry":"EG"},"sameAs":["https://www.facebook.com/visionguardegypt","https://www.instagram.com/visionguardegypt"]}</script><script type="application/ld+json">{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"إزاي أطلب؟","acceptedAnswer":{"@type":"Answer","text":"ضيف المنتجات للسلة واضغط «اطلب عبر واتساب»، هتتفتح محادثة معانا فيها طلبك جاهز، ونأكد معاك التوفر والسعر النهائي."}},{"@type":"Question","name":"إزاي أعرف أي كاميرا تناسبني؟","acceptedAnswer":{"@type":"Answer","text":"جرّب مخطط التغطية: ارفع صورة المكان وحدد أماكن الكاميرات وشوف نسبة التغطية. وابعتلنا المخطط على واتساب نساعدك في الاختيار."}},{"@type":"Question","name":"الأسعار بالجنيه المصري؟","acceptedAnswer":{"@type":"Answer","text":"أيوه، كل الأسعار بالجنيه المصري. الأسعار والتوفر ممكن يتغيروا، فبنأكد السعر النهائي معاك على واتساب قبل الطلب."}},{"@type":"Question","name":"إزاي الشحن والتوصيل؟","acceptedAnswer":{"@type":"Answer","text":"شحن سريع لكل محافظة. اكتبلنا محافظتك على واتساب ونرد عليك بتفاصيل التوصيل والمدة."}},{"@type":"Question","name":"إيه ضمان المنتجات؟","acceptedAnswer":{"@type":"Answer","text":"الضمان بيختلف من منتج لتاني، فاسألنا عن المنتج اللي يهمك على واتساب قبل الطلب ونوضحلك."}}]}</script>
<style>
/* ===== PALETTE: swap these tokens to apply your brand colors ===== */
:root{--brand:#0667a5;--on-brand:#fff;--accent:#05b3e9;--on-accent:#04202e;--sale:#ff5a1f;--bg:#f4f7fb;--surface:#fff;--text:#202224;--muted:#494b4a;--ok:#0b7a3a;--link:#0667a5;--border:#e1e1e1;
    
}box-sizing:border-box;padding-top:env(safe-area-inset-top,0px);padding-bottom:env(safe-area-inset-bottom,0px)}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]){--brand:#052e45;--accent:#05b3e9;--on-accent:#04202e;--bg:#0a1424;--surface:#101d33;--text:#e8eef8;--muted:#9b9c9b;--ok:#34d27b;--link:#05b3e9;--border:#35373a}}
:root[data-theme="dark"]{--brand:#052e45;--accent:#05b3e9;--on-accent:#04202e;--bg:#0a1424;--surface:#101d33;--text:#e8eef8;--muted:#9b9c9b;--ok:#34d27b;--link:#05b3e9;--border:#35373a}
html{scroll-padding-top:env(safe-area-inset-top,0px);scroll-behavior:smooth}
@media (prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important;scroll-behavior:auto!important}}
*,*::before,*::after{box-sizing:inherit}
body{margin:0;background:var(--bg);color:var(--text);font:15px/1.5 Cairo,system-ui,-apple-system,"Segoe UI",sans-serif}
a{color:inherit;text-decoration:none}
.wrap{max-width:1200px;margin:0 auto;padding:0 16px}
.top{background:var(--brand);color:var(--on-brand);font-size:13px;padding:7px 0;overflow:hidden;white-space:nowrap}
.top-mq{display:inline-flex;width:max-content;animation:topmq 18s linear infinite}
.top-mq span{padding-inline-end:48px;flex:0 0 auto}
@keyframes topmq{from{transform:translateX(0)}to{transform:translateX(-33.3333%)}}
@media(prefers-reduced-motion:reduce){.top-mq{animation:none}}
header{position:sticky;top:env(safe-area-inset-top,0px);z-index:5;background:var(--surface);border-bottom:1px solid var(--border);transition:box-shadow .25s}
header.scrolled{box-shadow:0 6px 18px rgba(8,20,40,.12)}
.bar{display:flex;align-items:center;gap:16px;padding:12px 0;flex-wrap:wrap}
.logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:23px;letter-spacing:.2px;direction:ltr;color:#4a4b4c}
.logo .mark{width:44px;height:44px;flex:none}.logo b{color:#06b6d4}.logo .wm{display:block}html[data-theme="dark"] .wm{filter:brightness(1.4) contrast(1.05)}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) header .logo,:root:not([data-theme="light"]) .drp .logo{color:#eef2f7}}
:root[data-theme="dark"] header .logo,:root[data-theme="dark"] .drp .logo{color:#eef2f7}
.flogo{margin:0 0 22px;display:inline-block;line-height:0;opacity:1;color:#fff;--vision-a:#eaf6ff;--vision-b:#ffffff}.flogo .wm{width:min(190px,60vw);height:auto;aspect-ratio:81/52;display:block}
.search{flex:1;min-width:180px;display:flex}
.search input{flex:1;min-width:0;width:100%;padding:10px 14px;border:1px solid var(--border);border-start-start-radius:10px;border-end-start-radius:10px;background:var(--bg);color:var(--text);font:inherit}
.search button{padding:0 16px;border:0;background:var(--accent);color:var(--on-accent);font-weight:700;border-start-end-radius:10px;border-end-end-radius:10px}
.theme{border:1px solid var(--border);background:var(--bg);color:var(--text);display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:10px;cursor:pointer;padding:0}.theme .i-moon{display:block}.theme .i-sun{display:none}:root[data-theme="dark"] .theme .i-moon{display:none}:root[data-theme="dark"] .theme .i-sun{display:block}.lang{border:1px solid var(--border);background:var(--bg);color:var(--text);font:inherit;font-weight:700;padding:8px 12px;border-radius:10px;cursor:pointer}
.cart{font:inherit;font-weight:700;padding:8px 14px;border-radius:10px;background:var(--brand);color:var(--on-brand);border:0;cursor:pointer}
dialog{border:1px solid var(--border);border-radius:16px;background:var(--surface);color:var(--text);padding:18px;width:min(520px,94vw);max-height:90vh;overflow:auto}dialog::backdrop{background:rgba(0,0,0,.5)}
.ci{display:grid;grid-template-columns:1fr auto auto;gap:10px;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);font-size:14px}.qty2{display:flex;gap:6px;align-items:center}
.cf input{width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:10px;background:var(--bg);color:var(--text);font:inherit;margin-top:8px}
.spt{width:100%;border-collapse:collapse;margin-top:14px;font-size:14px}.spt td{padding:6px 8px;border-bottom:1px solid var(--border)}.spt td:first-child{color:var(--muted);width:42%}
.pic,.card h4,.card h3{cursor:pointer} a.pic{text-decoration:none}.card h3 a{color:inherit;text-decoration:none;display:block}.card h3 a:hover{text-decoration:underline}.pic img{width:100%;height:100%;object-fit:contain;background:#fff}
.pdt{display:grid;grid-template-columns:minmax(120px,230px) 1fr;gap:16px}
.pdt .pic{cursor:zoom-in}
.pdt .pic img{transition:transform .2s ease}.rel{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}.pdq{display:flex;gap:8px;align-items:center;margin:10px 0}
.info{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:18px;margin:0 0 18px}.info h2{margin:0 0 8px;font-size:20px}.info p{margin:0;color:var(--muted)}
details{border-top:1px solid var(--border);padding:10px 0}summary{cursor:pointer;font-weight:700;padding:12px 0}:focus-visible{outline:3px solid var(--link);outline-offset:2px}details p{margin:8px 0 0}
@media(max-width:520px){.pdt{grid-template-columns:1fr}}
.wab{position:fixed;inset-inline-end:16px;bottom:calc(18px + env(safe-area-inset-bottom,0px));width:54px;height:54px;border-radius:50%;background:#25d366;display:grid;place-items:center;box-shadow:0 6px 18px rgba(0,0,0,.25);z-index:8;animation:wabPulse 2.6s ease-in-out infinite;transition:transform .2s}
.wab:hover{transform:scale(1.08)}.wab{display:none!important}@media(max-width:600px){.wab{bottom:calc(80px + env(safe-area-inset-bottom,0px))}}
@keyframes wabPulse{0%,100%{box-shadow:0 6px 18px rgba(0,0,0,.25),0 0 0 0 rgba(37,211,102,.35)}50%{box-shadow:0 6px 18px rgba(0,0,0,.25),0 0 0 9px rgba(37,211,102,0)}}
nav{display:none;background:var(--surface);border-bottom:1px solid var(--border)}
nav .wrap{display:flex;gap:6px;overflow-x:auto;padding-top:4px;padding-bottom:4px}
nav a{padding:8px 14px;white-space:nowrap;font-weight:600;color:var(--muted);border-radius:8px}
nav a.on,nav a:hover{color:var(--text);background:var(--bg)}
.hero{margin:20px 0;border-radius:18px;padding:36px 28px;color:var(--on-brand);background:linear-gradient(120deg,var(--brand),#05b3e9 62%,#06b6d4);background-size:180% 180%;display:grid;gap:10px;opacity:0;animation:heroIn .8s cubic-bezier(.16,1,.3,1) .05s forwards,heroGlow 9s ease-in-out infinite}
.hero h1{margin:0;font-size:clamp(26px,5vw,42px);line-height:1.15}
.hero p{margin:0;max-width:520px;opacity:.9}
.hero a{justify-self:start;margin-top:8px;background:#fff;color:#0667a5;font-weight:800;padding:10px 20px;border-radius:10px;transition:transform .2s,box-shadow .2s}
.hero a:hover{transform:translateY(-2px);box-shadow:0 8px 18px rgba(0,0,0,.25)}
@keyframes heroIn{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
@keyframes heroGlow{0%,100%{background-position:0% 50%}50%{background-position:100% 50%}}
.trust{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:10px;margin-bottom:22px}
.trust div{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:12px 14px;font-size:13px;color:var(--muted);transition:box-shadow .25s,border-color .25s}
.trust div:hover{box-shadow:0 8px 18px rgba(8,20,40,.1);border-color:var(--accent)}
.trust b{display:block;color:var(--text);font-size:14px}
.reveal{opacity:0;transform:translateY(16px);transition:opacity .6s ease,transform .6s ease}
.reveal.in{opacity:1;transform:translateY(0)}
.reveal.in .trust div,.reveal.in.trust div{transition-delay:calc(var(--ri,0)*60ms)}
.layout{display:grid;grid-template-columns:240px 1fr;gap:22px;align-items:start}
aside{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:16px}
aside h3{margin:14px 0 8px;font-size:14px}aside h3:first-child{margin-top:0}
aside label{display:flex;gap:8px;align-items:center;padding:3px 0;cursor:pointer;font-size:14px}
aside input[type=range]{width:100%;accent-color:var(--accent)}
aside{position:sticky;top:90px}
.filter-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
.filter-head h3{margin:0}
#resetf{background:none;border:none;color:var(--link);font-size:13px;font-weight:700;cursor:pointer;padding:8px 10px;min-height:32px}
#resetf:hover{text-decoration:underline}
aside h3{display:flex;align-items:center;gap:6px;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--muted);font-weight:800;padding-bottom:8px;border-bottom:1px solid var(--border)}
aside label{border-radius:10px;padding:6px 8px;margin:0 -8px;transition:background .15s}
aside label:hover{background:var(--bg)}
aside input[type="checkbox"]{appearance:none;-webkit-appearance:none;width:20px;height:20px;border:2px solid var(--border);border-radius:6px;display:inline-grid;place-content:center;cursor:pointer;flex:none;transition:border-color .15s,background .15s}
aside input[type="checkbox"]:checked{background:var(--accent);border-color:var(--accent)}
aside input[type="checkbox"]:checked::after{content:"";width:5px;height:9px;border:solid #fff;border-width:0 2px 2px 0;transform:rotate(45deg) translate(-1px,-1px)}
#pv{font-weight:800;color:var(--accent)}
.pf{padding:2px 0 10px}.pf-row{display:flex;align-items:flex-end;gap:8px}
.pf-f{flex:1;min-width:0;display:flex;flex-direction:column;gap:4px;font-size:12px;color:var(--muted);font-weight:700}
.pf-f input{width:100%;box-sizing:border-box;padding:9px 6px;border:1px solid var(--border);border-radius:8px;background:var(--card,var(--bg));color:inherit;font:inherit;font-size:14px;font-weight:700;text-align:center;min-height:42px;-moz-appearance:textfield}
.pf-f input::-webkit-outer-spin-button,.pf-f input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
.pf-f input:focus{outline:2px solid var(--accent);outline-offset:1px}
.pf-dash{padding-bottom:12px;color:var(--muted)}
.pf-slider{position:relative;height:28px;margin:16px 11px 4px}
.pf-track{position:absolute;top:12px;left:0;right:0;height:4px;border-radius:4px;background:var(--border)}
.pf-fill{position:absolute;top:0;bottom:0;background:var(--accent);border-radius:4px}
.pf input[type=range]{position:absolute;left:-11px;top:0;width:calc(100% + 22px);height:28px;margin:0;background:none;pointer-events:none;-webkit-appearance:none;appearance:none}
.pf input[type=range]::-webkit-slider-runnable-track{background:none;height:28px}
.pf input[type=range]::-moz-range-track{background:none}
.pf input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;pointer-events:auto;width:26px;height:26px;margin-top:1px;border-radius:50%;background:#fff;border:3px solid var(--accent);box-shadow:0 1px 4px rgba(0,0,0,.3);cursor:grab}
.pf input[type=range]::-moz-range-thumb{pointer-events:auto;width:20px;height:20px;border-radius:50%;background:#fff;border:3px solid var(--accent);cursor:grab}
.pf input[type=range]:focus-visible::-webkit-slider-thumb{outline:2px solid var(--accent);outline-offset:2px}
.pf-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:12px}
.pf-chips button{border:1px solid var(--border);background:var(--card,var(--bg));color:inherit;border-radius:999px;padding:6px 11px;font:inherit;font-size:12px;font-weight:700;cursor:pointer;min-height:32px}
.pf-chips button:hover{border-color:var(--accent)}
.pf-chips button[aria-pressed=true]{background:var(--accent);border-color:var(--accent);color:#fff}
.pf-chips .pf-clr{border-style:dashed;color:var(--muted)}.pf-chips .pf-clr[hidden]{display:none}
#topbar{padding:0;font-size:13px;letter-spacing:0;white-space:normal;overflow:visible}
#topbar .top-in{display:flex;align-items:center;justify-content:space-between;gap:14px;min-height:40px;padding-block:5px}
#topbar .top-msgs{display:flex;align-items:center;gap:22px;min-width:0}
#topbar .tm{display:inline-flex;align-items:center;gap:7px;font-weight:600;white-space:nowrap}
#topbar svg{width:15px;height:15px;flex:none;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
#topbar .tm+.tm::before{content:"";width:1px;height:14px;background:rgba(255,255,255,.35);margin-inline-end:15px}
#topbar .top-cta{display:flex;align-items:center;gap:8px;flex:none}
#topbar .tc{position:relative;display:inline-flex;align-items:center;gap:6px;min-height:28px;padding:3px 12px;border-radius:999px;font-weight:700;font-size:12.5px;color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.4);background:rgba(255,255,255,.12);transition:background .15s}
#topbar .tc:hover{background:rgba(255,255,255,.24)}
#topbar .tc-wa{background:#fff;color:var(--brand);border-color:#fff}
#topbar .tc-wa:hover{background:#eaf4fb}
#topbar .tc:focus-visible{outline:2px solid #fff;outline-offset:2px}
html[lang=en] .t-ar{display:none}html:not([lang=en]) .t-en{display:none}
@keyframes tmin{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:none}}
@media(min-width:821px) and (max-width:1000px){#topbar .tm:nth-child(1),#topbar .tm:nth-child(3){display:none}}
#topbar .top-track{display:flex;align-items:center;gap:22px}
#topbar .tm-clone{display:none}
@keyframes mqL{to{transform:translateX(-50%)}}
@keyframes mqR{to{transform:translateX(50%)}}
@media(max-width:820px){
#topbar .top-in{gap:8px;min-height:44px;padding-block:4px}
#topbar .top-msgs{flex:1;min-width:0;overflow:hidden;-webkit-mask-image:linear-gradient(90deg,transparent,#000 6%,#000 94%,transparent);mask-image:linear-gradient(90deg,transparent,#000 6%,#000 94%,transparent)}
#topbar .top-track{display:inline-flex;gap:0;width:max-content;animation:mqL 24s linear infinite}
html[dir=rtl] #topbar .top-track{animation-name:mqR}
#topbar .tm,#topbar .tm-clone{display:inline-flex;font-size:12.5px;padding-inline-end:34px}
#topbar .tm+.tm::before{display:none}
#topbar .tc{min-height:36px;font-size:13px}
#topbar .tc-wa{padding:0 14px}
#topbar .tc-wa .tn{display:none}
#topbar .tc-call{width:36px;padding:0;justify-content:center}
#topbar .tc-call .tn{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}
#topbar svg{width:16px;height:16px}
}
@media(prefers-reduced-motion:reduce){#topbar .top-track{animation:none!important}#topbar .tm-clone{display:none!important}}
@media(prefers-reduced-motion:reduce){#topbar .tm{animation:none}}
.pf-sum{margin-top:10px;font-size:13px;font-weight:800;color:var(--accent)}
.fbtn,.fclose,.fapply,#fbd{display:none}
@media(max-width:820px){
.fbtn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;min-height:48px;margin:0 0 12px;border:1px solid var(--border);border-radius:12px;background:var(--surface);color:inherit;font:inherit;font-size:15px;font-weight:800;cursor:pointer}
.fbtn b{background:var(--accent);color:#fff;border-radius:999px;min-width:22px;height:22px;display:inline-grid;place-items:center;font-size:12px;padding:0 6px}
.fbtn b:empty{display:none}
aside:not(.drp){position:fixed;left:0;right:0;bottom:0;top:auto;z-index:100000;max-height:88vh;overflow-y:auto;overscroll-behavior:contain;border-radius:18px 18px 0 0;display:flex;flex-direction:column;transform:translateY(105%);overflow-x:hidden;visibility:hidden;transition:transform .25s ease,visibility .25s;box-shadow:0 -8px 30px rgba(0,0,0,.25);padding-bottom:0}
aside:not(.drp).open{transform:none;visibility:visible}
aside:not(.drp) .filter-head{order:-3}aside:not(.drp) #pft{order:-2}aside:not(.drp) #pf{order:-2}
.fclose{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border:none;border-radius:50%;background:var(--bg);color:inherit;font-size:26px;line-height:1;cursor:pointer;margin-inline-start:6px}
.fapply{display:block;order:99;position:sticky;bottom:0;margin:12px -16px 0;padding:14px 16px;box-sizing:border-box;border:none;background:var(--accent);color:#fff;font:inherit;font-size:16px;font-weight:800;cursor:pointer;min-height:52px}
#fbd{display:block;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.5);opacity:0;pointer-events:none;transition:opacity .25s}
#fbd.open{opacity:1;pointer-events:auto}
html.fl-lock{overflow:hidden}
}
aside:not(.drp){display:flex;flex-direction:column}
aside:not(.drp) .filter-head{order:-3}
aside:not(.drp) .fg-price{order:-2}
aside:not(.drp) .fg{flex:none}
.fg>h3{cursor:pointer;user-select:none}
.fg>h3::after{content:"";margin-inline-start:auto;width:8px;height:8px;border:solid currentColor;border-width:0 2px 2px 0;transform:rotate(45deg);transition:transform .2s}
.fg.closed>h3::after{transform:rotate(-45deg)}
.fg.closed>:not(h3){display:none}
.fg>h3:focus-visible{outline:2px solid var(--accent);outline-offset:2px}
.fmore{background:none;border:none;color:var(--link);font:inherit;font-size:13px;font-weight:800;cursor:pointer;padding:6px 0;min-height:32px}
.cl5 label:nth-child(n+6):not(:has(input:checked)),.cl6 label:nth-child(n+7):not(:has(input:checked)){display:none}
@media(min-width:821px){aside:not(.drp){max-height:calc(100vh - 110px);overflow-y:auto;overscroll-behavior:contain;scrollbar-width:thin}.pf-chips button{padding:4px 8px;min-height:32px;font-size:12px}.pf-chips{gap:5px}}
.tools{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:10px;flex-wrap:wrap}
.tools h2{margin:0;font-size:20px}
select{padding:8px 10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font:inherit}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px}
.card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:12px;display:flex;flex-direction:column;gap:6px;position:relative;transition:transform .15s,box-shadow .15s;animation:cardIn .45s ease both}
.card:hover{transform:translateY(-3px);box-shadow:0 10px 24px rgba(8,20,40,.14)}
@keyframes cardIn{from{opacity:0;transform:translateY(10px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}
.pic{aspect-ratio:1/1;width:100%;border-radius:12px;background:radial-gradient(circle at 50% 40%,var(--surface),var(--bg));border:1px solid var(--border);display:grid;place-items:center;color:var(--brand);overflow:hidden}
.pic img{transition:transform .4s cubic-bezier(.16,1,.3,1)}
.card:hover .pic img{transform:scale(1.08)}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) .pic{color:var(--accent)}}
.pic svg{width:74%;height:auto}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) .logo img{background:#fff;border-radius:8px;padding:3px 8px}}
:root[data-theme="dark"] .logo img{background:#fff;border-radius:8px;padding:3px 8px}
.badge{position:absolute;top:10px;inset-inline-start:10px;background:var(--sale);color:#fff;font-size:12px;font-weight:800;padding:2px 8px;border-radius:99px;animation:badgePulse 2.2s ease-in-out infinite}
@keyframes badgePulse{0%,100%{box-shadow:0 0 0 0 rgba(255,90,31,.45)}50%{box-shadow:0 0 0 6px rgba(255,90,31,0)}}
.oosb{position:absolute;top:10px;inset-inline-end:10px;z-index:2;background:rgba(15,20,30,.78);color:#fff;font-size:11px;font-weight:800;padding:3px 10px;border-radius:99px}
.pic.oos{filter:grayscale(.55);opacity:.72}
.pic.oos img{filter:grayscale(.2)}
.card h4,.card h3{margin:0;font-size:14px;line-height:1.35;min-height:2.7em}
.meta{font-size:12px;color:var(--muted)}
.price{font-weight:800;font-size:17px}
.price s{font-weight:400;font-size:13px;color:var(--muted);margin-inline-start:6px}
.stock{font-size:12px;font-weight:700;color:var(--ok)}.stock.out{color:#d93a3a}
.card button{margin-top:auto;padding:9px;border:0;border-radius:9px;font:inherit;font-weight:700;background:var(--brand);color:var(--on-brand);cursor:pointer}
.card button:disabled{opacity:.45;cursor:not-allowed}
.empty{padding:40px;text-align:center;color:var(--muted)}
footer{background:var(--brand);color:var(--on-brand);margin-top:40px;padding:32px 0 20px}
.fcols{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:24px}.fsoc{display:flex;gap:10px;margin-top:10px}.fsoc a{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.12);display:grid;place-items:center;color:#fff}.fsoc a:hover{background:var(--accent)}
footer h5,footer h3{margin:0 0 8px;font-size:15px}footer p,footer a{display:block;font-size:14px;opacity:.85;margin:4px 0}
footer form{display:flex;gap:6px}footer input{flex:1;min-width:0;padding:9px;border-radius:8px;border:0}
footer button{padding:9px 14px;border:0;border-radius:8px;background:var(--accent);color:var(--on-accent);font-weight:700}
.copy{margin-top:22px;border-top:1px solid rgba(255,255,255,.15);padding-top:14px;font-size:13px;opacity:1}
#toast{position:fixed;left:50%;bottom:calc(20px + env(safe-area-inset-bottom,0px));transform:translate(-50%,40px);background:var(--brand);color:var(--on-brand);padding:10px 18px;border-radius:99px;opacity:0;transition:.25s;pointer-events:none;z-index:9}
#toast.show{opacity:1;transform:translate(-50%,0)}
.plan{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:18px;margin-bottom:24px}
.plan h2{margin:0 0 4px;font-size:22px}.plan p{margin:0 0 12px;color:var(--muted)}
.ptools{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:8px 0}
.btn{display:inline-block;border:1px solid var(--border);background:var(--bg);color:var(--text);font:inherit;font-weight:700;padding:8px 14px;border-radius:10px;cursor:pointer}
.btn.pri{background:var(--accent);color:var(--on-accent);border-color:transparent}
#cv{display:block;margin:8px auto 0;border-radius:12px;border:1px solid var(--border);background:var(--bg);touch-action:none;cursor:crosshair;max-width:100%}
.pstats{display:flex;gap:10px;flex-wrap:wrap;margin:12px 0 4px}
.pstats>span{background:var(--bg);border:1px solid var(--border);border-radius:99px;padding:4px 14px;font-size:13px}
.prstats{margin-top:12px}.prstats h3{margin:0 0 8px;font-size:15px}.prgrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px}.prcard{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:9px 12px}.prhead{display:flex;justify-content:space-between;gap:8px;font-size:13px;font-weight:700}.prbar{height:7px;margin-top:7px;border-radius:99px;background:var(--border);overflow:hidden}.prbar span{display:block;height:100%;border-radius:inherit;background:var(--accent)}
.sl{gap:18px;flex-wrap:wrap;align-items:center;font-size:13px;color:var(--muted);margin-top:10px}.sl input{accent-color:var(--accent);vertical-align:middle}.pgen{display:flex;flex-wrap:wrap;gap:10px;align-items:end;background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:12px;margin:10px 0}.pgf{display:flex;flex-direction:column;gap:4px;min-width:140px;flex:1}.pgf label{font-size:12px;color:var(--muted);font-weight:700}.pgf select,.pgf input{padding:9px 10px;border:1px solid var(--border);border-radius:9px;background:var(--surface);color:var(--text);font:inherit}.pcvwrap{position:relative;margin-top:8px}.pdimm{background:var(--bg);border:1px solid var(--border);border-radius:99px;padding:4px 14px;font-size:13px;color:var(--muted)}.plan{box-shadow:0 1px 2px rgba(8,20,40,.04),0 10px 28px -16px rgba(8,20,40,.16)}@media(max-width:620px){.pgen{flex-direction:column;align-items:stretch}.pgf{min-width:0}}
.brands{margin-bottom:22px}.brands h2{font-size:20px;margin:0 0 10px}
.bchips{display:flex;flex-wrap:wrap;gap:10px}
.bchip{display:flex;align-items:baseline;gap:8px;padding:10px 16px;background:var(--surface);border:1px solid var(--border);border-radius:12px;font:inherit;color:var(--text);cursor:pointer;font-weight:800;font-size:16px}
.bchip small{color:var(--muted);font-weight:600;font-size:12px}.bchip:hover{border-color:var(--accent)}
.pmopts{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
.pmopt{flex:1;min-width:120px;padding:10px 14px;border:1px solid var(--border);border-radius:12px;background:var(--surface);color:var(--text);font:inherit;font-weight:700;cursor:pointer}
.pmopt.active{background:var(--accent);color:var(--on-accent);border-color:transparent}
.ipbox{margin-top:10px;padding:12px;border:1px solid var(--border);border-radius:12px;background:var(--surface)}
.ipnum{font-size:20px;font-weight:800;margin:6px 0;direction:ltr;text-align:center}
.qrimg{display:block;max-width:220px;width:100%;height:auto;margin:10px auto;border-radius:10px;border:1px solid var(--border)}
.brands{margin:0 0 22px}.brands h3{margin:0 0 10px;font-size:17px}
.chips{display:flex;flex-wrap:wrap;gap:8px}
.chip{border:1px solid var(--border);background:var(--surface);color:var(--text);font:inherit;font-weight:700;padding:8px 16px;border-radius:99px;cursor:pointer}
.chip:hover{border-color:var(--accent)}.chip b{color:var(--accent);margin-inline-start:6px}
.burger{display:grid;width:42px;height:42px;border:1px solid var(--border);background:var(--bg);color:var(--text);border-radius:10px;cursor:pointer;place-items:center;padding:0;order:-1}.bar{direction:ltr}html[dir=rtl] .bar>*{direction:rtl}.bar .burger{order:1}.bar .lang{order:2}.bar .theme{order:3}.bar .cart{order:4}.bar .search{order:5}.bar .logo{order:9;margin-left:auto}@media(max-width:600px){.bar{gap:8px;padding-left:12px;padding-right:12px}.bar .search{order:10;flex:1 1 100%}}
.dr{position:fixed;inset:0;z-index:20;visibility:hidden;pointer-events:none}.dr.open{visibility:visible;pointer-events:auto}
.drb{position:absolute;inset:0;background:rgba(0,0,0,.5);opacity:0;transition:opacity .25s}.dr.open .drb{opacity:1}
.drp{position:absolute;top:0;bottom:0;left:0;right:auto;width:min(310px,86vw);background:var(--surface);color:var(--text);padding:calc(16px + env(safe-area-inset-top,0px)) 16px calc(16px + env(safe-area-inset-bottom,0px));overflow:auto;transition:transform .28s;display:flex;flex-direction:column;gap:6px}
.drp{transform:translateX(-100%)}.dr.open .drp{transform:none}
.drh{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}.drl a{display:block;padding:12px 8px;border-bottom:1px solid var(--border);font-weight:700}.drl details{border-top:0;padding:0}.drl summary{display:flex;align-items:center;justify-content:space-between;padding:12px 8px;border-bottom:1px solid var(--border);font-weight:700;cursor:pointer;list-style:none;color:var(--text)}.drl summary::-webkit-details-marker{display:none}.drl summary::after{content:'+';font-weight:700;color:var(--muted)}.drl details[open] summary::after{content:'−'}.drl details a{padding-inline-start:28px;font-weight:600;color:var(--muted);background:var(--bg)}.drl a.on{color:var(--accent)}
.drx{border:0;background:none;color:var(--text);font-size:26px;line-height:1;cursor:pointer}

@media(max-width:820px){.layout{grid-template-columns:1fr}}
@media(max-width:520px){.grid{grid-template-columns:repeat(2,1fr);gap:10px}.card{padding:8px}.card h4,.card h3{font-size:13px}.price{font-size:15px}}
/* premium */
body{-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility}
.top{font-size:12px;letter-spacing:.06em;padding:8px 0}
header{background:color-mix(in srgb,var(--surface) 88%,transparent);-webkit-backdrop-filter:saturate(1.5) blur(14px);backdrop-filter:saturate(1.5) blur(14px);border-bottom:1px solid color-mix(in srgb,var(--border) 60%,transparent)}
@media(max-width:820px){header{background:var(--surface);-webkit-backdrop-filter:none;backdrop-filter:none}}
.search input,.lang,.cart{border-radius:12px}
.hero{border-radius:24px;padding:56px 40px;box-shadow:0 28px 50px -28px rgba(10,31,68,.55),inset 0 1px 0 rgba(255,255,255,.14);animation:heroIn .8s cubic-bezier(.16,1,.3,1) .05s forwards}
.hero h1{letter-spacing:-.01em}
.hero a{border-radius:99px;padding:12px 28px}
.trust div,.info,aside,.card{border-radius:18px;border-color:color-mix(in srgb,var(--border) 70%,transparent);box-shadow:0 1px 2px rgba(8,20,40,.04),0 10px 28px -16px rgba(8,20,40,.16)}
.card{padding:14px;gap:8px}
.card:hover{transform:translateY(-4px);box-shadow:0 2px 4px rgba(8,20,40,.05),0 24px 44px -20px rgba(8,20,40,.3)}
.pic{border-radius:14px;border-color:transparent}
.badge{animation:none;letter-spacing:.02em}
.tools h2,.info h2{letter-spacing:-.005em}
footer{box-shadow:inset 0 1px 0 rgba(255,255,255,.08)}
.flogo{border-radius:12px;padding:5px 10px}.flogo .wm{width:min(130px,50vw)}
footer{padding:48px 0 0}.fgrid{display:grid;grid-template-columns:1.5fr .8fr 1.1fr 1.4fr;gap:40px;align-items:start}.fgrid h3{font-size:15px;font-weight:800;margin:0 0 14px}.fb .flogo{display:block;margin:0 0 14px;padding:0;border-radius:0;background:none;line-height:0}.fb .flogo img{display:block;width:132px;height:auto}.fb p{margin:0 0 16px;line-height:1.8;font-size:14px;opacity:.9;max-width:36ch}.fb .fsoc{margin-top:0}.fc a.fl{display:block;padding:7px 0;font-size:14px;color:inherit;opacity:.9}.fc a.fl:hover{opacity:1;text-decoration:underline}.fc a.fct{display:flex;align-items:center;gap:10px;padding:8px 0;font-size:14px;color:inherit}.fc a.fct svg{flex:none;opacity:.85}.fc a.fct span{white-space:nowrap}.fc a.fct:hover{text-decoration:underline}.fn p{margin:0 0 12px;font-size:14px;opacity:.9}#nlf{display:flex;gap:8px}#nlf input{flex:1;min-width:0;border:0;border-radius:10px;padding:11px 12px}#nlf button{flex:none;border:0;border-radius:10px;padding:0 16px;font-weight:700;cursor:pointer}.fbar{margin-top:36px;border-top:1px solid rgba(255,255,255,.18);padding:16px 0 20px;display:flex;flex-wrap:wrap;gap:12px 24px;align-items:center;justify-content:space-between}.fbar .paym{margin:0;justify-content:flex-start}.fbar .copy{margin:0;border:0;padding:0;font-size:13px;opacity:.85;display:flex;flex-wrap:wrap;gap:4px 14px}@media(max-width:900px){.fgrid{grid-template-columns:1fr 1fr;gap:28px}.fb,.fn{grid-column:1/-1}.fb p{max-width:none}}
.paym{display:flex;flex-wrap:wrap;align-items:center;gap:8px;justify-content:center;margin:0 0 14px;font-size:12px;color:inherit}
.pchip{background:var(--bg);border:1px solid var(--border);border-radius:999px;padding:4px 10px}
.bestsellers{margin:22px 0}
.bestsellers h2{margin:0 0 12px;font-size:19px}
.bsrow{display:flex;gap:14px;overflow-x:auto;padding-bottom:10px;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch}
.bsrow .card{flex:0 0 200px;scroll-snap-align:start}
.bsrow::-webkit-scrollbar{height:6px}
.bsrow::-webkit-scrollbar-thumb{background:var(--border);border-radius:99px}
.mnav{display:none}
@media(max-width:820px){.mnav{display:flex;position:fixed;inset-inline:0;bottom:0;background:var(--surface);border-top:1px solid var(--border);z-index:9;padding-bottom:env(safe-area-inset-bottom,0px)}.mnav button{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;padding:8px 0 6px;background:none;border:none;color:var(--muted);font-size:11px;cursor:pointer}.mnav button:active{color:var(--accent)}body{padding-bottom:calc(58px + env(safe-area-inset-bottom,0px))}.wab{bottom:calc(76px + env(safe-area-inset-bottom,0px))}}
.plan h3.ps{display:flex;align-items:center;gap:8px;font-size:16px;margin:18px 0 8px}
.plan h3.ps i{display:inline-grid;place-items:center;width:26px;height:26px;border-radius:50%;background:var(--accent);color:var(--on-accent);font-style:normal;font-size:13px;font-weight:800}
.plock{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);margin:6px 0}
.rcnt{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px}
.rc{display:flex;align-items:center;justify-content:space-between;gap:6px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:6px 8px;font-size:13px}
.rc>span:first-child{flex:1;min-width:0;font-weight:700}
.stp{display:inline-flex;align-items:center;gap:4px}
.stp button{width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--bg);color:var(--text);font-weight:800;font-size:16px;line-height:1;cursor:pointer;padding:0}
.stp button:hover{border-color:var(--accent)}
.stp b{min-width:24px;text-align:center}
.rdet{border:0;padding:6px 0}
.rdet summary{cursor:pointer;font-weight:700;color:var(--link)}
.rlist{display:grid;gap:6px;margin-top:8px}
.rrow{display:grid;grid-template-columns:1.2fr 1.4fr .8fr auto;gap:6px;align-items:center}
.rrow input,.rrow select{padding:7px 8px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font:inherit;min-width:0;width:100%}
.rrow button{border:1px solid var(--border);background:var(--bg);color:var(--text);border-radius:8px;width:32px;height:32px;cursor:pointer}
.seg{display:inline-flex;border:1px solid var(--border);border-radius:10px;overflow:hidden}
.seg button{border:0;background:var(--bg);color:var(--text);font:inherit;font-weight:700;padding:8px 12px;cursor:pointer}
.seg button.on{background:var(--accent);color:var(--on-accent)}
.pce{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px 18px;align-items:center;background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:10px 14px;margin-top:10px;font-size:13px}
.pce.off{opacity:.6}
.pceh{grid-column:1/-1;font-weight:800}
.pce label{display:grid;gap:4px;color:var(--muted)}
.pce input[type=range]{width:100%;accent-color:var(--accent)}
.pce b{color:var(--text)}
.psys{margin-top:16px;border:2px solid var(--accent);border-radius:16px;padding:16px;background:var(--bg)}
.psys h3{margin:0 0 4px;font-size:18px}
.sgrid{display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:6px 10px;align-items:center;margin:10px 0}
.sgrid .h{font-size:12px;font-weight:800;color:var(--muted)}
.sgrid .t{font-weight:800;display:flex;align-items:center;gap:6px}
.sgrid .t i{width:10px;height:10px;border-radius:50%;display:inline-block}
.sall{display:flex;flex-wrap:wrap;gap:6px;align-items:center;font-size:13px;margin-bottom:8px}
.sln{display:grid;grid-template-columns:1fr;gap:6px;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:10px 12px;margin-top:8px}
.sln select{width:100%;min-width:0}
.sln .sp{display:flex;justify-content:space-between;gap:8px;font-size:13px;flex-wrap:wrap}
.sln .sh{display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;align-items:center;font-weight:800}
.sln.na{border-style:dashed;color:var(--muted)}
.stot{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-top:12px;font-size:17px;font-weight:800}
.psys .dirty{border-color:var(--sale)}
.search{align-items:stretch}
.search .burger{order:-1;width:44px;height:auto;min-height:40px;flex:none;border:1px solid var(--border);border-inline-end:0;border-radius:0;border-start-start-radius:12px;border-end-start-radius:12px;background:var(--bg);color:var(--text);padding:0}
.search input{border-radius:0!important}
@media(max-width:620px){.rrow{grid-template-columns:1fr 1fr}.sgrid{grid-template-columns:1fr 1fr 1fr}}
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
var L=(/[?&]lang=en(?:&|$)/.test(location.search)?'en':null)||(function(){try{return localStorage.getItem('vg-lang')}catch(e){}})()||'ar',cart=0,$=function(id){return document.getElementById(id)},T=function(k){return D[L][k]},N=function(n){return n.toLocaleString('en-US')};
function chk(box,items,name,tr){$(box).innerHTML=items.map(function(x){return '<label><input type="checkbox" name="'+name+'" value="'+x+'"> <span'+(tr?' data-t="'+x+'"':'')+'>'+(tr?'':x)+'</span></label>'}).join('')}
chk('brands',[...new Set(P.map(function(p){return p.b}).filter(Boolean))],'b');chk('types',['in','out','both'],'t',1);chk('cats',['cat_hd','cat_ip','cat_wf','cat_dvr','cat_hdd','cat_mem','cat_psu','cat_cab','cat_rack','cat_acc','cat_net','cat_att'],'c',1);
function sel(n){return [...document.querySelectorAll('input[name='+n+']:checked')].map(function(e){return e.value})}
var SV={
bu:'<rect x="14" y="26" width="66" height="30" rx="13" fill="currentColor"/><rect x="78" y="22" width="14" height="38" rx="6" fill="currentColor"/><circle cx="90" cy="41" r="9" fill="var(--surface)"/><circle cx="90" cy="41" r="4" fill="var(--accent)"/><path d="M36 56v14h14" stroke="currentColor" stroke-width="7" fill="none" stroke-linecap="round"/>',
do:'<ellipse cx="60" cy="72" rx="38" ry="9" fill="currentColor" opacity=".25"/><path d="M24 66a36 36 0 0 1 72 0z" fill="currentColor"/><circle cx="60" cy="52" r="13" fill="var(--surface)"/><circle cx="60" cy="52" r="6" fill="var(--accent)"/>',
cu:'<rect x="34" y="24" width="52" height="42" rx="12" fill="currentColor"/><circle cx="60" cy="45" r="13" fill="var(--surface)"/><circle cx="60" cy="45" r="6" fill="var(--accent)"/><rect x="44" y="66" width="32" height="8" rx="4" fill="currentColor" opacity=".5"/><path d="M92 28a12 12 0 0 1 12 12M92 16a24 24 0 0 1 24 24" stroke="var(--accent)" stroke-width="4" fill="none" stroke-linecap="round"/>',
pt:'<rect x="50" y="8" width="20" height="10" rx="3" fill="currentColor"/><circle cx="60" cy="46" r="26" fill="currentColor"/><circle cx="60" cy="46" r="15" fill="var(--surface)"/><circle cx="60" cy="46" r="7" fill="var(--accent)"/><path d="M96 28a40 40 0 0 1 0 36" stroke="var(--accent)" stroke-width="4" fill="none" stroke-linecap="round"/>',
dv:'<rect x="12" y="30" width="96" height="32" rx="6" fill="currentColor"/><rect x="20" y="38" width="42" height="5" rx="2" fill="var(--surface)" opacity=".7"/><rect x="20" y="48" width="42" height="5" rx="2" fill="var(--surface)" opacity=".7"/><circle cx="82" cy="46" r="6" fill="var(--accent)"/><circle cx="97" cy="46" r="3" fill="var(--surface)"/><rect x="20" y="62" width="10" height="5" fill="currentColor"/><rect x="90" y="62" width="10" height="5" fill="currentColor"/>',
hd:'<rect x="26" y="12" width="68" height="64" rx="8" fill="currentColor"/><circle cx="58" cy="44" r="20" fill="var(--surface)" opacity=".85"/><circle cx="58" cy="44" r="6" fill="currentColor"/><path d="M84 20 66 42" stroke="var(--accent)" stroke-width="5" stroke-linecap="round"/>',
mc:'<path d="M38 12h34l16 16v50a4 4 0 0 1-4 4H38a4 4 0 0 1-4-4V16a4 4 0 0 1 4-4z" fill="currentColor"/><g fill="var(--accent)"><rect x="42" y="14" width="5" height="14"/><rect x="51" y="14" width="5" height="14"/><rect x="60" y="14" width="5" height="14"/></g><rect x="44" y="46" width="30" height="20" rx="3" fill="var(--surface)" opacity=".8"/>',
ps:'<rect x="22" y="22" width="76" height="46" rx="6" fill="currentColor"/><circle cx="46" cy="45" r="14" fill="var(--surface)" opacity=".85"/><path d="M45 36l-6 11h7l-3 10 11-14h-7l4-7z" fill="var(--accent)"/><rect x="72" y="32" width="18" height="5" rx="2" fill="var(--surface)" opacity=".6"/><rect x="72" y="42" width="18" height="5" rx="2" fill="var(--surface)" opacity=".6"/>',
ca:'<circle cx="60" cy="45" r="32" fill="currentColor"/><circle cx="60" cy="45" r="22" fill="var(--surface)" opacity=".9"/><circle cx="60" cy="45" r="14" fill="var(--accent)"/><circle cx="60" cy="45" r="6" fill="var(--surface)"/><path d="M92 58q14 6 18 20" stroke="currentColor" stroke-width="5" fill="none" stroke-linecap="round"/>',
rk:'<rect x="34" y="8" width="52" height="76" rx="5" fill="currentColor"/><g fill="var(--surface)" opacity=".85"><rect x="40" y="16" width="40" height="10" rx="2"/><rect x="40" y="31" width="40" height="10" rx="2"/><rect x="40" y="46" width="40" height="10" rx="2"/><rect x="40" y="61" width="40" height="10" rx="2"/></g><circle cx="74" cy="21" r="2.5" fill="var(--accent)"/><circle cx="74" cy="36" r="2.5" fill="var(--accent)"/>',
ad:'<rect x="38" y="26" width="38" height="38" rx="8" fill="currentColor"/><rect x="48" y="14" width="5" height="14" fill="var(--accent)"/><rect x="62" y="14" width="5" height="14" fill="var(--accent)"/><path d="M76 45h14q16 0 16 18" stroke="currentColor" stroke-width="5" fill="none" stroke-linecap="round"/>',
bx:'<rect x="26" y="20" width="68" height="50" rx="6" fill="currentColor"/><rect x="34" y="28" width="52" height="34" rx="3" fill="var(--surface)" opacity=".85"/><g fill="var(--accent)"><circle cx="31" cy="25" r="2.5"/><circle cx="89" cy="25" r="2.5"/><circle cx="31" cy="65" r="2.5"/><circle cx="89" cy="65" r="2.5"/></g>',
jk:'<rect x="34" y="34" width="44" height="22" rx="4" fill="currentColor"/><rect x="78" y="38" width="18" height="14" rx="3" fill="var(--accent)"/><rect x="22" y="40" width="12" height="10" rx="2" fill="currentColor" opacity=".6"/><path d="M50 34v22M60 34v22" stroke="var(--surface)" stroke-width="3"/>'};
function svg(k){return '<svg viewBox="0 0 120 90">'+SV[k]+'</svg>'}
var PAGE=(VGPAGE==='home'?8:16),SHOWN=PAGE,KEEP=false;
function render(){
 var q=$('q').value.toLowerCase(),b=sel('b'),t=sel('t'),c=sel('c'),mx=PHI,so=$('stock').checked,s=$('sort').value;
 window.pfSum&&pfSum();
 var R=P.filter(function(p){return !p.hide&&ep(p)>=PLO&&ep(p)<=mx&&($('onsale').checked?ep(p)<p.p:true)&&(!so||p.s)&&(!b.length||b.includes(p.b))&&(!c.length||c.includes(p.c))&&(!t.length||t.some(function(x){return p.pl==x||(p.pl=='both'&&x!='both')}))&&(p.a+' '+p.e+' '+p.b).toLowerCase().includes(q)});
 R.sort(function(x,y){return s=='pa'?ep(x)-ep(y):s=='pd'?ep(y)-ep(x):s=='az'?(L=='ar'?x.a.localeCompare(y.a,'ar'):x.e.localeCompare(y.e)):x.i-y.i});
 $('count').textContent='· '+PN(R.length);
 cupd();
 document.querySelectorAll('#brands label').forEach(function(l){var i=l.querySelector('input'),t=l.querySelector('span');if(i&&t)t.textContent=BN(i.value)});
 if(!KEEP)SHOWN=PAGE;KEEP=false;
 var RS=R.slice(0,SHOWN);
 $('grid').innerHTML=RS.length?RS.map(function(p,idx){var e=ep(p),d=e<p.p?Math.round((1-e/p.p)*100):0;
  return '<article class="card" style="animation-delay:'+(Math.min(idx,14)*30)+'ms">'+(d?'<span class="badge">-'+d+'%</span>':'')+(!p.s?'<span class="oosb">'+T('oos')+'</span>':'')+'<a class="pic'+(p.s?'':' oos')+'" href="'+(p.sl?'/p/'+encodeURIComponent(p.sl):'product.php?id='+p.id.slice(1))+'">'+pimg(p)+'</a><div class="meta">'+[esc(BN(p.b)),T(p.c),p.pl?T(p.pl):''].filter(Boolean).join(' · ')+'</div><h3><a href="'+(p.sl?'/p/'+encodeURIComponent(p.sl):'product.php?id='+p.id.slice(1))+'">'+esc(nmp(p))+'</a></h3><div class="price">'+prc(p)+'</div><div class="stock'+(p.s?'':' out')+'">'+(p.s?T('stock'):T('oos'))+'</div><button type="button"'+(p.s?'':' disabled')+' data-n="'+p.i+'">'+(p.s?T('add'):T('un'))+'</button></article>'
 }).join(''):'<div class="empty">'+T('none')+'</div>';
 $('loadmore').innerHTML=SHOWN<R.length?'<button type="button" class="btn" id="lmbtn">'+T('loadmore')+'</button>':''}
function bchips(){var m={};P.forEach(function(p){if(p.b)m[p.b]=(m[p.b]||0)+1});
 $('bchips').innerHTML=Object.keys(m).sort(function(a,b){return m[b]-m[a]}).map(function(b){return '<button type="button" class="bchip" data-b="'+esc(b)+'">'+esc(BN(b))+' <small>'+PN(m[b])+'</small></button>'}).join('')}
function cchips(){var m={};P.forEach(function(p){if(p.c)m[p.c]=(m[p.c]||0)+1});
 $('cchips').innerHTML=Object.keys(m).sort(function(a,b){return m[b]-m[a]}).map(function(c){return '<button type="button" class="bchip" data-c="'+c+'">'+T(c)+' <small>'+PN(m[c])+'</small></button>'}).join('')}
$('bchips').onclick=function(e){var k=e.target.closest('button[data-b]');if(!k)return;document.querySelectorAll('input[name=b]').forEach(function(x){x.checked=x.value==k.dataset.b});render();$('shop').scrollIntoView({behavior:'smooth'})};
$('cchips').onclick=function(e){var k=e.target.closest('button[data-c]');if(!k)return;if(VGPAGE!=='shop'){location.href='/shop?c='+encodeURIComponent(k.dataset.c);return}document.querySelectorAll('input[name=c]').forEach(function(x){x.checked=x.value==k.dataset.c});render();$('shop').scrollIntoView({behavior:'smooth'})};
$('loadmore').onclick=function(e){if(e.target.id=='lmbtn'){if(VGPAGE==='home'){location.href='/shop';return}KEEP=true;SHOWN+=PAGE;render()}};
(function(){
var ar=(typeof L==='undefined')||L==='ar';
var tx={t:ar?'السعر':'Price',a:ar?'من':'From',b:ar?'إلى':'To',cur:ar?'ج.م':'EGP',all:ar?'كل الأسعار':'All prices',clr:ar?'مسح السعر':'Clear price'};
function q(i){return document.getElementById(i)}
var r1=q('pr1'),r2=q('pr2'),n1=q('pmin'),n2=q('pmaxi'),fill=q('pffill'),chips=q('pfchips');
if(!r1||!r2||!n1||!n2)return;
function fmt(n){try{return N(n)}catch(e){return String(n)}}
q('pft').textContent=tx.t;q('pla').textContent=tx.a+' ('+tx.cur+')';q('plb').textContent=tx.b+' ('+tx.cur+')';
r1.max=r2.max=n1.max=n2.max=PMX;n2.placeholder=PMX;
r1.setAttribute('aria-label',tx.t+' - '+tx.a);r2.setAttribute('aria-label',tx.t+' - '+tx.b);
n1.setAttribute('aria-label',tx.a);n2.setAttribute('aria-label',tx.b);
var R=[[0,1000],[1000,3000],[3000,6000],[6000,PMX]].filter(function(x){return x[0]<PMX});
R.forEach(function(x){var b=document.createElement('button');b.type='button';b.dataset.lo=x[0];b.dataset.hi=x[1]>PMX?PMX:x[1];
 b.textContent=x[0]===0?(ar?'أقل من ':'Under ')+fmt(x[1]):(x[1]>=PMX?(ar?'أكثر من ':'Over ')+fmt(x[0]):fmt(x[0])+' – '+fmt(x[1]));
 b.onclick=function(){var l=+b.dataset.lo,h=+b.dataset.hi;if(PLO===l&&PHI===h){l=0;h=PMX}set(l,h)};chips.appendChild(b)});
var c=document.createElement('button');c.type='button';c.id='pclr';c.className='pf-clr';c.textContent=tx.clr;c.onclick=function(){set(0,PMX)};chips.appendChild(c);
function ui(){r1.value=PLO;r2.value=PHI;n1.value=PLO>0?vgFm(PLO):'';n2.value=PHI<PMX?vgFm(PHI):'';
 fill.style.insetInlineStart=(PLO/PMX*100)+'%';fill.style.insetInlineEnd=(100-PHI/PMX*100)+'%';
 chips.querySelectorAll('button[data-lo]').forEach(function(b){b.setAttribute('aria-pressed',(+b.dataset.lo===PLO&&+b.dataset.hi===PHI)?'true':'false')});
 c.hidden=!(PLO>0||PHI<PMX)}
function set(l,h){l=Math.max(0,Math.min(PMX,l));h=Math.max(0,Math.min(PMX,h));if(l>h){var t=l;l=h;h=t}PLO=l;PHI=h;ui();render()}
function num(v,d){v=String(v).replace(/[٠-٩]/g,function(x){return x.charCodeAt(0)-1632}).replace(/[,٬\s]/g,'');var n=parseFloat(v);return isNaN(n)?d:n}
r1.oninput=function(){var v=+r1.value;if(v>PHI)v=PHI;PLO=v;ui();render()};
r2.oninput=function(){var v=+r2.value;if(v<PLO)v=PLO;PHI=v;ui();render()};
n1.onchange=function(){set(num(n1.value,0),PHI)};
n2.onchange=function(){set(PLO,num(n2.value,PMX))};function vgFm(n){return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g,',')}[n1,n2].forEach(function(el){el.oninput=function(){var d=this.value.replace(/[٠-٩]/g,function(x){return x.charCodeAt(0)-1632}).replace(/\D/g,'');this.value=d?vgFm(+d):''}});
window.pfReset=function(){PLO=0;PHI=PMX;ui()};
window.pfSum=function(){var e=q('pv');if(!e)return;e.textContent=(PLO<=0&&PHI>=PMX)?tx.all:(ar?'من '+fmt(PLO)+' إلى '+fmt(PHI)+' '+tx.cur:fmt(PLO)+' – '+fmt(PHI)+' '+tx.cur)};
var as=document.querySelector('aside:not(.drp)'),sec=as&&as.parentElement.querySelector('section');
if(as&&sec){
var T2={f:ar?'الفلاتر والسعر':'Filters & price',x:ar?'إغلاق':'Close'};
var fb=document.createElement('button');fb.type='button';fb.className='fbtn';fb.setAttribute('aria-expanded','false');fb.innerHTML='<span></span><b id="fbadge"></b>';fb.firstChild.textContent=T2.f;
sec.insertBefore(fb,sec.firstChild);
var bd=document.createElement('div');bd.id='fbd';document.body.appendChild(bd);
var fx=document.createElement('button');fx.type='button';fx.className='fclose';fx.textContent='×';fx.setAttribute('aria-label',T2.x);
var hd=as.querySelector('.filter-head');(hd||as).appendChild(fx);
var ap=document.createElement('button');ap.type='button';ap.className='fapply';as.appendChild(ap);
function opn(o){as.classList.toggle('open',o);bd.classList.toggle('open',o);document.documentElement.classList.toggle('fl-lock',o);fb.setAttribute('aria-expanded',o?'true':'false');if(o)fx.focus({preventScroll:true});else if(document.activeElement&&as.contains(document.activeElement))fb.focus({preventScroll:true})}
fb.onclick=function(){opn(true)};fx.onclick=bd.onclick=function(){opn(false)};
ap.onclick=function(){opn(false);sec.scrollIntoView({behavior:'smooth',block:'start'})};
document.addEventListener('keydown',function(e){if(e.key==='Escape'&&as.classList.contains('open'))opn(false)});
var mq=window.matchMedia('(min-width:821px)');var onmq=function(){if(mq.matches)opn(false)};if(mq.addEventListener)mq.addEventListener('change',onmq);else mq.addListener(onmq);
function sync(){var n=as.querySelectorAll('input[type=checkbox]:checked').length+((PLO>0||PHI<PMX)?1:0);var b=q('fbadge');if(b)b.textContent=n?fmt(n):'';var ce=q('count');var cnt=ce?(ce.textContent.match(/[0-9٠-٩][0-9٠-٩,٬.]*/)||[''])[0]:'';ap.textContent=cnt?(ar?'عرض '+cnt+' منتج':'Show '+cnt+' products'):(ar?'عرض النتائج':'Show results')}
var ce2=q('count');if(ce2&&window.MutationObserver)new MutationObserver(sync).observe(ce2,{childList:true,characterData:true,subtree:true});
sync();
}
if(as){
var tg=ar?{all:function(n){return 'عرض الكل ('+n+')'},less:'عرض أقل'}:{all:function(n){return 'Show all ('+n+')'},less:'Show less'};
var cur=null;
[].slice.call(as.children).forEach(function(k){
if(k.tagName==='H3'){cur=document.createElement('div');cur.className='fg'+(k.id==='pft'?' fg-price':'');as.insertBefore(cur,k);cur.appendChild(k)}
else if(cur&&!k.classList.contains('fapply')&&!k.classList.contains('filter-head'))cur.appendChild(k)});
[['cats',5],['brands',6]].forEach(function(x){var c=q(x[0]);if(!c)return;var n=c.querySelectorAll('label').length;if(n<=x[1]+1)return;
c.classList.add('cl'+x[1]);var m=document.createElement('button');m.type='button';m.className='fmore';m.textContent=tg.all(n);m.setAttribute('aria-expanded','false');
m.onclick=function(){var on=c.classList.toggle('cl'+x[1]);m.textContent=on?tg.all(n):tg.less;m.setAttribute('aria-expanded',on?'false':'true')};
c.parentNode.insertBefore(m,c.nextSibling)});
[].slice.call(as.querySelectorAll('.fg')).forEach(function(g){var h=g.firstChild;h.setAttribute('role','button');h.tabIndex=0;
var closed=!!g.querySelector('#brands')&&!g.querySelector('#brands input:checked');
g.classList.toggle('closed',closed);h.setAttribute('aria-expanded',closed?'false':'true');
function t(){var c=g.classList.toggle('closed');h.setAttribute('aria-expanded',c?'false':'true')}
h.onclick=t;h.onkeydown=function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();t()}}});
}
(function(){var bar=document.getElementById('topbar');if(!bar)return;var w=bar.querySelector('.top-msgs');if(!w||w.querySelector('.top-track'))return;var tr=document.createElement('div');tr.className='top-track';[].slice.call(w.children).forEach(function(e){e.classList.remove('on');tr.appendChild(e)});[].slice.call(tr.children).forEach(function(e){var c=e.cloneNode(true);c.className='tm tm-clone';c.setAttribute('aria-hidden','true');tr.appendChild(c)});w.appendChild(tr)})();
ui();
})();
$('resetf').onclick=function(){document.querySelectorAll('aside input[type=checkbox]').forEach(function(x){x.checked=false});window.pfReset&&pfReset();render();};
function vgSeo(l){var h=document.getElementById('h1'),m=document.querySelector('meta[name="description"]'),o=document.querySelector('meta[property="og:locale"]'),s=VGSEO[l==='ar'?'ar':'en'];if(h)h.textContent=s.h;if(m)m.content=s.d;if(o)o.content=l==='ar'?'ar_EG':'en_US';try{var u=new URL(location.href);u.searchParams.delete('lang');history.replaceState(null,'',u.pathname+u.search)}catch(e){}}function setLang(l){L=l;vgSeo(l);try{localStorage.setItem('vg-lang',l)}catch(e){}
 var h=document.documentElement;h.lang=l;h.dir=l=='ar'?'rtl':'ltr';document.title=VGSEO[l=='ar'?'ar':'en'].t;
 document.querySelectorAll('[data-t]').forEach(function(e){e.textContent=T(e.dataset.t)});
 document.querySelectorAll('[data-p]').forEach(function(e){e.placeholder=T(e.dataset.p)});
 $('lang').textContent=l=='ar'?'EN':'عربي';cchips();render();bsRender();if($('pptype'))fillSizes();pstat();$('wab').href=WA+'?text='+encodeURIComponent(l=='ar'?'مرحبًا Vision Guard، عندي استفسار':'Hello Vision Guard, I have a question');if($('cd').open||VGPAGE==='cart')cartUI();if($('pdg').open)pd(PI);if(window.vgPageLang)vgPageLang()}
document.addEventListener('input',render);
$('lang').onclick=function(){setLang(L=='ar'?'en':'ar');location.reload()};
$('grid').addEventListener('click',function(e){var b=e.target.closest('button[data-n]');if(!b)return;var n=+b.dataset.n;if((CART[n]||0)>=avail(n)){tst('maxq');return}CART[n]=(CART[n]||0)+1;csave();cupd();var o=$('toast');o.textContent=T('added');o.classList.add('show');setTimeout(function(){o.classList.remove('show')},1400)});
var BS=[26,16,9,33,69,22,48,3];
function bsRender(){ var items=BS.map(function(id){return P.find(function(p){return p.id==='p'+id})}).filter(Boolean); var el=$('bsgrid'); if(!el)return; el.innerHTML=items.map(function(p){var e=ep(p),d=e<p.p?Math.round((1-e/p.p)*100):0; return '<article class="card">'+(d?'<span class="badge">-'+d+'%</span>':'')+(!p.s?'<span class="oosb">'+T('oos')+'</span>':'')+'<a class="pic'+(p.s?'':' oos')+'" href="'+(p.sl?'/p/'+encodeURIComponent(p.sl):'product.php?id='+p.id.slice(1))+'">'+pimg(p)+'</a><div class="meta">'+[esc(BN(p.b)),T(p.c),p.pl?T(p.pl):''].filter(Boolean).join(' \u00b7 ')+'</div><h3><a href="'+(p.sl?'/p/'+encodeURIComponent(p.sl):'product.php?id='+p.id.slice(1))+'">'+esc(nmp(p))+'</a></h3><div class="price">'+prc(p)+'</div><div class="stock'+(p.s?'':' out')+'">'+(p.s?T('stock'):T('oos'))+'</div><button type="button"'+(p.s?'':' disabled')+' data-n="'+p.i+'">'+(p.s?T('add'):T('un'))+'</button></article>'; }).join('')}
$('bsgrid')&&$('bsgrid').addEventListener('click',function(e){var b=e.target.closest('button[data-n]');if(!b)return;var n=+b.dataset.n;if((CART[n]||0)>=avail(n)){tst('maxq');return}CART[n]=(CART[n]||0)+1;csave();cupd();var o=$('toast');o.textContent=T('added');o.classList.add('show');setTimeout(function(){o.classList.remove('show')},1400)});
var __PL=(function(){
var TR={ar:{
ph2:'حدد نوع المكان ومساحته (من المساحات الجاهزة أو مساحة مخصصة)، وغيّر عدد الغرف والحمامات وأي مكوّن بحرية من غير أي حدود، حرّك الكاميرات وعدّل مداها وزاويتها، وبعد ما تخلّص هنرشّح لك نظام كاميرات مناسب من المتاح عندنا.',
p_s1:'جهّز مكانك',p_s2:'ضع الكاميرات على المخطط',popt_shop:'محل',p_w:'العرض (م)',p_l:'الطول (م)',
p_lock:'ثبّت المساحة الكلية لما أضيف أو أحذف غرف',p_rooms:'الغرف والمكونات (غيّر العدد زي ما تحب)',p_details:'تفاصيل الغرف (الاسم والمساحة)',p_rname:'الاسم (اختياري)',
p_shuffle:'غيّر توزيع الغرف',p_reset:'رجّع الإعداد الافتراضي',p_t_ip:'IP شبكة',p_t_hd:'HD سلكية',p_t_wf:'واي فاي',
p_grp_out:'خارجية',p_grp_in:'داخلية',p_ob:'خارجية Bullet',p_oz:'زاوية واسعة خارجية',p_id:'داخلية Dome',p_iz:'زاوية واسعة داخلية',
phint:'اضغط على المخطط لإضافة كاميرا · اسحب الكاميرا لتحريكها · اسحب النقطة البيضاء للتدوير · اسحب المربع لتغيير المدى · اسحب الدائرة الصغيرة على الحافة لتغيير الزاوية',
p_nosel:'اضغط على كاميرا في المخطط عشان تعدّل مداها وزاويتها',pfo:'زاوية الرؤية',prn:'المدى (متر)',p_rot:'الاتجاه',
pcv:'إجمالي التغطية التقريبية:',pnote:'التغطية تقريبية، بتحسب الجدران والأبواب بشكل مبسط ومش بتراقب الحمامات. للتصميم الدقيق تواصل مع فريقنا.',
p_priv:'غير مُراقَب (خصوصية)',p_covroom:'التغطية حسب الغرفة',p_open:'مساحة مفتوحة',p_finish:'خلّصت · رشّح لي النظام',
p_auto:'اقترحنا أماكن الكاميرات تلقائيًا',p_cleared:'اتمسحت الكاميرات لأن توزيع الغرف اتغير',
p_sys:'النظام المقترح لمكانك',p_sysp:'عدّل عدد الكاميرات الداخلية والخارجية ونوعها (IP / HD / واي فاي)، وهنختار لك المنتجات المناسبة من المتاح عندنا وتقدر تغيّر أي اختيار.',
p_allto:'حوّل كل الكاميرات إلى:',p_sync:'حدّث من المخطط',p_dirty:'المخطط اتغير بعد الترشيح، اضغط «حدّث من المخطط».',
p_cams:'الكاميرات',p_gear:'المستلزمات',p_dvr:'مسجل DVR',p_hdd:'هارد ديسك',p_psu:'باور سبلاي',p_cab:'سلك كاميرات (كواكس)',p_mem:'كروت ميموري',p_cat6:'سلك شبكة Cat6',
p_total:'الإجمالي التقريبي',p_addall:'أضف الكل للسلة',p_wa:'ابعت الترشيح على واتساب',p_na:'مش متوفر حاليًا، هنجهزلك عرض سعر على واتساب',
p_relax:'مفيش داخلي متوفر، دي خارجية وتصلح للداخل',p_ipn:'كاميرات IP محتاجة مسجل شبكة (NVR) وكابل شبكة وغالبًا سويتش PoE. كلمنا على واتساب نجهزلك عرض كامل.',
p_empty:'مفيش كاميرات في الترشيح. زوّد عدد أي نوع.',p_est:'الكميات تقديرية: مسجل بقنوات تكفي كل الكاميرات السلكية، هارد حوالي 300 جيجا لكل كاميرا، وسلك حوالي 15 متر للداخلية و25 متر للخارجية.',
p_pcs:'الكمية',p_include:'إضافة',p_rooms_s:'الغرف',p_cams_s:'الكاميرات'},
en:{
ph2:'Pick the property type and size (preset sizes or a custom area), then change rooms, bathrooms and anything else freely with no limits. Move the cameras, adjust their range and angle, and when you are done we recommend a matching camera system from what we have in stock.',
p_s1:'Set up your space',p_s2:'Place cameras on the plan',popt_shop:'Shop',p_w:'Width (m)',p_l:'Length (m)',
p_lock:'Keep total area fixed when adding or removing rooms',p_rooms:'Rooms and spaces (change counts as you like)',p_details:'Room details (name and area)',p_rname:'Name (optional)',
p_shuffle:'Rearrange rooms',p_reset:'Reset to default',p_t_ip:'IP network',p_t_hd:'HD wired',p_t_wf:'Wi-Fi',
p_grp_out:'Outdoor',p_grp_in:'Indoor',p_ob:'Outdoor Bullet',p_oz:'Outdoor wide angle',p_id:'Indoor Dome',p_iz:'Indoor wide angle',
phint:'Tap the plan to add a camera · drag a camera to move it · drag the white dot to rotate · drag the square to change range · drag the small circle on the edge to change the angle',
p_nosel:'Select a camera on the plan to adjust its range and angle',pfo:'Field of view',prn:'Range (m)',p_rot:'Direction',
pcv:'Estimated total coverage:',pnote:'Coverage is approximate, walls and doors are simplified and bathrooms are not monitored. For an exact design, contact our team.',
p_priv:'Not monitored (privacy)',p_covroom:'Coverage by room',p_open:'Open space',p_finish:'Done · Recommend my system',
p_auto:'We placed the cameras automatically',p_cleared:'Cameras were cleared because the room layout changed',
p_sys:'Your recommended system',p_sysp:'Adjust the number of indoor and outdoor cameras and their type (IP / HD / Wi-Fi). We pick matching products from our stock and you can change any choice.',
p_allto:'Switch all cameras to:',p_sync:'Sync from plan',p_dirty:'The plan changed after this recommendation. Tap "Sync from plan".',
p_cams:'Cameras',p_gear:'Required gear',p_dvr:'DVR recorder',p_hdd:'Hard drive',p_psu:'Power supply',p_cab:'Camera cable (coax)',p_mem:'Memory cards',p_cat6:'Cat6 network cable',
p_total:'Estimated total',p_addall:'Add all to cart',p_wa:'Send recommendation on WhatsApp',p_na:'Not in stock right now, we will prepare a quote on WhatsApp',
p_relax:'No indoor model in stock, this outdoor model also works indoors',p_ipn:'IP cameras need a network recorder (NVR), network cable and usually a PoE switch. Message us on WhatsApp for a full quote.',
p_empty:'No cameras in the recommendation. Increase any type.',p_est:'Quantities are estimates: a recorder with enough channels for all wired cameras, about 300 GB of storage per camera, and about 15 m of cable per indoor and 25 m per outdoor camera.',
p_pcs:'Qty',p_include:'Include',p_rooms_s:'Rooms',p_cams_s:'Cameras'}};
Object.keys(TR.ar).forEach(function(k){D.ar[k]=TR.ar[k]});
Object.keys(TR.en).forEach(function(k){D.en[k]=TR.en[k]});
function t(k){return (D[L]&&D[L][k])||k}
function esc(s){return String(s).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})}
function ar(){return L=='ar'}
function msg(x){var o=$('toast');o.textContent=x;o.classList.add('show');setTimeout(function(){o.classList.remove('show')},2200)}

var RT={
living:{ar:'صالة',en:'Living room',a:26,c:'#e9f2ff'},bedroom:{ar:'غرفة نوم',en:'Bedroom',a:15,c:'#fff3e2'},master:{ar:'غرفة نوم رئيسية',en:'Master bedroom',a:20,c:'#ffeedd'},
bathroom:{ar:'حمام',en:'Bathroom',a:5,c:'#e1f5f3',priv:1},kitchen:{ar:'مطبخ',en:'Kitchen',a:10,c:'#fdeaea'},reception:{ar:'استقبال',en:'Reception',a:14,c:'#efe9ff'},
office:{ar:'مكتب',en:'Office',a:14,c:'#eef6e6'},meeting:{ar:'غرفة اجتماعات',en:'Meeting room',a:18,c:'#f3ecdc'},balcony:{ar:'بلكونة',en:'Balcony',a:7,c:'#e8f7e4',out:1},
hall:{ar:'ممر / ردهة',en:'Hall / corridor',a:9,c:'#f1f1f1'},storage:{ar:'مخزن',en:'Storage',a:8,c:'#ece7e0'},garage:{ar:'جراج',en:'Garage',a:20,c:'#e6e9ee'},
yard:{ar:'حديقة / فناء',en:'Garden / yard',a:40,c:'#dff0d2',out:1},floor:{ar:'صالة رئيسية',en:'Main hall',a:60,c:'#eef1f6'},other:{ar:'غرفة أخرى',en:'Other room',a:12,c:'#f4f0f8'}};
var RTO=['living','bedroom','master','bathroom','kitchen','reception','office','meeting','balcony','hall','storage','garage','yard','floor','other'];
var RTP={apt:['living','master','bedroom','bathroom','kitchen','balcony','hall','storage','other'],villa:['living','reception','kitchen','master','bedroom','bathroom','balcony','hall','storage','garage','yard','other'],office:['reception','office','meeting','kitchen','bathroom','hall','storage','other'],shop:['floor','storage','office','bathroom','other'],factory:['floor','office','storage','bathroom','garage','yard','other'],farm:['yard','floor','storage','garage','office','bathroom','other']};
function rtl(){var base=RTP[ST.type]||RTO;return RTO.filter(function(k){return base.indexOf(k)>-1||ST.rooms.some(function(r){return r.type==k})})}
var PRE={
apt:{a:90,asp:1.25,t:'id',sizes:[70,90,120,150,200],r:['living','master','bedroom','bedroom','bathroom','bathroom','kitchen','hall']},
villa:{a:300,asp:1.35,t:'id',sizes:[200,300,400,600],r:['living','reception','kitchen','master','bedroom','bedroom','bedroom','bathroom','bathroom','bathroom','hall','garage','yard']},
office:{a:150,asp:1.6,t:'id',sizes:[80,150,250,400],r:['reception','office','office','office','meeting','bathroom','hall','storage']},
shop:{a:60,asp:1.6,t:'id',sizes:[30,60,100,150],r:['floor','storage','bathroom']},
factory:{a:1000,asp:1.8,t:'iz',sizes:[500,1000,2500,5000],r:['floor','office','storage','bathroom']},
farm:{a:2000,asp:1.4,t:'ob',sizes:[1000,2000,5000,10000],r:['yard','floor','storage']}};
var DEF={hd:{ob:[80,20],oz:[110,14],id:[90,9],iz:[120,7]},ip:{ob:[85,25],oz:[120,16],id:[95,11],iz:[130,8]},wf:{ob:[90,12],oz:[110,9],id:[100,8],iz:[115,6]}};
var TC={ip:'#05b3e9',hd:'#ff7a1a',wf:'#12a150'};
function tlab(k){return t('p_t_'+k)}
function ylab(ty){return t('p_'+ty)}
function clab(c){return tlab(c.k)+' · '+ylab(c.t)}
function plc(ty){return ty.charAt(0)=='o'?'out':'in'}
function shp(ty){return ty=='ob'?'bullet':ty=='id'?'dome':'wide'}

var ST={type:'apt',area:110,asp:1.25,lock:true,seed:3,rooms:[],photo:false,tech:'hd',ctype:'id'};
var CAMS=[],SEL=-1,DR=null,BG=null,PLAN=null,W=0,H=0,RID=0,cv=$('cv'),cx=cv.getContext('2d');
function mkRoom(type,area,name){return{id:++RID,type:type,name:name||'',area:area}}
function sumA(){var s=0;ST.rooms.forEach(function(r){s+=r.area});return s}
function scaleRooms(total){var s=sumA();if(!s)return;ST.rooms.forEach(function(r){r.area=Math.max(.5,r.area*total/s)})}
function loadPreset(type,area){var p=PRE[type];ST.type=type;ST.asp=p.asp;ST.area=area||p.a;ST.ctype=p.t;
var b=p.r.map(function(k){return RT[k].a}),sb=b.reduce(function(a,c){return a+c},0);
ST.rooms=p.r.map(function(k,i){return mkRoom(k,b[i]*ST.area/sb)})}
function norm(){if(ST.lock&&ST.rooms.length)scaleRooms(ST.area);else if(!ST.lock&&ST.rooms.length)ST.area=sumA()}
function addRoom(type){var d=RT[type].a,r;
if(ST.lock&&ST.rooms.length){var a=Math.min(d,ST.area/(ST.rooms.length+1));var k=(ST.area-a)/sumA();ST.rooms.forEach(function(x){x.area*=k});r=mkRoom(type,a)}else r=mkRoom(type,d);
ST.rooms.push(r);norm()}
function delRoom(i){ST.rooms.splice(i,1);norm()}
function setRoomArea(i,v){v=Math.max(1,+v||1);var n=ST.rooms.length;
if(ST.lock&&n>1){v=Math.min(v,ST.area-(n-1));var o=sumA()-ST.rooms[i].area,rest=ST.area-v;ST.rooms.forEach(function(r,j){if(j!=i)r.area=Math.max(.5,r.area*rest/o)});ST.rooms[i].area=v;scaleRooms(ST.area)}
else{ST.rooms[i].area=v;ST.area=sumA()}}
function nameOf(r){if(!ST.rooms.length||ST.rooms.indexOf(r)<0)return t('p_open');if(r.name)return r.name;var n=0,tot=0;ST.rooms.forEach(function(x){if(x.type==r.type){tot++;if(ST.rooms.indexOf(x)<=ST.rooms.indexOf(r))n++}});var b=RT[r.type][L];return tot>1?b+' '+n:b}
function dims(){var w=Math.sqrt(ST.area*ST.asp);return[w,ST.area/w]}
function rnd1(v){return Math.round(v*10)/10}

/* layout */
function mul(a){return function(){a|=0;a=a+0x6D2B79F5|0;var t=Math.imul(a^a>>>15,1|a);t=t+Math.imul(t^t>>>7,61|t)^t;return((t^t>>>14)>>>0)/4294967296}}
function wr(row,s,side){var mx=0,mn=1e18;row.forEach(function(r){mx=Math.max(mx,r.a);mn=Math.min(mn,r.a)});var s2=s*s,d2=side*side;return Math.max(d2*mx/s2,s2/(d2*mn))}
function squarify(items,x,y,w,h){var out=[],rest=items.slice();
while(rest.length){var side=Math.min(w,h),row=[rest[0]],s=rest[0].a,worst=wr(row,s,side),i=1;
while(i<rest.length){var r2=row.concat(rest[i]),s2=s+rest[i].a,w2=wr(r2,s2,side);if(w2<=worst){row=r2;s=s2;worst=w2;i++}else break}
if(w>=h){var cw=s/h,cy=y;row.forEach(function(it){var ch=it.a/cw;out.push({it:it,x:x,y:cy,w:cw,h:ch});cy+=ch});x+=cw;w-=cw}
else{var rh=s/w,cx2=x;row.forEach(function(it){var cw2=it.a/rh;out.push({it:it,x:cx2,y:y,w:cw2,h:rh});cx2+=cw2});y+=rh;h-=rh}
rest=rest.slice(row.length)}
return out}
function sn(v){return Math.round(v*1000)/1000}
function layout(wM,hM){
var list=ST.rooms.length?ST.rooms:[{id:0,type:'floor',name:'',area:ST.area}],tot=0;list.forEach(function(r){tot+=r.area});
var rn=mul(ST.seed*7919+list.length),items=list.map(function(r){return{r:r,a:r.area*wM*hM/tot,k:r.area*(.75+.5*rn())}});
items.sort(function(a,b){return b.k-a.k});
var rects=squarify(items,0,0,wM,hM).map(function(q){return{room:q.it.r,x0:sn(q.x),y0:sn(q.y),x1:sn(q.x+q.w),y1:sn(q.y+q.h)}});
var WM=sn(wM),HM=sn(hM);
var V={},Hh={};function add(o,c,a,b){(o[c]=o[c]||[]).push([a,b])}
rects.forEach(function(r){add(V,r.x0,r.y0,r.y1);add(V,r.x1,r.y0,r.y1);add(Hh,r.y0,r.x0,r.x1);add(Hh,r.y1,r.x0,r.x1)});
function merge(o,lim){var out={};Object.keys(o).forEach(function(k){var c=+k;if(c<=.0005||c>=lim-.0005)return;var a=o[k].slice().sort(function(p,q){return p[0]-q[0]}),m=[a[0].slice()];
for(var i=1;i<a.length;i++){var l=m[m.length-1];if(a[i][0]<=l[1]+1e-6)l[1]=Math.max(l[1],a[i][1]);else m.push(a[i].slice())}out[k]=m});return out}
var mv=merge(V,WM),mh=merge(Hh,HM);
var adj=rects.map(function(){return[]});
for(var i=0;i<rects.length;i++)for(var j=i+1;j<rects.length;j++){var p=rects[i],q=rects[j],ov;
if(p.x1==q.x0||q.x1==p.x0){ov=Math.min(p.y1,q.y1)-Math.max(p.y0,q.y0);if(ov>.01){var ln=p.x1==q.x0?p.x1:q.x1;var d={v:true,l:ln,a:Math.max(p.y0,q.y0),b:Math.min(p.y1,q.y1),ov:ov};adj[i].push([j,d]);adj[j].push([i,d])}}
if(p.y1==q.y0||q.y1==p.y0){ov=Math.min(p.x1,q.x1)-Math.max(p.x0,q.x0);if(ov>.01){var ln2=p.y1==q.y0?p.y1:q.y1;var d2={v:false,l:ln2,a:Math.max(p.x0,q.x0),b:Math.min(p.x1,q.x1),ov:ov};adj[i].push([j,d2]);adj[j].push([i,d2])}}}
var hub=0,best=-1;rects.forEach(function(r,i){var pr=({living:3,floor:3,reception:3,hall:2})[r.room.type]||0,ar2=(r.x1-r.x0)*(r.y1-r.y0),sc=pr*1e9+ar2;if(sc>best){best=sc;hub=i}});
var seen={},q2=[hub],doors=[];seen[hub]=1;
while(q2.length){var u=q2.shift();adj[u].slice().sort(function(a,b){return b[1].ov-a[1].ov}).forEach(function(e){if(seen[e[0]])return;seen[e[0]]=1;q2.push(e[0]);doors.push(e[1])})}
function sub(segs,a,b){var o=[];segs.forEach(function(s){if(b<=s[0]||a>=s[1]){o.push(s);return}if(a>s[0])o.push([s[0],a]);if(b<s[1])o.push([b,s[1]])});return o}
var dm=[];doors.forEach(function(d){var w=Math.min(.9,d.ov*.7),mid=(d.a+d.b)/2,a=mid-w/2,b=mid+w/2,tgt=d.v?mv:mh,key=Object.keys(tgt).filter(function(k){return Math.abs(+k-d.l)<1e-6})[0];
if(key!==undefined)tgt[key]=sub(tgt[key],a,b);dm.push({v:d.v,l:d.l,a:a,b:b})});
var walls=[];Object.keys(mv).forEach(function(k){mv[k].forEach(function(s){walls.push({x1:+k,y1:s[0],x2:+k,y2:s[1]})})});
Object.keys(mh).forEach(function(k){mh[k].forEach(function(s){walls.push({x1:s[0],y1:+k,x2:s[1],y2:+k})})});
return{rects:rects,walls:walls,doors:dm}}

function niceBar(wM,cw,sc){var o=[1,2,5,10,20,50,100,200,500,1000,2000,5000],b=1;o.forEach(function(v){if(v*sc<=cw*.28)b=v});return b}
function genBG(){
var d=dims(),wM=d[0],hM=d[1],sc=Math.max(.4,Math.min(1100/wM,800/hM,220)),cw=Math.max(120,Math.round(wM*sc)),ch=Math.max(90,Math.round(hM*sc));
var c=document.createElement('canvas');c.width=cw;c.height=ch;var g=c.getContext('2d');g.fillStyle='#f7f9fc';g.fillRect(0,0,cw,ch);
var lo=layout(wM,hM);
lo.rects.forEach(function(r){var T=RT[r.room.type];g.fillStyle=T.c;g.fillRect(r.x0*sc,r.y0*sc,(r.x1-r.x0)*sc,(r.y1-r.y0)*sc);
if(r.room.type=='yard'){g.strokeStyle='#bfdcaa';g.lineWidth=1;var st=Math.max(10,sc*3);for(var x=r.x0*sc+st;x<r.x1*sc;x+=st){g.beginPath();g.moveTo(x,r.y0*sc);g.lineTo(x,r.y1*sc);g.stroke()}}});
g.strokeStyle='#34495e';g.lineCap='butt';g.lineWidth=Math.max(2,sc*.12);
lo.walls.forEach(function(w){g.beginPath();g.moveTo(w.x1*sc,w.y1*sc);g.lineTo(w.x2*sc,w.y2*sc);g.stroke()});
g.strokeStyle='#7fb7d3';g.lineWidth=Math.max(1.5,sc*.06);
lo.doors.forEach(function(dr){g.beginPath();if(dr.v){g.moveTo(dr.l*sc,dr.a*sc);g.lineTo(dr.l*sc,dr.b*sc)}else{g.moveTo(dr.a*sc,dr.l*sc);g.lineTo(dr.b*sc,dr.l*sc)}g.stroke()});
var wp=Math.max(4,sc*.22);g.strokeStyle='#34495e';g.lineWidth=wp;g.strokeRect(wp/2,wp/2,cw-wp,ch-wp);
g.fillStyle='#5b6670';g.textAlign='center';g.textBaseline='middle';
lo.rects.forEach(function(r){var w=(r.x1-r.x0)*sc,h=(r.y1-r.y0)*sc,m=Math.min(w,h);if(m<30)return;var fs=Math.max(9,Math.min(15,m*.11));g.font='700 '+Math.round(fs)+'px Cairo,sans-serif';
var nm=nameOf(r.room),cxx=r.x0*sc+w/2,cyy=r.y0*sc+h/2,ar2=rnd1((r.x1-r.x0)*(r.y1-r.y0))+(ar()?' م²':' sqm');
if(g.measureText(nm).width>w-8)return;g.fillText(nm,cxx,cyy-fs*.6);g.font='600 '+Math.round(fs*.85)+'px Cairo,sans-serif';g.fillText(ar2,cxx,cyy+fs*.7)});
var bm=niceBar(wM,cw,sc),bw=bm*sc,bx=14,by=ch-14;g.strokeStyle='#202224';g.fillStyle='#202224';g.lineWidth=3;g.beginPath();g.moveTo(bx,by);g.lineTo(bx+bw,by);g.moveTo(bx,by-6);g.lineTo(bx,by+6);g.moveTo(bx+bw,by-6);g.lineTo(bx+bw,by+6);g.stroke();g.font='700 12px sans-serif';g.fillText(bm+'m',bx+bw/2,by-10);
BG=c;PLAN={type:ST.type,mode:'plan',wM:wM,hM:hM,rects:lo.rects,walls:lo.walls,area:ST.area,grid:null}}

function fit(){if(!BG)return;var iw=BG.naturalWidth||BG.width,ih=BG.naturalHeight||BG.height,w=(cv.parentElement.clientWidth-4)||600,h=w*ih/iw;if(h>580){h=580;w=h*iw/ih}
var d=window.devicePixelRatio||1;cv.style.width=w+'px';cv.style.height=h+'px';cv.width=w*d;cv.height=h*d;cx.setTransform(d,0,0,d,0,0);W=w;H=h;draw()}

function ppm(){return W/PLAN.wM}
function mkCam(k,ty,x,y,a){var d=DEF[k][ty];return{k:k,t:ty,x:x,y:y,a:a,f:d[0],r:d[1]}}
function rayT(ox,oy,dx,dy,maxT){var best=maxT,w=PLAN.walls;for(var i=0;i<w.length;i++){var s=w[i],ex=s.x2-s.x1,ey=s.y2-s.y1,den=dx*ey-dy*ex;if(Math.abs(den)<1e-9)continue;var tt=((s.x1-ox)*ey-(s.y1-oy)*ex)/den,u=((s.x1-ox)*dy-(s.y1-oy)*dx)/den;if(tt>.001&&tt<best&&u>=0&&u<=1)best=tt}return best}
function vis(c){var ox=c.x*PLAN.wM,oy=c.y*PLAN.hM,f=c.f*Math.PI/180,n=Math.max(14,Math.ceil(c.f/3)),pts=[];for(var j=0;j<=n;j++){var a=c.a-f/2+f*j/n,dx=Math.cos(a),dy=Math.sin(a),tt=rayT(ox,oy,dx,dy,c.r);pts.push([ox+dx*tt,oy+dy*tt])}return pts}
function cov(c,x,y){var ox=c.x*PLAN.wM,oy=c.y*PLAN.hM,dx=x-ox,dy=y-oy;if(dx*dx+dy*dy>c.r*c.r)return false;var a=Math.atan2(dy,dx)-c.a;a=Math.atan2(Math.sin(a),Math.cos(a));if(Math.abs(a)>c.f*Math.PI/360)return false;
var d=Math.sqrt(dx*dx+dy*dy);return rayT(ox,oy,dx/d,dy/d,d*.999)>=d*.999}
function grid(){if(PLAN.grid)return PLAN.grid;var step=Math.max(PLAN.wM,PLAN.hM)/85,p=[];
if(PLAN.rects){PLAN.rects.forEach(function(r,ri){if(RT[r.room.type].priv)return;for(var x=r.x0+step/2;x<r.x1;x+=step)for(var y=r.y0+step/2;y<r.y1;y+=step)p.push([x,y,ri])})}
else for(var x=step/2;x<PLAN.wM;x+=step)for(var y=step/2;y<PLAN.hM;y+=step)p.push([x,y,-1]);
return PLAN.grid=p}
function stats(){var g=grid(),hit=0,rc={};g.forEach(function(p){var cvd=false;for(var i=0;i<CAMS.length;i++)if(cov(CAMS[i],p[0],p[1])){cvd=true;break}if(cvd)hit++;var o=rc[p[2]]||(rc[p[2]]={h:0,n:0});o.n++;if(cvd)o.h++});
var rooms=[];if(PLAN.rects)PLAN.rects.forEach(function(r,i){var priv=!!RT[r.room.type].priv,o=rc[i];rooms.push({name:nameOf(r.room),priv:priv,pc:o&&o.n?Math.round(100*o.h/o.n):0})});
return{total:g.length?Math.round(100*hit/g.length):0,rooms:rooms}}

function hpos(c){var x=c.x*W,y=c.y*H,rp=c.r*ppm(),hd=Math.max(20,Math.min(rp,Math.hypot(Math.max(x,W-x),Math.max(y,H-y))-14));
return{rot:[x+Math.cos(c.a)*34,y+Math.sin(c.a)*34],rng:[x+Math.cos(c.a)*hd,y+Math.sin(c.a)*hd],fov:[x+Math.cos(c.a+c.f*Math.PI/360)*hd,y+Math.sin(c.a+c.f*Math.PI/360)*hd]}}
function draw(){if(!BG||!PLAN)return;cx.clearRect(0,0,W,H);cx.drawImage(BG,0,0,W,H);var pm=ppm();
CAMS.forEach(function(c,i){var col=TC[c.k],x=c.x*W,y=c.y*H,pl=vis(c);cx.beginPath();cx.moveTo(x,y);pl.forEach(function(p){cx.lineTo(p[0]*pm,p[1]*pm)});cx.closePath();cx.globalAlpha=.28;cx.fillStyle=col;cx.fill();cx.globalAlpha=.9;cx.strokeStyle=col;cx.lineWidth=1.3;cx.stroke();cx.globalAlpha=1;
var hp=hpos(c);cx.beginPath();cx.moveTo(x,y);cx.lineTo(hp.rot[0],hp.rot[1]);cx.stroke();cx.beginPath();cx.arc(hp.rot[0],hp.rot[1],7,0,7);cx.fillStyle='#fff';cx.fill();cx.stroke();
cx.save();cx.translate(x,y);cx.rotate(c.a);cx.fillStyle=col;cx.strokeStyle='#fff';cx.lineWidth=1.2;cx.beginPath();if(cx.roundRect)cx.roundRect(-3,-6,17,12,3);else cx.rect(-3,-6,17,12);cx.fill();cx.stroke();cx.restore();
cx.beginPath();cx.arc(x,y,12,0,7);cx.fillStyle=col;cx.fill();cx.lineWidth=2;cx.strokeStyle='#fff';cx.stroke();
if(i==SEL){cx.lineWidth=2;cx.strokeStyle=col;cx.beginPath();cx.arc(x,y,17,0,7);cx.stroke();
cx.fillStyle='#fff';cx.strokeStyle=col;cx.lineWidth=2;cx.fillRect(hp.rng[0]-7,hp.rng[1]-7,14,14);cx.strokeRect(hp.rng[0]-7,hp.rng[1]-7,14,14);cx.beginPath();cx.arc(hp.fov[0],hp.fov[1],6,0,7);cx.fill();cx.stroke()}
cx.fillStyle='#fff';cx.font='700 12px sans-serif';cx.textAlign='center';cx.textBaseline='middle';cx.fillText(i+1,x,y+1)})}

function pt(e){var r=cv.getBoundingClientRect();return[(e.clientX-r.left)*W/r.width,(e.clientY-r.top)*H/r.height]}
function dist(a,b){return Math.hypot(a[0]-b[0],a[1]-b[1])}
cv.onpointerdown=function(e){if(!PLAN)return;var p=pt(e),hit=-1,m='mv';
if(SEL>=0&&CAMS[SEL]){var h0=hpos(CAMS[SEL]);if(dist(p,h0.rng)<15){hit=SEL;m='rng'}else if(dist(p,h0.fov)<15){hit=SEL;m='fov'}}
if(hit<0)CAMS.forEach(function(c,i){if(dist(p,hpos(c).rot)<14){hit=i;m='rot'}});
if(hit<0)CAMS.forEach(function(c,i){if(dist(p,[c.x*W,c.y*H])<16){hit=i;m='mv'}});
if(hit<0){CAMS.push(mkCam(ST.tech,ST.ctype,p[0]/W,p[1]/H,Math.atan2(H/2-p[1],W/2-p[0])));hit=CAMS.length-1;DR=null}
else{DR={i:hit,m:m};if(m=='rng'){var cc=CAMS[hit];DR.off=cc.r*ppm()-dist(p,[cc.x*W,cc.y*H])}}
SEL=hit;try{cv.setPointerCapture(e.pointerId)}catch(x){}draw();pstat(true);if(e.preventDefault)e.preventDefault()};
cv.onpointermove=function(e){if(!DR)return;var p=pt(e),c=CAMS[DR.i];if(!c)return;
if(DR.m=='mv'){c.x=Math.min(1,Math.max(0,p[0]/W));c.y=Math.min(1,Math.max(0,p[1]/H))}
else if(DR.m=='rot')c.a=Math.atan2(p[1]-c.y*H,p[0]-c.x*W);
else if(DR.m=='rng')c.r=Math.max(1.5,Math.min(80,(dist(p,[c.x*W,c.y*H])+DR.off)/ppm()));
else{var an=Math.atan2(p[1]-c.y*H,p[0]-c.x*W)-c.a;an=Math.atan2(Math.sin(an),Math.cos(an));c.f=Math.max(20,Math.min(180,Math.abs(an)*360/Math.PI))}
draw();syncEditor()};
cv.onpointerup=cv.onpointercancel=function(){if(DR){DR=null;pstat(true)}};
cv.onkeydown=function(e){if((e.key=='Delete'||e.key=='Backspace')&&SEL>=0){CAMS.splice(SEL,1);SEL=-1;draw();pstat(true);e.preventDefault()}};

/* UI */
function fillTypes(){var cur=(SEL>=0&&CAMS[SEL])?CAMS[SEL].t:ST.ctype,s=$('pty');
s.innerHTML='<optgroup label="'+esc(t('p_grp_out'))+'"><option value="ob">'+esc(t('p_ob'))+'</option><option value="oz">'+esc(t('p_oz'))+'</option></optgroup><optgroup label="'+esc(t('p_grp_in'))+'"><option value="id">'+esc(t('p_id'))+'</option><option value="iz">'+esc(t('p_iz'))+'</option></optgroup>';s.value=cur}
function syncEditor(){var c=CAMS[SEL],el=$('pce');el.classList.toggle('off',!c);['sf','sr','sa','pdl'].forEach(function(id){$(id).disabled=!c});
if(c){$('sf').value=c.f;$('sr').value=c.r;var dg=Math.round(((c.a*180/Math.PI)%360+360)%360);$('sa').value=dg;$('sfv').textContent=Math.round(c.f)+'°';$('srv').textContent=rnd1(c.r)+(ar()?' م':' m');$('sav').textContent=dg+'°';$('pceh').textContent='#'+(SEL+1)+' · '+clab(c)}
else{$('pceh').textContent=t('p_nosel');$('sfv').textContent=$('srv').textContent=$('sav').textContent=''}
var k=c?c.k:ST.tech;document.querySelectorAll('#ptech button').forEach(function(b){b.classList.toggle('on',b.dataset.k==k)});
var ty=c?c.t:ST.ctype;if($('pty').value!=ty)$('pty').value=ty}
function counts(){var o={};ST.rooms.forEach(function(r){o[r.type]=(o[r.type]||0)+1});return o}
function renderPlanUI(){
var s=$('ppsize'),p=PRE[ST.type],h='',cur=Math.round(ST.area*10)/10,pre=p.sizes.indexOf(Math.round(ST.area))>=0&&Math.abs(ST.area-Math.round(ST.area))<.05&&!ST.photo;
p.sizes.forEach(function(v){h+='<option value="'+v+'">'+N(v)+(ar()?' م²':' sqm')+'</option>'});h+='<option value="custom">'+esc(t('pcustom'))+'</option>';s.innerHTML=h;
s.value=pre?String(Math.round(ST.area)):'custom';$('ppcwrap').style.display=pre?'none':'flex';if(!pre&&document.activeElement!==$('ppcustom'))$('ppcustom').value=cur;
$('pptype').value=ST.type;var d=dims();if(!ST.photo){if(document.activeElement!==$('pdW'))$('pdW').value=rnd1(d[0]);if(document.activeElement!==$('pdH'))$('pdH').value=rnd1(d[1])}
$('plock').checked=ST.lock;
var c=counts();$('rcnt').innerHTML=rtl().map(function(k){return'<div class="rc"><span>'+esc(RT[k][L])+'</span><span class="stp"><button type="button" data-rm="'+k+'" aria-label="-">&minus;</button><b>'+(c[k]||0)+'</b><button type="button" data-add="'+k+'" aria-label="+">+</button></span></div>'}).join('');
$('rlist').innerHTML=ST.rooms.map(function(r,i){return'<div class="rrow" data-i="'+i+'"><select data-f="type" aria-label="'+esc(r.name)+'">'+rtl().map(function(k){return'<option value="'+k+'"'+(k==r.type?' selected':'')+'>'+esc(RT[k][L])+'</option>'}).join('')+'</select><input data-f="name" value="'+esc(r.name)+'" placeholder="'+esc(nameOf(r))+'" aria-label="'+esc(t('p_rname'))+'"><input type="number" aria-label="'+esc(r.name)+' m²" data-f="area" min="1" step="any" value="'+rnd1(r.area)+'"><button type="button" data-del="1" aria-label="x">&times;</button></div>'}).join('')}
function planText(){var d=dims(),c=counts(),rs=RTO.filter(function(k){return c[k]}).map(function(k){return RT[k][L]+' x'+c[k]}).join(ar()?'، ':', ');
var o={};CAMS.forEach(function(x){var k=clab(x);o[k]=(o[k]||0)+1});var cl=Object.keys(o).map(function(k){return'- '+k+': '+o[k]}).join('\n');
return(ar()?'مرحبًا Vision Guard، عايز استشارة بخصوص مخطط تغطية كاميرات:\n':'Hello Vision Guard, I need advice on this camera plan:\n')+(PLAN?((ar()?'نوع العقار: ':'Property: ')+$('pptype').selectedOptions[0].textContent+' - '+N(Math.round(PLAN.area))+(ar()?' م²':' sqm')+' ('+rnd1(PLAN.wM)+' x '+rnd1(PLAN.hM)+')\n'):'')+(rs&&!ST.photo?(t('p_rooms_s')+': '+rs+'\n'):'')+cl+'\n'+t('pcv')+' '+$('pp').textContent}
function pstat(light){if(light!==true){renderPlanUI();fillTypes()}if(!PLAN)return;
var st=stats(),c={},io={in:0,out:0};CAMS.forEach(function(x){c[x.k]=(c[x.k]||0)+1;io[plc(x.t)]++});
$('pn').textContent=CAMS.length;$('pp').textContent=st.total+'%';
$('pb').textContent=CAMS.length?['ip','hd','wf'].filter(function(k){return c[k]}).map(function(k){return tlab(k)+': '+c[k]}).join(' · ')+' | '+t('p_grp_in')+': '+io.in+' · '+t('p_grp_out')+': '+io.out:'';
$('pdim').textContent=rnd1(PLAN.wM)+' × '+rnd1(PLAN.hM)+(ar()?' م':' m')+' ('+N(Math.round(PLAN.area))+(ar()?' م²':' sqm')+')';
var rs=st.rooms;$('proomstats').innerHTML=rs.length?'<h3>'+esc(t('p_covroom'))+'</h3><div class="prgrid">'+rs.map(function(r){return r.priv?'<div class="prcard"><div class="prhead"><span>'+esc(r.name)+'</span><span class="meta">'+esc(t('p_priv'))+'</span></div></div>':'<div class="prcard"><div class="prhead"><span>'+esc(r.name)+'</span><span>'+r.pc+'%</span></div><div class="prbar" role="progressbar" aria-label="'+esc(r.name)+'" aria-valuemin="0" aria-valuemax="100" aria-valuenow="'+r.pc+'"><span style="width:'+r.pc+'%"></span></div></div>'}).join('')+'</div>':'';
syncEditor();$('pcart').href=WA+'?text='+encodeURIComponent(planText());
if(SYS.open){$('psys').classList.toggle('dirty',sysSig()!=SYS.sig);sysRender()}}

function structural(){if(CAMS.length){CAMS=[];SEL=-1;msg(t('p_cleared'))}ST.photo=false;rebuild()}
function rebuild(keepCams){ST.photo=false;genBG();fit();pstat()}
function onNum(el,fn){var tm;el.addEventListener('input',function(){clearTimeout(tm);tm=setTimeout(fn,600)});el.addEventListener('change',function(){clearTimeout(tm);fn()})}
$('pptype').onchange=function(){loadPreset(this.value);CAMS=[];SEL=-1;rebuild()};window.addEventListener('load',function(){setTimeout(function(){try{var _pt=new URLSearchParams(location.search).get('t');if(_pt&&$('pptype').querySelector('option[value="'+_pt+'"]')&&$('pptype').value!==_pt){$('pptype').value=_pt;$('pptype').onchange()}}catch(e){}},400)});
$('ppsize').onchange=function(){if(this.value=='custom'){$('ppcwrap').style.display='flex';$('ppcustom').focus();return}ST.area=+this.value;ST.photo=false;scaleRooms(ST.area);rebuild()};
onNum($('ppcustom'),function(){var v=+$('ppcustom').value;if(!(v>=5))return;ST.area=v;ST.photo=false;scaleRooms(v);genBG();fit();pstat(true);renderPlanUI()});
function dimChange(){var w=+$('pdW').value,h=+$('pdH').value;if(!(w>=2&&h>=2))return;
if(ST.photo){ST.photo=false}
ST.asp=Math.max(.1,Math.min(10,w/h));ST.area=w*h;scaleRooms(ST.area);genBG();fit();pstat();}
onNum($('pdW'),function(){if(PLAN&&PLAN.mode=='photo'){var w=+$('pdW').value;if(w>=2){PLAN.hM=w*BG.height/BG.width;PLAN.wM=w;PLAN.area=PLAN.wM*PLAN.hM;PLAN.grid=null;draw();pstat(true)}return}dimChange()});
onNum($('pdH'),function(){if(PLAN&&PLAN.mode=='photo')return;dimChange()});
$('plock').onchange=function(){ST.lock=this.checked};
$('pshuf').onclick=function(){ST.seed++;structural()};
$('preset').onclick=function(){loadPreset(ST.type,ST.area);CAMS=[];SEL=-1;rebuild()};
$('rcnt').onclick=function(e){var a=e.target.closest('[data-add]'),r=e.target.closest('[data-rm]');
if(a){addRoom(a.dataset.add);structural()}else if(r){var k=r.dataset.rm,idx=-1;ST.rooms.forEach(function(x,i){if(x.type==k)idx=i});if(idx>=0){delRoom(idx);structural()}}};
$('rlist').onchange=function(e){var row=e.target.closest('.rrow');if(!row)return;var i=+row.dataset.i,r=ST.rooms[i],f=e.target.dataset.f;if(!r)return;
if(f=='type')r.type=e.target.value;else if(f=='name')r.name=e.target.value.trim();else if(f=='area')setRoomArea(i,e.target.value);structural()};
$('rlist').onclick=function(e){var b=e.target.closest('[data-del]');if(!b)return;delRoom(+b.parentNode.dataset.i);structural()};
$('ptech').onclick=function(e){var b=e.target.closest('button');if(!b)return;ST.tech=b.dataset.k;var c=CAMS[SEL];if(c){c.k=b.dataset.k;var d=DEF[c.k][c.t];c.f=d[0];c.r=d[1]}draw();pstat(true)};
$('pty').onchange=function(){ST.ctype=this.value;var c=CAMS[SEL];if(c){c.t=this.value;var d=DEF[c.k][c.t];c.f=d[0];c.r=d[1]}draw();pstat(true)};
$('sf').oninput=function(){var c=CAMS[SEL];if(c){c.f=+this.value;draw();pstat(true)}};
$('sr').oninput=function(){var c=CAMS[SEL];if(c){c.r=+this.value;draw();pstat(true)}};
$('sa').oninput=function(){var c=CAMS[SEL];if(c){c.a=this.value*Math.PI/180;draw();pstat(true)}};
$('pcl').onclick=function(){CAMS=[];SEL=-1;draw();pstat(true)};
$('pdl').onclick=function(){if(SEL>=0){CAMS.splice(SEL,1);SEL=-1;draw();pstat(true)}};
$('pf').onchange=function(){var f=this.files&&this.files[0];if(!f)return;var inp=this,r=new FileReader();r.onload=function(){var im=new Image();im.onload=function(){var wM=Math.max(2,+$('pdW').value||dims()[0]);ST.photo=true;BG=im;CAMS=[];SEL=-1;PLAN={type:'photo',mode:'photo',wM:wM,hM:wM*im.height/im.width,rects:null,walls:[],area:wM*wM*im.height/im.width,grid:null};fit();pstat(true);$('pdH').value=rnd1(PLAN.hM)};im.src=r.result};r.readAsDataURL(f);inp.value=''};

/* auto placement */
function recommend(){if(!PLAN)return;var cands=[],wM=PLAN.wM,hM=PLAN.hM,tech=ST.tech;
function push(x,y,a,ty,ri){cands.push({c:mkCam(tech,ty,x/wM,y/hM,a),ri:ri})}
var rects=PLAN.rects||[{room:{type:'floor'},x0:0,y0:0,x1:wM,y1:hM}];
rects.forEach(function(r,ri){var T=RT[r.room.type];if(T.priv)return;var w=r.x1-r.x0,h=r.y1-r.y0,ar2=w*h,ty=T.out?'ob':((r.room.type=='floor'||ar2>45)?'iz':'id'),pad=Math.max(.3,Math.min(w,h)*.1),mx=(r.x0+r.x1)/2,my=(r.y0+r.y1)/2;
[[r.x0+pad,r.y0+pad],[r.x1-pad,r.y0+pad],[r.x0+pad,r.y1-pad],[r.x1-pad,r.y1-pad]].forEach(function(p){push(p[0],p[1],Math.atan2(my-p[1],mx-p[0]),ty,ri)});
if(ar2>30)[[mx,r.y0+pad,Math.PI/2],[mx,r.y1-pad,-Math.PI/2],[r.x0+pad,my,0],[r.x1-pad,my,Math.PI]].forEach(function(p){push(p[0],p[1],p[2],ty,ri)});
if(ar2>120){var sp=Math.max(DEF[tech][ty][1]*.9,Math.sqrt(ar2/40));for(var x=r.x0+sp/2;x<r.x1;x+=sp)for(var y=r.y0+sp/2;y<r.y1;y+=sp)[0,Math.PI/2,Math.PI,-Math.PI/2].forEach(function(a){push(x,y,a,ty,ri)})}});
if(!cands.length)return;var g=grid();if(!g.length)return;
var lists=cands.map(function(cd){var l=[];for(var i=0;i<g.length;i++)if(cov(cd.c,g[i][0],g[i][1]))l.push(i);return l}),done=new Uint8Array(g.length),used={},chosen=[],covered=0,cap=(parseInt(($('pcnt')||{}).value,10)||60);
function take(ci){used[ci]=1;chosen.push(cands[ci].c);lists[ci].forEach(function(i){if(!done[i]){done[i]=1;covered++}})}
if(PLAN.rects){var PRI={reception:1,garage:1,yard:1,hall:1,floor:1},lim=Math.max(1,Math.floor(cap/2));PLAN.rects.forEach(function(r,ri){if(!PRI[r.room.type]||chosen.length>=lim)return;var b3=-1,g3=0;cands.forEach(function(cd,ci){if(used[ci]||cd.ri!=ri)return;var gn=0;lists[ci].forEach(function(i){if(!done[i])gn++});if(gn>g3){g3=gn;b3=ci}});if(b3>=0)take(b3)})}
while(chosen.length<cap){var bi=-1,bg=0;for(var ci=0;ci<cands.length;ci++){if(used[ci])continue;var gn=0,l=lists[ci];for(var k=0;k<l.length;k++)if(!done[l[k]])gn++;if(gn>bg){bg=gn;bi=ci}}
if(bi<0||bg<g.length*.006)break;take(bi);if(covered/g.length>=.92)break}
if(PLAN.rects){PLAN.rects.forEach(function(r,ri){if(RT[r.room.type].priv)return;var tot=0,hit=0;g.forEach(function(p,i){if(p[2]==ri){tot++;if(done[i])hit++}});if(tot&&hit/tot<.55){var bi2=-1,bg2=0;cands.forEach(function(cd,ci){if(used[ci]||cd.ri!=ri)return;var gn=0;lists[ci].forEach(function(i){if(!done[i])gn++});if(gn>bg2){bg2=gn;bi2=ci}});if(bi2>=0&&chosen.length<cap)take(bi2)}})}
CAMS=chosen;SEL=-1;draw();pstat(true)}
$('prec').onclick=recommend;
(function(){var pc=$('pcnt');if(!pc)return;function lab(){var en=(document.documentElement.lang||'').indexOf('en')===0;for(var i=0;i<pc.options.length;i++){var o=pc.options[i],n=+o.value;o.text=n===0?(en?'Cameras: auto (best)':'عدد الكاميرات: تلقائي (الأنسب)'):(en?n+' cameras':(n===2?'كاميرتان':n>=3&&n<=10?n+' كاميرات':n+' كاميرا'))}}lab();new MutationObserver(lab).observe(document.documentElement,{attributes:true,attributeFilter:['lang']});window.addEventListener('load',function(){setTimeout(lab,300)});pc.onchange=function(){if(CAMS.length)recommend()};document.addEventListener('click',function(){setTimeout(lab,80)})})();

/* system recommendation */
var SYS={open:false,c:{ip:{in:0,out:0},hd:{in:0,out:0},wf:{in:0,out:0}},sh:{},sel:{},sup:{},sig:''};
function sysSig(){var o={};CAMS.forEach(function(c){var k=c.k+plc(c.t);o[k]=(o[k]||0)+1});return JSON.stringify(o)}
function sysSync(){var sh={};SYS.c={ip:{in:0,out:0},hd:{in:0,out:0},wf:{in:0,out:0}};CAMS.forEach(function(c){var p=plc(c.t);SYS.c[c.k][p]++;var key=c.k+p;(sh[key]=sh[key]||{})[shp(c.t)]=((sh[key]||{})[shp(c.t)]||0)+1});
SYS.sh={};Object.keys(sh).forEach(function(k){var b='',m=0;Object.keys(sh[k]).forEach(function(s){if(sh[k][s]>m){m=sh[k][s];b=s}});SYS.sh[k]=b});SYS.sig=sysSig()}
var CAT={ip:'cat_ip',hd:'cat_hd',wf:'cat_wf'};
function pr(p){return ep(p)}
function camOpts(k,place,shape){var all=P.filter(function(p){return p.c==CAT[k]&&p.s}),ok=all.filter(function(p){return p.pl==place||p.pl=='both'}),rl=false;
if(!ok.length&&place=='in'){ok=all.filter(function(p){return p.pl=='out'});rl=ok.length>0}
var want=shape=='bullet'?['bu']:shape=='dome'?['do','cu','pt']:['do','bu','cu','pt'];
function sc(p){var s=want.indexOf(p.ic);s=s<0?9:s;if(shape=='wide'&&/F2\.?8|AF28|2\.8/i.test(p.e))s-=5;return s}
ok.sort(function(a,b){return sc(a)-sc(b)||pr(a)-pr(b)});return{list:ok,relaxed:rl}}
function byCat(c,f){return P.filter(function(p){return p.c==c&&p.s&&(!f||f(p))}).sort(function(a,b){return pr(a)-pr(b)})}
function gb(p){var m=/(\d+(?:\.\d+)?)\s*(TB|GB)/i.exec(p.e);return m?(+m[1])*(/TB/i.test(m[2])?1000:1):0}
function chn(p){var m=/(\d+)[- ]?Channel/i.exec(p.e);return m?+m[1]:0}
function amp(p){var m=/(\d+)\s*A\b/.exec(p.e);return m?+m[1]:0}
function len(p){var m=/(\d+)\s*(m|y)\b/i.exec(p.e);return m?(+m[1])*(/y/i.test(m[2])?.9144:1):0}
function pick(kind,list,need,cap,pref,on){if(!list.length)return null;var st=SYS.sup[kind]||(SYS.sup[kind]={on:on,i:null}),cur=list.filter(function(p){return p.i==st.i})[0];
if(!cur){var pl=pref?list.filter(pref):list;if(!pl.length)pl=list;cur=pl.slice().sort(function(a,b){return pr(a)-pr(b)}).filter(function(p){return cap(p)>=need})[0]||pl.slice().sort(function(a,b){return cap(b)-cap(a)})[0]}
return{kind:kind,list:list.slice().sort(function(a,b){return pr(a)-pr(b)}),p:cur,qty:Math.max(1,Math.ceil(need/Math.max(1,cap(cur)))),on:st.on}}
function vgDef(l){var nm=function(p){return /ليلية|ملونة|night|colo/i.test(p.e||p.n||'')?0:1};return l.slice().sort(function(a,b){return nm(a)-nm(b)||pr(a)-pr(b)})[0]}
function sysCalc(){var L2=[],miss=[],ct=SYS.c,tot=0;
['hd','ip','wf'].forEach(function(k){['in','out'].forEach(function(pl){var n=ct[k][pl];if(!n)return;var key=k+pl,shape=SYS.sh[key]||(pl=='out'?'bullet':'dome'),o=camOpts(k,pl,shape),cur=o.list.filter(function(p){return p.i==SYS.sel[key]})[0]||vgDef(o.list);
var ttl=tlab(k)+' · '+t('p_grp_'+pl)+' · '+t(shape=='bullet'?'p_ob':shape=='dome'?'p_id':(pl=='out'?'p_oz':'p_iz'));
if(!cur){miss.push(ttl+' x'+n);L2.push({cam:1,key:key,title:ttl,qty:n,na:1});return}
L2.push({cam:1,key:key,title:ttl,qty:n,list:o.list.slice().sort(function(a,b){return pr(a)-pr(b)}),p:cur,on:true,relax:o.relaxed})})});
var wired=ct.hd.in+ct.hd.out,hdc=wired,all=wired+ct.ip.in+ct.ip.out,note=ct.ip.in+ct.ip.out>0;
if(wired>0){var need=all,dvr=pick('dvr',byCat('cat_dvr'),need,chn,null,true);if(dvr){dvr.title=t('p_dvr');L2.push(dvr);var hdd=pick('hdd',byCat('cat_hdd'),all*300,gb,null,true);if(hdd){hdd.title=t('p_hdd');L2.push(hdd)}}else{miss.push(t('p_dvr'));L2.push({title:t('p_dvr'),na:1,qty:1})}
var psu=pick('psu',byCat('cat_psu'),Math.max(1,Math.ceil(wired*.8)),amp,null,true);if(psu){psu.title=t('p_psu');L2.push(psu)}
var m=ct.hd.in*15+ct.hd.out*25,cab=pick('cab',byCat('cat_cab',function(p){return /RG/i.test(p.e)}),m,len,null,true);if(cab){cab.title=t('p_cab');L2.push(cab)}}
var wf=ct.wf.in+ct.wf.out;if(wf>0){var mem=pick('mem',byCat('cat_mem'),wf,function(){return 1},function(p){return gb(p)>=64},true);if(mem){mem.title=t('p_mem');L2.push(mem)}}
if(note){var c6=pick('cat6',byCat('cat_cab',function(p){return /Cat6|UTP/i.test(p.e)}),ct.ip.in*15+ct.ip.out*25,len,null,false);if(c6){c6.title=t('p_cat6');L2.push(c6)}}
L2.forEach(function(l){l.sub=(l.p&&l.on)?pr(l.p)*l.qty:0;tot+=l.sub});return{lines:L2,miss:miss,total:tot,ipnote:note}}
function sysRender(){var el=$('psys'),cs=sysCalc(),h='<h3>'+esc(t('p_sys'))+'</h3><p class="meta">'+esc(t('p_sysp'))+'</p>';
h+='<div class="sall"><span>'+esc(t('p_allto'))+'</span>'+['ip','hd','wf'].map(function(k){return'<button type="button" class="btn" data-all="'+k+'">'+esc(tlab(k))+'</button>'}).join('')+'<button type="button" class="btn" data-sync="1">'+esc(t('p_sync'))+'</button></div>';
if(el.classList.contains('dirty'))h+='<p class="meta" style="color:var(--sale)">'+esc(t('p_dirty'))+'</p>';
h+='<div class="sgrid"><span></span><span class="h">'+esc(t('p_grp_in'))+'</span><span class="h">'+esc(t('p_grp_out'))+'</span>'+['ip','hd','wf'].map(function(k){return'<span class="t"><i style="background:'+TC[k]+'"></i>'+esc(tlab(k))+'</span>'+['in','out'].map(function(pl){return'<span class="stp"><button type="button" data-c="'+k+'|'+pl+'|-1" aria-label="-">&minus;</button><b>'+SYS.c[k][pl]+'</b><button type="button" data-c="'+k+'|'+pl+'|1" aria-label="+">+</button></span>'}).join('')}).join('')+'</div>';
var any=cs.lines.length>0;if(!any)h+='<p class="meta">'+esc(t('p_empty'))+'</p>';
cs.lines.forEach(function(l,i){var gear=!l.cam;
if(l.na){h+='<div class="sln na"><div class="sh"><span>'+esc(l.title)+' × '+l.qty+'</span></div><div class="sp">'+esc(t('p_na'))+'</div></div>';return}
h+='<div class="sln"><div class="sh"><span>'+(gear?'<label><input type="checkbox" data-on="'+l.kind+'"'+(l.on?' checked':'')+'> ':'')+esc(l.title)+(gear?'</label>':'')+'</span><span>× '+l.qty+'</span></div><select data-sel="'+(gear?l.kind:'cam:'+l.key)+'" aria-label="'+esc(l.title)+'">'+l.list.map(function(p){return'<option value="'+p.i+'"'+(p.i==l.p.i?' selected':'')+'>'+esc(nmp(p))+' — '+N(pr(p))+' '+esc(t('egp'))+'</option>'}).join('')+'</select><div class="sp"><span class="meta">'+(l.relax?esc(t('p_relax')):'')+'</span><b>'+(l.on?N(l.sub)+' '+esc(t('egp')):'—')+'</b></div></div>'});
if(cs.ipnote)h+='<p class="meta">'+esc(t('p_ipn'))+'</p>';
if(any)h+='<p class="meta">'+esc(t('p_est'))+'</p>';
h+='<div class="stot"><span>'+esc(t('p_total'))+': '+N(cs.total)+' '+esc(t('egp'))+'</span><span class="ptools" style="margin:0"><button type="button" class="btn pri" data-addall="1">'+esc(t('p_addall'))+'</button><a class="btn" id="psyswa" target="_blank" rel="noopener" href="#">'+esc(t('p_wa'))+'</a></span></div>';
el.innerHTML=h;var wa=$('psyswa');if(wa)wa.href=WA+'?text='+encodeURIComponent(sysText(cs))}
function sysText(cs){var m=(ar()?'مرحبًا Vision Guard، ده النظام اللي رشّحه المخطط وعايز أعرف التفاصيل:\n':'Hello Vision Guard, this is the system the planner recommended, I would like details:\n');
cs.lines.forEach(function(l){if(l.na)m+='- '+l.title+' x'+l.qty+' ('+t('p_na')+')\n';else if(l.on)m+='- '+nmp(l.p)+' x'+l.qty+' = '+N(l.sub)+' '+t('egp')+'\n'});
m+=t('p_total')+': '+N(cs.total)+' '+t('egp')+'\n\n'+planText();return m}
$('psys').onclick=function(e){var b;
if(b=e.target.closest('[data-c]')){var a=b.dataset.c.split('|');SYS.c[a[0]][a[1]]=Math.max(0,Math.min(500,SYS.c[a[0]][a[1]]+ +a[2]));sysRender()}
else if(b=e.target.closest('[data-all]')){var k=b.dataset.all,tot={in:0,out:0};['ip','hd','wf'].forEach(function(x){tot.in+=SYS.c[x].in;tot.out+=SYS.c[x].out;SYS.c[x]={in:0,out:0}});SYS.c[k]=tot;sysRender()}
else if(e.target.closest('[data-sync]')){sysSync();$('psys').classList.remove('dirty');sysRender()}
else if(e.target.closest('[data-addall]')){var cs=sysCalc(),n=0;cs.lines.forEach(function(l){if(l.p&&l.on){CART[l.p.i]=(CART[l.p.i]||0)+l.qty;n++}});if(n){csave();cupd();tst('added');$('cart').click()}}
else if(e.target.closest('#psyswa')){sendLead({type:'coverage_plan',message:sysText(sysCalc())})}};
$('psys').onchange=function(e){var s=e.target.closest('[data-sel]'),o=e.target.closest('[data-on]');
if(s){var k=s.dataset.sel;if(k.indexOf('cam:')==0)SYS.sel[k.slice(4)]=+s.value;else(SYS.sup[k]||(SYS.sup[k]={on:true})).i=+s.value;sysRender()}
else if(o){(SYS.sup[o.dataset.on]||(SYS.sup[o.dataset.on]={i:null})).on=o.checked;sysRender()}};
$('pfin').onclick=function(){if(!CAMS.length){recommend();if(CAMS.length)msg(t('p_auto'))}sysSync();SYS.open=true;var el=$('psys');el.hidden=false;el.classList.remove('dirty');sysRender();el.scrollIntoView({behavior:'smooth',block:'start'})};

window.addEventListener('resize',fit);
loadPreset('apt');genBG();fit();
return{pstat:pstat,fit:fit}})();
function pstat(l){return __PL.pstat(l)}
function fillSizes(){return __PL.pstat()}

(function(){var c={};P.forEach(function(p){if(p.b)c[p.b]=(c[p.b]||0)+1});
 $('bchips').innerHTML=Object.keys(c).sort(function(x,y){return c[y]-c[x]}).map(function(k){return '<button type="button" class="chip" data-b="'+k+'">'+k+'<b>'+c[k]+'</b></button>'}).join('');
 $('bchips').onclick=function(e){var b=e.target.closest('button[data-b]');if(!b)return;document.querySelectorAll('input[name=b]').forEach(function(x){x.checked=x.value==b.dataset.b});render();$('shop').scrollIntoView({behavior:'smooth'})}})();

var CART=(function(){try{var o=JSON.parse(localStorage.getItem('vg-cart')||'{}');Object.keys(o).forEach(function(k){if(!P[k])delete o[k]});return o}catch(e){return{}}})(),CN=(function(){try{return localStorage.getItem('vg-cn')||''}catch(e){return''}})(),CA=(function(){try{return localStorage.getItem('vg-ca')||''}catch(e){return''}})(),CPH=(function(){try{return localStorage.getItem('vg-cph')||''}catch(e){return''}})();
var PM=null,CSTEP='cart',CERR=false,LASTORDER=null;
function csave(){try{localStorage.setItem('vg-cart',JSON.stringify(CART));localStorage.setItem('vg-cn',CN);localStorage.setItem('vg-ca',CA);localStorage.setItem('vg-cph',CPH)}catch(e){}}
function cc(){var n=0;for(var k in CART)n+=CART[k];return n}
function epOn(){return EPD>0&&(PM==='instapay'||PM==='ewallet')}function ct(){var t=0;for(var k in CART)t+=CART[k]*ep(P[k]);if(epOn())t=Math.round(t*(100-EPD))/100;return t}function epMsg(){if(!(EPD>0))return '';var ar=(typeof L!=='undefined'&&L==='ar');return '<p class="meta" style="color:#15803d;font-weight:700">'+(epOn()?(ar?'تم خصم '+EPD+'% لأنك بتدفع إلكترونياً':EPD+'% off applied for online payment'):(ar?'ادفع بإنستاباي أو محفظة واحصل على خصم '+EPD+'%':'Pay with InstaPay or e-wallet and get '+EPD+'% off'))+'</p>'}
function PN(n){var w;if(L==='ar'){var r=n%100;w=n===1?'منتج':n===2?'منتجان':(r>=3&&r<=10)?'منتجات':'منتج'}else w=n===1?'product':'products';return n+' '+w}function cupd(){var cb=$('cart');cb.innerHTML='<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h3l2.6 12.2a1 1 0 0 0 1 .8h8.8a1 1 0 0 0 1-.8L20 7H6"/></svg><span class="cbadge">'+cc()+'</span>';cb.setAttribute('aria-label',T('cart')+' · '+cc());var lv=$('cartlive');if(lv)lv.textContent=T('cart')+' · '+cc()+' · '+N(ct())+' '+T('egp')}
function xe(v){return String(v).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;')}
function cm(){var ar=L=='ar',m=ar?'مرحبًا Vision Guard، عايز أطلب:\n':'Hello Vision Guard, I would like to order:\n';
 Object.keys(CART).forEach(function(k,i){var p=P[k];m+=(i+1)+') '+(ar?p.a:p.e)+' × '+CART[k]+' — '+N(CART[k]*ep(p))+' '+T('egp')+'\n'});
 m+=T('wtot')+': '+N(ct())+' '+T('egp');if(CN)m+='\n'+(ar?'الاسم: ':'Name: ')+CN;if(CPH)m+='\n'+(ar?'الهاتف: ':'Phone: ')+CPH;if(CA)m+='\n'+(ar?'العنوان: ':'Address: ')+CA;if(PM)m+='\n'+(ar?'طريقة الدفع: ':'Payment: ')+(PM=='instapay'?(ar?'إنستاباي':'InstaPay'):PM=='ewallet'?(ar?'محفظة إلكترونية':'E-Wallet'):(ar?'الدفع عند الاستلام':'Cash on Delivery'));return m}
function wl(){if($('wn1'))CN=$('wn1').value;if($('wn3'))CPH=$('wn3').value;if($('wn2'))CA=$('wn2').value;csave()}
var VGC=null,VGPEND=null;
function vgUpd(){var a=document.getElementById('acct');if(!a)return;a.classList.toggle('on',!!VGC);var ar=(typeof L!=='undefined'&&L==='ar');a.setAttribute('aria-label',VGC?VGC.name:(ar?'تسجيل الدخول / حساب جديد':'Sign in / Register'))}
/* ===== Account dropdown (header icon) ===== */
var VGM_T={
 ar:{hi:'أهلاً بيك',hiName:'أهلاً، ',signin:'تسجيل الدخول',newc:'عميل جديد؟',start:'ابدأ من هنا',
  account:'حسابي وطلباتي',track:'تتبّع طلبك',out:'تسجيل الخروج'},
 en:{hi:'Hello',hiName:'Hello, ',signin:'Sign in',newc:'New customer?',start:'Start here',
  account:'My account & orders',track:'Track your order',out:'Sign out'}
};
var vgmT=0,vgmOff=null;
function vgmCss(){
 if(document.getElementById('vgm-css'))return;
 var st=document.createElement('style');st.id='vgm-css';
 st.textContent=[
  '#vgacct,#vgacct *{box-sizing:border-box}',
  '#vgacct{--vga-on:#fff;position:fixed;z-index:99998;width:min(320px,calc(100vw - 16px));padding:16px;border:1px solid var(--border,#e1e1e1);border-radius:14px;background:var(--surface,#fff);color:var(--text,#202224);box-shadow:0 14px 36px rgba(0,0,0,.22);font-size:15px}',
  ':root[data-theme="dark"] #vgacct{--vga-on:#04202e}',
  '@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) #vgacct{--vga-on:#04202e}}',
  '#vgacct .vgm-hi{margin:0;font-size:16px;font-weight:800}',
  '#vgacct .vgm-sub{margin:2px 0 12px;color:var(--muted,#494b4a);font-size:13px;direction:ltr;text-align:start;word-break:break-all}',
  '#vgacct .vgm-btn{display:block;width:100%;margin-top:10px;padding:11px;border:0;border-radius:10px;background:var(--link,#0667a5);color:var(--vga-on);font:inherit;font-size:15.5px;font-weight:800;cursor:pointer}',
  '#vgacct .vgm-new{margin:10px 0 0;text-align:center;color:var(--muted,#494b4a);font-size:13.5px}',
  '#vgacct .vgm-new button{padding:0 4px;border:0;background:none;color:var(--link,#0667a5);font:inherit;font-weight:700;cursor:pointer}',
  '#vgacct .vgm-new button:hover{text-decoration:underline}',
  '#vgacct .vgm-sec{margin:12px 0 4px;border-top:1px solid var(--border,#e1e1e1)}',
  '#vgacct .vgm-a{display:block;padding:7px 8px;border-radius:8px;color:var(--text,#202224);font-size:14.5px}',
  '#vgacct .vgm-a:hover{background:var(--bg,#f4f7fb);color:var(--link,#0667a5)}',
  '#vgacct .vgm-out{display:block;width:100%;margin-top:12px;padding:10px;border:1px solid var(--border,#cbd5e1);border-radius:10px;background:none;color:inherit;font:inherit;font-size:14.5px;cursor:pointer}',
  '#vgacct .vgm-out:hover{background:var(--bg,#f4f7fb)}'
 ].join('\n');
 document.head.appendChild(st);
}
function vgMenuClose(){
 clearTimeout(vgmT);
 var m=document.getElementById('vgacct');if(m)m.remove();
 if(vgmOff){vgmOff();vgmOff=null}
}
function vgMenu(){
 if(document.getElementById('vgacct')){vgMenuClose();return}
 var a=document.getElementById('acct');if(!a)return;
 vgmCss();
 var ar=(typeof L!=='undefined'&&L==='ar'),t=VGM_T[ar?'ar':'en'],r=a.getBoundingClientRect();
 var m=document.createElement('div');m.id='vgacct';m.setAttribute('dir',ar?'rtl':'ltr');
 var head=VGC
  ?'<p class="vgm-hi">'+t.hiName+xe(VGC.name||'')+'</p><p class="vgm-sub">'+xe(VGC.email||VGC.phone||'')+'</p>'
  :'<p class="vgm-hi">'+t.hi+'</p><button type="button" class="vgm-btn" id="vgm-in">'+t.signin+'</button><p class="vgm-new">'+t.newc+' <button type="button" id="vgm-reg">'+t.start+'</button></p>';
 m.innerHTML=head+
  '<div class="vgm-sec"></div>'+
  '<a class="vgm-a" href="/account">'+t.account+'</a>'+
  '<a class="vgm-a" href="/order-status.php">'+t.track+'</a>'+
  (VGC?'<button type="button" class="vgm-out" id="vgm-out">'+t.out+'</button>':'');
 document.body.appendChild(m);
 var w=m.offsetWidth,left=ar?r.right-w:r.left;
 m.style.left=Math.min(Math.max(8,left),window.innerWidth-w-8)+'px';
 m.style.top=(r.bottom+6)+'px';
 var bi=document.getElementById('vgm-in');if(bi)bi.onclick=function(){vgMenuClose();vgAuth(null)};
 var br=document.getElementById('vgm-reg');if(br)br.onclick=function(){vgMenuClose();vgAuth(null,'register')};
 var bo=document.getElementById('vgm-out');
 if(bo)bo.onclick=function(){
  vgApi({action:'logout'}).then(function(){VGC=null;vgUpd();vgMenuClose();if(VGPAGE==='account'||VGPAGE==='cart')location.reload()}).catch(vgMenuClose);
 };
 m.addEventListener('mouseenter',function(){clearTimeout(vgmT)});
 m.addEventListener('mouseleave',function(){vgmT=setTimeout(vgMenuClose,220)});
 function onDoc(e){if(!m.contains(e.target)&&!(e.target.closest&&e.target.closest('#acct')))vgMenuClose()}
 function onKey(e){if(e.key==='Escape')vgMenuClose()}
 setTimeout(function(){document.addEventListener('click',onDoc);document.addEventListener('keydown',onKey)},0);
 vgmOff=function(){document.removeEventListener('click',onDoc);document.removeEventListener('keydown',onKey)};
}
/* Desktop: hover opens the menu, click goes straight to sign-in / account. Touch: tap toggles the menu. */
function vgMenuBind(a){
 if(!a||a._vgb)return;a._vgb=1;
 var hov=!!(window.matchMedia&&matchMedia('(hover:hover) and (pointer:fine)').matches);
 a.addEventListener('click',function(){
  if(!hov){vgMenu();return}
  vgMenuClose();
  if(VGC)location.href='/account';else vgAuth(null);
 });
 if(hov){
  a.addEventListener('mouseenter',function(){clearTimeout(vgmT);if(!document.getElementById('vgacct'))vgMenu()});
  a.addEventListener('mouseleave',function(){vgmT=setTimeout(vgMenuClose,220)});
 }
}
function vgBs(){
 var g=document.getElementById('bsgrid');if(!g||g._bs)return;g._bs=1;
 var bar=document.createElement('div');bar.className='bsnav';
 function ic(rt){return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="'+(rt?'M9 6l6 6-6 6':'M15 6l-6 6 6 6')+'"/></svg>'}
 var nx=document.createElement('button'),pv=document.createElement('button'),dots=document.createElement('div');
 nx.type=pv.type='button';nx.className=pv.className='bsarr';dots.className='bsdots';
 function rtl(){return getComputedStyle(g).direction==='rtl'}
 function step(){var c=g.firstElementChild;return c?c.offsetWidth+(parseFloat(getComputedStyle(g).columnGap)||12):1}
 function idx(){return Math.round(Math.abs(g.scrollLeft)/step())}
 function go(d){var i=Math.max(0,Math.min(g.children.length-1,idx()+d));g.scrollTo({left:(rtl()?-1:1)*i*step(),behavior:'smooth'})}
 nx.onclick=function(){go(1)};pv.onclick=function(){go(-1)};
 function draw(){var n=g.children.length,i=idx();
  if(dots.children.length!==n){dots.innerHTML='';for(var k=0;k<n;k++){(function(k){var b=document.createElement('button');b.type='button';b.setAttribute('aria-label',String(k+1));b.onclick=function(){g.scrollTo({left:(rtl()?-1:1)*k*step(),behavior:'smooth'})};dots.appendChild(b)})(k)}}
  for(var j=0;j<dots.children.length;j++)dots.children[j].className=j===i?'on':'';
  var r=rtl();nx.innerHTML=ic(!r);pv.innerHTML=ic(r);
  nx.setAttribute('aria-label',(typeof L!=='undefined'&&L==='ar')?'التالي':'Next');pv.setAttribute('aria-label',(typeof L!=='undefined'&&L==='ar')?'السابق':'Previous');
  pv.disabled=i<=0;nx.disabled=i>=n-1}
 bar.appendChild(pv);bar.appendChild(dots);bar.appendChild(nx);g.insertAdjacentElement('afterend',bar);
 g.addEventListener('scroll',function(){clearTimeout(g._t);g._t=setTimeout(draw,60)},{passive:true});
 window.addEventListener('resize',draw);draw();
}
function vgHdr(){vgBs();
 var bg=document.getElementById('bg'),ac=document.getElementById('acct'),sr=document.querySelector('header .search');
 if(!bg||!ac||!sr||document.getElementById('qcat'))return;
 ac.insertAdjacentElement('afterend',bg);bg.classList.add('hbg');
 var s=document.createElement('select');s.id='qcat';s.className='qcat';
 s.setAttribute('aria-label',(typeof L!=='undefined'&&L==='ar')?'القسم':'Category');
 sr.insertBefore(s,sr.firstChild);
 function fill(){var cur=s.value;var all=(typeof L!=='undefined'&&L==='ar')?'كل الأقسام':'All categories';s.innerHTML='';var o=document.createElement('option');o.value='';o.textContent=all;s.appendChild(o);
  document.querySelectorAll('input[name="c"]').forEach(function(i){var lb=i.closest('label');var o2=document.createElement('option');o2.value=i.value;o2.textContent=(lb?lb.textContent:i.value).replace(/\s+/g,' ').trim();s.appendChild(o2)});s.value=cur||''}
 function sync(){var c=[].slice.call(document.querySelectorAll('input[name="c"]:checked'));s.value=c.length===1?c[0].value:''}
 fill();
 s.addEventListener('change',function(){if(VGPAGE!=='shop'){location.href='/shop'+(s.value?'?c='+encodeURIComponent(s.value):'');return}document.querySelectorAll('input[name="c"]').forEach(function(i){i.checked=(i.value===s.value)});try{SHOWN=PAGE}catch(e){}render();
  var p=document.getElementById('price'),as=p&&p.closest('aside'),t=as&&as.parentElement;if(t){t.style.scrollMarginTop='90px';t.scrollIntoView({behavior:'smooth',block:'start'})}});
 document.querySelectorAll('input[name="c"]').forEach(function(i){i.addEventListener('change',sync)});
 var lg=document.getElementById('lang');if(lg)lg.addEventListener('click',function(){setTimeout(function(){fill();sync()},300)});
}
document.addEventListener('DOMContentLoaded',function(){var a=document.getElementById('acct');if(a)vgMenuBind(a);vgUpd();vgHdr()});
if(document.readyState!=='loading'){var _a=document.getElementById('acct');if(_a&&!_a._v){_a._v=1;vgMenuBind(_a);vgUpd();vgHdr()}}
function vgApi(b){return fetch('/api/customer.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(b)}).then(function(r){return r.json()})}
try{window.VGME=vgApi({action:'me'}).then(function(j){VGC=j&&j.customer||null;vgUpd()}).catch(function(){})}catch(e){}
function vgGoogle(el,cb){
 if(!el)return;
 function go(){try{google.accounts.id.initialize({client_id:'249338136868-dq98bt7kr5b5m552h69tgjeu2vj6u8ir.apps.googleusercontent.com',callback:function(r){vgApi({action:'google',credential:r.credential}).then(cb).catch(function(){cb(null)})}});google.accounts.id.renderButton(el,{theme:'outline',size:'large',width:300,text:'continue_with',locale:(typeof L!=='undefined'&&L==='ar')?'ar':'en'})}catch(e){}}
 if(window.google&&google.accounts&&google.accounts.id)return go();
 var sc=document.createElement('script');sc.src='https://accounts.google.com/gsi/client';sc.async=true;sc.onload=go;document.head.appendChild(sc);
}
/* ===== Customer auth UI: login / register, add email, password reset ===== */
var VGA_T={
 ar:{
  close:'إغلاق',show:'إظهار',hide:'إخفاء',busy:'لحظة...',or:'أو بالبريد الإلكتروني',
  name:'الاسم',email:'البريد الإلكتروني',pw:'كلمة المرور',pwHint:'10 أحرف على الأقل.',
  loginHint:'لو حسابك قديم، اكتب رقم موبايلك بدل البريد.',
  loginTitle:'تسجيل الدخول',loginTitleOrder:'سجّل دخولك لتأكيد الطلب',
  regTitle:'إنشاء حساب جديد',regTitleOrder:'أنشئ حسابك لتأكيد الطلب',
  go1:'دخول',go2:'إنشاء الحساب',sw1:'مفيش عندك حساب؟ أنشئ حساب',sw2:'عندك حساب؟ سجّل دخولك',fp:'نسيت كلمة المرور؟',
  addTitle:'أضف بريدك الإلكتروني',addText:'علشان نكمّل الطلب لازم يكون على حسابك بريد إلكتروني.',addGo:'حفظ ومتابعة',
  rsTitle:'استعادة كلمة المرور',rsText1:'اكتب البريد الإلكتروني المسجل عندنا، وهنبعتلك كود تحقق.',rsSend:'أرسل كود التحقق',
  rsText2:'لو البريد مسجل عندنا، هيوصلك كود خلال دقيقة (راجع الـ Spam كمان).',rsCode:'كود التحقق (6 أرقام)',rsNew:'كلمة المرور الجديدة',
  rsSave:'حفظ كلمة المرور',rsBack:'رجوع لتسجيل الدخول',rsDone:'تم تغيير كلمة المرور وتسجيل دخولك',
  er:{
   bad_email:'البريد الإلكتروني غير صحيح',bad_login_id:'اكتب بريدك الإلكتروني (أو رقم موبايل صحيح لو حسابك قديم)',
   bad_login:'البيانات أو كلمة المرور غير صحيحة',email_exists:'البريد ده مسجّل عندنا، سجّل دخولك',
   name_required:'اكتب اسمك',pw_required:'اكتب كلمة المرور',bad_password:'كلمة المرور لازم تكون 10 أحرف على الأقل',
   bad_code:'الكود غير صحيح أو انتهت صلاحيته',code_required:'اكتب كود التحقق (6 أرقام)',
   email_failed:'تعذّر إرسال الإيميل. حاول تاني أو كلمنا على واتساب.',too_many:'محاولات كتير، حاول بعد شوية',
   bad_origin:'حدّث الصفحة وحاول تاني',login_required:'سجّل دخولك تاني',net:'حصلت مشكلة في الاتصال، حاول تاني'
  }
 },
 en:{
  close:'Close',show:'Show',hide:'Hide',busy:'Please wait...',or:'or with email',
  name:'Name',email:'Email address',pw:'Password',pwHint:'At least 10 characters.',
  loginHint:'If your account is older, enter your mobile number instead.',
  loginTitle:'Sign in',loginTitleOrder:'Sign in to confirm your order',
  regTitle:'Create an account',regTitleOrder:'Create your account to confirm your order',
  go1:'Sign in',go2:'Create account',sw1:'No account? Create one',sw2:'Have an account? Sign in',fp:'Forgot password?',
  addTitle:'Add your email',addText:'To continue with your order, your account needs an email address.',addGo:'Save and continue',
  rsTitle:'Reset password',rsText1:'Enter the email registered with us and we will send you a verification code.',rsSend:'Send verification code',
  rsText2:'If the email is registered, a code will arrive within a minute (check Spam too).',rsCode:'Verification code (6 digits)',rsNew:'New password',
  rsSave:'Save password',rsBack:'Back to sign in',rsDone:'Password changed and you are signed in',
  er:{
   bad_email:'Invalid email address',bad_login_id:'Enter your email (or a valid mobile number for an older account)',
   bad_login:'Wrong details or password',email_exists:'This email is already registered, please sign in',
   name_required:'Please enter your name',pw_required:'Enter your password',bad_password:'Password must be at least 10 characters',
   bad_code:'Wrong or expired code',code_required:'Enter the 6-digit code',
   email_failed:'Could not send the email. Try again or contact us on WhatsApp.',too_many:'Too many attempts, try again later',
   bad_origin:'Refresh the page and try again',login_required:'Please sign in again',net:'Connection problem, try again'
  }
 }
};
function vgaText(){return VGA_T[(typeof L!=='undefined'&&L==='ar')?'ar':'en']}
function vgaCss(){
 if(document.getElementById('vga-css'))return;
 var st=document.createElement('style');st.id='vga-css';
 st.textContent=[
  '.vga-ov,.vga-ov *{box-sizing:border-box}',
  '.vga-ov{--vga-on:#fff;position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;overflow:auto;background:rgba(10,20,36,.62)}',
  ':root[data-theme="dark"] .vga-ov{--vga-on:#04202e}',
  '@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) .vga-ov{--vga-on:#04202e}}',
  '.vga-box{position:relative;width:100%;max-width:400px;max-height:calc(100vh - 32px);overflow-x:hidden;overflow-y:auto;padding:18px 20px 10px;border:1px solid var(--border,#e1e1e1);border-radius:16px;background:var(--surface,#fff);color:var(--text,#202224);box-shadow:0 20px 50px rgba(0,0,0,.3);font-family:inherit}',
  '.vga-x{position:absolute;top:12px;inset-inline-end:12px;display:flex;align-items:center;justify-content:center;width:34px;height:34px;padding:0;border:0;border-radius:50%;background:var(--bg,#eef2f7);color:inherit;font-size:22px;line-height:1;cursor:pointer}',
  '.vga-x:hover{filter:brightness(.94)}',
  '.vga-ttl{margin:0 0 10px;padding-inline-end:42px;font-size:20px;font-weight:800;line-height:1.35}',
  '.vga-p{margin:0 0 12px;color:var(--muted,#494b4a);font-size:14px}',
  '.vga-g{display:flex;justify-content:center;min-height:40px;margin:0}',
  '.vga-or{display:flex;align-items:center;gap:10px;margin:6px 0 10px;color:var(--muted,#494b4a);font-size:13px}',
  '.vga-or::before,.vga-or::after{content:"";flex:1;height:1px;background:var(--border,#e1e1e1)}',
  '.vga-f{margin:0 0 8px}',
  '.vga-f label{display:block;margin:0 0 5px;font-size:14px;font-weight:600}',
  '.vga-i{position:relative;display:flex}',
  '.vga-i input{width:100%;padding:10px 12px;border:1px solid var(--border,#cbd5e1);border-radius:10px;background:var(--bg,#fff);color:inherit;font:inherit;font-size:16px}',
  '.vga-i input:focus{outline:2px solid var(--link,#0667a5);outline-offset:1px}',
  '.vga-i input.vga-pad{padding-inline-end:72px}',
  '.vga-eye{position:absolute;top:50%;inset-inline-end:6px;transform:translateY(-50%);padding:8px;border:0;background:none;color:var(--link,#0667a5);font:inherit;font-size:13px;font-weight:700;cursor:pointer}',
  '.vga-h{margin:4px 0 0;color:var(--muted,#494b4a);font-size:12.5px}',
  '.vga-e{min-height:18px;margin:0 0 6px;color:#dc2626;font-size:14px}',
  ':root[data-theme="dark"] .vga-e{color:#fca5a5}',
  '@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) .vga-e{color:#fca5a5}}',
  '.vga-btn{width:100%;padding:11px;border:0;border-radius:10px;background:var(--link,#0667a5);color:var(--vga-on);font:inherit;font-size:17px;font-weight:800;cursor:pointer}',
  '.vga-btn:disabled{opacity:.65;cursor:wait}',
  '.vga-link{display:block;width:100%;margin-top:2px;padding:6px;border:0;background:none;color:var(--link,#0667a5);font:inherit;font-size:14.5px;cursor:pointer}',
  '.vga-link:hover{text-decoration:underline}',
  '.vga-hp{position:absolute;width:1px;height:1px;overflow:hidden;opacity:0;pointer-events:none}'
 ].join('\n');
 document.head.appendChild(st);
}
/* Opens a modal. close() just removes it; dismiss() also drops the pending action. */
function vgaModal(id,title){
 vgaCss();
 var old=document.getElementById(id);if(old)old.remove();
 var t=vgaText(),prev=document.activeElement;
 var ov=document.createElement('div');ov.id=id;ov.className='vga-ov';
 ov.setAttribute('role','dialog');ov.setAttribute('aria-modal','true');ov.setAttribute('aria-labelledby',id+'-t');
 ov.setAttribute('dir',(typeof L!=='undefined'&&L==='ar')?'rtl':'ltr');
 var box=document.createElement('div');box.className='vga-box';ov.appendChild(box);
 var m={id:id,ov:ov,box:box,t:t};
 m.close=function(){
  document.removeEventListener('keydown',onKey,true);
  ov.remove();
  if(prev&&prev.focus&&document.contains(prev)){try{prev.focus()}catch(e){}}
 };
 m.dismiss=function(){VGPEND=null;m.close()};
 m.head=function(text){
  return '<button type="button" class="vga-x" id="'+id+'-x" aria-label="'+t.close+'">&times;</button><h3 class="vga-ttl" id="'+id+'-t">'+text+'</h3>';
 };
 m.bindClose=function(){var x=document.getElementById(id+'-x');if(x)x.onclick=m.dismiss};
 function onKey(ev){
  if(ev.key==='Escape'){ev.stopPropagation();m.dismiss();return}
  if(ev.key!=='Tab')return;
  var f=[].filter.call(box.querySelectorAll('button,input,a[href]'),function(e){return !e.disabled&&!e.classList.contains('vga-hp')&&e.offsetParent!==null});
  if(!f.length)return;
  var first=f[0],last=f[f.length-1];
  if(ev.shiftKey&&document.activeElement===first){ev.preventDefault();last.focus()}
  else if(!ev.shiftKey&&document.activeElement===last){ev.preventDefault();first.focus()}
 }
 document.addEventListener('keydown',onKey,true);
 ov.addEventListener('mousedown',function(ev){if(ev.target===ov)m.dismiss()});
 (document.querySelector('dialog[open]')||document.body).appendChild(ov);
 return m;
}
function vgaField(o){
 var eye=o.toggle?'<button type="button" class="vga-eye" data-for="'+o.id+'">'+o.show+'</button>':'';
 return '<div class="vga-f"><label for="'+o.id+'">'+o.label+'</label><div class="vga-i"><input id="'+o.id+'" type="'+(o.type||'text')+'"'+(o.attrs||'')+(o.toggle?' class="vga-pad"':'')+'>'+eye+'</div>'+(o.hint?'<p class="vga-h">'+o.hint+'</p>':'')+'</div>';
}
function vgaBindEyes(m){
 [].forEach.call(m.box.querySelectorAll('.vga-eye'),function(b){
  b.onclick=function(){
   var i=document.getElementById(b.getAttribute('data-for')),on=i.type==='password';
   i.type=on?'text':'password';b.textContent=on?m.t.hide:m.t.show;
  };
 });
}
function vgaVal(id){var e=document.getElementById(id);return e?e.value:''}
function vgaBusy(btn,on,label){btn.disabled=on;if(on){btn.setAttribute('data-l',btn.textContent);btn.textContent=label}else if(btn.getAttribute('data-l')){btn.textContent=btn.getAttribute('data-l')}}
function vgaToast(msg){var o=document.getElementById('toast');if(!o)return;o.textContent=msg;o.classList.add('show');setTimeout(function(){o.classList.remove('show')},2600)}
function vgaFocus(id){setTimeout(function(){var e=document.getElementById(id);if(e)e.focus()},30)}
function vgaEmailOk(v){return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)}
function vgaDigits(v){return String(v).replace(/[٠-٩]/g,function(d){return d.charCodeAt(0)-1632}).replace(/[۰-۹]/g,function(d){return d.charCodeAt(0)-1776}).replace(/\D/g,'')}
/* Signed in (login, register, Google or reset): update state, run the pending action. */
function vgaSignedIn(m,c,msg){
 VGC=c;vgUpd();
 if(!CN)CN=c.name;
 if(!CPH&&c.phone)CPH=c.phone;
 m.close();
 var f=VGPEND;VGPEND=null;
 if(c.needs_email)vgAddEmail(f);else if(typeof f==='function')f();
 if(msg)vgaToast(msg);
}
function vgAuth(cb,startMode){
 VGPEND=cb||null;
 var pending=!!cb,mode=(startMode==='register'?'register':'login'),m=vgaModal('vgauth'),t=m.t;
 function draw(){
  var reg=mode==='register';
  m.box.innerHTML=m.head(reg?(pending?t.regTitleOrder:t.regTitle):(pending?t.loginTitleOrder:t.loginTitle))+
   '<div id="vga_g" class="vga-g"></div><div class="vga-or"><span>'+t.or+'</span></div>'+
   '<form id="vga_form" novalidate>'+
   (reg?vgaField({id:'vga_n',label:t.name,attrs:' autocomplete="name"'}):'')+
   vgaField({id:'vga_p',label:t.email,type:reg?'email':'text',attrs:' inputmode="email" autocomplete="'+(reg?'email':'username')+'" autocapitalize="off" spellcheck="false"',hint:reg?'':t.loginHint})+
   vgaField({id:'vga_w',label:t.pw,type:'password',attrs:' autocomplete="'+(reg?'new-password':'current-password')+'"',toggle:1,show:t.show,hint:reg?t.pwHint:''})+
   '<input id="vga_h" class="vga-hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">'+
   '<div id="vga_e" class="vga-e" role="alert"></div>'+
   '<button id="vga_go" class="vga-btn" type="submit">'+(reg?t.go2:t.go1)+'</button></form>'+
   (reg?'':'<button id="vga_fp" class="vga-link" type="button">'+t.fp+'</button>')+
   '<button id="vga_sw" class="vga-link" type="button">'+(reg?t.sw2:t.sw1)+'</button>';
  m.bindClose();vgaBindEyes(m);
  document.getElementById('vga_sw').onclick=function(){mode=reg?'login':'register';draw()};
  var fp=document.getElementById('vga_fp');if(fp)fp.onclick=function(){m.close();vgReset()};
  vgGoogle(document.getElementById('vga_g'),function(j){
   if(j&&j.ok&&j.customer)vgaSignedIn(m,j.customer);
   else{var e=document.getElementById('vga_e');if(e)e.textContent=t.er[j&&j.error]||t.er.net}
  });
  document.getElementById('vga_form').onsubmit=function(ev){
   ev.preventDefault();
   var e=document.getElementById('vga_e'),btn=document.getElementById('vga_go');e.textContent='';
   var email=vgaVal('vga_p').trim(),pass=vgaVal('vga_w'),name=reg?vgaVal('vga_n').trim():'';
   if(reg&&!name){e.textContent=t.er.name_required;return vgaFocus('vga_n')}
   if(!email){e.textContent=reg?t.er.bad_email:t.er.bad_login_id;return vgaFocus('vga_p')}
   if(reg&&!vgaEmailOk(email)){e.textContent=t.er.bad_email;return vgaFocus('vga_p')}
   if(!pass){e.textContent=t.er.pw_required;return vgaFocus('vga_w')}
   if(reg&&pass.length<10){e.textContent=t.er.bad_password;return vgaFocus('vga_w')}
   var b={action:mode,email:email,password:pass,website:vgaVal('vga_h')};
   if(reg)b.name=name;
   vgaBusy(btn,true,t.busy);
   vgApi(b).then(function(j){
    vgaBusy(btn,false);
    if(j&&j.ok&&j.customer)return vgaSignedIn(m,j.customer);
    var code=j&&j.error;
    if(!reg&&code==='bad_password')code='bad_login';
    e.textContent=t.er[code]||t.er.net;
   }).catch(function(){vgaBusy(btn,false);e.textContent=t.er.net});
  };
  vgaFocus(reg?'vga_n':'vga_p');
 }
 draw();
}
function vgAddEmail(cb){
 var m=vgaModal('vgemail'),t=m.t;
 m.box.innerHTML=m.head(t.addTitle)+'<p class="vga-p">'+t.addText+'</p>'+
  '<form id="vge_form" novalidate>'+
  vgaField({id:'vge_p',label:t.email,type:'email',attrs:' inputmode="email" autocomplete="email" autocapitalize="off" spellcheck="false"'})+
  '<div id="vge_e" class="vga-e" role="alert"></div>'+
  '<button id="vge_go" class="vga-btn" type="submit">'+t.addGo+'</button></form>';
 m.bindClose();
 document.getElementById('vge_form').onsubmit=function(ev){
  ev.preventDefault();
  var e=document.getElementById('vge_e'),btn=document.getElementById('vge_go'),email=vgaVal('vge_p').trim();e.textContent='';
  if(!vgaEmailOk(email)){e.textContent=t.er.bad_email;return vgaFocus('vge_p')}
  vgaBusy(btn,true,t.busy);
  vgApi({action:'set_email',email:email}).then(function(j){
   vgaBusy(btn,false);
   if(j&&j.ok&&j.customer){VGC=j.customer;vgUpd();m.close();if(typeof cb==='function')cb()}
   else e.textContent=t.er[j&&j.error]||t.er.net;
  }).catch(function(){vgaBusy(btn,false);e.textContent=t.er.net});
 };
 vgaFocus('vge_p');
}
function vgReset(){
 var m=vgaModal('vgreset'),t=m.t,step=1,email='';
 function draw(){
  m.box.innerHTML=m.head(t.rsTitle)+
   '<p class="vga-p">'+(step===1?t.rsText1:t.rsText2)+'</p><form id="vgr_form" novalidate>'+
   (step===1
    ?vgaField({id:'vgr_p',label:t.email,type:'email',attrs:' inputmode="email" autocomplete="email" autocapitalize="off" spellcheck="false" value="'+xe(email)+'"'})
    :vgaField({id:'vgr_c',label:t.rsCode,attrs:' inputmode="numeric" autocomplete="one-time-code" maxlength="6"'})+
     vgaField({id:'vgr_n',label:t.rsNew,type:'password',attrs:' autocomplete="new-password"',toggle:1,show:t.show,hint:t.pwHint}))+
   '<div id="vgr_e" class="vga-e" role="alert"></div>'+
   '<button id="vgr_go" class="vga-btn" type="submit">'+(step===1?t.rsSend:t.rsSave)+'</button></form>'+
   '<button id="vgr_b" class="vga-link" type="button">'+t.rsBack+'</button>';
  m.bindClose();vgaBindEyes(m);
  document.getElementById('vgr_b').onclick=function(){
   if(step===2){step=1;draw();return}
   var f=VGPEND;m.close();vgAuth(f);
  };
  document.getElementById('vgr_form').onsubmit=function(ev){
   ev.preventDefault();
   var e=document.getElementById('vgr_e'),btn=document.getElementById('vgr_go'),body;e.textContent='';
   if(step===1){
    email=vgaVal('vgr_p').trim();
    if(!vgaEmailOk(email)){e.textContent=t.er.bad_email;return vgaFocus('vgr_p')}
    body={action:'request',email:email,website:''};
   }else{
    var code=vgaDigits(vgaVal('vgr_c')),pass=vgaVal('vgr_n');
    if(code.length!==6){e.textContent=t.er.code_required;return vgaFocus('vgr_c')}
    if(pass.length<10){e.textContent=t.er.bad_password;return vgaFocus('vgr_n')}
    body={action:'verify',email:email,code:code,password:pass,website:''};
   }
   vgaBusy(btn,true,t.busy);
   fetch('/api/password_reset.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)})
    .then(function(r){return r.json()}).then(function(j){
     vgaBusy(btn,false);
     if(step===1&&j&&j.ok){step=2;draw();return}
     if(step===2&&j&&j.ok&&j.customer)return vgaSignedIn(m,j.customer,t.rsDone);
     e.textContent=t.er[j&&j.error]||t.er.net;
    }).catch(function(){vgaBusy(btn,false);e.textContent=t.er.net});
  };
  vgaFocus(step===1?'vgr_p':'vgr_c');
 }
 draw();
}
function placeOrder(){
 if(window.__po)return;
if(!VGC){vgAuth(function(){placeOrder()});return}
if(VGC.needs_email){vgAddEmail(function(){placeOrder()});return}
 var digits=(CPH||'').replace(/\D/g,'');
 if(!CN||!CN.trim()||digits.length<8||!CA||!CA.trim()||['cod','instapay','ewallet'].indexOf(PM)<0){CERR=true;cartUI();return}
 CERR=false;
 var items=[];
 for(var k in CART){var p=P[k];items.push({id:p?parseInt(String(p.id).replace(/^p/,''),10):0,qty:CART[k]})}
 if(!items.length){CERR=true;cartUI();return}
 var tot=ct(),msg=cm();
 window.__po=1;
 sendLead({type:'order',name:CN,phone:CPH,message:(L=='ar'?'العنوان: ':'Address: ')+CA,cart:items,total:tot,payment_method:PM}).then(function(res){
  window.__po=0;
   if(!res||res.ok!==true||!res.id){var em=res&&res.error;if(em==='login_required'){VGC=null;vgUpd();vgAuth(function(){placeOrder()});return}if(em==='email_required'){vgAddEmail(function(){placeOrder()});return}alert(em==='product_out_of_stock'?(L=='ar'?'في منتج خلص من المخزون. حدّث الصفحة وراجع السلة.':'An item just went out of stock. Refresh the page and review your cart.'):em==='insufficient_stock'?(L=='ar'?'الكمية المطلوبة أكبر من المتاح. عدّل الكمية في السلة.':'The requested quantity is larger than the available stock. Please reduce it.'):(L=='ar'?'لم نتمكن من حفظ الطلب. السلة ما زالت محفوظة، حاول مرة أخرى.':'We could not save your order. Your cart is still saved. Please try again.'));return}
  var confirmedTotal=(typeof res.total==='number'?res.total:tot),confirmedItems=res.items||items;
  msg=(L=='ar'?'مرحبًا Vision Guard، تم تسجيل طلبي رقم #':'Hello Vision Guard, my order #')+res.id+'\n';
  confirmedItems.forEach(function(it,i){msg+=(i+1)+') '+(L=='ar'?(it.name||it.name_en||'منتج'):(it.name_en||it.name||'Product'))+' × '+it.qty+' — '+N(it.line_total||0)+' '+T('egp')+'\n'});
  msg+=T('wtot')+': '+N(confirmedTotal)+' '+T('egp')+'\n'+(L=='ar'?'الاسم: ':'Name: ')+CN+'\n'+(L=='ar'?'الهاتف: ':'Phone: ')+CPH+'\n'+(L=='ar'?'العنوان: ':'Address: ')+CA+'\n'+(L=='ar'?'طريقة الدفع: ':'Payment: ')+PM;
  LASTORDER={items:confirmedItems,total:confirmedTotal,name:CN,phone:CPH,addr:CA,pm:PM,id:res.id,wa:WA+'?text='+encodeURIComponent(msg)};
  CART={};csave();cupd();CSTEP='done';cartUI();
 }).catch(function(){window.__po=0;alert(L=='ar'?'حصل خطأ في الاتصال. السلة ما زالت محفوظة.':'Connection error. Your cart is still saved.');});
}
function avail(k){var p=P[k];if(!p||p.q==null)return 50;return Math.min(50,Math.max(0,p.q))}
function cq(k,d){var cap=avail(k);if(d>0&&(CART[k]||0)>=cap){tst('maxq');return}CART[k]=Math.max(0,Math.min(cap,(CART[k]||0)+d));if(CART[k]<=0)delete CART[k];csave();cupd();cartUI()}
function cartUI(){
 var ks=Object.keys(CART),h='';
 if(CSTEP=='done'){
  var o=LASTORDER||{items:[],total:0};
  h='<h3 style="margin:0 0 10px">'+T('odone')+'</h3>'+(o.id?'<p><b>'+(L==='ar'?'رقم طلبك: #':'Order #')+o.id+'</b></p><p><a href="/order-status.php?id='+o.id+'">'+(L==='ar'?'تتبّع طلبك':'Track your order')+'</a></p>':'')+'<p class="meta">'+T('odthank')+'</p>';
  h+='<p><b>'+T('wtot')+': '+N(o.total)+' '+T('egp')+'</b></p>';
  if(o.pm=='instapay'){h+='<div class="ipbox"><p>'+T('ipmsg')+'</p><p class="ipnum">'+xe(IPN)+'</p><img class="qrimg" src="images/instapay-qr.png" alt="InstaPay QR"><p class="meta">'+T('ipnote')+'</p></div>'}
  else if(o.pm=='ewallet'){h+='<div class="ipbox"><p>'+T('ewmsg')+'</p><p class="ipnum">'+xe(EWN)+'</p><p class="meta">'+T('ewnote')+'</p></div>'}
  else{h+='<p class="meta">'+T('codnote')+'</p>'}
  h+='<div class="ptools" style="margin-top:12px">'+(o.wa?'<a class="btn pri" target="_blank" rel="noopener" href="'+xe(o.wa)+'">'+(L==='ar'?'إرسال تفاصيل الطلب على واتساب':'Send order details on WhatsApp')+'</a>':'')+'<button type="button" class="btn" onclick="CSTEP=\'cart\';vgCartClose()">'+(VGPAGE==='cart'?(L==='ar'?'متابعة التسوق':'Continue shopping'):T('wx'))+'</button></div>';
  $('cdc').innerHTML=h;return}
 if(CSTEP=='checkout'){
  h='<h3 style="margin:0 0 10px">'+T('cot')+'</h3>';
  h+='<p class="meta">'+T('wtot')+': '+N(ct())+' '+T('egp')+'</p>';h+=epMsg();
  h+='<div class="cf"><input id="wn1" oninput="wl()" placeholder="'+T('wn')+'" value="'+xe(CN)+'"><input id="wn3" oninput="wl()" placeholder="'+T('wph')+'" value="'+xe(CPH)+'"><input id="wn2" oninput="wl()" placeholder="'+T('wad')+'" value="'+xe(CA)+'"></div>';
  h+='<p class="meta" style="margin-top:10px">'+T('pmt')+'</p>';
  h+='<div class="pmopts"><button type="button" class="pmopt'+(PM=='cod'?' active':'')+'" onclick="PM=\'cod\';cartUI()">'+T('pmcod')+'</button><button type="button" class="pmopt'+(PM=='instapay'?' active':'')+'" onclick="PM=\'instapay\';cartUI()">'+T('pmip')+'</button><button type="button" class="pmopt'+(PM=='ewallet'?' active':'')+'" onclick="PM=\'ewallet\';cartUI()">'+T('pmew')+'</button></div>';
  if(CERR)h+='<p class="meta" style="color:#e5484d;margin-top:8px">'+T('cerr')+'</p>';
   if(PM=='instapay'){h+='<div class="ipbox"><p>'+T('ipmsg')+'</p><p class="ipnum">'+xe(IPN)+'</p><img class="qrimg" src="images/instapay-qr.png" alt="InstaPay QR"><p class="meta">'+T('ipnote')+'</p></div>'}else if(PM=='ewallet'){h+='<div class="ipbox"><p>'+T('ewmsg')+'</p><p class="ipnum">'+xe(EWN)+'</p><p class="meta">'+T('ewnote')+'</p></div>'}
  h+='<div class="ptools" style="margin-top:12px"><button type="button" class="btn pri" onclick="placeOrder()">'+T('coconf')+'</button><button type="button" class="btn" onclick="CSTEP=\'cart\';cartUI()">'+T('coback')+'</button></div>';
  $('cdc').innerHTML=h;return}
 h='<h3 id="cdt" style="margin:0 0 10px">'+T('wt')+'</h3>';
 if(!ks.length)h+='<p class="meta">'+T('we')+'</p>';
 else h+=ks.map(function(k){var p=P[k];return'<div class="ci"><span>'+(L=='ar'?p.a:p.e)+'</span><span class="qty2"><button type="button" class="btn sm" onclick="cq('+k+',-1)" aria-label="'+(L==='ar'?'تقليل كمية ':'Decrease quantity of ')+(L==='ar'?p.a:p.e).replace(/"/g,'&quot;')+'">−</button><b>'+CART[k]+'</b><button type="button" class="btn sm" onclick="cq('+k+',1)" aria-label="'+(L==='ar'?'زيادة كمية ':'Increase quantity of ')+(L==='ar'?p.a:p.e).replace(/"/g,'&quot;')+'">+</button></span><span>'+N(CART[k]*ep(p))+' '+T('egp')+'</span></div>'}).join('')+
  '<p><b>'+T('wtot')+': '+N(ct())+' '+T('egp')+'</b></p>';
 h+='<div class="ptools" style="margin-top:12px">'+(ks.length?'<button type="button" class="btn pri" onclick="vgCheckout()">'+T('cobtn')+'</button><button type="button" class="btn" onclick="CART={};csave();cupd();cartUI()">'+T('wclr')+'</button>':'')+'<button type="button" class="btn" onclick="vgCartClose()">'+(VGPAGE==='cart'?(L==='ar'?'متابعة التسوق':'Continue shopping'):T('wx'))+'</button></div>';
 $('cdc').innerHTML=h}
$('cart').onclick=function(){if(VGPAGE==='cart'){CSTEP='cart';CERR=false;cartUI();window.scrollTo({top:0,behavior:'smooth'});return}location.href='/cart'};
function vgCartClose(){if(VGPAGE==='cart'){location.href='/shop'}else{$('cd').close()}}
function vgCheckout(){(window.VGME||Promise.resolve()).then(function(){if(!VGC){vgAuth(function(){CSTEP='checkout';CERR=false;cartUI()});return}if(VGC.needs_email){vgAddEmail(function(){vgCheckout()});return}if(!CN)CN=VGC.name;if(!CPH&&VGC.phone)CPH=VGC.phone;CSTEP='checkout';CERR=false;cartUI()})}
var OFF=<?php echo offers_js_array(); ?>,PI=0,PQ=1,DCAT={cat_hd:'bu',cat_ip:'pt',cat_wf:'cu',cat_dvr:'dv',cat_hdd:'hd',cat_mem:'mc',cat_psu:'ps',cat_cab:'ca',cat_rack:'rk',cat_acc:'ad',cat_net:'bx',cat_att:'bx'};
function offOK(o){var t=new Date().toLocaleDateString('sv-SE');return o.on&&(!o.from||t>=o.from)&&(!o.to||t<=o.to)}
function ep(p){var b=p.p;OFF.filter(offOK).forEach(function(o){if(o.sc=='all'||(o.sc=='cat'&&o.tg==p.c)||(o.sc=='brand'&&o.tg==p.b)||(o.sc=='prod'&&o.tg==p.id)){var v=o.k=='pct'?p.p*(1-o.v/100):p.p-o.v;if(v<b)b=Math.max(0,v)}});return Math.round(b*100)/100}
function prc(p){var e=ep(p);return N(e)+' '+T('egp')+(e<p.p?'<s>'+N(p.p)+'</s>':'')}
var BA={};function BN(b){return L=='ar'&&BA[b]||b}
function nmp(p){return L=='ar'?p.a:p.e}
function pimg(p){var __v=IMGV[p.id]||IMGV['p'+p.id];return'<img src="images/'+p.id+'.jpg'+(__v?'?v='+__v:'')+'" alt="'+esc(nmp(p))+'" loading="lazy" decoding="async" onerror="ph(this,\''+p.ic+'\')">'}
function ph(e,k){e.outerHTML=svg(k)}
function tst(k){var o=$('toast');o.textContent=T(k);o.classList.add('show');setTimeout(function(){o.classList.remove('show')},1400)}
function fc(c){document.querySelectorAll('input[name=c]').forEach(function(x){x.checked=x.value==c});$('onsale').checked=false;render()}
function fcb(c,b){document.querySelectorAll('input[name=c]').forEach(function(x){x.checked=x.value==c});document.querySelectorAll('input[name=b]').forEach(function(x){x.checked=x.value==b});$('onsale').checked=false;render()}
function fo(){document.querySelectorAll('input[name=c]').forEach(function(x){x.checked=false});$('onsale').checked=true;render()}
function pq(d){PQ=Math.max(1,PQ+d);$('pqv').textContent=PQ}
function pa(i){var cap=avail(i);if((CART[i]||0)+PQ>cap){tst('maxq');return}CART[i]=(CART[i]||0)+PQ;cupd();tst('added')}
var FT={'Mic':'fmic','Night Vision':'fnv','Color':'fcol','Fixed':'ffix','Pan & Tilt':'fpt'};
var XS={"p26":{"a":[["sdt","1920×1080 (1080p)"],["sptr","تحريك أفقي 360° / رأسي 114° تقريبًا"],["snv","أشعة تحت حمراء حتى 9 متر"],["slens","4mm"],["sau","اتجاهين (ميكروفون وسماعة)"],["sst","فتحة كارت microSD"],["snw","Wi-Fi 2.4GHz"],["sfn","قد تختلف بعض التفاصيل حسب إصدار الجهاز"]],"e":[["sdt","1920×1080 (1080p)"],["sptr","360° pan / 114° tilt"],["snv","Infrared, up to 9 m"],["slens","4mm"],["sau","Two-way (mic and speaker)"],["sst","microSD card slot"],["snw","Wi-Fi 2.4GHz"],["sfn","Some details may vary by hardware version"]]},"p27":{"a":[["sdt","2304×1296 (3MP)"],["snv","حتى 30 متر"],["sip","IP66"],["slens","زاوية عرض حوالي 100°"],["sau","اتجاهين (ميكروفون وسماعة)"],["sst","microSD حتى 128GB"],["snw","Wi-Fi 2.4GHz أو كابل شبكة"],["sfn","كشف حركة بإشعارات، إنذار صوتي وضوئي"]],"e":[["sdt","2304×1296 (3MP)"],["snv","Up to 30 m"],["sip","IP66"],["slens","About 100° wide angle"],["sau","Two-way (mic and speaker)"],["sst","microSD up to 128GB"],["snw","Wi-Fi 2.4GHz or Ethernet"],["sfn","Motion alerts, sound and light alarm"]]},"p28":{"a":[["sdt","2560×1440 (2K QHD، 4MP)"],["sptr","تحريك أفقي 360° / رأسي 130° تقريبًا"],["snv","حتى 30 متر + رؤية ليلية ملونة (Starlight)"],["sip","IP66"],["sau","اتجاهين (ميكروفون وسماعة)"],["sst","microSD حتى 512GB"],["snw","Wi-Fi 2.4GHz أو كابل شبكة"],["sfn","كشف أشخاص وحيوانات ومركبات، إنذار صوتي وضوئي"]],"e":[["sdt","2560×1440 (2K QHD, 4MP)"],["sptr","360° pan / about 130° tilt"],["snv","Up to 30 m + full-color starlight night vision"],["sip","IP66"],["sau","Two-way (mic and speaker)"],["sst","microSD up to 512GB"],["snw","Wi-Fi 2.4GHz or Ethernet"],["sfn","Person, pet and vehicle detection, sound and light alarm"]]},"p25":{"a":[["sdt","1920×1080 (1080p)"],["sptr","تحريك أفقي 360° / رأسي 114° تقريبًا"],["snv","حتى 9 متر"],["sau","اتجاهين (ميكروفون وسماعة)"],["sst","microSD أو تخزين سحابي (باشتراك)"],["snw","Wi-Fi 2.4GHz"],["sfn","كشف حركة، إنذار صوتي وضوئي"]],"e":[["sdt","1920×1080 (1080p)"],["sptr","360° pan / 114° tilt"],["snv","Up to 9 m"],["sau","Two-way (mic and speaker)"],["sst","microSD or cloud storage (subscription)"],["snw","Wi-Fi 2.4GHz"],["sfn","Motion detection, sound and light alarm"]]},"p29":{"a":[["sdt","2304×1296 (3MP) — إصدار V3.0"],["sptr","تحريك أفقي 0–355° / رأسي -15° إلى 55°"],["sau","اتجاهين (Full-duplex)"],["snw","Wi-Fi 6 (2.4GHz)"],["sfn","تتبع الحركة، تسجيل تلقائي عند كشف الحركة"]],"e":[["sdt","2304×1296 (3MP), V3.0"],["sptr","Pan 0–355° / tilt -15° to 55°"],["sau","Two-way (full-duplex)"],["snw","Wi-Fi 6 (2.4GHz)"],["sfn","Motion tracking, auto recording on motion"]]},"p13":{"a":[["sdt","2304×1296 (3MP)"],["sptr","تحريك أفقي 0–355° / رأسي -5° إلى 50°"],["snv","حتى 10 متر + رؤية ليلية ملونة"],["sau","ميكروفون وسماعة"],["sst","microSD حتى 512GB أو تخزين سحابي"],["snw","Wi-Fi 6 (2.4GHz)"],["sfn","كشف أشخاص وتتبع ذكي للحركة"]],"e":[["sdt","2304×1296 (3MP)"],["sptr","Pan 0–355° / tilt -5° to 50°"],["snv","Up to 10 m + full-color night vision"],["sau","Mic and speaker"],["sst","microSD up to 512GB or cloud storage"],["snw","Wi-Fi 6 (2.4GHz)"],["sfn","Human detection and smart motion tracking"]]},"p17":{"a":[["sdt","2880×1620 (5MP)"],["sptr","تحريك أفقي 0–355° / رأسي 0–90°"],["snv","حتى 30 متر، 4 أوضاع رؤية ليلية"],["sip","IP66"],["slens","3.6mm"],["sau","اتجاهين (ميكروفون وسماعة)"],["sst","microSD حتى 512GB"],["snw","Wi-Fi"],["sfn","كشف أشخاص، أضواء تحذير حمراء وزرقاء"]],"e":[["sdt","2880×1620 (5MP)"],["sptr","Pan 0–355° / tilt 0–90°"],["snv","Up to 30 m, 4 night vision modes"],["sip","IP66"],["slens","3.6mm"],["sau","Two-way (mic and speaker)"],["sst","microSD up to 512GB"],["snw","Wi-Fi"],["sfn","Human detection, red and blue warning lights"]]},"p20":{"a":[["sdt","1920×1080 (2MP) — يوجد إصدار 4MP"],["snv","حتى 30 متر + كشاف ضوئي مدمج"],["sip","IP67"],["slens","2.8mm (متوفرة 3.6 و6mm)"],["sau","ميكروفون مدمج"],["sst","microSD حتى 256GB"],["snw","Wi-Fi 2.4GHz + منفذ شبكة 100Mbps"],["sfn","كشف أشخاص، ONVIF"]],"e":[["sdt","1920×1080 (2MP), a 4MP version also exists"],["snv","Up to 30 m + built-in spotlight"],["sip","IP67"],["slens","2.8mm (3.6 and 6mm available)"],["sau","Built-in microphone"],["sst","microSD up to 256GB"],["snw","Wi-Fi 2.4GHz + 100Mbps Ethernet port"],["sfn","Human detection, ONVIF"]]}};
function spec(p){var e=p.e||'',r=[],m;function A(k,v){if(v)r.push([k,v])}A('sbr',esc(BN(p.b)));
 if(/^cat_(hd|ip|wf)$/.test(p.c)){m=e.match(/(\d+)MP/);A('sres',m&&m[1]+'MP');A('spl',p.pl?T(p.pl):'');A('scn',T(p.c=='cat_hd'?'cn1':p.c=='cat_ip'?'cn2':'cn3'));A('sft',Object.keys(FT).filter(function(k){return e.indexOf(k)>-1}).map(function(k){return T(FT[k])}).join(' · '))}
 else if(p.c=='cat_dvr'){m=e.match(/(\d+)-Channel (\d+MP)/);A('sch',m&&m[1]);A('sres',m&&m[2])}
 else if(p.c=='cat_hdd'||p.c=='cat_mem'){m=e.match(/(\d+(?:\.\d+)?)\s?(TB|GB)/i);A('scap',m&&m[1]+m[2].toUpperCase())}
 else if(p.c=='cat_psu'){m=e.match(/^(\d+)A/);A('sam',m&&m[1]+'A')}
 else if(p.c=='cat_cab'){m=e.match(/RG\d+|Cat6/);A('sty',m&&m[0]);m=e.match(/(\d+)m\b/);A('sln',m&&m[1]+' m')}
 else if(p.c=='cat_rack'){m=e.match(/(\d+U)/);A('ssz',m&&m[1])}
 else if(p.c=='cat_acc'){m=e.match(/^(\d+)A/);A('sam',m&&m[1]+'A');m=e.match(/(\d+×\d+)/);A('ssz',m&&m[1]);m=e.match(/^(BNC|DC)\b/);A('sty',m&&m[1])}
 var x=XS[p.id];if(x)x[L=='ar'?'a':'e'].forEach(function(q){r.push(q)});return r}
function dsc(p){var e=p.e||'',k={cat_hd:'dc_hd',cat_ip:'dc_ip',cat_wf:'dc_wf',cat_dvr:'dc_dvr',cat_mem:'dc_mem',cat_psu:'dc_psu',cat_rack:'dc_rack'}[p.c];
 if(p.c=='cat_hdd')k=p.b=='WD'?'dc_wd':'dc_hdd';else if(p.c=='cat_cab')k=/Cat6/.test(e)?'dc_utp':'dc_coax';
 else if(p.c=='cat_acc')k=/Adaptor/.test(e)?'dc_ad':/Box/.test(e)?'dc_box':/^BNC/.test(e)?'dc_bnc':/^DC/.test(e)?'dc_dc':'dc_x';
 return T(k||'dc_x')}
function spt(p){var r=spec(p);return(r.length?'<h4 style="margin:14px 0 0">'+T('sp')+'</h4><table class="spt">'+r.map(function(x){return'<tr><td>'+T(x[0])+'</td><td>'+x[1]+'</td></tr>'}).join('')+'</table>':'')+'<p class="meta" style="margin:8px 0 0">'+T('snote')+'</p>'}
function pd(i){var p=P[i],r=P.filter(function(x){return x.c==p.c&&x.i!=i&&!x.hide}).slice(0,3),ar=L=='ar',
 m=(ar?'مرحبًا Vision Guard، عايز أسأل عن: ':'Hello Vision Guard, I would like to ask about: ')+nmp(p)+' ('+N(ep(p))+' '+T('egp')+')\n'+location.href.split('#')[0]+'#'+p.id;PI=i;PQ=1;
 $('pdc').innerHTML='<div class="pdt"><div class="pic">'+pimg(p)+'</div><div><div class="meta">'+[esc(BN(p.b)),T(p.c),p.pl?T(p.pl):''].filter(Boolean).join(' · ')+'</div><h3 style="margin:6px 0">'+esc(nmp(p))+'</h3><div class="price" style="font-size:22px">'+prc(p)+'</div><div class="stock'+(p.s?'':' out')+'">'+(p.s?T('stock'):T('oos'))+'</div><p class="meta" style="margin:8px 0">'+dsc(p)+'</p>'+
 (p.s?'<div class="pdq"><button type="button" class="btn sm" onclick="pq(-1)">−</button><b id="pqv">1</b><button type="button" class="btn sm" onclick="pq(1)">+</button><button type="button" class="btn pri" onclick="pa('+i+')">'+T('add')+'</button></div>':'')+
 '<a class="btn" target="_blank" rel="noopener" href="'+WA+'?text='+encodeURIComponent(m)+'">'+T('ask')+'</a></div></div>'+spt(p)+
 (r.length?'<h4 style="margin:14px 0 0">'+T('rel')+'</h4><div class="rel">'+r.map(function(x){return'<button type="button" class="btn sm" onclick="pd('+x.i+')">'+nmp(x)+'</button>'}).join('')+'</div>':'')+
 '<div class="ptools" style="margin-top:14px"><button type="button" class="btn" onclick="$(\'pdg\').close()">'+T('wx')+'</button></div>';
 if(!$('pdg').open)$('pdg').showModal();}
$('pdg').onclose=function(){try{history.replaceState(null,'',location.pathname+location.search)}catch(e){}};
function hs(){var h=location.hash.slice(1),p=P.find(function(x){return x.id==h});if(p)pd(p.i)}
function sync(d){if(!d||!d.products)return;var m={};P.forEach(function(p){m[p.id]=p});
 d.products.forEach(function(x){var p=m[x.id];if(!p){p={i:P.length,id:x.id,c:x.category,pl:'',b:x.brand||'',a:x.name,e:x.name,ic:DCAT[x.category]||'bu',s:1};P.push(p);m[x.id]=p}
  p.p=+x.price||0;p.hide=x.visible===false;if(x.name)p.a=x.name;if(x.stock!=null)p.s=x.stock>0?1:0});
 OFF=d.offers||[];chk('brands',[...new Set(P.map(function(p){return p.b}).filter(Boolean))],'b');cchips();render();bsRender();hs()}
function bgo(o){$('dr').classList.toggle('open',o);$('bg').setAttribute('aria-expanded',o);document.body.style.overflow=o?'hidden':''}
$('drl').innerHTML=document.querySelector('nav .wrap').innerHTML;
(function(){try{D.ar.vgAll='كل المنتجات';D.en.vgAll='All products';var dl=$('drl'),pa=dl.querySelector('a[data-t="n1"]');if(!pa)return;var ds=[];var n=pa.nextElementSibling;while(n&&n.tagName==='DETAILS'){ds.push(n);n=n.nextElementSibling}if(!ds.length)return;var pr=document.createElement('details');pr.className='nvprod';var sm=document.createElement('summary');sm.setAttribute('data-t','n1');sm.textContent=pa.textContent;pr.appendChild(sm);var all=document.createElement('a');all.href='/shop';all.setAttribute('data-t','vgAll');all.textContent=L==='ar'?'كل المنتجات':'All products';pr.appendChild(all);ds.forEach(function(x){pr.appendChild(x)});pa.parentNode.replaceChild(pr,pa);if(VGPAGE==='shop')pr.open=true}catch(e){}})();
$('bg').onclick=function(){bgo(true)};$('drb').onclick=$('drx').onclick=function(){bgo(false)};
$('drl').onclick=function(e){if(e.target.closest('a'))bgo(false)};
if($('mnHome'))$('mnHome').onclick=function(){if(VGPAGE!=='home'){location.href='/';return}window.scrollTo({top:0,behavior:'smooth'})};
if($('mnSearch'))$('mnSearch').onclick=function(){window.scrollTo({top:0,behavior:'smooth'});setTimeout(function(){$('q').focus()},300)};
if($('mnCat'))$('mnCat').onclick=function(){bgo(true)};
if($('mnCart'))$('mnCart').onclick=function(){$('cart').click()};

document.addEventListener('keydown',function(e){if(e.key=='Escape')bgo(false)});
setLang(L);
hs();

/* ===== multi-page: page-specific rendering ===== */
function vgToast(s){var o=$('toast');o.textContent=s;o.classList.add('show');setTimeout(function(){o.classList.remove('show')},2600)}
function vgTiles(){var ar=L==='ar',el=$('hometiles');if(!el)return;
el.innerHTML='<a href="/shop"><b>'+(ar?'تسوق كل المنتجات':'Shop all products')+'</b><span>'+(ar?'كاميرات، مسجلات، هاردات وملحقات مع فلترة بالسعر والماركة':'Cameras, recorders, drives and accessories with price and brand filters')+'</span></a>'+
'<a href="/planner"><b>'+(ar?'مخطط التغطية':'Coverage planner')+'</b><span>'+(ar?'ارسم مكانك واعرف محتاج كام كاميرا وفين':'Sketch your place and see how many cameras you need')+'</span></a>'+
'<a href="/contact"><b>'+(ar?'استشارة مجانية':'Free consultation')+'</b><span>'+(ar?'كلمنا ونساعدك تختار النظام المناسب':'Talk to us and we will help you choose')+'</span></a>'}
function vgContactUI(){var ar=L==='ar',el=$('contact');if(!el)return;
el.innerHTML='<h2>'+(ar?'اتصل بنا':'Contact us')+'</h2><p class="vgsub">'+(ar?'مواعيد العمل: السبت إلى الخميس من 12 ظهرًا إلى 8 مساءً · الجمعة إجازة':'Working hours: Saturday to Thursday, 12:00 to 20:00 · Closed on Friday')+'</p><div class="vgcgrid"><div class="vgcl">'+
'<a href="tel:+201260087815"><span>'+(ar?'اتصال':'Call')+'<small dir="ltr">01260087815</small></span></a>'+
'<a href="'+WA+'" target="_blank" rel="noopener"><span>'+(ar?'واتساب':'WhatsApp')+'<small dir="ltr">01105006854</small></span></a>'+
'<div><span>'+(ar?'العنوان':'Address')+'<small>'+(ar?'حدائق أكتوبر، الجيزة':'October Gardens, Giza')+'</small></span></div>'+
'<a href="/order-status.php"><span>'+(ar?'تتبّع طلبك':'Track your order')+'<small>'+(ar?'برقم الطلب ورقم الموبايل':'With your order number and mobile')+'</small></span></a></div>'+
'<form class="vgform" onsubmit="return vgContactSend(this)"><input name="n" required maxlength="120" autocomplete="name" aria-label="'+(ar?'الاسم':'Name')+'" placeholder="'+(ar?'الاسم':'Name')+'"><input name="p" required type="tel" inputmode="tel" maxlength="20" autocomplete="tel" aria-label="'+(ar?'رقم الموبايل':'Mobile number')+'" placeholder="'+(ar?'رقم الموبايل':'Mobile number')+'"><textarea name="m" required maxlength="1500" aria-label="'+(ar?'رسالتك':'Your message')+'" placeholder="'+(ar?'اكتب رسالتك أو استفسارك':'Your message')+'"></textarea><input name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;opacity:0;width:1px;height:1px;overflow:hidden;pointer-events:none"><button class="btn pri" type="submit">'+(ar?'إرسال الرسالة':'Send message')+'</button></form></div>'}
function vgContactSend(f){var ar=L==='ar',b=f.querySelector('button');b.disabled=true;
sendLead({type:'contact',name:f.n.value,phone:f.p.value,message:f.m.value,website:f.website.value}).then(function(r){b.disabled=false;if(r&&r.ok){f.reset();vgToast(ar?'تم إرسال رسالتك، هنتواصل معاك قريب':'Message sent. We will contact you soon')}else{vgToast(r&&r.error==='too_many_requests'?(ar?'محاولات كتير، حاول بعد شوية':'Too many attempts, try later'):(ar?'حصلت مشكلة، حاول تاني أو كلمنا واتساب':'Something went wrong, please try again'))}});return false}
function vgAcct(){var ar=L==='ar',el=$('acctmount');if(!el)return;
if(!VGC){el.innerHTML='<h2>'+(ar?'حسابي':'My account')+'</h2><p class="vgsub">'+(ar?'سجّل دخولك أو أنشئ حساب عميل جديد علشان تطلب وتتابع طلباتك.':'Sign in or create a customer account to order and track your orders.')+'</p><button type="button" class="btn pri" onclick="vgAuth(vgAcct)">'+(ar?'تسجيل الدخول / حساب جديد':'Sign in / Register')+'</button>';return}
el.innerHTML='<h2>'+(ar?'أهلاً ':'Hello ')+xe(VGC.name)+'</h2><p class="vgsub"><span dir="ltr">'+xe(VGC.email||VGC.phone||'')+'</span></p><h3 style="margin:14px 0 10px">'+(ar?'طلباتي':'My orders')+'</h3><div id="vgorders"><p class="meta">'+(ar?'جاري التحميل...':'Loading...')+'</p></div><div class="ptools" style="margin-top:14px"><a class="btn pri" href="/shop">'+(ar?'متابعة التسوق':'Continue shopping')+'</a><button type="button" class="btn" onclick="vgApi({action:\'logout\'}).then(function(){location.reload()})">'+(ar?'تسجيل الخروج':'Sign out')+'</button></div>';
vgApi({action:'orders'}).then(function(j){var box=$('vgorders');if(!box)return;
if(!j||!j.ok){box.innerHTML='<p class="meta">'+(ar?'تعذر تحميل الطلبات، حدّث الصفحة.':'Could not load orders, refresh the page.')+'</p>';return}
if(!j.orders.length){box.innerHTML='<p class="meta">'+(ar?'لسه مفيش طلبات على حسابك.':'No orders on your account yet.')+'</p>';return}
var st={'new':ar?'جديد':'New',contacted:ar?'تم التواصل':'Contacted',closed:ar?'مكتمل':'Completed',cancelled:ar?'ملغي':'Cancelled'},pm={cod:ar?'الدفع عند الاستلام':'Cash on delivery',instapay:'InstaPay',ewallet:ar?'محفظة إلكترونية':'Mobile wallet'};
box.innerHTML=j.orders.map(function(o){return '<div class="vgord"><div class="vgoh"><span>'+(ar?'طلب #':'Order #')+o.id+'</span><span class="vgtag">'+(st[o.status]||xe(o.status))+'</span></div><div class="meta">'+xe(o.date)+(pm[o.payment_method]?' · '+pm[o.payment_method]:'')+'</div><ul>'+o.items.map(function(it){return '<li>'+xe(ar?(it.name||it.name_en):(it.name_en||it.name))+' × '+it.qty+'</li>'}).join('')+'</ul><div style="margin-top:8px;font-weight:800">'+T('wtot')+': '+N(o.total)+' '+T('egp')+'</div></div>'}).join('')
}).catch(function(){})}
function hpCard(p){var e=ep(p),d=e<p.p?Math.round((1-e/p.p)*100):0;return '<article class="card">'+(d?'<span class="badge">-'+d+'%</span>':'')+(!p.s?'<span class="oosb">'+T('oos')+'</span>':'')+'<a class="pic'+(p.s?'':' oos')+'" href="'+(p.sl?'/p/'+encodeURIComponent(p.sl):'product.php?id='+p.id.slice(1))+'">'+pimg(p)+'</a><div class="meta">'+[esc(BN(p.b)),T(p.c),p.pl?T(p.pl):''].filter(Boolean).join(' \u00b7 ')+'</div><h3><a href="'+(p.sl?'/p/'+encodeURIComponent(p.sl):'product.php?id='+p.id.slice(1))+'">'+esc(nmp(p))+'</a></h3><div class="price">'+prc(p)+'</div><div class="stock'+(p.s?'':' out')+'">'+(p.s?T('stock'):T('oos'))+'</div><button type="button"'+(p.s?'':' disabled')+' data-n="'+p.i+'">'+(p.s?T('add'):T('un'))+'</button></article>'}
function hpDraw(){var g=$('hpgrid');if(!g)return;var l=P.filter(function(p){var e=ep(p);return (!HPR.min||e>=HPR.min)&&(!HPR.max||e<=HPR.max)}).sort(function(a,b){return (b.s?1:0)-(a.s?1:0)||ep(a)-ep(b)}).slice(0,12);g.innerHTML=l.length?l.map(hpCard).join(''):'<p class="vgsub">'+(L==='ar'?'مفيش منتجات في النطاق ده':'No products in this range')+'</p>';var a=$('hpall');if(a){var q=[];if(HPR.min)q.push('pmin='+HPR.min);if(HPR.max)q.push('pmax='+HPR.max);a.href='/shop'+(q.length?'?'+q.join('&'):'')}}
function vgPrices(){var el=$('homeprices');if(!el)return;if(!window.HPR)window.HPR={min:0,max:0};var ar=L==='ar';var MX=25*Math.ceil(Math.max.apply(null,P.map(function(p){return ep(p)}))/25)||10000;var CH=[[0,1000],[1000,3000],[3000,6000],[6000,MX]];var lab=[ar?'أقل من 1,000':'Under 1,000','1,000 - 3,000','3,000 - 6,000',ar?'أكثر من 6,000':'Over 6,000'];el.innerHTML='<h2>'+(ar?'تسوق حسب السعر':'Shop by price')+'</h2><div class="hpbox"><div class="pf"><div class="pf-row"><div class="pf-f"><span>'+(ar?'من (ج.م)':'From (EGP)')+'</span><input type="text" id="hpmin" autocomplete="off" inputmode="numeric" placeholder="0"></div><span class="pf-dash" aria-hidden="true">\u2013</span><div class="pf-f"><span>'+(ar?'إلى (ج.م)':'To (EGP)')+'</span><input type="text" id="hpmax" autocomplete="off" inputmode="numeric" placeholder="'+MX+'"></div></div><div class="pf-slider"><div class="pf-track"><div class="pf-fill" id="hpfill"></div></div><input type="range" id="hpr1" min="0" max="'+MX+'" step="25" value="0" aria-label="min"><input type="range" id="hpr2" min="0" max="'+MX+'" step="25" value="'+MX+'" aria-label="max"></div><div class="pf-chips" id="hpchips">'+CH.map(function(c,i){return '<button type="button" data-lo="'+c[0]+'" data-hi="'+c[1]+'" aria-pressed="false">'+lab[i]+'</button>'}).join('')+'<button type="button" id="hpclr" class="pf-clr" hidden>'+(ar?'مسح السعر':'Clear price')+'</button></div></div></div><div class="bsrow hpgrid" id="hpgrid"></div><div style="text-align:center;margin:16px 0"><a class="hpall" id="hpall" href="/shop">'+(ar?'عرض كل المنتجات':'View all products')+'</a></div>';function setR(lo,hi,src){lo=Math.max(0,Math.min(lo||0,MX));hi=hi>0?Math.min(hi,MX):0;if(hi&&lo>hi){if(src==='lo')lo=hi;else hi=lo}HPR.min=lo;HPR.max=(hi&&hi<MX)?hi:0;if(src!=='in'){$('hpmin').value=lo?lo:'';$('hpmax').value=HPR.max?HPR.max:''}$('hpr1').value=lo;$('hpr2').value=hi||MX;$('hpfill').style.insetInlineStart=(lo/MX*100)+'%';$('hpfill').style.insetInlineEnd=(100-(hi||MX)/MX*100)+'%';$('hpclr').hidden=!(HPR.min||HPR.max);document.querySelectorAll('#hpchips button[data-lo]').forEach(function(b){b.setAttribute('aria-pressed',(+b.dataset.lo===HPR.min&&(+b.dataset.hi===HPR.max||(+b.dataset.hi===MX&&!HPR.max)))?'true':'false')});hpDraw()}$('hpmin').oninput=function(){setR(+this.value.replace(/\D/g,'')||0,HPR.max,'in')};$('hpmax').oninput=function(){setR(HPR.min,+this.value.replace(/\D/g,'')||0,'in')};$('hpr1').oninput=function(){setR(+this.value,+$('hpr2').value,'lo')};$('hpr2').oninput=function(){setR(+$('hpr1').value,+this.value,'hi')};$('hpchips').onclick=function(e){var b=e.target.closest('button');if(!b)return;if(b.id==='hpclr')setR(0,0);else setR(+b.dataset.lo,+b.dataset.hi)};$('hpgrid').addEventListener('click',function(e){var b=e.target.closest('button[data-n]');if(!b)return;var n=+b.dataset.n;if((CART[n]||0)>=avail(n)){tst('maxq');return}CART[n]=(CART[n]||0)+1;csave();cupd();var o=$('toast');o.textContent=T('added');o.classList.add('show');setTimeout(function(){o.classList.remove('show')},1400)});setR(HPR.min,HPR.max)}
function vgCatRow(){var el=$('catrow');if(!el)return;var ar=L==='ar',m={};P.forEach(function(p){if(p.c)m[p.c]=(m[p.c]||0)+1});var ks=Object.keys(m).sort(function(a,b){return m[b]-m[a]});el.innerHTML='<button type="button" class="cr" data-k="">'+(ar?'الكل':'All')+'</button>'+ks.map(function(c){return '<button type="button" class="cr" data-k="'+c+'">'+T(c)+' <small>'+PN(m[c])+'</small></button>'}).join('');vgCatAct()}
function vgCatAct(){var el=$('catrow');if(!el)return;var on=[].slice.call(document.querySelectorAll('input[name=c]:checked')).map(function(x){return x.value});el.querySelectorAll('.cr').forEach(function(b){var k=b.dataset.k;b.classList.toggle('on',k?(on.length===1&&on[0]===k):on.length===0)})}
(function(){var el=document.getElementById('catrow');if(!el)return;el.addEventListener('click',function(e){var b=e.target.closest('.cr');if(!b)return;var k=b.dataset.k;if(k)fc(k);else{var r=document.getElementById('resetf');if(r)r.click()}vgCatAct();el.scrollIntoView({behavior:'smooth',block:'start'})});var sh=document.getElementById('shop');if(sh){sh.addEventListener('change',function(){setTimeout(vgCatAct,0)});sh.addEventListener('click',function(){setTimeout(vgCatAct,0)})}})();
function vgA11y(){var ar=(typeof L==='undefined')||L==='ar';var M={'Menu':'القائمة','Search':'بحث','Toggle theme':'تبديل المظهر','Sort':'الترتيب','Property type':'نوع العقار','Size':'المساحة','min':'الحد الأدنى','max':'الحد الأقصى','x':'حذف','Email':'البريد الإلكتروني','Camera technology':'تقنية الكاميرا','Camera type':'نوع الكاميرا','Camera count':'عدد الكاميرات','Coverage plan':'مخطط التغطية','Close':'إغلاق','WhatsApp':'واتساب','Previous':'السابق','Next':'التالي'};var R={};for(var k in M)R[M[k]]=k;document.querySelectorAll('[aria-label]').forEach(function(e){var cur=e.getAttribute('aria-label');var o=e.getAttribute('data-al0')||(cur in R?R[cur]:cur);if(!(o in M))return;e.setAttribute('data-al0',o);e.setAttribute('aria-label',ar?M[o]:o)})}
function vgPageLang(){vgA11y();setTimeout(vgA11y,1200);vgTiles();vgPrices();vgCatRow();if(VGPAGE==='contact')vgContactUI();if(VGPAGE==='account')vgAcct()}
(function(){
if(VGPAGE==='cart'){$('cartmount').appendChild($('cdc'));CSTEP='cart';CERR=false;cartUI()}
if(VGPAGE==='account'&&window.VGME)VGME.then(vgAcct);
var sp=new URLSearchParams(location.search);
if(VGPAGE==='shop'&&(sp.get('pmin')||sp.get('pmax'))){setTimeout(function(){if($('pmin'))$('pmin').value=sp.get('pmin')||'';if($('pmaxi'))$('pmaxi').value=sp.get('pmax')||'';if($('fapply'))$('fapply').click()},300)}
if(VGPAGE==='shop'){var c=sp.get('c'),b=sp.get('b'),qq=sp.get('q');
if(qq)$('q').value=qq;
if(sp.get('offers'))fo();else if(c&&b)fcb(c,b);else if(c)fc(c);else if(b){document.querySelectorAll('input[name=b]').forEach(function(x){x.checked=x.value==b});render()}else if(qq)render();
if(c)setTimeout(function(){var qc=$('qcat');if(qc)qc.value=c},500)}
var sb=document.querySelector('header .search button[data-t="search"]');
if(sb)sb.addEventListener('click',function(){if(VGPAGE!=='shop')location.href='/shop?q='+encodeURIComponent($('q').value.trim())});
})();

/* ===== presentation: scroll header shadow + reveal-on-scroll + product image zoom ===== */
(function(){
 var hd=document.querySelector('header');
 function onScroll(){hd.classList.toggle('scrolled',window.scrollY>6)}
 onScroll();window.addEventListener('scroll',onScroll,{passive:true});

 var io=('IntersectionObserver' in window)?new IntersectionObserver(function(es){
  es.forEach(function(en){if(en.isIntersecting){en.target.classList.add('in');io.unobserve(en.target)}})
 },{threshold:.12,rootMargin:'0px 0px -40px 0px'}):null;
 function arm(){document.querySelectorAll('.reveal:not(.in)').forEach(function(el){
  if(io)io.observe(el);else el.classList.add('in')
 })}
 arm();
 var mo=new MutationObserver(arm);mo.observe(document.body,{childList:true,subtree:true});

 document.addEventListener('mousemove',function(e){
  var img=e.target.closest('.pdt .pic img');
  if(!img)return;
  var r=img.parentElement.getBoundingClientRect();
  var ox=((e.clientX-r.left)/r.width*100).toFixed(1),oy=((e.clientY-r.top)/r.height*100).toFixed(1);
  img.style.transformOrigin=ox+'% '+oy+'%';img.style.transform='scale(1.9)'
 });
 document.addEventListener('mouseleave',function(e){
  var img=e.target.closest&&e.target.closest('.pdt .pic img');
  if(img){img.style.transform='scale(1)'}
 },true);
})();
function sendLead(payload){try{payload.source=location.href;try{payload.ft=JSON.parse(localStorage.getItem('vgft')||'null')}catch(e){}return fetch('api/lead.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)}).then(function(r){return r.json()}).catch(function(){return {ok:false}});}catch(e){return Promise.resolve({ok:false})}}
function nlSubmit(f){var em=f.querySelector('#nlEmail').value;if(em){sendLead({type:'newsletter',email:em});tst('subok');f.reset()}return false}
document.addEventListener('click',function(e){
  if(e.target.closest('#pcart')){
    var pn=document.getElementById('pn'),pp=document.getElementById('pp');
    sendLead({type:'coverage_plan',message:(pn?('Cameras: '+pn.textContent+' '):'')+(pp?('Coverage: '+pp.textContent):'')});
  }
});

document.addEventListener('click', function(e){
  var a = e.target.closest('a[href^="#"]');
  if(!a) return;
  var id = a.getAttribute('href').slice(1);
  if(!id){ e.preventDefault(); return; }
  var vgMap={shop:'/shop',planner:'/planner',about:'/about',faq:'/faq',contact:'/contact'};
  if(vgMap[id]&&VGPAGE!==id){ e.preventDefault(); location.href=vgMap[id]; return; }
  var t = document.getElementById(id);
  if(!t) return;
  e.preventDefault();
  t.scrollIntoView({behavior:'smooth'});
});
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
