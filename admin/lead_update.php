<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('leads_status');
require_once __DIR__ . '/../api/_stock.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        if (isset($_POST['payment_status']) && $_POST['payment_status'] === 'paid' && can('leads')) {
            $q = db()->prepare("UPDATE leads SET payment_status = 'paid' WHERE id = ? AND type = 'order' AND payment_method IN ('instapay','ewallet') AND payment_status = 'pending'");
            $q->execute([$id]);
            if ($q->rowCount() === 1) { flash_set('تم تأكيد استلام التحويل.'); } else { flash_set('لم يتم التحديث: تأكد أن الطلب تحويل معلّق ولم يتم تأكيده من قبل.'); }
        }
        if (isset($_POST['assigned_to']) && can('leads')) { db()->prepare('UPDATE leads SET assigned_to = ? WHERE id = ?')->execute([((int)$_POST['assigned_to']) ?: null, $id]); }
        if (!empty($_POST['delete']) && can('orders_delete')) {
            // Deleting an order must not lose reserved units.
            vg_stock_release_lead($id);
            db()->prepare('DELETE FROM leads WHERE id = ?')->execute([$id]);
        } elseif (!empty($_POST['status'])) {
            try { $vgc = db()->query("SHOW COLUMNS FROM leads LIKE 'status'")->fetch(); if ($vgc && stripos($vgc['Type'], 'enum') === 0 && stripos($vgc['Type'], 'delivered') === false) { db()->exec("ALTER TABLE leads MODIFY status VARCHAR(20) NOT NULL DEFAULT 'new'"); } } catch (Throwable $e) { error_log('leads.status migrate: ' . $e->getMessage()); }
$allowed = ['new', 'contacted', 'delivered', 'closed', 'cancelled'];
            if (in_array($_POST['status'], $allowed, true)) {
                if ($_POST['status'] === 'cancelled') vg_stock_release_lead($id);
                db()->prepare('UPDATE leads SET status = ? WHERE id = ?')->execute([$_POST['status'], $id]);
            }
        }
    }
}
header('Location: leads.php');
exit;
