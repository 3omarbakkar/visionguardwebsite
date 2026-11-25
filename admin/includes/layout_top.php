<?php
/** Expects $page_title to be set before include. */
require_once __DIR__ . '/perms.php';
$admin = current_admin();
$flash = flash_get();
?><!doctype html>
<html lang="ar" dir="rtl"<?= !empty($vgForceLight) ? ' data-theme="light"' : '' ?>>
<head><meta charset="utf-8">

<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($page_title ?? 'لوحة التحكم') ?> · Vision Guard</title>
<meta name="robots" content="noindex,nofollow">
<script>(function(){var f=document.documentElement;if(f.hasAttribute("data-theme"))return;try{f.setAttribute("data-theme",localStorage.getItem("vg-theme")==="light"?"light":"dark")}catch(e){f.setAttribute("data-theme","dark")}})();</script>
<style id="vg-tokens">:root{--vg-focus:#1d4ed8}</style>
<style id="vg-head-mobile">
@media (max-width:760px){
  .vg-head{position:fixed !important;top:0 !important;left:0 !important;right:0 !important;width:auto !important;margin:0 !important;z-index:60 !important}
}
</style>
<style id="vg-head-fade">
.vg-head{position:fixed}
.vg-head::after{content:"";position:absolute;left:0;right:0;top:100%;height:22px;background:linear-gradient(#f3f6fb,rgba(243,246,251,0));pointer-events:none}
</style>
<style id="vg-panel-css">
.vg-head{display:none !important}
#vg-panel-tools{display:flex;flex-wrap:wrap;align-items:center;gap:10px;padding:10px 10px 12px;margin:4px 0 10px;border-bottom:1px solid rgba(255,255,255,.15)}
#vg-panel-tools .vg-title{display:none}
#vg-panel-tools .vg-hb,#vg-panel-tools a,#vg-panel-tools .vg-user{background:rgba(255,255,255,.12) !important;color:#fff !important;border:0 !important;border-radius:8px;padding:6px 10px;font-size:13px;line-height:1.4;text-decoration:none;cursor:pointer;box-shadow:none !important}
#vg-panel-tools .vg-hb svg{color:#fff}
#vg-panel-tools a{flex:1 1 100%;display:inline-flex;align-items:center;justify-content:center;gap:8px;height:44px;white-space:nowrap}
#vg-panel-tools .vg-lang{flex:1 1 0;min-width:44px;height:44px;display:inline-flex;align-items:center;justify-content:center;font-weight:700;padding:0 !important}
#vg-panel-tools .vg-user{width:44px;height:44px;flex:0 0 44px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;padding:0 !important;font-weight:700;font-size:15px;background:#fff !important;color:#1d4ed8 !important;cursor:default}
nav.side a:not(#vg-panel-tools a){padding:6px 12px !important;margin-bottom:1px !important}
nav.side .sep{margin:8px 0 !important}
nav.side a:not(#vg-panel-tools a){min-height:44px !important}
nav.side summary{min-height:44px !important;padding:6px 16px !important}
nav.side{padding-bottom:8px !important}
#vg-panel-tools{padding:6px 10px 8px !important;margin:0 0 4px !important}
#vg-panel-tools a,#vg-panel-tools .vg-lang{height:44px !important}
body nav.side h1{margin:0 0 2px !important}
.vg-burger{position:fixed;top:10px;inset-inline-end:10px;z-index:70;width:44px;height:44px;border:0;border-radius:10px;background:#0667a5;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,.2)}
@media (min-width:761px){.vg-burger{display:none !important}}
</style>
<script id="vg-panel-js">
document.addEventListener('DOMContentLoaded',function(){
  var h=document.querySelector('.vg-head'), nav=document.querySelector('nav.side');
  if(!h||!nav) return;
  var box=document.createElement('div'); box.id='vg-panel-tools';
  var t=h.querySelector('.vg-title'); if(t) box.appendChild(t);
  var l=h.querySelector('.vg-head-l'); if(l){ while(l.firstChild) box.appendChild(l.firstChild); }
  var h1=nav.querySelector('h1'); if(h1&&h1.nextSibling) nav.insertBefore(box,h1.nextSibling); else nav.insertBefore(box,nav.firstChild);
  var b=h.querySelector('.vg-burger'); if(b) document.body.appendChild(b);
  setTimeout(function(){ var ph=h.previousElementSibling; if(ph&&ph.style&&ph.style.height&&ph.id!=='vg-panel-tools'&&!ph.id) ph.remove(); var lh=h.querySelector('.vg-head-l'); if(lh&&box.parentNode){ while(lh.firstChild) box.appendChild(lh.firstChild); } var uu=box.querySelector('.vg-user'); if(uu&&!uu.dataset.v){ var nm=uu.textContent.trim(); uu.title=nm; uu.dataset.v='1'; uu.textContent=(nm.charAt(0)||'?').toUpperCase(); } },300);
});
</script>
<style id="vg-pop-css">
nav.side > a[href$="account.php"]{display:none !important}
#vg-panel-tools .vg-user{cursor:pointer !important}
#vg-panel-tools .vg-user:focus-visible{outline:2px solid #fff;outline-offset:2px}
nav.side .vg-pop{position:absolute;z-index:90;width:210px;background:#fff;color:#1f3154;border-radius:12px;padding:6px;box-shadow:0 12px 32px -8px rgba(0,0,0,.45)}
nav.side .vg-pop[hidden]{display:none}
nav.side .vg-pop .vg-pop-head{display:flex;align-items:center;gap:10px;padding:8px 8px 10px;border-bottom:1px solid #e5eaf1;margin-bottom:4px}
nav.side .vg-pop .vg-pop-av{width:44px;height:44px;flex:0 0 44px;border-radius:50%;background:#1d4ed8;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:18px}
nav.side .vg-pop .vg-pop-name{min-width:0;font-size:13px;font-weight:700;color:#1f3154;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
nav.side .vg-pop a.vg-danger{color:#b91c1c !important}
#vg-crop{position:fixed;inset:0;z-index:300;background:rgba(10,20,40,.62);display:flex;align-items:center;justify-content:center;padding:16px}
#vg-crop .vc-box{width:100%;max-width:340px;background:#fff;color:#1f3154;border-radius:16px;padding:16px;box-shadow:0 24px 60px -12px rgba(0,0,0,.5);font-family:inherit}
#vg-crop .vc-t{font-weight:800;font-size:15px;margin:0 0 12px;text-align:center}
#vg-crop .vc-stage{position:relative;width:100%;aspect-ratio:1/1;background:#0f172a;border-radius:12px;overflow:hidden;touch-action:none;cursor:grab;user-select:none;-webkit-user-select:none}
#vg-crop .vc-stage:active{cursor:grabbing}
#vg-crop .vc-stage img{position:absolute;left:50%;top:50%;max-width:none;transform-origin:center;pointer-events:none;-webkit-user-drag:none}
#vg-crop .vc-ring{position:absolute;left:50%;top:50%;width:84%;aspect-ratio:1/1;transform:translate(-50%,-50%);border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 999px rgba(15,23,42,.62);pointer-events:none}
#vg-crop .vc-zoom{display:flex;align-items:center;gap:10px;margin:14px 2px 4px;font-size:12px;color:#64748b}
#vg-crop .vc-zoom input{flex:1;accent-color:#1d4ed8}
#vg-crop .vc-act{display:flex;gap:8px;margin-top:14px}
#vg-crop .vc-act button{flex:1;height:42px;border:0;border-radius:10px;font:inherit;font-weight:700;font-size:14px;cursor:pointer}
#vg-crop .vc-ok{background:#1d4ed8;color:#fff}
#vg-crop .vc-no{background:#eef3fa;color:#1f3154}
#vg-crop button:disabled{opacity:.6;cursor:default}
#vg-crop button:focus-visible,#vg-crop input:focus-visible{outline:2px solid var(--vg-focus);outline-offset:2px}
nav.side .vg-pop a{color:#1f3154 !important;background:none;display:flex;align-items:center;gap:10px;padding:8px 10px !important;border-radius:8px;font-size:13.5px;min-height:36px !important}
nav.side .vg-pop a:hover{background:#eef3fa !important}
</style>
<script id="vg-pop-js">
(function(){
  function crop(file,fm,done){
    var url=URL.createObjectURL(file), img=new Image();
    img.onerror=function(){ URL.revokeObjectURL(url); alert('تعذر فتح الصورة، جرب JPG أو PNG.'); done(); };
    img.onload=function(){
      var W=img.naturalWidth, H=img.naturalHeight, z=1, ox=0, oy=0, D=0, s0=1;
      var ov=document.createElement('div'); ov.id='vg-crop'; ov.setAttribute('role','dialog'); ov.setAttribute('aria-modal','true'); ov.setAttribute('aria-label','قص الصورة');
      ov.innerHTML='<div class="vc-box"><p class="vc-t">اضبط الصورة</p><div class="vc-stage"></div><div class="vc-zoom"><span>تصغير</span><input type="range" min="1" max="3" step="0.01" value="1" aria-label="تكبير"><span>تكبير</span></div><div class="vc-act"><button type="button" class="vc-ok">حفظ</button><button type="button" class="vc-no">إلغاء</button></div></div>';
      var st=ov.querySelector('.vc-stage'), rg=ov.querySelector('input'), ok=ov.querySelector('.vc-ok'), no=ov.querySelector('.vc-no');
      var ring=document.createElement('div'); ring.className='vc-ring'; img.style.width=W+'px'; img.style.height=H+'px'; st.appendChild(img); st.appendChild(ring);
      document.body.appendChild(ov);
      D=ring.getBoundingClientRect().width; s0=D/Math.min(W,H);
      function clamp(){ var sc=s0*z, mx=Math.max(0,(W*sc-D)/2), my=Math.max(0,(H*sc-D)/2); ox=Math.max(-mx,Math.min(mx,ox)); oy=Math.max(-my,Math.min(my,oy)); }
      function draw(){ clamp(); img.style.transform='translate(-50%,-50%) translate('+ox+'px,'+oy+'px) scale('+(s0*z)+')'; }
      draw();
      var drag=null;
      st.addEventListener('pointerdown',function(e){ drag={x:e.clientX,y:e.clientY,ox:ox,oy:oy}; try{st.setPointerCapture(e.pointerId);}catch(x){} });
      st.addEventListener('pointermove',function(e){ if(!drag) return; ox=drag.ox+(e.clientX-drag.x); oy=drag.oy+(e.clientY-drag.y); draw(); });
      function up(){ drag=null; } st.addEventListener('pointerup',up); st.addEventListener('pointercancel',up);
      st.addEventListener('wheel',function(e){ e.preventDefault(); z=Math.max(1,Math.min(3,z-e.deltaY*0.002)); rg.value=z; draw(); },{passive:false});
      rg.addEventListener('input',function(){ z=parseFloat(rg.value)||1; draw(); });
      function close(){ document.removeEventListener('keydown',key); ov.remove(); URL.revokeObjectURL(url); done(); }
      function key(e){ if(e.key==='Escape'){ e.stopPropagation(); close(); } }
      document.addEventListener('keydown',key);
      no.addEventListener('click',close);
      ov.addEventListener('mousedown',function(e){ if(e.target===ov) close(); });
      ok.addEventListener('click',function(){
        ok.disabled=true; no.disabled=true; ok.textContent='جارٍ الحفظ…';
        var sc=s0*z, side=D/sc, cx=W/2-ox/sc, cy=H/2-oy/sc, N=384;
        var cv=document.createElement('canvas'); cv.width=N; cv.height=N; var g=cv.getContext('2d'); g.fillStyle='#fff'; g.fillRect(0,0,N,N);
        g.imageSmoothingQuality='high'; g.drawImage(img,cx-side/2,cy-side/2,side,side,0,0,N,N);
        function fail(){ alert('تعذر رفع الصورة، حاول تاني.'); ok.disabled=false; no.disabled=false; ok.textContent='حفظ'; }
        cv.toBlob(function(b){
          if(!b){ fail(); return; }
          var fd=new FormData(fm); fd.set('avatar',new File([b],'avatar.jpg',{type:'image/jpeg'}));
          fetch('avatar.php',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){ if(!r.ok) throw 0; location.reload(); }).catch(fail);
        },'image/jpeg',0.9);
      });
      ok.focus();
    };
    img.src=url;
  }
  function go(){
    var nav=document.querySelector('nav.side'), box=document.getElementById('vg-panel-tools'); if(!nav||!box) return false;
    var uu=box.querySelector('.vg-user'); if(!uu||!uu.dataset.v) return false;
    if(uu.dataset.pop) return true; uu.dataset.pop='1';
    var old=null; nav.querySelectorAll(':scope > a').forEach(function(a){ if(/account\.php/.test(a.getAttribute('href')||'')) old=a; });
    var pop=document.createElement('div'); pop.className='vg-pop'; pop.id='vg-pop'; pop.hidden=true; pop.setAttribute('role','menu');
    var photo=uu.dataset.photo||'', letter=uu.textContent;
    function paint(el){ if(photo){ el.textContent=''; el.style.setProperty('background','url('+photo+') center/cover no-repeat #fff','important'); } }
    paint(uu);
    var hd=document.createElement('div'); hd.className='vg-pop-head';
    var av=document.createElement('span'); av.className='vg-pop-av'; av.textContent=letter; paint(av);
    var nm=document.createElement('span'); nm.className='vg-pop-name'; nm.textContent=uu.title||'';
    hd.appendChild(av); hd.appendChild(nm); pop.appendChild(hd);
    var fm=document.createElement('form'); fm.method='post'; fm.action='avatar.php'; fm.enctype='multipart/form-data'; fm.innerHTML=uu.dataset.csrf||'';
    var fi=document.createElement('input'); fi.type='file'; fi.name='avatar'; fi.accept='image/jpeg,image/png,image/webp'; fi.hidden=true;
    fi.addEventListener('change',function(){ if(!fi.files.length) return; var f=fi.files[0]; if(f.size>12*1024*1024){ alert('الصورة كبيرة، الحد الأقصى 12 ميجا.'); fi.value=''; return; } open(false); crop(f,fm,function(){ fi.value=''; }); });
    var up=document.createElement('a'); up.href='#'; up.setAttribute('role','menuitem'); up.innerHTML='<span>'+(photo?'تغيير الصورة':'إضافة صورة')+'</span>';
    up.addEventListener('click',function(e){ e.preventDefault(); fi.click(); });
    fm.appendChild(fi); fm.appendChild(up);
    if(photo){ var rm=document.createElement('a'); rm.href='#'; rm.className='vg-danger'; rm.setAttribute('role','menuitem'); rm.innerHTML='<span>حذف الصورة</span>';
      rm.addEventListener('click',function(e){ e.preventDefault(); if(!confirm('تحذف الصورة؟')) return; var r=document.createElement('input'); r.type='hidden'; r.name='remove'; r.value='1'; fm.appendChild(r); fi.disabled=true; fm.submit(); });
      fm.appendChild(rm); }
    pop.appendChild(fm);
    var a; if(old){ a=old.cloneNode(true); old.remove(); } else { a=document.createElement('a'); a.href='account.php'; a.innerHTML='<span>تغيير كلمة المرور</span>'; }
    a.removeAttribute('class'); a.setAttribute('role','menuitem'); pop.appendChild(a);
    nav.appendChild(pop);
    uu.setAttribute('tabindex','0'); uu.setAttribute('role','button'); uu.setAttribute('aria-haspopup','menu'); uu.setAttribute('aria-controls','vg-pop'); uu.setAttribute('aria-expanded','false');
    function place(){ var nr=nav.getBoundingClientRect(), ur=uu.getBoundingClientRect(); pop.style.top=(ur.bottom-nr.top+nav.scrollTop+8)+'px'; pop.style.right=Math.max(8,nr.right-ur.right)+'px'; }
    function open(v){ pop.hidden=!v; uu.setAttribute('aria-expanded',v?'true':'false'); if(v) place(); }
    uu.addEventListener('click',function(e){ e.stopPropagation(); open(pop.hidden); });
    uu.addEventListener('keydown',function(e){ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); open(pop.hidden); } });
    document.addEventListener('click',function(e){ if(!pop.hidden&&!pop.contains(e.target)) open(false); });
    document.addEventListener('keydown',function(e){ if(e.key==='Escape'&&!pop.hidden){ open(false); uu.focus(); } });
    return true;
  }
  document.addEventListener('DOMContentLoaded',function(){ var n=0; (function t(){ if(go()||++n>20) return; setTimeout(t,150); })(); });
})();
</script>
<style id="vg-v3">
.vg-burger{background:#0667a5 !important;color:#fff !important;border:0 !important;box-shadow:0 4px 12px rgba(15,27,45,.28) !important}
.vg-burger:hover{background:#055589 !important}
.vg-burger:focus-visible{outline:2px solid var(--vg-focus);outline-offset:2px}
.vg-burger .ic{width:22px;height:22px}
main .card{border:1px solid #e3e9f1;border-radius:14px;box-shadow:0 1px 2px rgba(15,27,45,.05)}
main table th{font-size:12px;font-weight:600;color:#64748b;white-space:nowrap}
main table td{font-variant-numeric:tabular-nums}
main table tr:not(.vg-hrow):hover td{background:#f6f9fc}
main .topbar{flex-wrap:wrap;gap:10px}
@media (max-width:760px){
  main{padding-top:64px !important}
  main .btn,main button:not(.vg-burger),main select,main input:not([type=checkbox]):not([type=radio]):not([type=file]){min-height:44px}
  main .btn.sm{min-height:38px}
  main input,main select,main textarea{font-size:16px}
}
@media (max-width:640px){
  table.vg-stack,table.vg-stack tbody,table.vg-stack thead{display:block;width:100%;border:0}
  table.vg-stack{min-width:0 !important;width:100% !important}
  table.vg-stack td{white-space:normal}
  table.vg-stack td.vg-img::before{display:none !important}
  table.vg-stack tr.vg-hrow{display:none}
  table.vg-stack tr{display:flex;flex-wrap:wrap;align-items:center;gap:8px 14px;padding:12px;margin:0 0 10px;background:#fff;border:1px solid #e3e9f1;border-radius:12px}
  table.vg-stack tr:last-child{margin-bottom:0}
  table.vg-stack td{display:block;flex:1 1 28%;min-width:0;padding:0;border:0;background:none !important;text-align:start}
  table.vg-stack td[data-label]:not([data-label=""])::before{content:attr(data-label);display:block;margin-bottom:2px;font-size:11px;font-weight:600;color:#64748b}
  table.vg-stack td.vg-main{flex:1 1 100%;font-weight:700;font-size:14.5px}
  table.vg-stack td.vg-main::before{display:none !important}
  table.vg-stack td.vg-img{flex:0 0 52px}
  table.vg-stack td.vg-img + td.vg-main{flex:1 1 calc(100% - 70px)}
  table.vg-stack td.vg-img img{width:52px;height:52px;object-fit:cover;border-radius:10px}
  table.vg-stack td input,table.vg-stack td select{width:100%}
  table.vg-stack td[colspan],table.vg-stack td[data-label=""]{flex:1 1 100%}
  .tw{overflow-x:visible}
}
</style>
<script id="vg-v3-js">
document.addEventListener('DOMContentLoaded',function(){
  [].forEach.call(document.querySelectorAll('main table'),function(tb){
    if(tb.classList.contains('no-stack')) return;
    var rows=tb.rows, hr=null, i;
    for(i=0;i<rows.length;i++){ if(rows[i].querySelector('th')){ hr=rows[i]; break; } }
    if(!hr) return;
    var labels=[].map.call(hr.cells,function(c){ return c.textContent.trim(); }), main=-1, img=-1;
    labels.forEach(function(l,j){ if(main<0&&/^(المنتج|الاسم|العميل|رقم الطلب|المصدر)$/.test(l)) main=j; if(img<0&&l==='صورة') img=j; });
    hr.classList.add('vg-hrow');
    [].forEach.call(rows,function(r){ if(r===hr) return; [].forEach.call(r.cells,function(c,j){ if(c.colSpan>1) return; c.setAttribute('data-label',labels[j]||''); if(j===main) c.classList.add('vg-main'); if(j===img) c.classList.add('vg-img'); }); });
    tb.classList.add('vg-stack');
  });
});
</script>
<script id="vg-headfix">
(function(){
  function init(){
    var h=document.querySelector('.vg-head'); if(!h) return;
    var main=h.parentElement, ph=null, fixed=false;
    function place(){ var r=main.getBoundingClientRect(); h.style.left='auto'; h.style.right=Math.max(0,document.documentElement.clientWidth-r.right)+'px'; h.style.width=r.width+'px'; }
    function toFixed(){
      if(fixed) return; fixed=true;
      var cs=getComputedStyle(h), hh=h.offsetHeight;
      ph=document.createElement('div'); ph.style.cssText='height:'+hh+'px;margin:0;padding:0;flex:none';
      h.parentNode.insertBefore(ph,h);
      h.style.setProperty('position','fixed','important'); h.style.setProperty('top','0','important'); h.style.margin='0'; h.style.zIndex='30'; h.style.boxSizing='border-box';
      place(); window.addEventListener('resize',place);
    }
    function check(){ if(fixed) return; var y=window.pageYOffset||document.documentElement.scrollTop; if(y>20 && h.getBoundingClientRect().top < -2) toFixed(); }
    window.addEventListener('scroll',check,{passive:true});
    toFixed();
    if(window.ResizeObserver){ try{ new ResizeObserver(place).observe(main); }catch(e){} }
    window.addEventListener('load',place); setTimeout(place,400);
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',init); else init();
})();
</script>
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
a:focus-visible,button:focus-visible,summary:focus-visible{outline:2px solid var(--vg-focus);outline-offset:2px}
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
.btn.sm,.vg-hb{min-height:44px;display:inline-flex;align-items:center;justify-content:center}
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
  .vg-hb{padding:0;width:44px}
  th{top:0}
  .vg-head{padding:0 12px}
}


/* vg-polish5 */
@media(max-width:860px){
  main{overflow-x:visible}
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
body nav.side h1{display:flex !important;flex-direction:column;align-items:center;gap:6px;margin:0 0 4px !important;padding:12px 16px 10px !important;color:#fff !important;font-size:20px !important;text-align:center;background:none !important;border-bottom:1px solid rgba(255,255,255,.16) !important}
body nav.side h1::before{content:"";width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,.16) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6zM9 12l2 2 4-4'/%3E%3C/svg%3E") center/32px no-repeat;box-shadow:inset 0 0 0 1px rgba(255,255,255,.2)}
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
.vg-head{background:#f3f6fb !important;box-shadow:none !important;border:0 !important;color:#0f1b2d !important;position:sticky !important;top:0 !important;z-index:30}
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


/* vg-logo */body nav.side h1{font-size:0 !important;gap:6px !important;padding:12px 16px 10px !important}body nav.side h1::before{width:64px !important;height:64px !important;border-radius:20px !important;background:url(/images/vg-logo-footer.png) center/68px auto no-repeat,linear-gradient(145deg,rgba(255,255,255,.22),rgba(255,255,255,.08)) !important;box-shadow:inset 0 0 0 1px rgba(255,255,255,.22),0 10px 24px -10px rgba(0,0,0,.45) !important}body nav.side h1::after{font-size:12px !important}

/* vg-logo-band */body nav.side h1{background:#fff !important;margin:0 0 6px !important;padding:10px 16px 8px !important;gap:4px !important;border-bottom:0 !important;box-shadow:0 8px 20px -12px rgba(0,0,0,.4)}body nav.side h1::before{width:112px !important;height:54px !important;border-radius:0 !important;background:url(/images/vg-logo-footer.png) center/contain no-repeat !important;box-shadow:none !important;filter:brightness(0) saturate(100%) invert(22%) sepia(96%) saturate(2100%) hue-rotate(190deg) brightness(93%) contrast(98%)}body nav.side h1::after{color:#5a6b85 !important;font-size:11.5px !important}

/* vg-burger-fix */.vg-head button{color:#0f1b2d !important;background:#fff !important;border:1px solid #e1e8f1 !important;border-radius:10px}.vg-head button svg{color:#0f1b2d !important}
</style>
<?php $vgNames = []; try { $vgNames = db()->query('SELECT name_ar, name_en FROM products WHERE name_ar <> "" AND name_en <> ""')->fetchAll(PDO::FETCH_KEY_PAIR); } catch (Throwable $e) { $vgNames = []; } ?>
<script>window.VG_NAMES=<?= json_encode($vgNames, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="assets/i18n.js?v=20261120"></script>
<link rel="stylesheet" href="assets/flutter-theme.css?v=1">
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
 'menu'=>'M4 6h16M4 12h16M4 18h16',
 'moon'=>'M12 3a9 9 0 1 0 9 9 7 7 0 0 1-9-9z'
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
<nav class="side" id="vg-nav">
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
  <button class="vg-theme" type="button" aria-pressed="false"><?= $vgIc('moon') ?><span class="vg-theme-lb">الوضع الفاتح</span></button>
  <a href="logout.php"><?= $vgIc('out') ?><span>تسجيل الخروج</span></a>
</nav>
<?php endif; ?>
<?php endif; ?>
<main>
<?php if ($admin): $vgMAvF = dirname(__DIR__, 2) . '/images/avatars/u' . (int)($admin['id'] ?? 0) . '.jpg';
$vgMPhoto = is_file($vgMAvF) ? '/images/avatars/u' . (int)$admin['id'] . '.jpg?v=' . filemtime($vgMAvF) : '';
$vgMName = (string)(($admin['full_name'] ?? '') ?: ($admin['username'] ?? '')); ?>
<div class="vg-mtools">
  <button class="vg-mtheme vg-theme" type="button" aria-label="تبديل الوضع الفاتح والداكن" aria-pressed="false"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a9 9 0 1 0 9 9 7 7 0 0 1-9-9z"/></svg></button>
  <a class="vg-mava" href="account.php" aria-label="الحساب: <?= htmlspecialchars($vgMName) ?>"<?= $vgMPhoto ? ' style="background-image:url(\'' . htmlspecialchars($vgMPhoto) . '\')"' : '' ?>><?= $vgMPhoto ? '' : htmlspecialchars(mb_strtoupper(mb_substr($vgMName ?: '?', 0, 1))) ?></a>
</div>
<?php endif; ?>
<div class="vg-head">
<script>(function(){function set(t){document.documentElement.setAttribute("data-theme",t);try{localStorage.setItem("vg-theme",t)}catch(e){}}function sync(){var d=document.documentElement.getAttribute("data-theme")==="dark";document.querySelectorAll(".vg-theme").forEach(function(b){b.setAttribute("aria-pressed",d?"true":"false");var t=b.querySelector(".vg-theme-lb");if(t)t.textContent=d?"الوضع الفاتح":"الوضع الداكن";b.title=d?"الوضع الفاتح":"الوضع الداكن";});}document.addEventListener("click",function(e){var b=e.target.closest&&e.target.closest(".vg-theme");if(!b)return;set(document.documentElement.getAttribute("data-theme")==="dark"?"light":"dark");sync();});document.addEventListener("DOMContentLoaded",sync);})();</script>
  <div class="vg-head-r">
    <button class="vg-burger" type="button" aria-label="القائمة" aria-expanded="false" aria-controls="vg-nav" onclick="this.setAttribute('aria-expanded',document.body.classList.toggle('nav-open')?'true':'false')"><?= $admin ? $vgIc('menu') : '' ?></button>
    <b class="vg-title"><?= htmlspecialchars($page_title ?? 'لوحة التحكم') ?></b>
  </div>
  <div class="vg-head-l">
    <?php if ($admin): ?><span class="vg-user" data-csrf="<?= htmlspecialchars(csrf_field(), ENT_QUOTES) ?>"<?php $vgAvF = dirname(__DIR__, 2) . '/images/avatars/u' . (int)($admin['id'] ?? 0) . '.jpg'; if (is_file($vgAvF)) echo ' data-photo="/images/avatars/u' . (int)$admin['id'] . '.jpg?v=' . filemtime($vgAvF) . '"'; ?>><?= htmlspecialchars((string)(($admin['full_name'] ?? '') ?: ($admin['username'] ?? ''))) ?></span><?php endif; ?>
  </div>
</div>
<?php if ($flash): ?><div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>
