<?php
/** Keeps a JSON copy of anything deleted from the admin panel so it can be restored by hand. */
function vg_trash_put(string $kind, int $refId, array $row): void {
    try {
        $pdo = db();
        $pdo->exec('CREATE TABLE IF NOT EXISTS vg_trash (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, kind VARCHAR(20) NOT NULL, ref_id INT NOT NULL, data MEDIUMTEXT NOT NULL, deleted_by VARCHAR(120) NOT NULL, deleted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY idx_trash_kind (kind, ref_id)) DEFAULT CHARSET=utf8mb4');
        $u = function_exists('current_admin') ? current_admin() : null;
        $by = is_array($u) ? (string)($u['username'] ?? $u['name'] ?? $u['email'] ?? '') : '';
        $pdo->prepare('INSERT INTO vg_trash (kind, ref_id, data, deleted_by) VALUES (?, ?, ?, ?)')
            ->execute([$kind, $refId, json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), mb_substr($by, 0, 120)]);
    } catch (Throwable $e) {
        error_log('Vision Guard trash failed: ' . $e->getMessage());
    }
}
