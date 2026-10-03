-- Chacha Prime Marketplace — canonical base manual-import SQL schema
-- Legacy returns_requests, shipping_shipments and invoices definitions were removed.
-- Import their canonical definitions from returns_refunds.sql, shipping_fulfillment.sql and invoices_tax.sql.
-- No Laravel migrations are used.
-- MySQL 8+ recommended.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(40) NULL,
  email_verified_at DATETIME NULL,
  two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0,
  two_factor_secret TEXT NULL,
  two_factor_confirmed_at DATETIME NULL,
  status ENUM('active','blocked','pending') NOT NULL DEFAULT 'active',
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_roles (
  user_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY(user_id, role_id),
  CONSTRAINT fk_user_roles_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_roles_role FOREIGN KEY(role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_profiles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL UNIQUE,
  country_code CHAR(2) NULL,
  date_of_birth DATE NULL,
  avatar_path VARCHAR(500) NULL,
  preferred_currency VARCHAR(10) NOT NULL DEFAULT 'USD',
  preferred_language VARCHAR(10) NOT NULL DEFAULT 'en',
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_customer_profiles_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS addresses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  type ENUM('billing','shipping','business') NOT NULL DEFAULT 'shipping',
  full_name VARCHAR(150) NOT NULL,
  phone VARCHAR(40) NULL,
  address_line_1 VARCHAR(255) NOT NULL,
  address_line_2 VARCHAR(255) NULL,
  city VARCHAR(100) NOT NULL,
  state VARCHAR(100) NULL,
  postal_code VARCHAR(30) NULL,
  country_code CHAR(2) NOT NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_addresses_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_addresses_user_type(user_id,type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_profiles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL UNIQUE,
  company_name VARCHAR(200) NOT NULL,
  business_type VARCHAR(120) NULL,
  country_code CHAR(2) NOT NULL,
  business_email VARCHAR(190) NULL,
  business_phone VARCHAR(40) NULL,
  business_address TEXT NULL,
  verification_status ENUM('pending','approved','rejected','more_info') NOT NULL DEFAULT 'pending',
  verified_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_business_profiles_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS business_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_profile_id BIGINT UNSIGNED NOT NULL,
  document_type ENUM('passport','national_id','driving_license') NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  reviewed_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_business_documents_profile FOREIGN KEY(business_profile_id) REFERENCES business_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS brands (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL UNIQUE,
  slug VARCHAR(180) NOT NULL UNIQUE,
  logo_path VARCHAR(500) NULL,
  description TEXT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parent_id BIGINT UNSIGNED NULL,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  description TEXT NULL,
  image_path VARCHAR(500) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  ai_generated TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_categories_parent FOREIGN KEY(parent_id) REFERENCES categories(id) ON DELETE SET NULL,
  INDEX idx_categories_parent(parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_code VARCHAR(80) NOT NULL UNIQUE,
  sku VARCHAR(100) NOT NULL UNIQUE,
  slug VARCHAR(220) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  short_description TEXT NULL,
  description LONGTEXT NULL,
  highlights JSON NULL,
  features JSON NULL,
  additional_details JSON NULL,
  brand_id BIGINT UNSIGNED NULL,
  category_id BIGINT UNSIGNED NULL,
  retail_price DECIMAL(18,2) NOT NULL DEFAULT 0,
  wholesale_price DECIMAL(18,2) NULL,
  group_price DECIMAL(18,2) NULL,
  cost_price DECIMAL(18,2) NULL,
  stock_qty INT NOT NULL DEFAULT 0,
  reserved_qty INT NOT NULL DEFAULT 0,
  low_stock_threshold INT NOT NULL DEFAULT 5,
  return_window_business_days INT NOT NULL DEFAULT 7,
  shipping_template_id BIGINT UNSIGNED NULL,
  ship_from VARCHAR(150) NULL,
  status ENUM('draft','active','inactive','out_of_stock','archived') NOT NULL DEFAULT 'draft',
  qr_value VARCHAR(255) NOT NULL UNIQUE,
  qr_path VARCHAR(500) NULL,
  rating_avg DECIMAL(3,2) NOT NULL DEFAULT 0,
  review_count INT UNSIGNED NOT NULL DEFAULT 0,
  total_sold INT UNSIGNED NOT NULL DEFAULT 0,
  monthly_sold INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_products_brand FOREIGN KEY(brand_id) REFERENCES brands(id) ON DELETE SET NULL,
  CONSTRAINT fk_products_category FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL,
  INDEX idx_products_status(status),
  INDEX idx_products_category(category_id),
  INDEX idx_products_brand(brand_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_media (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL,
  type ENUM('image','video') NOT NULL,
  path VARCHAR(500) NOT NULL,
  alt_text VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  CONSTRAINT fk_product_media_product FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_product_media_product(product_id,type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_variants (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL,
  sku VARCHAR(120) NOT NULL UNIQUE,
  attributes JSON NOT NULL,
  price DECIMAL(18,2) NULL,
  wholesale_price DECIMAL(18,2) NULL,
  group_price DECIMAL(18,2) NULL,
  stock_qty INT NOT NULL DEFAULT 0,
  reserved_qty INT NOT NULL DEFAULT 0,
  qr_value VARCHAR(255) NOT NULL UNIQUE,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_product_variants_product FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_product_variants_product(product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_specifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL,
  spec_group VARCHAR(120) NULL,
  spec_key VARCHAR(150) NOT NULL,
  spec_value TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_product_specs_product FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_product_specs_product(product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shipping_templates (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  economy_days INT NOT NULL DEFAULT 7,
  economy_price DECIMAL(18,2) NOT NULL DEFAULT 0,
  express_days INT NULL,
  express_price DECIMAL(18,2) NULL,
  free_economy TINYINT(1) NOT NULL DEFAULT 1,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS group_buying_campaigns (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL,
  variant_id BIGINT UNSIGNED NULL,
  group_price DECIMAL(18,2) NOT NULL,
  minimum_buyers INT UNSIGNED NOT NULL,
  maximum_buyers INT UNSIGNED NULL,
  start_at DATETIME NOT NULL,
  end_at DATETIME NOT NULL,
  current_buyers INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('scheduled','active','successful','failed','cancelled') NOT NULL DEFAULT 'scheduled',
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_group_campaign_product FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_group_campaign_variant FOREIGN KEY(variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
  INDEX idx_group_campaign_status(status,end_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wallets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL UNIQUE,
  currency VARCHAR(10) NOT NULL DEFAULT 'USD',
  balance DECIMAL(18,2) NOT NULL DEFAULT 0,
  status ENUM('active','locked') NOT NULL DEFAULT 'active',
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_wallets_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
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

CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(60) NOT NULL UNIQUE,
  user_id BIGINT UNSIGNED NOT NULL,
  billing_address_id BIGINT UNSIGNED NULL,
  shipping_address_id BIGINT UNSIGNED NULL,
  order_type ENUM('retail','wholesale','group_buy') NOT NULL DEFAULT 'retail',
  status ENUM('pending','confirmed','processing','packing','shipped','in_transit','out_for_delivery','delivered','cancelled','returned','refunded') NOT NULL DEFAULT 'pending',
  subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
  shipping_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  wallet_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
  currency VARCHAR(10) NOT NULL DEFAULT 'USD',
  placed_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_orders_user FOREIGN KEY(user_id) REFERENCES users(id),
  CONSTRAINT fk_orders_billing_address FOREIGN KEY(billing_address_id) REFERENCES addresses(id) ON DELETE SET NULL,
  CONSTRAINT fk_orders_shipping_address FOREIGN KEY(shipping_address_id) REFERENCES addresses(id) ON DELETE SET NULL,
  INDEX idx_orders_user_status(user_id,status),
  INDEX idx_orders_created(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  variant_id BIGINT UNSIGNED NULL,
  sku VARCHAR(120) NOT NULL,
  product_title VARCHAR(255) NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  unit_price DECIMAL(18,2) NOT NULL,
  total_price DECIMAL(18,2) NOT NULL,
  created_at DATETIME NULL,
  CONSTRAINT fk_order_items_order FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY(product_id) REFERENCES products(id),
  CONSTRAINT fk_order_items_variant FOREIGN KEY(variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS group_buying_orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  campaign_id BIGINT UNSIGNED NOT NULL,
  order_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(18,2) NOT NULL,
  status ENUM('pending','qualified','confirmed','refunded','cancelled') NOT NULL DEFAULT 'pending',
  joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_group_order_campaign FOREIGN KEY(campaign_id) REFERENCES group_buying_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_group_order_order FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_group_order_user FOREIGN KEY(user_id) REFERENCES users(id),
  UNIQUE KEY uq_group_campaign_order(campaign_id,order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  provider VARCHAR(80) NOT NULL,
  method VARCHAR(80) NOT NULL,
  provider_transaction_id VARCHAR(190) NULL,
  amount DECIMAL(18,2) NOT NULL,
  currency VARCHAR(10) NOT NULL DEFAULT 'USD',
  status ENUM('pending','authorized','paid','failed','refunded','partially_refunded') NOT NULL DEFAULT 'pending',
  paid_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_payments_order FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
  INDEX idx_payments_provider_tx(provider,provider_transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_status_history (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  status VARCHAR(60) NOT NULL,
  note VARCHAR(500) NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_history_order FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_history_user FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_order_history_order_created(order_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reviews (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  order_item_id BIGINT UNSIGNED NULL,
  rating TINYINT UNSIGNED NOT NULL,
  title VARCHAR(255) NULL,
  body TEXT NULL,
  status ENUM('pending','published','rejected') NOT NULL DEFAULT 'pending',
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_reviews_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_product FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_order_item FOREIGN KEY(order_item_id) REFERENCES order_items(id) ON DELETE SET NULL,
  INDEX idx_reviews_product_status(product_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wishlist_items (
  user_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(user_id,product_id),
  CONSTRAINT fk_wishlist_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_wishlist_product FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_tickets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_number VARCHAR(60) NOT NULL UNIQUE,
  user_id BIGINT UNSIGNED NOT NULL,
  subject VARCHAR(255) NOT NULL,
  priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  status ENUM('open','pending','resolved','closed') NOT NULL DEFAULT 'open',
  assigned_to BIGINT UNSIGNED NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_support_user FOREIGN KEY(user_id) REFERENCES users(id),
  CONSTRAINT fk_support_agent FOREIGN KEY(assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_conversations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  session_token VARCHAR(190) NOT NULL,
  status ENUM('active','transferred','closed') NOT NULL DEFAULT 'active',
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_ai_conversation_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_ai_session(session_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversation_id BIGINT UNSIGNED NOT NULL,
  role ENUM('user','assistant','system','agent') NOT NULL,
  message LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ai_messages_conversation FOREIGN KEY(conversation_id) REFERENCES ai_conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS scraper_imports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  source VARCHAR(80) NOT NULL,
  source_url TEXT NOT NULL,
  status ENUM('pending','processing','completed','failed','draft') NOT NULL DEFAULT 'pending',
  raw_data JSON NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_scraper_created_by FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(190) NOT NULL UNIQUE,
  setting_value LONGTEXT NULL,
  is_encrypted TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
