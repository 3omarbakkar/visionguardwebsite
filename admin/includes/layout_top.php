<?php
/** Expects $page_title to be set before include. */
require_once __DIR__ . '/perms.php';
$admin = current_admin();
$flash = flash_get();
?><!doctype html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8">

<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($page_title ?? 'لوحة التحكم') ?> · Vision Guard</title>
<meta name="robots" content="noindex,nofollow">
<style id="vg-admin-base">
/* block 1 */
:root{--brand:#0a1f44;--accent:#1d4ed8;--bg:#f4f7fb;--surface:#fff;--text:#0f1b2d;--muted:#5a6678;--border:#dde5ef;--danger:#dc2626;--ok:#16a34a}
*{box-sizing:border-box}
body{margin:0;font:15px/1.5 -apple-system,Segoe UI,Tahoma,Arial,sans-serif;background:var(--bg);color:var(--text)}
a{color:inherit}
.wrap{display:flex;min-height:100vh}
nav.side{width:220px;flex:0 0 220px;background:var(--brand);color:#fff;padding:18px 14px;position:sticky;top:0;height:100vh}
nav.side h1{font-size:17px;margin:0 0 20px;padding:0 6px}
nav.side a{display:block;padding:10px 12px;border-radius:8px;color:#dbe4f5;text-decoration:none;margin-bottom:4px;font-size:14px}
nav.side a:hover,nav.side a.active{background:rgba(255,255,255,.12);color:#fff}
nav.side .sep{border-top:1px solid rgba(255,255,255,.15);margin:14px 0}
main{flex:1;padding:24px;max-width:1100px}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:10px;flex-wrap:wrap}
.topbar h2{margin:0;font-size:20px}
.card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:18px;margin-bottom:16px}
.btn{display:inline-block;background:var(--accent);color:#fff;border:0;padding:9px 16px;border-radius:8px;font-weight:600;cursor:pointer;text-decoration:none;font-size:14px}
.btn:disabled{opacity:.45;cursor:not-allowed}
.btn.secondary{background:var(--surface);color:var(--text);border:1px solid var(--border)}
.btn.danger{background:var(--danger)}
.btn.sm{padding:5px 10px;font-size:12.5px}
table{width:100%;border-collapse:collapse;font-size:14px}
th,td{padding:9px 8px;border-bottom:1px solid var(--border);text-align:right;vertical-align:middle}
th{color:var(--muted);font-weight:600;font-size:13px}
input[type=text],input[type=number],input[type=password],input[type=email],input[type=tel],select,textarea{width:100%;padding:9px 10px;border:1px solid var(--border);border-radius:8px;font:inherit;background:#fff;color:var(--text)}
label{display:block;font-size:13px;color:var(--muted);margin:10px 0 4px}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:0 16px}
.flash{padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:14px}
.flash.ok{background:#dcfce7;color:#166534}
.flash.err{background:#fee2e2;color:#991b1b}
.badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:12px;font-weight:600}
.badge.in{background:#dcfce7;color:#166534}
.badge.out{background:#fee2e2;color:#991b1b}
.thumb{width:40px;height:40px;border-radius:6px;object-fit:cover;background:#eef2f7;border:1px solid var(--border)}
.muted{color:var(--muted);font-size:13px}
@media (max-width:760px){.wrap{flex-direction:column}nav.side{width:100%;height:auto;position:static}main{padding:14px}.grid2{grid-template-columns:1fr}}


/* block 2 */
:root{--accent:#2780b8;--bg:#eef1f5;--border:#e2e8ef;--text:#2b3440;--muted:#6b7785}
body{background:var(--bg)}
.wrap{align-items:flex-start}
nav.side{width:250px;flex:0 0 250px;background:#fff;color:var(--text);padding:0 0 20px;border-left:1px solid var(--border);box-shadow:0 0 16px rgba(15,30,60,.06);overflow-y:auto;z-index:40}
nav.side h1{margin:0;padding:22px 16px;text-align:center;font-size:21px;color:#1f3154;border-bottom:1px solid var(--border)}
nav.side details{border-bottom:1px solid #f0f2f5}
nav.side summary{list-style:none;display:flex;align-items:center;gap:10px;padding:13px 16px;cursor:pointer;font-weight:700;font-size:14px;color:#3a4654}
nav.side summary::-webkit-details-marker{display:none}
nav.side summary .chev{margin-right:auto;width:16px;height:16px;flex-basis:16px;transition:transform .2s;color:#8a96a5}
nav.side details[open] summary .chev{transform:rotate(180deg)}
nav.side a{display:flex;align-items:center;gap:10px;margin:0;padding:11px 16px;border-radius:0;color:#4a5665;font-size:13.5px;text-decoration:none}
nav.side details a{padding-right:44px;background:#fafbfc}
nav.side a:hover{background:#f1f6fb;color:var(--accent)}
nav.side a.active{background:#e6f2fa;color:var(--accent);font-weight:700;box-shadow:inset -3px 0 0 var(--accent)}
nav.side .sep{border-top:1px solid var(--border);margin:8px 0}
.ic{width:20px;height:20px;flex:0 0 20px}
main{padding:0 0 32px;max-width:none;min-width:0}
main>:not(.vg-head){margin-left:24px;margin-right:24px}
main>.vg-head+*{margin-top:20px}
.vg-head{position:sticky;top:0;z-index:30;display:flex;align-items:center;justify-content:space-between;gap:12px;background:var(--accent);color:#fff;height:56px;padding:0 20px;box-shadow:0 2px 8px rgba(0,0,0,.12)}
.vg-head-r,.vg-head-l{display:flex;align-items:center;gap:12px}
.vg-title{font-size:16px}
.vg-hb{display:inline-flex;align-items:center;gap:6px;color:#fff;text-decoration:none;font-size:13.5px;padding:6px 12px;border-radius:6px;background:rgba(255,255,255,.15)}
.vg-hb:hover{background:rgba(255,255,255,.25)}
.vg-hb .ic{width:16px;height:16px;flex-basis:16px}
.vg-user{font-size:13.5px;background:rgba(255,255,255,.18);padding:5px 12px;border-radius:99px}
.vg-burger{display:none;background:transparent;border:0;color:#fff;padding:4px;cursor:pointer}
.vg-scrim{display:none}
.topbar{background:#fff;border-radius:6px;padding:14px 18px;box-shadow:0 1px 3px rgba(15,30,60,.08)}
.card,.kpi{border-radius:6px !important;border-color:transparent !important;box-shadow:0 1px 4px rgba(15,30,60,.08)}
.card h3{font-size:16px}
th{background:#f6f8fb;font-size:12.5px}
@media(max-width:860px){
  nav.side{position:fixed;top:0;right:0;bottom:0;height:100vh;transform:translateX(100%);transition:transform .25s}
  body.nav-open nav.side{transform:none;box-shadow:0 0 30px rgba(0,0,0,.25)}
  body.nav-open .vg-scrim{display:block;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:35}
  .vg-burger{display:inline-flex}
  .vg-user{display:none}
  main>:not(.vg-head){margin-left:12px;margin-right:12px}
}

</style>
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
<style id="vg-admin-theme">
/* vg-site-bg */
:root{--bg:#00008b;--accent:#0667a5;--cyan:#05b3e9}
body{background:#00008b linear-gradient(180deg,#00008b 0%,#0a0aa6 100%) fixed;font-family:Cairo,"Segoe UI",Tahoma,Arial,sans-serif}
input,select,textarea,button{font-family:inherit}
.vg-head{background:#0b0ba6;border-bottom:1px solid #3d3dd6;box-shadow:0 2px 10px rgba(0,0,60,.4)}
.vg-head .vg-hb{background:rgba(5,179,233,.18)}
.vg-head .vg-hb:hover{background:rgba(5,179,233,.35)}
.vg-user{background:rgba(5,179,233,.22)}
nav.side{border-left:1px solid #3d3dd6}
nav.side a.active{background:#e3f4fc;color:#0667a5;box-shadow:inset -3px 0 0 var(--cyan)}
.vg-tabs a.on{background:var(--cyan);border-color:var(--cyan);color:#00115a}
.btn{background:#0667a5}
.btn.secondary{background:#fff;color:#1f3154}
.btn.danger{background:#dc2626}
main>p,main>h1,main>h2{color:#fff}
.flash{border-radius:8px}


/* vg-polish */
html{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;text-rendering:optimizeLegibility}
h1,h2,h3,h4{text-wrap:balance}
p{text-wrap:pretty}
td,th,.kpi b,.tot,.vg-tabs b{font-variant-numeric:tabular-nums}
/* layered shadows instead of borders for depth */
.card,.kpi,.topbar,.oc{border:0 !important;box-shadow:0 0 0 1px rgba(15,30,60,.05),0 1px 2px rgba(15,30,60,.06),0 6px 16px -6px rgba(15,30,60,.12) !important}
/* concentric radii: card 14 = inner 8 + padding 6 */
.card,.kpi,.topbar,.oc{border-radius:14px !important}
.vg-box,.vg-tabs a,table,input,select,textarea,.btn{border-radius:10px}
.vg-box{border-radius:10px}
.vg-head{height:60px}
.vg-title{font-size:17px;font-weight:800;letter-spacing:0}
/* sidebar polish */
nav.side h1{font-weight:800;letter-spacing:.2px;background:linear-gradient(180deg,#fff,#f4f8fc)}
nav.side a,nav.side summary{transition:background-color .15s ease-out,color .15s ease-out}
nav.side a{min-height:42px}
nav.side summary{min-height:46px}
nav.side summary:hover{background:#f6f9fc}
nav.side .ic{opacity:.85}
nav.side a.active .ic{opacity:1}
nav.side details[open]>summary{color:#1f3154}
/* controls */
.btn,button{transition:background-color .15s ease-out,box-shadow .15s ease-out,scale .12s ease-out,opacity .15s ease-out;min-height:36px}
.btn:hover{box-shadow:0 4px 12px -4px rgba(6,103,165,.55)}
.btn:active,.vg-hb:active{scale:.96}
.btn.sm{min-height:32px}
input[type=text],input[type=number],input[type=email],input[type=tel],input[type=password],input[type=search],select,textarea{min-height:38px;border:1px solid #d5dde8;background:#fff;transition:border-color .15s ease-out,box-shadow .15s ease-out}
input:focus-visible,select:focus-visible,textarea:focus-visible{outline:0;border-color:#05b3e9;box-shadow:0 0 0 3px rgba(5,179,233,.25)}
a:focus-visible,button:focus-visible,summary:focus-visible{outline:2px solid #05b3e9;outline-offset:2px}
/* tables */
table{border-collapse:separate;border-spacing:0}
th{position:sticky;top:0;text-align:right;color:#51607a;font-weight:700;border-bottom:1px solid #e2e8ef}
tbody tr{transition:background-color .12s ease-out}
tbody tr:hover{background:#f5f9fd}
td{border-bottom:1px solid #eef1f5}
tr:last-child td{border-bottom:0}
/* kpi tiles */
.kpi{position:relative;overflow:hidden;transition:transform .15s ease-out,box-shadow .15s ease-out}
a.kpi:hover{transform:translateY(-2px)}
.kpi::before{content:"";position:absolute;inset-inline:0;top:0;height:3px;background:linear-gradient(90deg,#05b3e9,#0667a5)}
.kpi b{font-size:26px;font-weight:800}
/* order cards */
.oc{transition:box-shadow .15s ease-out}
.oc:hover{box-shadow:0 0 0 1px rgba(5,179,233,.35),0 10px 24px -10px rgba(15,30,60,.25) !important}
.vg-tabs a{transition:background-color .15s ease-out,color .15s ease-out}
.vg-tabs a:not(.on):hover{background:#eaf6fd}
/* flash messages */
.flash{border-radius:10px;font-weight:600;box-shadow:0 1px 3px rgba(0,0,0,.12)}
/* thin scrollbars */
nav.side{scrollbar-width:thin}
.tw,.vg-tabs{scrollbar-width:thin}
@media (prefers-reduced-motion:reduce){*{transition:none !important}}
@media(max-width:860px){.btn,button,select,input{min-height:42px}.vg-head{height:56px}}


/* vg-polish2 */
/* status stripe on order cards (was removed by the border reset) */
.oc{--st:#94a3b8;border-right:5px solid var(--st) !important}
.oc.s-new{--st:#2563eb}.oc.s-contacted{--st:#d97706}.oc.s-delivered{--st:#16a34a}.oc.s-closed{--st:#6366f1}.oc.s-cancelled{--st:#dc2626}
/* hit areas: at least 40px on desktop controls */
.btn.sm,.vg-hb{min-height:40px;display:inline-flex;align-items:center;justify-content:center}
/* icon stroke matches text weight */
nav.side a .ic{stroke-width:1.5}
nav.side summary .ic{stroke-width:2}
/* RTL: arrows that imply direction must flip */
a[href="logout.php"] .ic,.vg-hb .ic,nav.side a[target="_blank"] .ic{transform:scaleX(-1)}
/* sticky table headers sit below the sticky top bar */
th{top:60px}
.tw th{top:0}
/* subtle image outline (pure black, light surface) */
main img{outline:1px solid oklch(0 0 0 / .1);outline-offset:-1px}


/* vg-polish3 */
/* dark blue background */
:root{--bg:#071433}
body{background:#071433 linear-gradient(180deg,#071433 0%,#0b1f4d 100%) fixed}
.vg-head{background:#0c1d4f;border-bottom:1px solid #1f3a7a;box-shadow:0 2px 10px rgba(0,10,40,.5)}
nav.side{border-left:1px solid #1f3a7a}
/* main must never grow wider than the screen */
main{width:100%;min-width:0;max-width:100%;box-sizing:border-box}
@media(max-width:860px){
  .card{overflow-x:auto}
  .card table{min-width:560px}
  .topbar{flex-wrap:wrap}
  .sbar input{min-width:0;flex:1}
}


/* vg-polish4 */
.vg-title,.vg-hb span{white-space:nowrap}
.vg-title{overflow:hidden;text-overflow:ellipsis;max-width:46vw}
@media(max-width:860px){
  .vg-hb span{display:none}
  .vg-hb{padding:0;width:40px}
  th{top:0}
  .vg-head{padding:0 12px}
}


/* vg-polish5 */
@media(max-width:860px){
  main{overflow-x:clip}
  .grid2,.two,.oc-b{grid-template-columns:minmax(0,1fr) !important}
  .kpis{grid-template-columns:repeat(2,minmax(0,1fr)) !important}
  .kpi b{font-size:21px}
  .kv{grid-template-columns:100px minmax(0,1fr)}
  .topbar form{width:100%}
  .topbar form input[type=text],.topbar form input[type=search]{min-width:0;flex:1}
  .qtyform input[type=number]{width:72px}
  .bars{gap:6px}
}


/* vg-polish6 */
@media(max-width:860px){
  .topbar form{display:flex;flex-wrap:nowrap;gap:8px}
  .topbar form input[type=text],.topbar form input[type=search]{min-width:0 !important;flex:1 1 0;width:auto}
  .topbar form .btn{flex:0 0 auto}
}


/* vg-polish7 */
.vg-nav a .ic,.vg-nav a svg{width:18px;height:18px;stroke-width:1.75}
.vg-nav a svg{flex:0 0 18px;opacity:.85}
.vg-nav a:hover svg,.vg-nav a.active svg{opacity:1}
.vg-nav a.active svg{color:var(--accent)}
.card{transition:box-shadow .18s ease,transform .18s ease}
a.card:hover{box-shadow:0 10px 24px -10px rgba(6,103,165,.35),0 1px 2px rgba(15,27,45,.08);transform:translateY(-1px)}
a.card:focus-visible,.btn:focus-visible{outline:2px solid var(--cyan);outline-offset:2px}
@media (prefers-reduced-motion:reduce){.card{transition:none}a.card:hover{transform:none}}


/* vg-polish8 */
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:18px}
.kpis .kpi{position:relative;overflow:hidden;border-radius:16px;margin:0;box-shadow:0 1px 2px rgba(15,27,45,.06),0 8px 20px -10px rgba(0,0,0,.35)}
.kpis .kpi::after{content:"";position:absolute;inset-inline:0;bottom:0;height:3px;background:var(--tone,#05b3e9)}
.kpis .kpi.on{--tone:#b91c1c}
.kpis .kpi{border:0 !important;border-top:0 !important;padding:16px 18px 19px}
.kpis .kpi::before{display:none !important}
.card table th,.vg-box table th{background:#f3f7fb;color:#51607a;font-size:12.5px;font-weight:700;border-bottom:1px solid #e3e9f1}
.card table tbody tr{transition:background .15s}
.card table tbody tr:hover{background:#f6fafe}
.card table td{border-bottom:1px solid #eef1f5}
.card table tr:last-child td{border-bottom:0}
.oc{border-radius:16px}
.oc:hover{box-shadow:0 10px 24px -10px rgba(6,103,165,.35)}


/* vg-polish9 */
.kpis:not(:has(.hero)){grid-template-columns:repeat(3,minmax(0,1fr)) !important}
@media(min-width:1280px){.kpis:not(:has(.hero)){grid-template-columns:repeat(6,minmax(0,1fr)) !important}}
@media(max-width:520px){.kpis:not(:has(.hero)){grid-template-columns:repeat(2,minmax(0,1fr)) !important}}
@media(max-width:480px){.kpis:has(.hero){grid-template-columns:1fr !important}.kpis .kpi.hero{grid-column:auto !important}}


/* vg-polish10 */
.oc .vg-h{display:flex;align-items:center;gap:8px}
.oc .vg-h::before{content:"";width:18px;height:18px;flex:0 0 18px;background:var(--accent,#0667a5);-webkit-mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='1.9' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 3l8 4.5v9L12 21l-8-4.5v-9zM12 12l8-4.5M12 12v9M12 12L4 7.5'/%3E%3C/svg%3E") center/contain no-repeat;mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='1.9' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 3l8 4.5v9L12 21l-8-4.5v-9zM12 12l8-4.5M12 12v9M12 12L4 7.5'/%3E%3C/svg%3E") center/contain no-repeat}
.oc .vg-box:nth-of-type(2) .vg-h::before,.oc .vg-box + .vg-box .vg-h::before{-webkit-mask-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='1.9' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 21s7-6.2 7-11a7 7 0 0 0-14 0c0 4.8 7 11 7 11zM12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5'/%3E%3C/svg%3E");mask-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='1.9' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 21s7-6.2 7-11a7 7 0 0 0-14 0c0 4.8 7 11 7 11zM12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5'/%3E%3C/svg%3E")}

</style>
<script id="vg-nav-collapse">document.addEventListener('DOMContentLoaded',function(){var a=document.querySelector('nav.side a.active');if(a){var d=a.closest('details');if(d)d.open=true;}});</script>
<style id="vg-admin-final">
/* vg-polish11 */
body{background:#f3f6fb !important;color:#0f1b2d}
body nav.side{background:linear-gradient(180deg,#0a78bf 0%,#0667a5 38%,#0b3f87 78%,#0b2f6b 100%) !important;color:#fff !important;border:0 !important;box-shadow:0 0 40px -12px rgba(11,47,107,.5)}
body nav.side h1{display:flex !important;flex-direction:column;align-items:center;gap:10px;margin:0 0 6px !important;padding:26px 16px 20px !important;color:#fff !important;font-size:20px !important;text-align:center;background:none !important;border-bottom:1px solid rgba(255,255,255,.16) !important}
body nav.side h1::before{content:"";width:62px;height:62px;border-radius:50%;background:rgba(255,255,255,.16) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6zM9 12l2 2 4-4'/%3E%3C/svg%3E") center/32px no-repeat;box-shadow:inset 0 0 0 1px rgba(255,255,255,.2)}
body nav.side h1::after{content:"نظام إدارة الطلبات والمخزون";font-size:12px;font-weight:500;color:rgba(255,255,255,.72)}
body nav.side h1{position:relative}
body nav.side h1 span.vgmark{display:none}
body nav.side a,body nav.side summary{color:rgba(255,255,255,.9) !important;border-radius:12px !important;margin:2px 12px !important;border:0 !important;background:transparent}
body nav.side summary{font-weight:700}
body nav.side a:hover,body nav.side summary:hover{background:rgba(255,255,255,.1) !important;color:#fff !important}
body nav.side a.active{background:rgba(255,255,255,.2) !important;color:#fff !important;font-weight:700;box-shadow:inset 0 0 0 1px rgba(255,255,255,.18)}
body nav.side svg,body nav.side .ic{color:#fff !important;stroke:currentColor;opacity:.95}
body nav.side details,body nav.side details>summary{border:0 !important;border-top:0 !important;border-bottom:0 !important;box-shadow:none !important}
body nav.side details[open]{background:rgba(255,255,255,.05) !important;border-radius:14px;margin:2px 8px}
body nav.side .sep{background:rgba(255,255,255,.16) !important;opacity:1}
body nav.side details a{padding-inline-start:38px}
main{color:#0f1b2d}
.vg-head{background:transparent !important;box-shadow:none !important;border:0 !important;color:#0f1b2d !important}
.vg-head h1,.vg-head h2,.vg-head .t,.vg-head strong{color:#0f1b2d !important}
.vg-head .vg-hb,.vg-head a,.vg-head .vg-user{background:#fff !important;color:#0667a5 !important;border:1px solid #e1e8f1 !important;box-shadow:0 1px 2px rgba(15,27,45,.05)}
.vg-head .vg-hb svg{color:#0667a5}
.card,.vg-box,.oc{border:1px solid #e8eef5;box-shadow:0 1px 2px rgba(15,27,45,.04),0 10px 26px -14px rgba(15,27,45,.14) !important}
.vg-tabs a,.vg-tabs button{border:1px solid #e1e8f1 !important;background:#fff}
.vg-tabs a.on,.vg-tabs a.active{background:#0667a5 !important;border-color:#0667a5 !important;color:#fff !important}
.kpi-top{flex-direction:column-reverse !important;align-items:flex-start !important;gap:12px !important}
.kchip{width:46px !important;height:46px !important;border-radius:14px !important;flex-basis:46px !important}
.kpi.hero .kpi-top{flex-direction:row !important;align-items:center !important}
.quick{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:6px 0 18px}
.quick h3{grid-column:1/-1;margin:6px 0 0;font-size:17px}
.quick a{display:flex;flex-direction:column;align-items:center;gap:12px;padding:20px 12px;text-decoration:none;color:#0f1b2d;font-weight:700;font-size:14.5px}
.quick a .kchip{margin:0}
.quick a:hover{box-shadow:0 12px 26px -12px rgba(6,103,165,.4) !important;transform:translateY(-2px)}
@media(max-width:800px){.quick{grid-template-columns:repeat(2,minmax(0,1fr))}}


/* vg-logo */body nav.side h1{font-size:0 !important;gap:12px !important;padding:24px 16px 18px !important}body nav.side h1::before{width:92px !important;height:92px !important;border-radius:26px !important;background:url(/images/vg-logo-footer.png) center/68px auto no-repeat,linear-gradient(145deg,rgba(255,255,255,.22),rgba(255,255,255,.08)) !important;box-shadow:inset 0 0 0 1px rgba(255,255,255,.22),0 10px 24px -10px rgba(0,0,0,.45) !important}body nav.side h1::after{font-size:12px !important}

/* vg-logo-band */body nav.side h1{background:#fff !important;margin:0 0 10px !important;padding:16px 16px 12px !important;gap:6px !important;border-bottom:0 !important;box-shadow:0 8px 20px -12px rgba(0,0,0,.4)}body nav.side h1::before{width:128px !important;height:62px !important;border-radius:0 !important;background:url(/images/vg-logo-footer.png) center/contain no-repeat !important;box-shadow:none !important;filter:brightness(0) saturate(100%) invert(22%) sepia(96%) saturate(2100%) hue-rotate(190deg) brightness(93%) contrast(98%)}body nav.side h1::after{color:#5a6b85 !important;font-size:11.5px !important}

/* vg-burger-fix */.vg-head button{color:#0f1b2d !important;background:#fff !important;border:1px solid #e1e8f1 !important;border-radius:10px}.vg-head button svg{color:#0f1b2d !important}
</style>
</head>
<body>
<div class="wrap">
<?php if ($admin): $role = $admin['role'] ?? 'admin'; ?>
<?php
$vgRole = is_array($admin) ? ($admin['role'] ?? 'admin') : '';
$vgActive = $active ?? '';
$vgIcons = [
  'tag' => 'M3 12V4h8l10 10-8 8zM7.5 8v.01',
  'receipt' => 'M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6M9 12h6',
  'idcard' => 'M3 6h18v12H3zM8 12h.01M6.5 15.5c.5-1.5 3.5-1.5 4 0M14 10h4M14 14h3',
  'stack' => 'M12 3l9 4.5-9 4.5-9-4.5zM3 12l9 4.5 9-4.5M3 16.5L12 21l9-4.5',
  'pie' => 'M12 3v9h9a9 9 0 1 1-9-9zM15 3.5A9 9 0 0 1 20.5 9H15z',
  'trend' => 'M3 17l6-6 4 4 8-8M15 7h6v6',
  'lock' => 'M6 11h12v9H6zM8.5 11V8a3.5 3.5 0 0 1 7 0v3',
 'home'=>'M3 10.5 12 3l9 7.5V21h-6v-6H9v6H3z',
 'cart'=>'M3 4h2l2.4 11h11L21 7H6M9 20h.01M17 20h.01',
 'clip'=>'M9 4h6v3H9zM7 5H5v16h14V5h-2M8 12h8M8 16h5',
 'users'=>'M16 20v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6M20 20v-2a3 3 0 0 0-2-2.8M16 4.2a3 3 0 0 1 0 5.6',
 'box'=>'M21 8 12 3 3 8v8l9 5 9-5zM3 8l9 5 9-5M12 13v8',
 'wh'=>'M3 9l9-6 9 6v12H3zM8 21v-7h8v7',
 'chart'=>'M4 20V10M10 20V4M16 20v-8M22 20H2',
 'globe'=>'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18',
 'img'=>'M3 5h18v14H3zM3 16l5-5 4 4 3-3 6 6',
 'bell'=>'M6 8a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8M10 21h4',
 'gear'=>'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2',
 'user'=>'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M4 21a8 8 0 0 1 16 0',
 'out'=>'M9 21H5V3h4M16 17l5-5-5-5M21 12H9',
 'ext'=>'M14 4h6v6M20 4 10 14M18 14v6H4V6h6',
 'clock'=>'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18M12 7v5l3 2',
 'down'=>'M6 9l6 6 6-6',
 'menu'=>'M4 6h16M4 12h16M4 18h16'
];
$vgIc = function ($k, $cls = 'ic') use ($vgIcons) {
    return '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $vgIcons[$k] . '"/></svg>';
};
$vgLink = function ($href, $keys, $label, $icon) use ($vgIc, $vgActive) {
    $on = in_array($vgActive, (array)$keys, true);
    return '<a href="' . $href . '" class="' . ($on ? 'active' : '') . '">' . $vgIc($icon) . '<span>' . htmlspecialchars($label) . '</span></a>';
};
$vgGroup = function ($title, $icon, $links) use ($vgIc) {
    $links = array_filter($links);
    if (!$links) return '';
    return '<details><summary>' . $vgIc($icon) . '<span>' . htmlspecialchars($title) . '</span>' . $vgIc('down', 'ic chev') . '</summary>' . implode('', $links) . '</details>';
};
$vgIsAdmin = ($vgRole === 'admin' || (function_exists('current_role') && current_role() === 'admin'));
if ($admin): ?>
<div class="vg-scrim" onclick="document.body.classList.remove('nav-open')"></div>
<nav class="side">
  <h1>Vision Guard</h1>
  <?= can('dashboard') ? $vgLink('index.php', 'dashboard', 'الرئيسية', 'home') : '' ?>
  <?= $vgGroup('المبيعات', 'cart', [
      can('leads') ? $vgLink('leads.php', 'leads', 'الطلبات والرسائل', 'receipt') : '',
      can('customers') ? $vgLink('customers.php', 'customers', 'العملاء', 'idcard') : '',
      can('products') ? $vgLink('discounts.php', 'discounts', 'الخصومات', 'tag') : '',
  ]) ?>
  <?= $vgGroup('المنتجات والمخزون', 'box', [
      can('products') ? $vgLink('products.php', 'products', 'المنتجات', 'box') : '',
      can('products') ? $vgLink('inventory.php', 'inventory', 'المخزون', 'stack') : '',
  ]) ?>
  <?= $vgGroup('التقارير', 'chart', [
      can('dashboard') ? $vgLink('dashboard.php', 'reports', 'لوحة التقارير', 'pie') : '',
      can('insights') ? $vgLink('insights.php', 'insights', 'الإحصائيات', 'trend') : '',
  ]) ?>
  <?= $vgGroup('الموقع', 'globe', [
      can('settings') ? $vgLink('settings.php', 'settings', 'إعدادات الصفحة الرئيسية', 'gear') : '',
      can('hero') ? $vgLink('hero.php', 'hero', 'الهيرو (البانرات)', 'img') : '',
      $vgIsAdmin ? $vgLink('notify.php', 'notify', 'إشعارات العروض', 'bell') : '',
  ]) ?>
  <?= $vgGroup('الفريق', 'users', [
      can('staff_manage') ? $vgLink('staff.php', ['staff', 'attendance'], 'الموظفين', 'users') : (can('attendance_self') ? $vgLink('attendance.php', 'attendance', 'الحضور والانصراف', 'clock') : ''),
      (can('staff_orders') && $vgRole !== 'admin') ? $vgLink('staff_orders.php', 'staff_orders', 'متابعة الطلبات', 'receipt') : '',
  ]) ?>
  <div class="sep"></div>
  <?= $vgLink('account.php', 'account', 'تغيير كلمة المرور', 'lock') ?>
  <?php if ($vgRole === 'admin'): ?><a href="<?= htmlspecialchars(SITE_URL) ?>/" target="_blank"><?= $vgIc('ext') ?><span>فتح الموقع</span></a><?php endif; ?>
  <a href="logout.php"><?= $vgIc('out') ?><span>تسجيل الخروج</span></a>
</nav>
<?php endif; ?>
<?php endif; ?>
<main>
<div class="vg-head">
  <div class="vg-head-r">
    <button class="vg-burger" type="button" aria-label="القائمة" onclick="document.body.classList.toggle('nav-open')"><?= $admin ? $vgIc('menu') : '' ?></button>
    <b class="vg-title"><?= htmlspecialchars($page_title ?? 'لوحة التحكم') ?></b>
  </div>
  <div class="vg-head-l">
    <?php if ($admin && $vgRole === 'admin'): ?><a class="vg-hb" href="<?= htmlspecialchars(SITE_URL) ?>/" target="_blank"><?= $vgIc('ext') ?><span>الذهاب إلى الموقع</span></a><?php endif; ?>
    <?php if ($admin): ?><span class="vg-user"><?= htmlspecialchars((string)(($admin['full_name'] ?? '') ?: ($admin['username'] ?? ''))) ?></span><?php endif; ?>
  </div>
</div>
<?php if ($flash): ?><div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>
