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
function bsRender(){ var items=[]; BS.forEach(function(id){ var p=P.find(function(x){return x.id==='p'+id}); if(p&&p.s&&!p.hide) items.push(p); }); var want=BS.length; if(items.length<want){ P.forEach(function(p){ if(items.length>=want) return; if(p.s&&!p.hide&&items.indexOf(p)<0) items.push(p); }); } var el=$('bsgrid'); if(!el)return; el.innerHTML=items.map(function(p){var e=ep(p),d=e<p.p?Math.round((1-e/p.p)*100):0; return '<article class="card">'+(d?'<span class="badge">-'+d+'%</span>':'')+(!p.s?'<span class="oosb">'+T('oos')+'</span>':'')+'<a class="pic'+(p.s?'':' oos')+'" href="'+(p.sl?'/p/'+encodeURIComponent(p.sl):'product.php?id='+p.id.slice(1))+'">'+pimg(p)+'</a><div class="meta">'+[esc(BN(p.b)),T(p.c),p.pl?T(p.pl):''].filter(Boolean).join(' \u00b7 ')+'</div><h3><a href="'+(p.sl?'/p/'+encodeURIComponent(p.sl):'product.php?id='+p.id.slice(1))+'">'+esc(nmp(p))+'</a></h3><div class="price">'+prc(p)+'</div><div class="stock'+(p.s?'':' out')+'">'+(p.s?T('stock'):T('oos'))+'</div><button type="button"'+(p.s?'':' disabled')+' data-n="'+p.i+'">'+(p.s?T('add'):T('un'))+'</button></article>'; }).join('')}
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
