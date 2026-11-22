<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('products');
require_once __DIR__ . '/../api/_stock.php';
vg_schema_v2();
$LOW = 3; $vgiCats = ['cat_hd' => 'كاميرات HD', 'cat_wf' => 'كاميرات واي فاي', 'cat_ip' => 'كاميرات IP', 'cat_dvr' => 'مسجلات DVR', 'cat_hdd' => 'هارد ديسك', 'cat_mem' => 'كروت الميموري', 'cat_psu' => 'باور سبلاي', 'cat_cab' => 'الكابلات', 'cat_rack' => 'الراكات', 'cat_acc' => 'ملحقات', 'cat_net' => 'شبكات', 'cat_att' => 'أجهزة حضور وانصراف'];
function vgi_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
try {
    db()->exec("CREATE TABLE IF NOT EXISTS vg_stock_log (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        product_id INT UNSIGNED NOT NULL,
        old_qty INT NULL, new_qty INT NULL,
        by_user VARCHAR(80) NOT NULL DEFAULT '',
        created_at DATETIME NOT NULL,
        KEY idx_log_product (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $raw = trim((string)($_POST['stock_qty'] ?? ''));
    $who = '';
    $cur = current_admin();
    if (is_array($cur)) { $who = (string)($cur['username'] ?? ''); }
    if ($id) {
        $s = db()->prepare('SELECT stock_qty FROM products WHERE id = ?');
        $s->execute([$id]);
        $row = $s->fetch();
        if ($row) {
            $old = $row['stock_qty'] === null ? null : (int)$row['stock_qty'];
            if ($raw === '') { $new = null; }
            elseif (ctype_digit($raw)) { $new = (int)$raw; }
            else { $new = false; }
            if ($new === false) {
                flash_set('اكتب رقم صحيح للكمية.', 'error');
            } else {
                db()->prepare('UPDATE products SET stock_qty = ? WHERE id = ?')->execute([$new, $id]);
                try {
                    db()->prepare('INSERT INTO vg_stock_log (product_id, old_qty, new_qty, by_user, created_at) VALUES (?,?,?,?,NOW())')
                        ->execute([$id, $old, $new, $who]);
                } catch (Throwable $e) {}
                flash_set($new === null ? 'تم إيقاف تتبع الكمية لهذا المنتج.' : 'تم تحديث الكمية.');
            }
        }
    }
    $back = 'inventory.php?f=' . urlencode((string)($_POST['f'] ?? '')) . '&q=' . urlencode((string)($_POST['q'] ?? ''));
    header('Location: ' . $back);
    exit;
}

$f = (string)($_GET['f'] ?? '');
$q = trim((string)($_GET['q'] ?? ''));
$all = db()->query('SELECT id, name_ar, name_en, brand, category, price, in_stock, stock_qty FROM products ORDER BY id ASC')->fetchAll();

$vgCatOrd = ['cat_hd','cat_ip','cat_wf','cat_dvr','cat_hdd','cat_mem','cat_psu','cat_cab','cat_rack','cat_net','cat_att','cat_acc'];
$vgMp = function($r) {
  if (!in_array($r['category'], ['cat_hd','cat_ip','cat_wf','cat_dvr'], true)) return PHP_INT_MAX;
  foreach ([(string)$r['name_ar'], (string)$r['name_en']] as $vgN) {
    if (preg_match_all('/(?<![A-Za-z0-9])(\d+)\s*MP\b/i', $vgN, $vgM)) return (int)end($vgM[1]);
  }
  return PHP_INT_MAX;
};
usort($all, function($a, $b) use ($vgCatOrd, $vgMp) {
  $ca = array_search($a['category'], $vgCatOrd); $cb = array_search($b['category'], $vgCatOrd);
  $ca = $ca === false ? 99 : $ca; $cb = $cb === false ? 99 : $cb;
  if ($ca !== $cb) return $ca <=> $cb;
  $qa = $vgMp($a); $qb = $vgMp($b);
  if ($qa !== $qb) return $qa <=> $qb;
  $pa = (float)$a['price']; $pb = (float)$b['price'];
  if ($pa !== $pb) return $pa <=> $pb;
  return strcmp((string)$a['name_ar'], (string)$b['name_ar']);
});

function vgi_state($r, $LOW) {
    if ($r['stock_qty'] === null) {
        return ((int)$r['in_stock'] === 1) ? ['untracked', 'غير متتبع'] : ['out', 'غير متاح'];
    }
    $n = (int)$r['stock_qty'];
    if ($n <= 0) return ['out', 'نفد'];
    if ($n <= $LOW) return ['low', 'منخفض'];
    return ['ok', 'متاح'];
}
$cnt = ['ok' => 0, 'low' => 0, 'out' => 0, 'untracked' => 0];
$value = 0;
foreach ($all as $r) {
    $st = vgi_state($r, $LOW);
    $cnt[$st[0]]++;
    if ($r['stock_qty'] !== null) { $value += max(0, (int)$r['stock_qty']) * (float)$r['price']; }
}
$rows = [];
foreach ($all as $r) {
    $st = vgi_state($r, $LOW);
    if ($f !== '' && $st[0] !== $f) continue;
    if ($q !== '') {
        $hay = mb_strtolower($r['name_ar'] . ' ' . $r['name_en'] . ' ' . $r['brand']);
        if (mb_strpos($hay, mb_strtolower($q)) === false) continue;
    }
    $rows[] = [$r, $st];
}
$log = [];
try {
    $log = db()->query('SELECT l.*, p.name_ar, p.name_en FROM vg_stock_log l LEFT JOIN products p ON p.id = l.product_id ORDER BY l.id DESC LIMIT 15')->fetchAll();
} catch (Throwable $e) {}

$page_title = 'المخزون';
$active = 'inventory';
require __DIR__ . '/includes/layout_top.php';
?>
<style>
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px}
.kpi{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:14px 16px;text-decoration:none;display:block}
.kpi b{display:block;font-size:24px;line-height:1.2}
.kpi span{color:var(--muted);font-size:13px}
.kpi.on{border-color:var(--accent);box-shadow:0 0 0 2px rgba(29,78,216,.12)}
.badge{display:inline-block;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600}
.badge.ok{background:#dcfce7;color:#166534}.badge.low{background:#fef3c7;color:#92400e}
.badge.out{background:#fee2e2;color:#991b1b}.badge.untracked{background:#e5e7eb;color:#374151}
.qtyform{display:flex;gap:6px;align-items:center}
.qtyform input[type=number]{width:90px;padding:6px 8px}
.tw{overflow-x:auto}
</style>
<div class="topbar">
  <h2>المخزون (<?= count($rows) ?> من <?= count($all) ?>)</h2>
  <form method="get" style="display:flex;gap:8px">
    <input type="hidden" name="f" value="<?= vgi_h($f) ?>">
    <input type="text" name="q" value="<?= vgi_h($q) ?>" placeholder="ابحث باسم المنتج أو الماركة" style="min-width:220px">
    <button class="btn" type="submit">بحث</button>
  </form>
</div>
<div class="kpis">
  <a class="kpi <?= $f===''?'on':'' ?>" href="inventory.php"><b><?= count($all) ?></b><span>كل المنتجات</span></a>
  <a class="kpi <?= $f==='ok'?'on':'' ?>" href="inventory.php?f=ok"><b><?= $cnt['ok'] ?></b><span>متاح</span></a>
  <a class="kpi <?= $f==='low'?'on':'' ?>" href="inventory.php?f=low"><b style="color:#b45309"><?= $cnt['low'] ?></b><span>منخفض (<?= $LOW ?> أو أقل)</span></a>
  <a class="kpi <?= $f==='out'?'on':'' ?>" href="inventory.php?f=out"><b style="color:var(--danger)"><?= $cnt['out'] ?></b><span>نفد / غير متاح</span></a>
  <a class="kpi <?= $f==='untracked'?'on':'' ?>" href="inventory.php?f=untracked"><b><?= $cnt['untracked'] ?></b><span>غير متتبع بالكمية</span></a>
  <div class="kpi"><b><?= number_format($value, 0) ?></b><span>قيمة المخزون المتتبع (ج.م)</span></div>
</div>
<div class="card tw">
  <p class="muted" style="margin-top:0;color:var(--muted);font-size:13px">الكمية بتتخصم تلقائياً عند الطلب وبترجع لو الطلب اتلغى. سيب الخانة فاضية لو مش عايز تتبع كمية المنتج ده.</p>
  <style>.vgnm{display:inline-flex;align-items:center;gap:10px}.vgthumb{width:44px;height:44px;flex:0 0 44px;object-fit:cover;border-radius:8px;border:1px solid #e1e8f1;background:#f5f7fa}</style>
<table>
    <thead><tr><th>المنتج</th><th>القسم</th><th>الماركة</th><th>السعر</th><th>الحالة</th><th>الكمية الحالية</th></tr></thead>
    <tbody>
    <?php foreach ($rows as [$r, $st]): ?>
      <tr>
        <td><?php if (file_exists(__DIR__ . '/../images/p' . (int)$r['id'] . '.jpg')): ?><img class="vgthumb" src="/images/p<?= (int)$r['id'] ?>.jpg" alt="" loading="lazy"><?php endif; ?><span class="vgnm" data-en="<?= vgi_h($r['name_en'] ?? '') ?>"><?= vgi_h($r['name_ar'] ?: $r['name_en']) ?></span></td>
        <td><?= vgi_h($vgiCats[$r['category']] ?? $r['category']) ?></td>
        <td><?= vgi_h($r['brand']) ?></td>
        <td><?= number_format((float)$r['price'], 0) ?> ج.م</td>
        <td><span class="badge <?= $st[0] ?>"><?= $st[1] ?></span></td>
        <td>
          <form method="post" class="qtyform">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <input type="hidden" name="f" value="<?= vgi_h($f) ?>">
            <input type="hidden" name="q" value="<?= vgi_h($q) ?>">
            <input type="number" min="0" name="stock_qty" value="<?= $r['stock_qty'] === null ? '' : (int)$r['stock_qty'] ?>" placeholder="—">
            <button class="btn sm" type="submit">حفظ</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="6" style="color:var(--muted)">مفيش منتجات مطابقة.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php if ($log): ?>
<div class="card tw">
  <h3 style="margin-top:0">آخر تعديلات الكمية</h3>
  <table>
    <thead><tr><th>المنتج</th><th>قبل</th><th>بعد</th><th>بواسطة</th><th>التاريخ</th></tr></thead>
    <tbody>
    <?php foreach ($log as $l): ?>
      <tr>
        <td><span class="vgnm" data-en="<?= vgi_h($l['name_en'] ?? '') ?>"><?= vgi_h($l['name_ar']) ?></span></td>
        <td><?= $l['old_qty'] === null ? '—' : (int)$l['old_qty'] ?></td>
        <td><?= $l['new_qty'] === null ? '—' : (int)$l['new_qty'] ?></td>
        <td><?= vgi_h($l['by_user']) ?></td>
        <td><?= vgi_h($l['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<style id="vgfilt-css">
#vgfilt{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:0 0 12px;padding:12px;background:#fff;border:1px solid #e1e8f1;border-radius:12px}
#vgfilt input,#vgfilt select{font:inherit;font-size:14px;height:40px;padding:0 12px;border:1px solid #d6dee8;border-radius:10px;background:#fff;color:#0f1b2d;min-width:0}
#vgfilt input{flex:1 1 200px}
#vgfilt select{flex:0 1 170px}
#vgfilt button{font:inherit;font-size:14px;height:40px;padding:0 16px;border:1px solid #d6dee8;border-radius:10px;background:#fff;color:#51607a;cursor:pointer}
#vgfilt .vgf-n{font-size:13px;color:#51607a;margin-inline-start:auto;white-space:nowrap}
tr.vgf-hide{display:none}
</style>
<script id="vgfilt-js">(function(){
var tb=document.querySelector("form.qtyform");if(!tb)return;var table=tb.closest("table");var rows=[].slice.call(table.querySelectorAll("tbody tr"));
function uniq(i){var s={};rows.forEach(function(r){var v=r.children[i].textContent.trim();if(v)s[v]=1});return Object.keys(s).sort()}
function st(r){var b=r.querySelector(".badge");if(!b)return"";return b.classList.contains("untracked")?"untracked":b.classList.contains("low")?"low":b.classList.contains("out")?"out":"ok"}
var bar=document.createElement("div");bar.id="vgfilt";bar.dir="rtl";
function sel(first,opts){var s=document.createElement("select");var o=document.createElement("option");o.value="";o.textContent=first;s.appendChild(o);opts.forEach(function(v){var x=document.createElement("option");x.value=v[0];x.textContent=v[1];s.appendChild(x)});return s}
var q=document.createElement("input");q.type="search";q.placeholder="ابحث باسم المنتج";
var cat=sel("كل الأقسام",uniq(1).map(function(v){return[v,v]}));
var br=sel("كل الماركات",uniq(2).map(function(v){return[v,v]}));
var ss=sel("كل الحالات",[["untracked","غير متتبع"],["ok","متاح"],["low","منخفض"],["out","نفد / غير متاح"]]);
var rs=document.createElement("button");rs.type="button";rs.textContent="مسح الفلتر";
var n=document.createElement("span");n.className="vgf-n";
[q,cat,br,ss,rs,n].forEach(function(e){bar.appendChild(e)});
var wrap=table.parentElement;wrap.parentElement.insertBefore(bar,wrap);
function run(){var t=q.value.trim().toLowerCase(),c=cat.value,b=br.value,s=ss.value,k=0;
rows.forEach(function(r){var ok=(!t||r.children[0].textContent.toLowerCase().indexOf(t)>-1)&&(!c||r.children[1].textContent.trim()===c)&&(!b||r.children[2].textContent.trim()===b)&&(!s||st(r)===s);r.classList.toggle("vgf-hide",!ok);if(ok)k++});
n.textContent=k+" من "+rows.length+" منتج"}
[q,cat,br,ss].forEach(function(e){e.addEventListener("input",run)});
rs.onclick=function(){q.value="";cat.value="";br.value="";ss.value="";run()};run();
})();</script>
<style id="vgbulk-css">
#vgbulk{position:static;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:16px 0 0;padding:12px 16px;background:#fff;border:1px solid #e1e8f1;border-radius:12px;box-shadow:0 4px 16px -8px rgba(15,27,45,.2);font-family:inherit}
#vgbulk.on{display:flex}
#vgbulk .vgb-n{font-size:14px;color:#51607a}
#vgbulk button{font:inherit;font-size:14px;font-weight:600;border-radius:10px;padding:10px 20px;cursor:pointer;border:1px solid #e1e8f1;background:#fff;color:#0f1b2d}
#vgbulk button.vgb-save{background:#0667a5;border-color:#0667a5;color:#fff}
#vgbulk button:disabled{opacity:.6;cursor:default}

</style>
<div id="vgbulk" dir="rtl"><span class="vgb-n" id="vgbN"></span><button type="button" class="vgb-save" id="vgbSave">حفظ</button><button type="button" id="vgbReset">تراجع</button></div>
<script id="vgbulk-js">(function(){
var forms=[].slice.call(document.querySelectorAll("form.qtyform"));if(!forms.length)return;
var bar=document.getElementById("vgbulk"),n=document.getElementById("vgbN"),sv=document.getElementById("vgbSave"),rs=document.getElementById("vgbReset");
var items=forms.map(function(f){var i=f.querySelector("input[name=stock_qty]");return{f:f,i:i,o:i.value}});
function dirty(){return items.filter(function(x){return x.i.value!==x.o&&x.i.value!==""})}
function upd(){var d=dirty();sv.disabled=!d.length;rs.disabled=!d.length;n.textContent=d.length?(d.length+" تعديل لم يتم حفظه"):"اكتب الأرقام في الخانات ثم اضغط حفظ"}
items.forEach(function(x){x.i.addEventListener("input",upd)});upd();
rs.onclick=function(){items.forEach(function(x){x.i.value=x.o});upd()};
sv.onclick=async function(){var d=dirty();if(!d.length)return;sv.disabled=rs.disabled=true;var ok=0,bad=0;
for(var k=0;k<d.length;k++){n.textContent="جاري الحفظ "+(k+1)+" من "+d.length;
try{var r=await fetch(location.href.split("#")[0],{method:"POST",body:new FormData(d[k].f),credentials:"same-origin"});if(r.ok)ok++;else bad++}catch(e){bad++}}
n.textContent=bad?("تم حفظ "+ok+" وفشل "+bad):("تم حفظ "+ok+" بنجاح");setTimeout(function(){location.reload()},900)};
})();</script>
<style id="vgeasy-css">
form.qtyform{display:flex;align-items:center;gap:4px}
form.qtyform .vgq{width:32px;height:36px;border:1px solid #d6dee8;border-radius:8px;background:#fff;color:#0f1b2d;font:inherit;font-size:18px;line-height:1;cursor:pointer;padding:0}
form.qtyform .vgq:hover{background:#eef3f9}
form.qtyform input[name=stock_qty]{width:64px;text-align:center}
#vgfill{display:flex;flex-wrap:wrap;gap:8px;align-items:center;flex:1 1 100%;padding-top:8px;border-top:1px solid #eef1f5;font-size:14px;color:#51607a}
#vgfill input{flex:0 0 90px !important;text-align:center}
#vgfill button{background:#0667a5 !important;border-color:#0667a5 !important;color:#fff !important}
</style>
<script id="vgeasy-js">(function(){
var forms=[].slice.call(document.querySelectorAll("form.qtyform"));if(!forms.length)return;
function fire(i){i.dispatchEvent(new Event("input",{bubbles:true}))}
function vis(){return forms.filter(function(f){return !f.closest("tr").classList.contains("vgf-hide")})}
forms.forEach(function(f){var i=f.querySelector("input[name=stock_qty]");
function mk(t,d){var b=document.createElement("button");b.type="button";b.className="vgq";b.textContent=t;b.setAttribute("aria-label",d>0?"زيادة":"نقص");b.onclick=function(){var v=parseInt(i.value,10);if(isNaN(v))v=0;v=Math.max(0,v+d);i.value=v;fire(i)};return b}
f.insertBefore(mk("−",-1),i);f.insertBefore(mk("+",1),i.nextSibling);
i.addEventListener("keydown",function(e){if(e.key==="Enter"||e.key==="ArrowDown"||e.key==="ArrowUp"){e.preventDefault();var v=vis(),k=v.indexOf(f),n=v[k+(e.key==="ArrowUp"?-1:1)];if(n){var x=n.querySelector("input[name=stock_qty]");x.focus();x.select()}}});
i.addEventListener("focus",function(){i.select()})});
var bar=document.getElementById("vgfilt");if(bar){var w=document.createElement("div");w.id="vgfill";
var l=document.createElement("span");l.textContent="كمية لكل المنتجات الظاهرة:";var n=document.createElement("input");n.type="number";n.min="0";n.placeholder="0";
var b=document.createElement("button");b.type="button";b.textContent="تطبيق";
var e=document.createElement("label");var ck=document.createElement("input");ck.type="checkbox";ck.checked=true;ck.style.cssText="flex:0 0 auto !important;height:auto;width:auto;margin-inline-end:6px";e.appendChild(ck);e.appendChild(document.createTextNode("الخانات الفاضية فقط"));
b.onclick=function(){if(n.value==="")return;var v=Math.max(0,parseInt(n.value,10)||0);vis().forEach(function(f){var i=f.querySelector("input[name=stock_qty]");if(ck.checked&&i.value!=="")return;i.value=v;fire(i)})};
[l,n,b,e].forEach(function(x){w.appendChild(x)});bar.appendChild(w)}
})();</script>
<script id="vgdep-js">(function(){
var bar=document.getElementById("vgfilt");if(!bar)return;var s=bar.querySelectorAll("select"),cat=s[0],br=s[1];
var rows=[].slice.call(document.querySelectorAll("form.qtyform")).map(function(f){return f.closest("tr")});
function build(){var c=cat.value,keep=br.value,set={};rows.forEach(function(r){if(c&&r.children[1].textContent.trim()!==c)return;var v=r.children[2].textContent.trim();if(v)set[v]=1});
var list=Object.keys(set).sort();while(br.options.length>1)br.remove(1);list.forEach(function(v){var o=document.createElement("option");o.value=v;o.textContent=v;br.appendChild(o)});
br.options[0].textContent=c?("كل ماركات "+c):"كل الماركات";br.value=list.indexOf(keep)>-1?keep:"";br.dispatchEvent(new Event("input",{bubbles:true}))}
cat.addEventListener("input",build);bar.querySelector("button").addEventListener("click",function(){setTimeout(build,0)});
})();</script>
<script id="vgnm-en">(function(){try{if(localStorage.getItem("vg_admin_lang")!=="en")return}catch(e){return}document.querySelectorAll(".vgnm[data-en]").forEach(function(s){var e=s.getAttribute("data-en");if(e)s.textContent=e})})();</script>
<script id="vgsort-js">(function(){
var bar=document.getElementById("vgfilt");if(!bar)return;var f0=document.querySelector("form.qtyform");if(!f0)return;var tb=f0.closest("tbody");
var rows=[].slice.call(tb.querySelectorAll("tr"));rows.forEach(function(r,i){r._o=i});
function num(s){var m=String(s).replace(/,/g,"").match(/[0-9]+(\.[0-9]+)?/);return m?parseFloat(m[0]):0}
function mp(r){var re=/(?:^|[^A-Za-z0-9])([0-9]+(?:\.[0-9]+)?)\s*MP\b/gi,t=r.children[0].textContent,m,l=null;while((m=re.exec(t)))l=m[1];return l?parseFloat(l):null}
function qty(r){var v=r.querySelector("input[name=stock_qty]").value;return v===""?null:parseInt(v,10)}
function nm(r){return r.children[0].textContent.trim()}function ct(r){return r.children[1].textContent.trim()}function brd(r){return r.children[2].textContent.trim()}function pr(r){return num(r.children[3].textContent)}
function stt(r){var b=r.querySelector(".badge");if(!b)return 9;return b.classList.contains("out")?0:b.classList.contains("low")?1:b.classList.contains("ok")?2:3}
function txt(x,y){return x.localeCompare(y,"ar",{numeric:true,sensitivity:"base"})}
function last(fn,dir){return function(a,b){var x=fn(a),y=fn(b);if(x===null&&y===null)return 0;if(x===null)return 1;if(y===null)return -1;return dir*(x-y)}}
function then(){var fs=arguments;return function(a,b){for(var i=0;i<fs.length;i++){var v=fs[i](a,b);if(v)return v}return a._o-b._o}}
var byName=function(a,b){return txt(nm(a),nm(b))};
var modes={"":function(a,b){return a._o-b._o},
az:then(byName),za:then(function(a,b){return txt(nm(b),nm(a))}),
cat:then(function(a,b){return txt(ct(a),ct(b))},byName),
baz:then(function(a,b){var x=brd(a),y=brd(b);if(!x&&y)return 1;if(x&&!y)return -1;return txt(x,y)},byName),
bza:then(function(a,b){var x=brd(a),y=brd(b);if(!x&&y)return 1;if(x&&!y)return -1;return txt(y,x)},byName),
qhi:then(last(mp,-1),function(a,b){return pr(b)-pr(a)}),
qlo:then(last(mp,1),function(a,b){return pr(a)-pr(b)}),
phi:then(function(a,b){return pr(b)-pr(a)}),plo:then(function(a,b){return pr(a)-pr(b)}),
klo:then(last(qty,1),byName),khi:then(last(qty,-1),byName),
st:then(function(a,b){return stt(a)-stt(b)},byName)};
var groups=[["",[["","الترتيب: الافتراضي"]]],["الاسم",[["az","الاسم: أ ← ي"],["za","الاسم: ي ← أ"]]],["القسم والماركة",[,["baz","الماركة: A ← Z"],["bza","الماركة: Z ← A"]]],["الجودة (دقة الكاميرا)",[["qlo","الجودة: الأقل أولاً"],["qhi","الجودة: الأعلى أولاً"]]],["السعر",[["plo","السعر: الأقل أولاً"],["phi","السعر: الأعلى أولاً"]]],["المخزون",[["klo","الكمية: الأقل أولاً"],["khi","الكمية: الأعلى أولاً"],["st","الحالة: نفد ثم منخفض ثم متاح"]]]];
var s=document.createElement("select");s.id="vgsort";
groups.forEach(function(g){var p=s;if(g[0]){p=document.createElement("optgroup");p.label=g[0];s.appendChild(p)}g[1].forEach(function(v){var o=document.createElement("option");o.value=v[0];o.textContent=v[1];p.appendChild(o)})});
s.style.flex="0 1 230px";
var btn=bar.querySelector("button");bar.insertBefore(s,btn);
function run(){rows.slice().sort(modes[s.value]||modes[""]).forEach(function(r){tb.appendChild(r)})}
s.addEventListener("input",run);btn.addEventListener("click",function(){s.value="";run()});
})();</script>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
