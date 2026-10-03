-- Chacha Prime B2B Business Verification
-- Manual SQL import; no Laravel migrations.
CREATE TABLE IF NOT EXISTS business_verifications (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 business_name VARCHAR(255) NOT NULL,
 business_email VARCHAR(190) NOT NULL,
 country VARCHAR(100) NOT NULL,
 business_address TEXT NOT NULL,
 identity_type ENUM('passport','national_id','driving_license') NOT NULL,
 identity_document_path VARCHAR(500) NOT NULL,
 status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
 reviewed_by BIGINT UNSIGNED NULL,
 reviewed_at DATETIME NULL,
 created_at DATETIME NULL,
 updated_at DATETIME NULL,
 UNIQUE KEY uq_business_verification_user(user_id),
 KEY idx_business_verification_status(status),
 CONSTRAINT fk_business_verification_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_business_verification_reviewer FOREIGN KEY(reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
