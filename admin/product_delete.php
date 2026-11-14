<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perms.php'; require_perm('products_delete');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        delete_product_image($id);
        flash_set('اتحذف المنتج.');
    }
}
header('Location: products.php');
exit;
