-- Chacha Prime support tickets and conversations
CREATE TABLE IF NOT EXISTS support_tickets (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 ticket_number VARCHAR(40) NOT NULL UNIQUE,
 user_id BIGINT UNSIGNED NOT NULL,
 subject VARCHAR(255) NOT NULL,
 priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
 status ENUM('open','pending','resolved','closed') NOT NULL DEFAULT 'open',
 assigned_to BIGINT UNSIGNED NULL,
 created_at DATETIME NULL,
 updated_at DATETIME NULL,
 CONSTRAINT fk_support_ticket_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_support_ticket_assignee FOREIGN KEY(assigned_to) REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_support_ticket_user_status(user_id,status),
 INDEX idx_support_ticket_status_priority(status,priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_ticket_messages (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 ticket_id BIGINT UNSIGNED NOT NULL,
 user_id BIGINT UNSIGNED NOT NULL,
 message TEXT NOT NULL,
 created_at DATETIME NULL,
 updated_at DATETIME NULL,
 CONSTRAINT fk_support_message_ticket FOREIGN KEY(ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
 CONSTRAINT fk_support_message_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_support_messages_ticket_created(ticket_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;