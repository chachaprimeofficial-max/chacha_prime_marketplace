-- Chacha Prime payment provider configuration
-- Manual SQL import; never store secret keys in this table. Use environment secrets.
CREATE TABLE IF NOT EXISTS payment_provider_configs (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 provider VARCHAR(60) NOT NULL,
 display_name VARCHAR(120) NOT NULL,
 enabled TINYINT(1) NOT NULL DEFAULT 0,
 mode ENUM('sandbox','live') NOT NULL DEFAULT 'sandbox',
 sort_order INT NOT NULL DEFAULT 0,
 settings_json JSON NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 PRIMARY KEY(id),
 UNIQUE KEY payment_provider_unique(provider),
 KEY payment_provider_enabled_index(enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO payment_provider_configs(provider,display_name,enabled,mode,sort_order,created_at,updated_at)
VALUES ('manual','Manual / COD',1,'live',1,NOW(),NOW())
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name),updated_at=NOW();
