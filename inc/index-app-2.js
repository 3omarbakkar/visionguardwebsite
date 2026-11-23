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
