-- Chacha Prime wallet schema.
-- Safe standalone manual import; matches database/sql/01_schema.sql.
-- No Laravel migrations.

CREATE TABLE IF NOT EXISTS wallets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL UNIQUE,
  currency VARCHAR(10) NOT NULL DEFAULT 'USD',
  balance DECIMAL(18,2) NOT NULL DEFAULT 0,
  status ENUM('active','locked') NOT NULL DEFAULT 'active',
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wallet_ledger (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  wallet_id BIGINT UNSIGNED NOT NULL,
  reference_type VARCHAR(80) NULL,
  reference_id BIGINT UNSIGNED NULL,
  entry_type ENUM('credit','debit') NOT NULL,
  reason ENUM('refund','group_refund','bonus','coupon','purchase','adjustment','top_up','withdrawal') NOT NULL,
  amount DECIMAL(18,2) NOT NULL,
  balance_after DECIMAL(18,2) NOT NULL,
  description VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_wallet_ledger_wallet FOREIGN KEY(wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
  INDEX idx_wallet_ledger_wallet_created(wallet_id,created_at),
  INDEX idx_wallet_ledger_reference(reference_type,reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
