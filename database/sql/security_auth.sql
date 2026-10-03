-- Chacha Prime authentication and account security
-- Manual SQL import; no Laravel migrations.
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS remember_token VARCHAR(100) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_secret TEXT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_recovery_codes TEXT NULL;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
 email VARCHAR(255) NOT NULL,
 token VARCHAR(255) NOT NULL,
 created_at TIMESTAMP NULL,
 PRIMARY KEY(email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_security_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 event VARCHAR(80) NOT NULL,
 ip_address VARCHAR(45) NULL,
 user_agent VARCHAR(1000) NULL,
 created_at TIMESTAMP NULL,
 KEY security_user(user_id,created_at),
 CONSTRAINT fk_login_security_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;