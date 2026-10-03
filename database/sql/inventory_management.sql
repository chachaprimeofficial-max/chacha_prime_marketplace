-- Chacha Prime Inventory Management
-- Manual SQL import; no Laravel migration files.
CREATE TABLE IF NOT EXISTS inventory_movements (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 product_id BIGINT UNSIGNED NOT NULL,
 variant_id BIGINT UNSIGNED NULL,
 type ENUM('stock_in','stock_out','adjustment','reservation','release','return','cancel_restore') NOT NULL,
 quantity INT NOT NULL,
 quantity_before INT NOT NULL,
 quantity_after INT NOT NULL,
 reference_type VARCHAR(60) NULL,
 reference_id BIGINT UNSIGNED NULL,
 note VARCHAR(500) NULL,
 created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 PRIMARY KEY(id),
 KEY inventory_product_index(product_id),
 KEY inventory_variant_index(variant_id),
 KEY inventory_reference_index(reference_type,reference_id),
 KEY inventory_created_index(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE products
 ADD COLUMN IF NOT EXISTS stock_qty INT NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS reserved_qty INT NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS low_stock_threshold INT NOT NULL DEFAULT 5;

CREATE TABLE IF NOT EXISTS inventory_alerts (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 product_id BIGINT UNSIGNED NOT NULL,
 variant_id BIGINT UNSIGNED NULL,
 alert_type ENUM('low_stock','out_of_stock') NOT NULL,
 status ENUM('open','resolved') NOT NULL DEFAULT 'open',
 created_at TIMESTAMP NULL,
 resolved_at TIMESTAMP NULL,
 PRIMARY KEY(id),
 KEY inventory_alert_product_index(product_id),
 KEY inventory_alert_status_index(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
