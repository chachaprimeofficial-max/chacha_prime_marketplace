-- Chacha Prime supplier/import audit log
CREATE TABLE IF NOT EXISTS scraper_imports (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 source VARCHAR(80) NOT NULL,
 source_url VARCHAR(1000) NULL,
 status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
 raw_data JSON NULL,
 error_message TEXT NULL,
 created_at DATETIME NULL,
 updated_at DATETIME NULL,
 INDEX idx_scraper_import_source_status(source,status),
 INDEX idx_scraper_import_created(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;