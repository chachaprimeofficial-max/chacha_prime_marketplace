CREATE TABLE IF NOT EXISTS payment_refunds (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 payment_id BIGINT UNSIGNED NOT NULL,
 amount DECIMAL(14,2) NOT NULL,
 reason VARCHAR(500) NULL,
 status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 PRIMARY KEY(id), KEY payment_refund_payment_index(payment_id), KEY payment_refund_status_index(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE payments ADD COLUMN IF NOT EXISTS transaction_reference VARCHAR(191) NULL, ADD COLUMN IF NOT EXISTS failure_reason VARCHAR(500) NULL, ADD COLUMN IF NOT EXISTS paid_at TIMESTAMP NULL;
