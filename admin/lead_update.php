<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('leads_status');
require_once __DIR__ . '/../api/_stock.php';
require_once __DIR__ . '/includes/trash.php';
require_once __DIR__ . '/includes/crm.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        if (isset($_POST['payment_status']) && $_POST['payment_status'] === 'paid' && can('leads')) {
            $q = db()->prepare("UPDATE leads SET payment_status = 'paid' WHERE id = ? AND type = 'order' AND payment_method IN ('instapay','ewallet') AND payment_status = 'pending'");
            $q->execute([$id]);
            if ($q->rowCount() === 1) { flash_set('تم تأكيد استلام التحويل.'); } else { flash_set('لم يتم التحديث: تأكد أن الطلب تحويل معلّق ولم يتم تأكيده من قبل.'); }
        }
        if (!empty($_POST['followup']) && vg_crm_ready()) {
            // Follow-up date + note. "تمت المتابعة" clears the date and keeps the note.
            $fd = (string)($_POST['followup_at'] ?? '');
            $fd = (!empty($_POST['followup_done']) || !preg_match('/^\d{4}-\d\d-\d\d$/', $fd)) ? null : $fd . ' 00:00:00';
            $fn = trim((string)($_POST['followup_note'] ?? ''));
            $fn = $fn === '' ? null : mb_substr($fn, 0, 500);
            db()->prepare('UPDATE leads SET followup_at = ?, followup_note = ? WHERE id = ?')->execute([$fd, $fn, $id]);
            flash_set(!empty($_POST['followup_done']) ? 'تمام، اتشالت المتابعة.' : ($fd ? 'اتحفظ ميعاد المتابعة.' : 'اتحفظت الملاحظة.'));
        }
        if (!empty($_POST['project']) && vg_projects_ready()) {
            // Site survey -> quote -> install, for prospects only. Moving into a stage means the lead was contacted.
            $ps = (string)($_POST['project_stage'] ?? '');
            $ps = isset(vg_project_stages()[$ps]) ? $ps : null;
            $qv = str_replace(',', '', trim((string)($_POST['quote_value'] ?? '')));
            $qv = ($qv !== '' && is_numeric($qv) && $qv >= 0 && $qv < 100000000) ? round((float)$qv, 2) : null;
            db()->prepare("UPDATE leads SET project_stage = ?, quote_value = ? WHERE id = ? AND type IN ('coverage_plan','contact')")->execute([$ps, $qv, $id]);
            if ($ps !== null) {
                if (vg_crm_ready()) { try { db()->prepare("UPDATE leads SET contacted_at = CURRENT_TIMESTAMP WHERE id = ? AND contacted_at IS NULL AND status = 'new'")->execute([$id]); } catch (Throwable $e) { error_log('leads.contacted_at: ' . $e->getMessage()); } }
                db()->prepare("UPDATE leads SET status = 'contacted' WHERE id = ? AND status = 'new' AND type IN ('coverage_plan','contact')")->execute([$id]);
            }
            flash_set($ps === null ? 'اتشالت مرحلة المشروع.' : 'اتحفظت مرحلة المشروع: ' . vg_project_stages()[$ps] . '.');
        }
        if (isset($_POST['assigned_to']) && can('leads')) { db()->prepare('UPDATE leads SET assigned_to = ? WHERE id = ?')->execute([((int)$_POST['assigned_to']) ?: null, $id]); }
        if (!empty($_POST['delete']) && can('orders_delete')) {
            // Deleting an order must not lose reserved units.
            vg_stock_release_lead($id);
            $vgrs = db()->prepare('SELECT * FROM leads WHERE id = ?'); $vgrs->execute([$id]); $vgrow = $vgrs->fetch(PDO::FETCH_ASSOC); if ($vgrow) vg_trash_put('lead', $id, $vgrow);
            db()->prepare('DELETE FROM leads WHERE id = ?')->execute([$id]);
        } elseif (!empty($_POST['status'])) {
            try { $vgc = db()->query("SHOW COLUMNS FROM leads LIKE 'status'")->fetch(); if ($vgc && stripos($vgc['Type'], 'enum') === 0 && stripos($vgc['Type'], 'delivered') === false) { db()->exec("ALTER TABLE leads MODIFY status VARCHAR(20) NOT NULL DEFAULT 'new'"); } } catch (Throwable $e) { error_log('leads.status migrate: ' . $e->getMessage()); }
$allowed = ['new', 'contacted', 'delivered', 'closed', 'cancelled'];
            if (in_array($_POST['status'], $allowed, true)) {
                if ($_POST['status'] === 'cancelled' || $_POST['status'] === 'new') {
                    vg_stock_release_lead($id);
                } elseif ($_POST['status'] === 'contacted' || $_POST['status'] === 'delivered') {
                    $vgc = vg_stock_confirm_lead($id);
                    if (!empty($vgc['skipped'])) flash_set('الطلب اتأكد، لكن الكمية مش كفاية فمخصمتش من المخزون: ' . implode('، ', $vgc['skipped']));
                }
                // A lead moving out of "new" for the first time = first reply, for the dashboard's average response time.
                // Runs before the status update so it only matches leads that are still "new".
                if ($_POST['status'] !== 'new' && vg_crm_ready()) { try { db()->prepare("UPDATE leads SET contacted_at = CURRENT_TIMESTAMP WHERE id = ? AND contacted_at IS NULL AND status = 'new'")->execute([$id]); } catch (Throwable $e) { error_log('leads.contacted_at: ' . $e->getMessage()); } }
                db()->prepare('UPDATE leads SET status = ? WHERE id = ?')->execute([$_POST['status'], $id]);
            }
        }
    }
}
$vgBack = 'leads.php'; $vgRef = parse_url((string)($_SERVER['HTTP_REFERER'] ?? '')); if (!empty($vgRef['path']) && substr($vgRef['path'], -10) === '/leads.php') { $vgBack = 'leads.php' . (isset($vgRef['query']) ? '?' . $vgRef['query'] : ''); }
header('Location: ' . $vgBack);
exit;
