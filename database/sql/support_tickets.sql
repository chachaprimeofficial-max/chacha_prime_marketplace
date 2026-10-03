-- Chacha Prime support ticket conversations
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