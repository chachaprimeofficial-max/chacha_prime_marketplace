-- Chacha Prime payment refund ledger
-- Manual SQL import; no Laravel migration file.
CREATE TABLE IF NOT EXISTS payment_refunds (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 payment_id BIGINT UNSIGNED NOT NULL,
 amount DECIMAL(18,2) NOT NULL,
 reason VARCHAR(500) NULL,
 status ENUM('pending','completed','failed') NOT NULL DEFAULT 'completed',
 created_at DATETIME NULL,
 updated_at DATETIME NULL,
 PRIMARY KEY(id),
 KEY payment_refunds_payment(payment_id,status),
 CONSTRAINT fk_payment_refunds_payment FOREIGN KEY(payment_id) REFERENCES payments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
