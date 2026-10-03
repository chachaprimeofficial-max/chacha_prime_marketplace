-- Chacha Prime coupon management
-- Manual SQL import; no Laravel migration file.
CREATE TABLE IF NOT EXISTS coupons (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 code VARCHAR(80) NOT NULL,
 type ENUM('fixed','percent') NOT NULL,
 value DECIMAL(18,2) NOT NULL,
 min_order_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
 max_uses INT UNSIGNED NULL,
 used_count INT UNSIGNED NOT NULL DEFAULT 0,
 starts_at DATETIME NULL,
 expires_at DATETIME NULL,
 status ENUM('active','inactive') NOT NULL DEFAULT 'active',
 created_at DATETIME NULL,
 updated_at DATETIME NULL,
 PRIMARY KEY(id),
 UNIQUE KEY coupons_code_unique(code),
 KEY coupons_status_dates(status,starts_at,expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
