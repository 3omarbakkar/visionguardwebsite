<?php
// Promo code rules. A code is valid when it is active, not expired and under its use limit.
// Shared by api/promo.php (checkout preview) and api/lead.php (the authoritative price).

/** Returns the discount percent for a valid code, or 0 when the code is not usable. */
function vg_promo_percent($pdo, $code) {
    $code = strtoupper(trim((string)$code));
    if ($code === '') return 0;
    $st = $pdo->prepare('SELECT percent FROM vg_promo_codes WHERE code = ? AND active = 1 AND (ends_at IS NULL OR ends_at >= CURDATE()) AND (max_uses IS NULL OR used_count < max_uses)');
    $st->execute([$code]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ? max(0, min(90, (int)$row['percent'])) : 0;
}

/** Codes currently live, for the home banner. Returns null when none. */
function vg_promo_live($pdo) {
    $row = $pdo->query('SELECT code, percent FROM vg_promo_codes WHERE active = 1 AND (ends_at IS NULL OR ends_at >= CURDATE()) AND (max_uses IS NULL OR used_count < max_uses) ORDER BY percent DESC, code LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}
