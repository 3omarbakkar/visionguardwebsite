<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('products');

function vgdc_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$pdo = db();
$pdo->exec("CREATE TABLE IF NOT EXISTS vg_discount_state (
  id TINYINT NOT NULL PRIMARY KEY,
  active TINYINT NOT NULL DEFAULT 0,
  percent TINYINT NOT NULL DEFAULT 10,
  changed_at DATETIME NULL,
  changed_by VARCHAR(80) NULL
) DEFAULT CHARSET=utf8mb4");
$pdo->exec("INSERT IGNORE INTO vg_discount_state (id, active, percent) VALUES (1, 0, 10)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['act'] ?? '';
    $who = (string)(current_admin()['full_name'] ?? current_admin()['username'] ?? 'admin');
    if ($act === 'on') {
        $pct = (int)($_POST['percent'] ?? 0);
        if ($pct < 1 || $pct > 90) {
            flash_set('اكتب نسبة خصم من 1 إلى 90.', 'error');
        } else {
            $pdo->prepare("UPDATE vg_discount_state SET active = 1, percent = ?, changed_at = NOW(), changed_by = ? WHERE id = 1")->execute([$pct, $who]);
            flash_set('تم تفعيل خصم ' . $pct . '% لمن يدفع بإنستاباي أو محفظة.', 'success');
        }
    } elseif ($act === 'off') {
        $pdo->prepare("UPDATE vg_discount_state SET active = 0, changed_at = NOW(), changed_by = ? WHERE id = 1")->execute([$who]);
        flash_set('تم إيقاف الخصم.', 'success');
    }
    header('Location: discounts.php');
    exit;
}

$st = $pdo->query("SELECT * FROM vg_discount_state WHERE id = 1")->fetch() ?: ['active' => 0, 'percent' => 10];
$on = (int)$st['active'] === 1;
$pct = (int)$st['percent'];
$ex = 1000;
$after = round($ex * (1 - $pct / 100));

$page_title = 'الخصومات';
$active = 'discounts';
require __DIR__ . '/includes/layout_top.php';
?>
<style>
.dc{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(0,1fr);gap:16px;align-items:start}
@media(max-width:900px){.dc{grid-template-columns:1fr}}
.dc-hero{padding:22px;display:flex;flex-direction:column;gap:16px}
.dc-state{display:flex;align-items:center;gap:14px}
.dc-dot{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;background:#eef1f5;color:#64748b;flex:0 0 46px}
.dc-dot svg{width:24px;height:24px}
.on .dc-dot{background:#e3f6ea;color:#15803d}
.dc-title{font-size:20px;font-weight:800;margin:0}
.dc-sub{color:var(--muted);font-size:13.5px;margin:2px 0 0}
.dc-big{font-size:46px;font-weight:800;line-height:1;color:#0667a5}
.dc-big small{font-size:16px;color:var(--muted);font-weight:700;margin-inline-start:6px}
.dc-chips{display:flex;flex-wrap:wrap;gap:8px}
.dc-chips label{cursor:pointer}
.dc-chips input{position:absolute;opacity:0;pointer-events:none}
.dc-chips span{display:inline-block;padding:8px 16px;border-radius:99px;border:1px solid #d9e2ee;background:#fff;font-weight:700;font-size:14px;transition:all .15s}
.dc-chips input:checked+span{background:#0667a5;border-color:#0667a5;color:#fff}
.dc-chips input:focus-visible+span{outline:2px solid var(--cyan);outline-offset:2px}
.dc-custom{display:flex;align-items:center;gap:8px;margin-top:12px}
.dc-custom input[type=number]{width:90px}
.dc-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:4px}
.dc-btn{border:0;border-radius:12px;padding:12px 22px;font:inherit;font-weight:800;font-size:15px;cursor:pointer;color:#fff;background:linear-gradient(135deg,#0667a5,#05b3e9)}
.dc-btn.off{background:#b91c1c}
.dc-btn:focus-visible{outline:2px solid var(--cyan);outline-offset:2px}
.dc-list{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:12px}
.dc-list li{display:flex;gap:10px;align-items:flex-start;font-size:14px;line-height:1.7}
.dc-list b{color:#0f1b2d}
.dc-note{background:#f3f7fb;border-radius:12px;padding:12px 14px;font-size:13px;color:#51607a;line-height:1.8}
.dc-ex{display:flex;align-items:center;gap:12px;flex-wrap:wrap;background:#f3f7fb;border-radius:12px;padding:12px 14px;font-size:14px}
.dc-ex b{font-size:18px;color:#0f1b2d}
.dc-ex .arr{color:#94a3b8}
</style>
<div class="topbar"><h2>الخصومات</h2></div>
<div class="dc">
  <form method="post" class="card dc-hero <?= $on ? 'on' : '' ?>" id="dcForm">
    <?= csrf_field() ?>
    <div class="dc-state">
      <span class="dc-dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12V4h8l10 10-8 8zM7.5 8v.01"/></svg></span>
      <div>
        <p class="dc-title"><?= $on ? 'خصم الدفع الإلكتروني شغّال' : 'مفيش خصم شغّال دلوقتي' ?></p>
        <p class="dc-sub"><?= $on ? 'العميل اللي بيدفع بإنستاباي أو محفظة بياخد الخصم على إجمالي الطلب.' : 'الأسعار والإجمالي على الموقع عادية لكل طرق الدفع.' ?></p>
      </div>
    </div>
    <?php if ($on): ?>
      <div class="dc-big"><?= $pct ?>%<small>على الدفع بإنستاباي أو محفظة</small></div>
    <?php endif; ?>
    <div>
      <p class="dc-sub" style="margin-bottom:8px"><?= $on ? 'عايز تغيّر النسبة؟ اختار نسبة جديدة وفعّل تاني.' : 'اختار نسبة الخصم:' ?></p>
      <div class="dc-chips">
        <?php foreach ([5, 10, 15, 20, 25, 30] as $v): ?>
          <label><input type="radio" name="preset" value="<?= $v ?>" <?= $v === $pct ? 'checked' : '' ?>><span><?= $v ?>%</span></label>
        <?php endforeach; ?>
      </div>
      <div class="dc-custom">
        <label for="dcPct" class="dc-sub">أو اكتب نسبة تانية:</label>
        <input type="number" id="dcPct" name="percent" min="1" max="90" value="<?= $pct ?>" required>
        <span>%</span>
      </div>
    </div>
    <div class="dc-ex" id="dcEx">مثال: طلب بـ <b>1,000</b> ج.م <span class="arr">←</span> بإنستاباي أو محفظة يدفع <b id="dcAfter"><?= number_format($after) ?></b> ج.م</div>
    <div class="dc-actions">
      <button type="submit" name="act" value="on" class="dc-btn"><?= $on ? 'تحديث النسبة' : 'تفعيل الخصم' ?></button>
      <?php if ($on): ?>
        <button type="submit" name="act" value="off" class="dc-btn off" onclick="return confirm('إيقاف الخصم؟');">إيقاف الخصم</button>
      <?php endif; ?>
    </div>
    <?php if (!empty($st['changed_at'])): ?>
      <p class="dc-sub">آخر تغيير: <?= vgdc_h($st['changed_at']) ?><?= $st['changed_by'] ? ' بواسطة ' . vgdc_h($st['changed_by']) : '' ?></p>
    <?php endif; ?>
  </form>
  <div class="card" style="padding:22px;display:flex;flex-direction:column;gap:16px">
    <h3 style="margin:0">إزاي بيشتغل؟</h3>
    <ul class="dc-list">
      <li><span>1.</span><span><b>الدفع عند الاستلام:</b> بدون خصم، والعميل بيشوف تلميح "ادفع بإنستاباي أو محفظة واحصل على خصم".</span></li>
      <li><span>2.</span><span><b>إنستاباي أو محفظة:</b> الإجمالي في السلة وفي رسالة واتساب بيتخصم منه النسبة أول ما العميل يختار الطريقة دي.</span></li>
      <li><span>3.</span><span><b>الحساب على السيرفر:</b> الإجمالي المسجّل في الطلب بيتحسب هناك، فمحدش يقدر يغيّره من المتصفح.</span></li>
    </ul>
    <div class="dc-note">الخصم بيتطبق على إجمالي السلة بعد أي خصم خاص بالمنتجات. لو العميل بدّل الطريقة قبل ما يبعت الطلب، الإجمالي بيتغيّر معاه. وإيقاف الخصم بيرجّع كل حاجة عادية فوراً.</div>
  </div>
</div>
<script>
(function(){var f=document.getElementById('dcForm'),n=document.getElementById('dcPct'),a=document.getElementById('dcAfter');
function ex(){var p=parseInt(n.value,10);if(p>=1&&p<=90){a.textContent=Math.round(1000*(1-p/100)).toLocaleString('en-US');}}
f.querySelectorAll('input[name=preset]').forEach(function(r){r.addEventListener('change',function(){n.value=r.value;ex();});});
n.addEventListener('input',function(){f.querySelectorAll('input[name=preset]').forEach(function(r){r.checked=(r.value===n.value);});ex();});})();
</script>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
