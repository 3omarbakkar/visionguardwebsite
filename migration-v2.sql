CREATE TABLE IF NOT EXISTS vg_stock_reservations (
  lead_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  qty INT NOT NULL,
  created_at DATETIME NOT NULL,
  released_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (lead_id, product_id),
  KEY idx_stock_product (product_id),
  KEY idx_stock_released (released_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS vg_password_resets (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @vg_db = DATABASE();
SET @vg_sql = (SELECT IF(COUNT(*)=0, 'ALTER TABLE products ADD COLUMN stock_qty INT NULL DEFAULT NULL AFTER in_stock', 'SELECT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @vg_db AND TABLE_NAME = 'products' AND COLUMN_NAME = 'stock_qty');
PREPARE vg_stmt FROM @vg_sql;
EXECUTE vg_stmt;
DEALLOCATE PREPARE vg_stmt;
