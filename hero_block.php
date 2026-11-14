<?php
if (!function_exists('vg_webp')) { function vg_webp($img) { $w = preg_replace('/\.png$/i', '.webp', (string)$img); if ($w !== $img && is_file(__DIR__ . $w) && is_file(__DIR__ . $img) && filemtime(__DIR__ . $w) >= filemtime(__DIR__ . $img)) return $w; return $img; } }
// قالب الهيرو للصفحة الرئيسية. يتضمّن من index.php بعد وسم <main>.
if (!function_exists('db')) { require_once __DIR__ . '/admin/includes/db.php'; }
try {
    $vg_slides = db()->query('SELECT image, image_dark, link, alt FROM hero_slides WHERE active = 1 AND (ends_at IS NULL OR ends_at >= CURDATE()) ORDER BY sort_order, id')->fetchAll();
} catch (Throwable $e) {
    try { $vg_slides = db()->query('SELECT image, link, alt FROM hero_slides WHERE active = 1 ORDER BY sort_order, id')->fetchAll(); } catch (Throwable $e2) { $vg_slides = []; }
}
if (!$vg_slides) { return; }
$vg_n = count($vg_slides);
?>
<style>
.vg-hero{position:relative;max-width:1280px;margin:12px auto 8px;aspect-ratio:1600/700;border-radius:22px;overflow:hidden;background:#eef3f8;touch-action:pan-y}
.vg-slide{position:absolute;inset:0;display:block;opacity:0;transition:opacity 1s;pointer-events:none}
.vg-slide.on{opacity:1;pointer-events:auto}
.vg-slide img{width:100%;height:100%;object-fit:cover;display:block}
.vg-nav{position:absolute;top:50%;transform:translateY(-50%);z-index:5;width:40px;height:40px;border-radius:50%;border:1px solid rgba(31,49,84,.25);background:rgba(255,255,255,.85);color:#1f3154;font-size:20px;line-height:1;cursor:pointer;transition:background .2s,color .2s}
.vg-nav:hover,.vg-nav:focus-visible{background:#05b3e9;color:#04202e;outline:none}
.vg-prev{inset-inline-start:10px}.vg-next{inset-inline-end:10px}
.vg-nav{transition:background .2s,color .2s,opacity .25s}
@media (hover:hover){.vg-nav{opacity:0;pointer-events:none}.vg-hero:hover .vg-nav,.vg-hero:focus-within .vg-nav{opacity:1;pointer-events:auto}}
.vg-dots{position:absolute;bottom:2px;right:18px;left:auto;z-index:5;display:flex;gap:0;justify-content:flex-end}
.vg-dots button{width:24px;height:24px;padding:0;border:0;background:none;position:relative;cursor:pointer}.vg-dots button::before{content:"";position:absolute;top:50%;left:50%;width:8px;height:8px;border-radius:50%;background:rgba(31,49,84,.35);transform:translate(-50%,-50%);transition:all .25s}
.vg-dots button.on::before{width:22px;border-radius:5px;background:#0667a5}
@media (max-width:600px){.vg-hero{margin:8px 8px;border-radius:14px}.vg-nav{width:32px;height:32px;padding:0;font-size:18px;opacity:.85}.vg-prev{inset-inline-start:2px}.vg-next{inset-inline-end:2px}}
@media (prefers-reduced-motion:reduce){.vg-slide{transition:none}}
.vg-slide .vg-d{display:none}
html[data-theme=dark] .vg-hero{background:transparent;border-radius:0}
html[data-theme=dark] .vg-slide .vg-d{display:block}
html[data-theme=dark] .vg-slide .vg-d~.vg-l{display:none}
@media (max-width:400px){.vg-nav{width:28px;height:28px;font-size:16px}}
</style>
<section class="vg-hero" id="vgHero" aria-roledescription="carousel" aria-label="عروض Vision Guard">
<?php foreach ($vg_slides as $i => $s):
    $href = trim((string)$s['link']);
    $isExt = preg_match('~^https?://~i', $href);
    $path = __DIR__ . vg_webp($s['image']);
    $src = vg_webp($s['image']) . (is_file($path) ? '?v=' . filemtime($path) : '');
    $tag = $href !== '' ? 'a' : 'div';
?>
  <<?= $tag ?> class="vg-slide<?= $i === 0 ? ' on' : '' ?>"<?= $href !== '' ? ' href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"' . ($isExt ? ' target="_blank" rel="noopener"' : '') : '' ?> role="group" aria-roledescription="slide" aria-label="<?= ($i + 1) . ' / ' . $vg_n ?>"<?= $i === 0 ? '' : ' aria-hidden="true"' ?>>
<?php if (!empty($s['image_dark'])): $dp = __DIR__ . vg_webp($s['image_dark']); $dsrc = vg_webp($s['image_dark']) . (is_file($dp) ? '?v=' . filemtime($dp) : ''); ?>
    <img class="vg-d" src="<?= htmlspecialchars($dsrc, ENT_QUOTES, 'UTF-8') ?>" alt="" width="1600" height="700" loading="lazy">
<?php endif; ?>
    <img class="vg-l" src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$s['alt'], ENT_QUOTES, 'UTF-8') ?>" width="1600" height="700"<?= $i === 0 ? ' fetchpriority="high"' : ' loading="lazy"' ?>>
  </<?= $tag ?>>
<?php endforeach; ?>
<?php if ($vg_n > 1): ?>
  <button type="button" class="vg-nav vg-prev" aria-label="<?php echo (isset($vgEn)&&$vgEn)?'Previous':'السابق'; ?>">&lsaquo;</button>
  <button type="button" class="vg-nav vg-next" aria-label="<?php echo (isset($vgEn)&&$vgEn)?'Next':'التالي'; ?>">&rsaquo;</button>
  <div class="vg-dots"></div>
<?php endif; ?>
</section>
<?php if ($vg_n > 1): ?>
<script>
(function(){
  var h=document.getElementById('vgHero');if(!h)return;
  var sl=h.querySelectorAll('.vg-slide'),dw=h.querySelector('.vg-dots'),cur=0,t=null,dots=[];
  var reduce=window.matchMedia&&matchMedia('(prefers-reduced-motion: reduce)').matches;
  function go(i){
    cur=(i+sl.length)%sl.length;
    sl.forEach(function(s,k){var on=k===cur;s.classList.toggle('on',on);s.setAttribute('aria-hidden',on?'false':'true');
      var a=s.tagName==='A';if(a)s.tabIndex=on?0:-1;});
    dots.forEach(function(d,k){d.classList.toggle('on',k===cur);});
  }
  function play(){if(reduce)return;stop();t=setInterval(function(){go(cur+1)},5000)}
  function stop(){if(t){clearInterval(t);t=null}}
  sl.forEach(function(_,i){var b=document.createElement('button');b.type='button';b.setAttribute('aria-label',(document.documentElement.lang==='en'?'Slide ':'الشريحة ')+(i+1));
    b.onclick=function(){go(i);play()};dw.appendChild(b);dots.push(b);});
  h.querySelector('.vg-next').onclick=function(){go(cur+1);play()};
  h.querySelector('.vg-prev').onclick=function(){go(cur-1);play()};
  h.addEventListener('mouseenter',stop);h.addEventListener('mouseleave',play);
  h.addEventListener('focusin',stop);h.addEventListener('focusout',play);
  var x0=null;
  h.addEventListener('touchstart',function(e){x0=e.touches[0].clientX;stop()},{passive:true});
  h.addEventListener('touchend',function(e){
    if(x0!==null){var dx=e.changedTouches[0].clientX-x0;
      if(Math.abs(dx)>40){go(cur+(dx<0?1:-1))}}
    x0=null;play();},{passive:true});
  go(0);play();
})();
</script>
<?php endif; ?>
<script>
(function(){
  var h=document.getElementById('vgHero');if(!h)return;
  h.addEventListener('click',function(e){
    var a=e.target.closest('a[href^="#"]');if(!a)return;
    var id=a.getAttribute('href').slice(1);
    e.preventDefault();
    if(id.indexOf('cat_')===0){var b=document.querySelector('button[data-c="'+id+'"]');if(b)b.click();}
    var s=document.getElementById('shop');
    if(s)s.scrollIntoView({behavior:'smooth',block:'start'});
  });
})();
</script>
