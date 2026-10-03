-- Chacha Prime returns and refunds
-- Canonical return workflow. Manual SQL import; no Laravel migration file.
CREATE TABLE IF NOT EXISTS returns (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id BIGINT UNSIGNED NOT NULL,
 user_id BIGINT UNSIGNED NOT NULL,
 reason VARCHAR(190) NOT NULL,
 description TEXT NULL,
 evidence JSON NULL,
 status ENUM('requested','approved','rejected','received','inspected','refunded','closed','cancelled') NOT NULL DEFAULT 'requested',
 refund_method ENUM('original_payment','wallet') NULL,
 refund_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 requested_at TIMESTAMP NULL,
 approved_at TIMESTAMP NULL,
 refunded_at TIMESTAMP NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 KEY returns_user(user_id,created_at),
 KEY returns_order(order_id,status),
 CONSTRAINT fk_returns_order FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
 CONSTRAINT fk_returns_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS return_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 return_id BIGINT UNSIGNED NOT NULL,
 order_item_id BIGINT UNSIGNED NOT NULL,
 quantity DECIMAL(12,3) NOT NULL,
 reason VARCHAR(190) NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 KEY return_items_return(return_id),
 KEY return_items_order_item(order_item_id),
 CONSTRAINT fk_return_items_return FOREIGN KEY(return_id) REFERENCES returns(id) ON DELETE CASCADE,
 CONSTRAINT fk_return_items_order_item FOREIGN KEY(order_item_id) REFERENCES order_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS refund_transactions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 return_id BIGINT UNSIGNED NOT NULL,
 method ENUM('original_payment','wallet') NOT NULL,
 amount DECIMAL(14,2) NOT NULL,
 status ENUM('pending','processed','failed') NOT NULL DEFAULT 'pending',
 provider_reference VARCHAR(190) NULL,
 processed_at TIMESTAMP NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 KEY refund_return(return_id,status),
 CONSTRAINT fk_refund_transactions_return FOREIGN KEY(return_id) REFERENCES returns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;