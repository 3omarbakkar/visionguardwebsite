<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('dashboard');
require_once __DIR__ . '/../api/_stock.php';
require_once __DIR__ . '/includes/crm.php';
vg_schema_v2();
function vgd_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function vgd_one($sql) { try { return db()->query($sql)->fetch(); } catch (Throwable $e) { return []; } }
function vgd_all($sql) { try { return db()->query($sql)->fetchAll(); } catch (Throwable $e) { return []; } }
function vgd_p($sql, $a) { try { $s = db()->prepare($sql); $s->execute($a); return $s->fetchAll(); } catch (Throwable $e) { return []; } }
function vgd_p1($sql, $a) { $r = vgd_p($sql, $a); return $r[0] ?? []; }
/** Change badge (+12%, -5%) against the previous period. */
function vgd_delta($cur, $prev, $cls = '') {
    $cur = (float)$cur; $prev = (float)$prev;
    if ($prev <= 0) return $cur > 0 ? '<span class="dlt up ' . $cls . '">جديد</span>' : '<span class="dlt flat ' . $cls . '">—</span>';
    $p = (int)round(($cur - $prev) / $prev * 100);
    $c = $p > 0 ? 'up' : ($p < 0 ? 'down' : 'flat');
    return '<span class="dlt ' . $c . ' ' . $cls . '" dir="ltr">' . ($p > 0 ? '+' : '') . $p . '%</span>';
}
function vgd_ic($d, $cls = 'kic') { return '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>'; }
$P = [
  'cash' => 'M3 7h18v10H3zM12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4M6.5 10v.01M17.5 14v.01',
  'cal' => 'M4 6h16v14H4zM4 10h16M8 3v4M16 3v4',
  'inbox' => 'M4 13l2-8h12l2 8M4 13v6h16v-6M4 13h4l1 2h6l1-2h4',
  'truck' => 'M2 7h11v9H2zM13 10h4l3 3v3h-7zM6.5 18.5h.01M17.5 18.5h.01',
  'alert' => 'M12 4l9 16H3zM12 10v4M12 17v.01',
  'receipt' => 'M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6M9 12h6',
  'users' => 'M3 6h18v12H3zM8 12h.01M7 15c.5-1.5 3.5-1.5 4 0M14 10h4M14 14h3',
  'target' => 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8M12 12h.01',
  'bell' => 'M6 10a6 6 0 1 1 12 0c0 5 2 6 2 6H4s2-1 2-6M10 20a2 2 0 0 0 4 0',
  'bag' => 'M5 8h14l-1 12H6zM9 8V6a3 3 0 0 1 6 0v2',
];
$ok = "type = 'order' AND status <> 'cancelled'";
$prospectTypes = "type IN ('coverage_plan','contact')";

// Period filter: ?r=1|7|30|90 days, each compared with the same number of days just before it.
$rangeOpts = ['1' => 'النهارده', '7' => '7 أيام', '30' => '30 يوم', '90' => '90 يوم'];
$cmpLabel = ['1' => 'عن امبارح', '7' => 'عن الأسبوع اللي قبله', '30' => 'عن الـ 30 يوم اللي قبلهم', '90' => 'عن الـ 90 يوم اللي قبلهم'];
$r = (string)($_GET['r'] ?? '7');
if (!isset($rangeOpts[$r])) $r = '7';
$nd = (int)$r;
$todayD = vg_db_today();
$from = date('Y-m-d', strtotime($todayD . ' -' . ($nd - 1) . ' days'));
$to = date('Y-m-d', strtotime($todayD . ' +1 day'));        // exclusive
$pFrom = date('Y-m-d', strtotime($from . ' -' . $nd . ' days'));
$cur = [$from, $to];
$prev = [$pFrom, $from];

$sumSql = "SELECT COUNT(*) c, COALESCE(SUM(total),0) s FROM leads WHERE $ok AND created_at >= ? AND created_at < ?";
$per = vgd_p1($sumSql, $cur);
$perPrev = vgd_p1($sumSql, $prev);
$nPer = (int)($per['c'] ?? 0);
$sPer = (float)($per['s'] ?? 0);
$avg = $nPer > 0 ? $sPer / $nPer : 0;
$avgPrev = (int)($perPrev['c'] ?? 0) > 0 ? (float)$perPrev['s'] / (int)$perPrev['c'] : 0;
$today = vgd_p1($sumSql, [$todayD, $to]);

$pending = vgd_one("SELECT COUNT(*) c FROM leads WHERE type = 'order' AND status = 'new'");
$byStatus = []; $byStatusSum = [];
foreach (vgd_p("SELECT status, COUNT(*) c, COALESCE(SUM(total),0) s FROM leads WHERE type = 'order' AND created_at >= ? AND created_at < ? GROUP BY status", $cur) as $row) { $byStatus[$row['status']] = (int)$row['c']; $byStatusSum[$row['status']] = (float)$row['s']; }
$pipeTotal = array_sum($byStatus);
$low = vgd_one("SELECT COUNT(*) c FROM products WHERE in_stock = 1 AND stock_qty IS NOT NULL AND stock_qty <= 3");
$lowList = vgd_all("SELECT id, name_ar, stock_qty FROM products WHERE in_stock = 1 AND stock_qty IS NOT NULL AND stock_qty <= 3 ORDER BY stock_qty ASC LIMIT 8");
$cust = vgd_one("SELECT COUNT(*) c FROM customers");
$custNew = vgd_p1("SELECT COUNT(*) c FROM customers WHERE created_at >= ? AND created_at < ?", $cur);

// Sales chart: one bar per day (at least the last 7 days), one bar per week for 90 days.
$chartFrom = $nd < 7 ? date('Y-m-d', strtotime($todayD . ' -6 days')) : $from;
$days = [];
foreach (vgd_p("SELECT DATE(created_at) d, COALESCE(SUM(total),0) s, COUNT(*) c FROM leads WHERE $ok AND created_at >= ? AND created_at < ? GROUP BY DATE(created_at)", [$chartFrom, $to]) as $row) { $days[(string)$row['d']] = $row; }
$step = $nd === 90 ? 7 : 1;
$series = [];
$max = 1;
$chartTotal = 0;
for ($t = strtotime($chartFrom); $t < strtotime($to); $t += 86400 * $step) {
    $s = 0; $c = 0;
    for ($k = 0; $k < $step; $k++) {
        $d = date('Y-m-d', $t + 86400 * $k);
        if ($d >= $to) break;
        if (isset($days[$d])) { $s += (float)$days[$d]['s']; $c += (int)$days[$d]['c']; }
    }
    $series[] = [date('Y-m-d', $t), $s, $c];
    $chartTotal += $s;
    if ($s > $max) $max = $s;
}
$chartTitle = $step === 7 ? 'المبيعات بالأسبوع' : 'المبيعات باليوم';

$topAgg = []; foreach (vgd_p("SELECT cart_json FROM leads WHERE $ok AND created_at >= ? AND created_at < ?", $cur) as $tr) { $tIt = json_decode((string)$tr['cart_json'], true); if (!is_array($tIt)) continue; foreach ($tIt as $ti) { $tn = trim((string)($ti['name'] ?? '')); if ($tn === '') continue; $tq = (int)($ti['qty'] ?? 1); if (!isset($topAgg[$tn])) $topAgg[$tn] = ['name_ar' => $tn, 'q' => 0, 'v' => 0]; $topAgg[$tn]['q'] += $tq; $topAgg[$tn]['v'] += (float)($ti['price'] ?? 0) * $tq; } } uasort($topAgg, function ($a, $b) { return $b['q'] <=> $a['q']; }); $top = array_slice(array_values($topAgg), 0, 8);
$recent = vgd_all("SELECT id, name, phone, total, status, created_at FROM leads WHERE type = 'order' ORDER BY created_at DESC LIMIT 8");

// Prospects: coverage-plan requests and contact messages (the leads that are not orders yet).
$crm = vg_crm_ready();
$canContact = can('customer_contact');
$dbNow = vg_db_now();
$proj = vg_projects_ready();
$pros = vgd_p("SELECT id, type, name, phone, status, created_at" . ($crm ? ', contacted_at, followup_at' : '') . ($proj ? ', project_stage, quote_value' : '') . " FROM leads WHERE $prospectTypes AND created_at >= ? AND created_at < ? ORDER BY created_at DESC", $cur);
$prosPrev = vgd_p1("SELECT COUNT(*) c FROM leads WHERE $prospectTypes AND created_at >= ? AND created_at < ?", $prev);
$orderAt = [];   // phone key => latest non-cancelled order time, to see which prospects went on to order
foreach (vgd_p("SELECT phone, created_at FROM leads WHERE $ok AND created_at >= ?", [$from]) as $ord) { $k = vg_phone_key($ord['phone']); if ($k !== '' && (!isset($orderAt[$k]) || $ord['created_at'] > $orderAt[$k])) $orderAt[$k] = $ord['created_at']; }
$pf = ['total' => count($pros), 'coverage_plan' => 0, 'contact' => 0, 'replied' => 0, 'won' => 0, 'waiting' => 0];
$respSum = 0; $respN = 0;
// Prospect pipeline: each prospect sits in exactly one stage. "survey" counts the site-survey (coverage plan) requests in it.
$stageKeys = $proj ? ['new', 'contacted', 'followup', 'survey', 'quote', 'install', 'won', 'lost'] : ['new', 'contacted', 'followup', 'won', 'lost'];
$stage = []; $stageSurvey = []; $stageVal = [];
foreach ($stageKeys as $sk) { $stage[$sk] = 0; $stageSurvey[$sk] = 0; $stageVal[$sk] = 0.0; }
$stale = 0;   // replied to, nothing scheduled, and quiet for over 7 days
foreach ($pros as $pr) {
    $pf[$pr['type']] = ($pf[$pr['type']] ?? 0) + 1;
    if ($pr['status'] === 'new') $pf['waiting']++; else $pf['replied']++;
    $k = vg_phone_key($pr['phone']);
    $isWon = $k !== '' && isset($orderAt[$k]) && $orderAt[$k] >= $pr['created_at'];
    if ($isWon) $pf['won']++;
    if ($crm && !empty($pr['contacted_at'])) { $dt = strtotime($pr['contacted_at']) - strtotime($pr['created_at']); if ($dt >= 0) { $respSum += $dt; $respN++; } }
    $ps = $proj ? (string)($pr['project_stage'] ?? '') : '';
    if ($isWon || $ps === 'done') $stg = 'won';
    elseif (in_array($pr['status'], ['closed', 'cancelled'], true)) $stg = 'lost';
    elseif (in_array($ps, ['survey', 'quote', 'install'], true)) $stg = $ps;
    elseif ($pr['status'] === 'new') $stg = 'new';
    elseif (!empty($pr['followup_at'])) $stg = 'followup';
    else {
        $stg = 'contacted';
        $since = strtotime((string)(($pr['contacted_at'] ?? '') ?: $pr['created_at']));
        if ($since && $dbNow - $since > 7 * 86400) $stale++;
    }
    $stage[$stg]++;
    if ($pr['type'] === 'coverage_plan') $stageSurvey[$stg]++;
    if ($proj && $pr['quote_value'] !== null) $stageVal[$stg] += (float)$pr['quote_value'];
}
$waitList = vgd_all("SELECT id, type, name, phone, created_at FROM leads WHERE $prospectTypes AND status = 'new' ORDER BY created_at ASC LIMIT 5");
$waitAll = vgd_one("SELECT COUNT(*) c FROM leads WHERE $prospectTypes AND status = 'new'");

// Follow-ups due today or missed (needs migration-followups.sql).
$fu = []; $fuAll = 0;
if ($crm) {
    $fu = vgd_p("SELECT id, type, name, phone, status, followup_at, followup_note FROM leads WHERE followup_at IS NOT NULL AND followup_at < ? ORDER BY followup_at ASC LIMIT 8", [$to]);
    $fuAll = (int)(vgd_p1("SELECT COUNT(*) c FROM leads WHERE followup_at IS NOT NULL AND followup_at < ?", [$to])['c'] ?? 0);
}

$labels = ['new' => 'جديد', 'contacted' => 'تم التواصل', 'delivered' => 'تم التوصيل', 'closed' => 'مغلق', 'cancelled' => 'ملغي'];
$page_title = 'لوحة التقارير';
$active = 'reports';
require __DIR__ . '/includes/layout_top.php';
?>
<?php
$nToday = (int)($today['c'] ?? 0);
$nLow = (int)($low['c'] ?? 0);
$nNew = (int)($pending['c'] ?? 0);
$nWait = (int)($waitAll['c'] ?? 0);
$typeLabels = ['coverage_plan' => 'مخطط تغطية', 'contact' => 'رسالة تواصل', 'order' => 'طلب'];
$stageLabels = ['new' => 'مستني رد', 'contacted' => 'تم التواصل', 'followup' => 'متابعة محددة', 'survey' => 'معاينة', 'quote' => 'عرض سعر', 'install' => 'تركيب', 'won' => $proj ? 'اتحوّل لطلب / اتركّب' : 'اتحوّل لطلب', 'lost' => 'مقفول / ملغي'];
$stageLabels = array_intersect_key($stageLabels, array_flip($stageKeys));
$stageBadge = ['new' => 'new', 'contacted' => 'contacted', 'followup' => 'followup', 'survey' => 'survey', 'quote' => 'quote', 'install' => 'install', 'won' => 'delivered', 'lost' => 'cancelled'];
$pct = function ($n) use ($pf) { return $pf['total'] > 0 ? round($n / $pf['total'] * 100) : 0; };
?>
<style>
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin-bottom:18px}
.kpi{display:flex;flex-direction:column;gap:12px;padding:16px 18px;text-decoration:none;color:inherit}
.kpi-top{display:flex;align-items:center;justify-content:space-between;gap:10px}
.kpi-label{font-size:13.5px;font-weight:600;color:var(--muted)}
.kchip{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;flex:0 0 40px;background:#e8f3fb;color:#0667a5}
.kchip .kic{width:21px;height:21px}
.kchip.green{background:#e3f6ea;color:#15803d}
.kchip.amber{background:#fdf1dc;color:#b45309}
.kchip.red{background:#fde8e8;color:#b91c1c}
.kchip.violet{background:#ece9fd;color:#5b46d6}
.kchip.cyan{background:#e0f6fd;color:#0a8fb8}
.kval{display:flex;align-items:baseline;gap:6px;font-size:30px;font-weight:800;line-height:1.1;color:#0f1b2d}
.kval small{font-size:13px;font-weight:700;color:var(--muted)}
.ksub{font-size:12.5px;color:var(--muted);min-height:18px}
.ksub b{color:#0f1b2d}
.kpi::before{display:none}
.kpi.on-alert{box-shadow:0 0 0 1px rgba(185,28,28,.25),0 6px 16px -6px rgba(185,28,28,.25) !important}
.grid2{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;margin-bottom:16px}
.ch{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px}
.ch h3{margin:0;font-size:16px}
.ch span{font-size:12.5px;color:var(--muted)}
.bars{display:flex;align-items:flex-end;gap:10px;height:160px;padding-top:8px}
.bar{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;font-size:11px;color:var(--muted);gap:4px}
.bar i{display:block;width:100%;max-width:40px;background:linear-gradient(180deg,#05b3e9,#0667a5);border-radius:8px 8px 3px 3px;min-height:3px}
.bar.zero i{background:#e2e8ef}
.badge{display:inline-block;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600;background:#e5e7eb;color:#374151}
.badge.new{background:#dbeafe;color:#1e40af}.badge.contacted{background:#fef3c7;color:#92400e}
.badge.followup{background:#ece9fd;color:#5b46d6}
.badge.survey{background:#e0f6fd;color:#0a8fb8}.badge.quote{background:#fce7f3;color:#9d174d}.badge.install{background:#e0e7ff;color:#3730a3}
a.badge{text-decoration:none}
.badge.delivered{background:#dcfce7;color:#166534}.badge.closed{background:#e0e7ff;color:#3730a3}.badge.cancelled{background:#fee2e2;color:#991b1b}
.srow{display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid #eef1f5}
.srow:last-child{border-bottom:0}
.srow b{font-variant-numeric:tabular-nums}
.empty{display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center;color:var(--muted);padding:18px 8px}
.empty .kic{width:34px;height:34px;color:#94a3b8}
.empty p{margin:0;max-width:300px;line-height:1.7;font-size:13.5px}
.tw{overflow-x:auto}
.kpis{grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.kpi.card{margin:0;position:relative;overflow:hidden;border-radius:16px;padding:16px 18px 18px}
.kpi.card::after{content:"";position:absolute;inset-inline:0;bottom:0;height:3px;background:var(--tone,#0667a5);opacity:.9}
.kpi.hero{grid-column:span 2;background:linear-gradient(135deg,#0667a5 0%,#0a8fd0 55%,#05b3e9 100%);color:#fff;--tone:#7fe3ff}
.kpi.hero .kpi-label,.kpi.hero .kval small,.kpi.hero .ksub{color:rgba(255,255,255,.85)}
.kpi.hero .kval{color:#fff;font-size:40px}
.kpi.hero .ksub b{color:#fff}
.kpi.hero .kchip{background:rgba(255,255,255,.18);color:#fff}
.kpi.t-cyan{--tone:#05b3e9}.kpi.t-violet{--tone:#5b46d6}.kpi.t-green{--tone:#15803d}.kpi.t-amber{--tone:#d97706}.kpi.t-red{--tone:#b91c1c}
@media(max-width:1000px){.kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:480px){.kpis{grid-template-columns:1fr}.kpi.hero{grid-column:auto}.kpi.hero .kval{font-size:34px}}

.rng{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:14px}
.rng .rng-tabs{display:inline-flex;background:#fff;border:1px solid var(--border);border-radius:99px;padding:3px;gap:2px}
.rng .rng-tabs a{padding:6px 14px;border-radius:99px;text-decoration:none;color:var(--text);font-size:13.5px;font-weight:600;white-space:nowrap}
.rng .rng-tabs a.on{background:#0667a5 !important;color:#fff !important}
.rng .cmp{font-size:12.5px;color:var(--muted)}
.dlt{display:inline-block;padding:1px 8px;border-radius:99px;font-size:12px;font-weight:700;margin-inline-start:6px;vertical-align:middle;background:#eef2f7;color:#475569}
.dlt.up{background:#dcfce7;color:#166534}.dlt.down{background:#fee2e2;color:#991b1b}
.kval .dlt{font-size:12px !important;line-height:20px;font-weight:700}
.kpi.hero .dlt{background:rgba(255,255,255,.2);color:#fff}
.bars.dense{gap:3px}.bars.dense .bar>span:first-child{display:none}.bars.dense .bar>span:last-child{visibility:hidden}.bars.dense .bar:nth-child(5n+1)>span:last-child{visibility:visible}
.pipe{display:flex;flex-direction:column;gap:12px}
.pstage{display:flex;flex-direction:column;gap:5px}
.pst{display:grid;grid-template-columns:1fr auto 44px;align-items:center;gap:8px}
.pst b{font-variant-numeric:tabular-nums;font-size:15px}
.pst .badge{justify-self:start}
.pst small{color:var(--muted);font-size:12px;text-align:end}
.ptr{height:8px;background:#eef2f7;border-radius:99px;overflow:hidden}
.ptr i{display:block;height:100%;border-radius:99px;background:#05b3e9}
.ptr i.followup{background:#7c3aed}.ptr i.survey{background:#05b3e9}.ptr i.quote{background:#db2777}.ptr i.install{background:#6366f1}
html[data-theme="dark"] body .badge.survey{background:rgba(5,179,233,.18);color:#7dd3fc}html[data-theme="dark"] body .badge.quote{background:rgba(219,39,119,.2);color:#f9a8d4}html[data-theme="dark"] body .badge.install{background:rgba(99,102,241,.22);color:#a5b4fc}
.stale{display:flex;align-items:center;gap:8px;margin-top:12px;padding:9px 12px;border-radius:10px;background:#fdf1dc;color:#92400e;font-size:13px}
html[data-theme="dark"] body .stale{background:rgba(217,119,6,.15);color:#fbbf24}
html[data-theme="dark"] body .badge.followup{background:rgba(124,58,237,.2);color:#c4b5fd}
.ptr i.delivered{background:#16a34a}.ptr i.closed{background:#6366f1}.ptr i.cancelled{background:#dc2626}.ptr i.contacted{background:#d97706}.ptr i.new{background:#2563eb}
.pv{color:var(--muted);font-size:12px}
html[data-theme="dark"] body .ptr{background:rgba(255,255,255,.08)}
.funnel{display:flex;flex-direction:column;gap:8px;margin:4px 0 12px}
.fstep{display:grid;grid-template-columns:120px 1fr 44px;align-items:center;gap:10px;font-size:13.5px}
.fstep .tr{height:12px;background:#eef2f7;border-radius:99px;overflow:hidden}
.fstep .tr i{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,#05b3e9,#0667a5)}
.fstep.won .tr i{background:linear-gradient(90deg,#22c55e,#15803d)}
.fstep b{text-align:end;font-variant-numeric:tabular-nums}
.mini{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:12px}
.mini div{background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:8px 10px;font-size:12px;color:var(--muted)}
.mini b{display:block;font-size:17px;color:#0f1b2d;margin-top:2px}
.prow{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:9px 0;border-bottom:1px solid #eef1f5;font-size:13.5px}
.prow:last-child{border-bottom:0}
.prow .who{display:flex;flex-direction:column;gap:2px;min-width:0}
.prow .who small{color:var(--muted);font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:260px}
.prow .act{display:flex;align-items:center;gap:6px;flex:0 0 auto}
.late{color:#b91c1c;font-weight:700}
.note-sm{font-size:12.5px;color:var(--muted);margin:0 0 8px}
.bars.dense .bar{min-width:0}.bars.dense .bar>span:last-child{width:0;overflow:visible;white-space:nowrap;display:flex;justify-content:center}
html[data-theme="dark"] body .kval{color:#fff}
html[data-theme="dark"] body .kpi .dlt{color:#cbd5e1 !important;background:rgba(255,255,255,.08)}
html[data-theme="dark"] body .kpi .dlt.up{color:#4ade80 !important;background:rgba(34,197,94,.14)}
html[data-theme="dark"] body .kpi .dlt.down{color:#f87171 !important;background:rgba(239,68,68,.14)}
html[data-theme="dark"] body .rng .rng-tabs{background:var(--fx-card,#2a2d3e);border-color:var(--fx-line,#3a3d52)}
html[data-theme="dark"] body .rng .rng-tabs a:not(.on){color:var(--fx-text,#e5e7eb)}
html[data-theme="dark"] body .mini div{background:var(--fx-field,#212332);border-color:var(--fx-line,#3a3d52)}
html[data-theme="dark"] body .mini b{color:#fff}
html[data-theme="dark"] body .fstep .tr{background:rgba(255,255,255,.08)}
html[data-theme="dark"] body .prow,html[data-theme="dark"] body .srow{border-color:var(--fx-line,#3a3d52)}
html[data-theme="dark"] body .late{color:#f87171}
</style>
<div class="rng">
  <div class="rng-tabs" aria-label="الفترة">
    <?php foreach ($rangeOpts as $k => $lb): ?><a class="<?= (string)$k === $r ? 'on' : '' ?>" href="dashboard.php?r=<?= $k ?>"<?= (string)$k === $r ? ' aria-current="page"' : '' ?>><?= vgd_h($lb) ?></a><?php endforeach; ?>
  </div>
  <span class="cmp">النسب مقارنة <?= vgd_h($cmpLabel[$r]) ?></span>
</div>
<div class="kpis">
  <div class="kpi card hero">
    <div class="kpi-top"><span class="kpi-label">المبيعات (<?= vgd_h($rangeOpts[$r]) ?>)</span><span class="kchip"><?= vgd_ic($P['cash']) ?></span></div>
    <div class="kval"><?= number_format($sPer, 0) ?><small>ج.م</small><?= vgd_delta($sPer, $perPrev['s'] ?? 0) ?></div>
    <div class="ksub"><?= $r === '1' ? ($nToday ? '<b>' . $nToday . '</b> ' . ($nToday === 1 ? 'طلب' : 'طلبات') . ' النهارده' : 'لسه مفيش طلبات النهارده') : 'النهارده: <b>' . number_format((float)($today['s'] ?? 0), 0) . '</b> ج.م من <b>' . $nToday . '</b> ' . ($nToday === 1 ? 'طلب' : 'طلبات') ?></div>
  </div>
  <div class="kpi card t-cyan">
    <div class="kpi-top"><span class="kpi-label">عدد الطلبات</span><span class="kchip cyan"><?= vgd_ic($P['bag']) ?></span></div>
    <div class="kval"><?= $nPer ?><?= vgd_delta($nPer, $perPrev['c'] ?? 0) ?></div>
    <div class="ksub">طلبات غير ملغية في الفترة</div>
  </div>
  <div class="kpi card t-amber">
    <div class="kpi-top"><span class="kpi-label">متوسط قيمة الطلب</span><span class="kchip amber"><?= vgd_ic($P['receipt']) ?></span></div>
    <div class="kval"><?= number_format($avg, 0) ?><small>ج.م</small><?= vgd_delta($avg, $avgPrev) ?></div>
    <div class="ksub">في نفس الفترة</div>
  </div>
  <a class="kpi card t-violet" href="leads.php?status=new">
    <div class="kpi-top"><span class="kpi-label">طلبات جديدة</span><span class="kchip violet"><?= vgd_ic($P['inbox']) ?></span></div>
    <div class="kval"><?= $nNew ?></div>
    <div class="ksub"><?= $nNew ? 'محتاجة متابعة منك' : 'مفيش طلبات في الانتظار' ?></div>
  </a>
  <a class="kpi card t-green" href="leads.php?type=prospects">
    <div class="kpi-top"><span class="kpi-label">عملاء محتملين جدد</span><span class="kchip green"><?= vgd_ic($P['target']) ?></span></div>
    <div class="kval"><?= $pf['total'] ?><?= vgd_delta($pf['total'], $prosPrev['c'] ?? 0) ?></div>
    <div class="ksub"><?= $nWait ? '<b>' . $nWait . '</b> مستنيين رد منك' : 'كلهم اتردّ عليهم' ?></div>
  </a>
  <?php if ($crm): ?>
  <a class="kpi card <?= $fuAll ? 'on-alert t-red' : 't-cyan' ?>" href="leads.php?f=followups">
    <div class="kpi-top"><span class="kpi-label">متابعات النهارده والمتأخرة</span><span class="kchip <?= $fuAll ? 'red' : 'cyan' ?>"><?= vgd_ic($P['bell']) ?></span></div>
    <div class="kval"><?= $fuAll ?></div>
    <div class="ksub"><?= $fuAll ? 'مكالمات ورسايل لازم تتعمل' : 'مفيش متابعات النهارده' ?></div>
  </a>
  <?php endif; ?>
  <a class="kpi card <?= $nLow ? 'on-alert t-red' : 't-amber' ?>" href="inventory.php?f=low">
    <div class="kpi-top"><span class="kpi-label">مخزون منخفض</span><span class="kchip <?= $nLow ? 'red' : 'amber' ?>"><?= vgd_ic($P['alert']) ?></span></div>
    <div class="kval"><?= $nLow ?></div>
    <div class="ksub"><?= $nLow ? 'منتجات قربت تخلص، راجعها' : 'مفيش منتجات قربت تخلص' ?></div>
  </a>
  <?php if (!$crm): ?>
  <a class="kpi card t-cyan" href="customers.php">
    <div class="kpi-top"><span class="kpi-label">العملاء المسجّلون</span><span class="kchip cyan"><?= vgd_ic($P['users']) ?></span></div>
    <div class="kval"><?= (int)($cust['c'] ?? 0) ?></div>
    <div class="ksub"><?= (int)($custNew['c'] ?? 0) ? '<b>' . (int)$custNew['c'] . '</b> جداد في الفترة' : 'مفيش حسابات جديدة في الفترة' ?></div>
  </a>
  <?php endif; ?>
</div>
<div class="grid2">
  <div class="card">
    <div class="ch"><h3>العملاء المحتملين</h3><span>مخططات التغطية ورسايل التواصل</span></div>
    <?php if ($pf['total']): ?>
    <div class="mini">
      <div>مخطط تغطية<b><?= (int)$pf['coverage_plan'] ?></b></div>
      <div>رسالة تواصل<b><?= (int)$pf['contact'] ?></b></div>
      <div>متوسط وقت الرد<b><?= $respN ? vgd_h(vg_age_ar((int)round($respSum / $respN))) : '—' ?></b></div>
    </div>
    <div class="funnel">
      <div class="fstep"><span>وصلوا</span><span class="tr"><i style="width:100%"></i></span><b><?= $pf['total'] ?></b></div>
      <div class="fstep"><span>اتردّ عليهم</span><span class="tr"><i style="width:<?= $pct($pf['replied']) ?>%"></i></span><b><?= $pf['replied'] ?></b></div>
      <div class="fstep won"><span>اتحولوا لطلبات</span><span class="tr"><i style="width:<?= $pct($pf['won']) ?>%"></i></span><b><?= $pf['won'] ?></b></div>
    </div>
    <p class="note-sm"><?= $pf['total'] ? 'نسبة التحويل <b>' . $pct($pf['won']) . '%</b>. بنعدّ العميل اتحوّل لو عمل طلب بنفس رقم الموبايل بعدها.' : '' ?><?= !$crm ? ' وقت الرد هيظهر بعد تفعيل المتابعات.' : '' ?></p>
    <?php else: ?>
      <div class="empty"><?= vgd_ic($P['target']) ?><p>مفيش طلبات معاينة أو رسايل تواصل في الفترة دي.</p></div>
    <?php endif; ?>
  </div>
  <div class="card">
    <div class="ch"><h3>مراحل العملاء المحتملين</h3><span><?= vgd_h($rangeOpts[$r]) ?></span></div>
    <?php if ($pf['total']): ?>
    <div class="pipe">
    <?php foreach ($stageLabels as $sk => $sl): $n = $stage[$sk]; $sh = $pct($n); ?>
      <div class="pstage">
        <?php $isPs = in_array($sk, ['survey', 'quote', 'install'], true); ?>
        <div class="pst"><?php if ($isPs): ?><a class="badge <?= $stageBadge[$sk] ?>" href="leads.php?type=prospects&amp;stage=<?= $sk ?>"><?= vgd_h($sl) ?></a><?php else: ?><span class="badge <?= $stageBadge[$sk] ?>"><?= vgd_h($sl) ?></span><?php endif; ?><b><?= $n ?></b><small><?= $sh ?>%</small></div>
        <div class="ptr"><i class="<?= $sk === 'won' ? 'delivered' : ($sk === 'lost' ? 'cancelled' : $sk) ?>" style="width:<?= $sh ?>%"></i></div>
        <small class="pv"><?= $stageSurvey[$sk] ? 'منهم ' . $stageSurvey[$sk] . ' طلب معاينة' : '—' ?><?= $stageVal[$sk] > 0 ? ' · ' . number_format($stageVal[$sk], 0) . ' ج.م' : '' ?></small>
      </div>
    <?php endforeach; ?>
    </div>
    <?php if ($stale): ?><div class="stale"><?= vgd_ic($P['alert'], 'kic') ?><span><b><?= $stale ?></b> عميل اتردّ عليهم من أكتر من 7 أيام ومفيش ميعاد متابعة. حدّد لهم متابعة قبل ما يبردوا.</span></div><?php endif; ?>
    <?php else: ?>
      <div class="empty"><?= vgd_ic($P['target']) ?><p>مفيش عملاء محتملين في الفترة دي.</p></div>
    <?php endif; ?>
  </div>
  <div class="card tw">
    <div class="ch"><h3>مستنيين رد</h3><?php if ($nWait): ?><a class="btn sm secondary" href="leads.php?type=prospects&amp;status=new">عرض الكل (<?= $nWait ?>)</a><?php endif; ?></div>
    <?php if ($waitList): ?>
      <?php foreach ($waitList as $w): $age = max(0, $dbNow - strtotime($w['created_at'])); $wa = $canContact ? vg_wa_url($w['phone']) : ''; ?>
        <div class="prow">
          <div class="who"><b><?= vgd_h($w['name'] ?: '—') ?></b><small><?= vgd_h($typeLabels[$w['type']] ?? $w['type']) ?> · <span class="<?= $age > 86400 ? 'late' : '' ?>">من <?= vgd_h(vg_age_ar($age)) ?></span></small></div>
          <div class="act"><?php if ($wa): ?><a class="btn sm secondary" target="_blank" rel="noopener" href="<?= vgd_h($wa) ?>">واتساب</a><?php endif; ?></div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty"><?= vgd_ic($P['inbox']) ?><p>مفيش حد مستني رد. كل العملاء المحتملين اتردّ عليهم.</p></div>
    <?php endif; ?>
  </div>
  <div class="card tw">
    <div class="ch"><h3>متابعات النهارده</h3><?php if ($crm && $fuAll): ?><a class="btn sm secondary" href="leads.php?f=followups">عرض الكل (<?= $fuAll ?>)</a><?php endif; ?></div>
    <?php if (!$crm): ?>
      <div class="empty"><?= vgd_ic($P['bell']) ?><p>المتابعات محتاجة تحديث بسيط لقاعدة البيانات. شغّل ملف <b dir="ltr">migration-followups.sql</b> مرة واحدة من phpMyAdmin بعد ما تاخد نسخة احتياطية، وبعدها تقدر تحط ميعاد متابعة وملاحظة لكل عميل.</p></div>
    <?php elseif ($fu): ?>
      <?php foreach ($fu as $f): $isLate = substr((string)$f['followup_at'], 0, 10) < $todayD; $wa = $canContact ? vg_wa_url($f['phone']) : ''; ?>
        <div class="prow">
          <div class="who"><b><?= vgd_h($f['name'] ?: '—') ?> <span class="badge"><?= vgd_h($typeLabels[$f['type']] ?? $f['type']) ?></span></b><small><span class="<?= $isLate ? 'late' : '' ?>"><?= $isLate ? 'متأخرة من ' . vgd_h(date('d/m', strtotime($f['followup_at']))) : 'النهارده' ?></span><?= $f['followup_note'] !== null && $f['followup_note'] !== '' ? ' · ' . vgd_h($f['followup_note']) : '' ?></small></div>
          <div class="act"><?php if ($wa): ?><a class="btn sm secondary" target="_blank" rel="noopener" href="<?= vgd_h($wa) ?>">واتساب</a><?php endif; ?><a class="btn sm" href="leads.php?f=followups#l<?= (int)$f['id'] ?>">فتح</a></div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty"><?= vgd_ic($P['bell']) ?><p>مفيش متابعات النهارده. تقدر تحط ميعاد متابعة لأي عميل من صفحة الطلبات والرسائل.</p></div>
    <?php endif; ?>
  </div>
  <div class="card">
    <div class="ch"><h3><?= vgd_h($chartTitle) ?></h3><span><?= number_format($chartTotal, 0) ?> ج.م</span></div>
    <div class="bars<?= count($series) > 14 ? ' dense' : '' ?>">
      <?php foreach ($series as $s): ?>
        <div class="bar <?= $s[1] > 0 ? '' : 'zero' ?>" title="<?= vgd_h($s[0]) ?>: <?= number_format($s[1], 0) ?> ج.م (<?= $s[2] ?> طلب)">
          <span><?= $s[1] > 0 ? number_format($s[1], 0) : '' ?></span>
          <i style="height:<?= max(3, round($s[1] / $max * 100)) ?>%"></i>
          <span><?= vgd_h(date('d/m', strtotime($s[0]))) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card">
    <div class="ch"><h3>خط الطلبات</h3><span><?= vgd_h($rangeOpts[$r]) ?></span></div>
    <div class="pipe">
    <?php foreach ($labels as $k => $lb): $n = (int)($byStatus[$k] ?? 0); $v = (float)($byStatusSum[$k] ?? 0); $sh = $pipeTotal > 0 ? round($n / $pipeTotal * 100) : 0; ?>
      <div class="pstage">
        <div class="pst"><span class="badge <?= $k ?>"><?= $lb ?></span><b><?= $n ?></b><small><?= $sh ?>%</small></div>
        <div class="ptr"><i class="<?= $k ?>" style="width:<?= $sh ?>%"></i></div>
        <small class="pv"><?= $v > 0 ? number_format($v, 0) . ' ج.م' : '—' ?></small>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
  <div class="card tw">
    <div class="ch"><h3>الأكثر مبيعاً</h3><span><?= vgd_h($rangeOpts[$r]) ?></span></div>
    <?php if ($top): ?>
    <table><thead><tr><th>المنتج</th><th>الكمية</th><th>القيمة</th></tr></thead><tbody>
      <?php foreach ($top as $t): ?>
        <tr><td><?= vgd_h($t['name_ar']) ?></td><td><?= (int)$t['q'] ?></td><td><?= $t['v'] > 0 ? number_format((float)$t['v'], 0) . ' ج.م' : '—' ?></td></tr>
      <?php endforeach; ?>
    </tbody></table>
    <?php else: ?>
      <div class="empty"><?= vgd_ic($P['receipt']) ?><p>مفيش مبيعات منتجات في الفترة دي.</p><a class="btn sm secondary" href="inventory.php">فتح المخزون</a></div>
    <?php endif; ?>
  </div>
  <div class="card tw">
    <div class="ch"><h3>تنبيهات المخزون</h3><a class="btn sm secondary" href="inventory.php">فتح المخزون</a></div>
    <?php if ($lowList): ?><div style="font-size:12.5px;color:var(--muted);margin-bottom:6px">عرض <?= count($lowList) ?> من <?= $nLow ?> منتج</div>
      <?php foreach ($lowList as $p): ?>
        <div class="srow"><span><?= vgd_h($p['name_ar']) ?></span><span class="badge <?= (int)$p['stock_qty'] <= 0 ? 'cancelled' : 'contacted' ?>"><?= (int)$p['stock_qty'] <= 0 ? 'نفد' : (int)$p['stock_qty'] . ' متبقي' ?></span></div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty"><?= vgd_ic($P['truck']) ?><p>المخزون في وضع كويس، ومفيش منتجات قربت تخلص.</p></div>
    <?php endif; ?>
  </div>
</div>
<div class="card tw">
  <div class="ch"><h3>آخر الطلبات</h3><a class="btn sm secondary" href="leads.php?status=all">عرض الكل</a></div>
  <table><thead><tr><th>رقم الطلب</th><th>العميل</th><th>الإجمالي</th><th>الحالة</th><th>التاريخ</th></tr></thead><tbody>
    <?php foreach ($recent as $ro): ?>
      <tr>
        <td><a href="order.php?id=<?= (int)$ro['id'] ?>">#<?= (int)$ro['id'] ?></a></td>
        <td><?= vgd_h($ro['name']) ?></td>
        <td><?= number_format((float)$ro['total'], 0) ?> ج.م</td>
        <td><span class="badge <?= vgd_h($ro['status']) ?>"><?= vgd_h($labels[$ro['status']] ?? $ro['status']) ?></span></td>
        <td><?= vgd_h(date('d/m/Y H:i', strtotime($ro['created_at']))) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$recent): ?><tr><td colspan="5" style="color:var(--muted)">لسه مفيش طلبات. أول طلب هيظهر هنا تلقائياً.</td></tr><?php endif; ?>
  </tbody></table>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
