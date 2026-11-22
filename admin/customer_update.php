<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('customers_manage');
require_once __DIR__ . '/includes/trash.php';

$id = 0;
$action = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');
    if ($id > 0) {
        $pdo = db();
        try {
            $s = $pdo->prepare('SELECT * FROM customers WHERE id = ?');
            $s->execute([$id]);
            $c = $s->fetch(PDO::FETCH_ASSOC);
            if (!$c) {
                flash_set('العميل غير موجود.');
            } elseif ($action === 'update') {
                $name = trim((string)($_POST['name'] ?? ''));
                $phone = preg_replace('/[^\d+]/', '', (string)($_POST['phone'] ?? ''));
                if ($phone === '') $phone = (string)$c['phone'];
                if ($name === '' || mb_strlen($name) > 120) {
                    flash_set('الاسم مطلوب (حتى 120 حرف).');
                } else {
                    $d = $pdo->prepare('SELECT id FROM customers WHERE phone = ? AND id <> ?');
                    $d->execute([$phone, $id]);
                    if ($d->fetch()) {
                        flash_set('الرقم ده مسجّل لعميل تاني.');
                    } else {
                        $pdo->prepare('UPDATE customers SET name = ?, phone = ? WHERE id = ?')->execute([$name, $phone, $id]);
                        flash_set('تم حفظ بيانات العميل.');
                    }
                }
            } elseif ($action === 'delete') {
                unset($c['pass_hash']);
                vg_trash_put('customer', $id, $c);
                $pdo->beginTransaction();
                $pdo->prepare('UPDATE leads SET customer_id = NULL WHERE customer_id = ?')->execute([$id]);
                $pdo->prepare('DELETE FROM customers WHERE id = ?')->execute([$id]);
                $pdo->commit();
                flash_set('تم حذف حساب العميل. طلباته فضلت محفوظة.');
                header('Location: customers.php');
                exit;
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('customer_update: ' . $e->getMessage());
            flash_set('حصلت مشكلة ومحصلش تغيير.');
        }
    }
}
header('Location: customers.php' . ($id > 0 && $action === 'update' ? '?id=' . $id : ''));
exit;
