<?php
/**
 * Vision Guard - roles & permissions.
 * Roles: admin (everything), sales, inventory, tech, employee (legacy: attendance + order status).
 * Per-user denies (column admin_users.perm_deny, comma list) can switch off
 * individual sub-permissions such as customer_contact or products_price.
 */
require_once __DIR__ . '/auth.php';

function vg_roles() {
    return [
        'admin'     => ['label' => 'مدير',                 'home' => 'index.php'],
        'sales'     => ['label' => 'مبيعات / خدمة عملاء',  'home' => 'leads.php'],
        'inventory' => ['label' => 'مخزن / منتجات',        'home' => 'products.php'],
        'tech'      => ['label' => 'فني / تركيب',          'home' => 'staff_orders.php'],
        'employee'  => ['label' => 'موظف (حضور فقط)',      'home' => 'attendance.php'],
    ];
}

function vg_role_perms() {
    return [
        'sales'     => ['leads', 'leads_status', 'customers', 'customer_contact', 'attendance_self'],
        'inventory' => ['products', 'products_price', 'attendance_self'],
        'tech'      => ['staff_orders', 'customer_contact', 'attendance_self'],
        'employee'  => ['attendance_self'],
    ];
}

/** Sub-permissions the manager can switch off per user. */
function vg_sub_perms() {
    return [
        'customer_contact' => 'إخفاء رقم العميل وعنوانه',
        'products_price'   => 'منع تعديل الأسعار',
    ];
}

function vg_denied() {
    static $d = null;
    if ($d !== null) return $d;
    $d = [];
    $a = current_admin();
    if ($a && (($a['role'] ?? 'admin') !== 'admin')) {
        try {
            $s = db()->prepare('SELECT perm_deny FROM admin_users WHERE id = ?');
            $s->execute([(int)$a['id']]);
            $v = (string)$s->fetchColumn();
            foreach (explode(',', $v) as $x) { $x = trim($x); if ($x !== '') $d[] = $x; }
        } catch (Throwable $e) { /* column not created yet */ }
    }
    return $d;
}

function can($perm) {
    $a = current_admin();
    if (!$a) return false;
    $r = $a['role'] ?? 'admin';
    if ($r === 'admin') return true;
    $map = vg_role_perms();
    if (!isset($map[$r]) || !in_array($perm, $map[$r], true)) return false;
    return !in_array($perm, vg_denied(), true);
}

function require_perm($perm) {
    require_login();
    if (!can($perm)) {
        http_response_code(403);
        die('<!doctype html><meta charset="utf-8"><body style="font-family:sans-serif;padding:40px;direction:rtl">ليس لديك صلاحية لهذه الصفحة. <a href="logout.php">تسجيل الخروج</a></body>');
    }
}

function role_label($r) {
    $m = vg_roles();
    return isset($m[$r]) ? $m[$r]['label'] : $r;
}

function role_home($r) {
    $m = vg_roles();
    return isset($m[$r]) ? $m[$r]['home'] : 'attendance.php';
}
