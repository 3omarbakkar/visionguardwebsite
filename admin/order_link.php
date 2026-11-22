<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('customers_manage');

$id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $cid = (int)($_POST['customer_id'] ?? 0);
    if ($id > 0) {
        $pdo = db();
        try {
            $s = $pdo->prepare('SELECT id FROM leads WHERE id = ?');
            $s->execute([$id]);
            if (!$s->fetch()) {
                flash_set('الطلب غير موجود.');
                $id = 0;
            } elseif ($cid > 0) {
                $s = $pdo->prepare('SELECT id, name FROM customers WHERE id = ?');
                $s->execute([$cid]);
                $c = $s->fetch(PDO::FETCH_ASSOC);
                if (!$c) {
                    flash_set('العميل غير موجود.');
                } else {
                    $pdo->prepare('UPDATE leads SET customer_id = ? WHERE id = ?')->execute([$cid, $id]);
                    flash_set('تم ربط الطلب بحساب العميل: ' . $c['name']);
                }
            } else {
                $pdo->prepare('UPDATE leads SET customer_id = NULL WHERE id = ?')->execute([$id]);
                flash_set('تم فك ربط الطلب من حساب العميل.');
            }
        } catch (Throwable $e) {
            flash_set('حصل خطأ أثناء ربط الطلب.');
        }
    }
}
header('Location: ' . ($id > 0 ? 'order.php?id=' . $id : 'leads.php'));
exit;
