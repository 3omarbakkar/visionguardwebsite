-- Promo codes shown on the home banner and typed at checkout.
-- Not deployed (*.sql is excluded from the deploy). Run once on the server.
-- Safe to re-run: the table is created only if missing, and the seed row is inserted only if absent.

CREATE TABLE IF NOT EXISTS vg_promo_codes (
  code VARCHAR(32) NOT NULL PRIMARY KEY,
  percent TINYINT UNSIGNED NOT NULL,
  ends_at DATE NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  max_uses INT UNSIGNED NULL,
  used_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO vg_promo_codes (code, percent, ends_at, active, max_uses)
VALUES ('VG10', 10, '2026-11-30', 1, NULL);
