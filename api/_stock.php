<?php
// Shared stock helpers. Included by public pages, the order API and admin pages.
// A product with stock_qty = NULL is "not counted" and behaves exactly like before
// (availability comes from in_stock). A numeric stock_qty is enforced and reserved.

require_once __DIR__ . '/../admin/includes/db.php';

function vg_schema_v2(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = db();
    try {
        if (setting('vg_schema_v2') === '1') return;
    } catch (Throwable $e) {
        // settings table unavailable: fall through and try anyway
    }
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS vg_stock_reservations (
            lead_id INT UNSIGNED NOT NULL,
            product_id INT UNSIGNED NOT NULL,
            qty INT NOT NULL,
            created_at DATETIME NOT NULL,
            released_at DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (lead_id, product_id),
            KEY idx_stock_product (product_id),
            KEY idx_stock_released (released_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS vg_password_resets (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            customer_id INT UNSIGNED NOT NULL,
            phone_hash CHAR(64) NOT NULL,
            code_hash VARCHAR(255) NOT NULL,
            attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
            ip_hash CHAR(64) NOT NULL,
            created_at DATETIME NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL DEFAULT NULL,
            KEY idx_reset_phone (phone_hash, created_at),
            KEY idx_reset_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $has = $pdo->query("SHOW COLUMNS FROM products LIKE 'stock_qty'")->fetch();
        if (!$has) {
            $pdo->exec('ALTER TABLE products ADD COLUMN stock_qty INT NULL DEFAULT NULL AFTER in_stock');
        }
        $pdo->exec("INSERT INTO settings (meta_key, meta_value) VALUES ('vg_schema_v2', '1')
                    ON DUPLICATE KEY UPDATE meta_value = '1'");
    } catch (Throwable $e) {
        // Never break a page or an order because a DDL guard failed; the explicit
        // migration-v2.sql is the supported path. Log once per request.
        error_log('Vision Guard schema v2: ' . $e->getMessage());
    }
}

/** Remaining units, or null when this product is not quantity-tracked. */
function vg_stock_left(array $product): ?int {
    if (!array_key_exists('stock_qty', $product) || $product['stock_qty'] === null) return null;
    return max(0, (int)$product['stock_qty']);
}

/** True when the product can be ordered at all. */
function vg_stock_available(array $product): bool {
    if ((int)($product['in_stock'] ?? 0) !== 1) return false;
    $left = vg_stock_left($product);
    return $left === null || $left > 0;
}

/** Arabic label used on product/category pages. */
function vg_stock_label(array $product): string {
    if (!vg_stock_available($product)) return 'غير متوفر';
    $left = vg_stock_left($product);
    if ($left === null || $left > 3) return 'متوفر';
    if ($left === 1) return 'آخر قطعة';
    if ($left === 2) return 'آخر قطعتين';
    return 'آخر ' . $left . ' قطع';
}

/**
 * Reserve stock for an order. Must be called inside an open transaction, after the
 * product rows were read with FOR UPDATE. Returns nothing; throws on failure.
 */
function vg_stock_reserve(PDO $pdo, int $leadId, array $quantities): void {
    foreach ($quantities as $productId => $qty) {
        // Only tracked rows match: stock_qty IS NOT NULL. Untracked rows keep NULL.
        $u = $pdo->prepare('UPDATE products SET stock_qty = stock_qty - ? WHERE id = ? AND stock_qty IS NOT NULL AND stock_qty >= ?');
        $u->execute([(int)$qty, (int)$productId, (int)$qty]);
        if ($u->rowCount() === 1) {
            $pdo->prepare('INSERT INTO vg_stock_reservations (lead_id, product_id, qty, created_at) VALUES (?, ?, ?, NOW())')
                ->execute([$leadId, (int)$productId, (int)$qty]);
        }
    }
}

/**
 * Release every active reservation of an order and put the units back.
 * Safe to call more than once; only unreleased rows are restored.
 */
function vg_stock_release_lead(int $leadId): void {
    if ($leadId <= 0) return;
    vg_schema_v2();
    $pdo = db();
    try {
        $pdo->beginTransaction();
        $s = $pdo->prepare('SELECT product_id, qty FROM vg_stock_reservations WHERE lead_id = ? AND released_at IS NULL FOR UPDATE');
        $s->execute([$leadId]);
        $rows = $s->fetchAll();
        if ($rows) {
            $up = $pdo->prepare('UPDATE products SET stock_qty = stock_qty + ? WHERE id = ? AND stock_qty IS NOT NULL');
            foreach ($rows as $r) {
                $up->execute([(int)$r['qty'], (int)$r['product_id']]);
            }
            $pdo->prepare('UPDATE vg_stock_reservations SET released_at = NOW() WHERE lead_id = ? AND released_at IS NULL')
                ->execute([$leadId]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Vision Guard stock release failed for lead ' . $leadId . ': ' . $e->getMessage());
    }
}
