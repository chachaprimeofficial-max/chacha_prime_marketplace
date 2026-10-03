-- Chacha Prime payment refund ledger
-- Manual SQL import; no Laravel migration file.
CREATE TABLE IF NOT EXISTS payment_refunds (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 payment_id BIGINT UNSIGNED NOT NULL,
 amount DECIMAL(18,2) NOT NULL,
 provider_refund_id VARCHAR(190) NULL,
 reason VARCHAR(500) NULL,
 status ENUM('pending','completed','failed') NOT NULL DEFAULT 'completed',
 created_at DATETIME NULL,
 updated_at DATETIME NULL,
 PRIMARY KEY(id),
 KEY payment_refunds_payment(payment_id,status),
 KEY payment_refunds_provider(provider_refund_id),
 CONSTRAINT fk_payment_refunds_payment FOREIGN KEY(payment_id) REFERENCES payments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE payment_refunds ADD COLUMN IF NOT EXISTS provider_refund_id VARCHAR(190) NULL;
ALTER TABLE payment_refunds ADD KEY IF NOT EXISTS payment_refunds_provider(provider_refund_id);
