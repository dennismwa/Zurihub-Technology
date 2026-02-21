-- =====================================================
-- CHAT SUPPORT & TICKETING - Append to existing schema
-- Run this AFTER the main schema.sql
-- =====================================================

SET NAMES utf8mb4;

-- =====================================================
-- CHAT CONVERSATIONS (visitor sessions)
-- =====================================================
DROP TABLE IF EXISTS `chat_conversations`;
CREATE TABLE `chat_conversations` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `visitor_name` VARCHAR(100) NOT NULL,
    `visitor_email` VARCHAR(100) NOT NULL,
    `visitor_token` VARCHAR(64) NOT NULL,
    `status` ENUM('open', 'closed') DEFAULT 'open',
    `last_message_at` DATETIME DEFAULT NULL,
    `last_message_preview` VARCHAR(160) DEFAULT NULL,
    `assigned_to` INT(11) UNSIGNED DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `visitor_token` (`visitor_token`),
    KEY `status` (`status`),
    KEY `last_message_at` (`last_message_at`),
    KEY `assigned_to` (`assigned_to`),
    CONSTRAINT `chat_conversation_admin_fk` FOREIGN KEY (`assigned_to`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CHAT MESSAGES
-- =====================================================
DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `conversation_id` INT(11) UNSIGNED NOT NULL,
    `sender_type` ENUM('visitor', 'staff') NOT NULL,
    `staff_id` INT(11) UNSIGNED DEFAULT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `conversation_id` (`conversation_id`),
    KEY `sender_type` (`sender_type`),
    KEY `staff_id` (`staff_id`),
    CONSTRAINT `chat_message_conversation_fk` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `chat_message_staff_fk` FOREIGN KEY (`staff_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SUPPORT TICKETS
-- =====================================================
DROP TABLE IF EXISTS `support_tickets`;
CREATE TABLE `support_tickets` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_ref` VARCHAR(20) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `visitor_name` VARCHAR(100) NOT NULL,
    `visitor_email` VARCHAR(100) NOT NULL,
    `visitor_phone` VARCHAR(20) DEFAULT NULL,
    `status` ENUM('open', 'in_progress', 'waiting_reply', 'resolved', 'closed') DEFAULT 'open',
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `department` VARCHAR(50) DEFAULT 'general',
    `conversation_id` INT(11) UNSIGNED DEFAULT NULL,
    `assigned_to` INT(11) UNSIGNED DEFAULT NULL,
    `last_reply_at` DATETIME DEFAULT NULL,
    `last_reply_by` ENUM('visitor', 'staff') DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ticket_ref` (`ticket_ref`),
    KEY `status` (`status`),
    KEY `visitor_email` (`visitor_email`),
    KEY `assigned_to` (`assigned_to`),
    KEY `conversation_id` (`conversation_id`),
    CONSTRAINT `ticket_assigned_fk` FOREIGN KEY (`assigned_to`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `ticket_conversation_fk` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SUPPORT TICKET REPLIES
-- =====================================================
DROP TABLE IF EXISTS `support_ticket_replies`;
CREATE TABLE `support_ticket_replies` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_id` INT(11) UNSIGNED NOT NULL,
    `reply_by` ENUM('visitor', 'staff') NOT NULL,
    `staff_id` INT(11) UNSIGNED DEFAULT NULL,
    `message` TEXT NOT NULL,
    `attachments` JSON DEFAULT NULL,
    `is_internal_note` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ticket_id` (`ticket_id`),
    KEY `staff_id` (`staff_id`),
    CONSTRAINT `ticket_reply_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `ticket_reply_staff_fk` FOREIGN KEY (`staff_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DROP OLD DASHBOARD STATS VIEW (so we can recreate with new columns)
-- =====================================================
DROP VIEW IF EXISTS `dashboard_stats`;

-- =====================================================
-- DASHBOARD STATS VIEW (includes chat & tickets)
-- =====================================================
CREATE VIEW `dashboard_stats` AS
SELECT
    (SELECT COUNT(*) FROM quotation_requests WHERE status = 'new') AS new_quotations,
    (SELECT COUNT(*) FROM quotation_requests) AS total_quotations,
    (SELECT COUNT(*) FROM contact_messages WHERE status = 'new') AS new_messages,
    (SELECT COUNT(*) FROM contact_messages) AS total_messages,
    (SELECT COUNT(*) FROM career_applications WHERE status = 'new') AS new_applications,
    (SELECT COUNT(*) FROM portfolio_projects WHERE is_active = 1) AS total_projects,
    (SELECT COUNT(*) FROM testimonials WHERE is_approved = 1) AS total_testimonials,
    (SELECT SUM(views) FROM portfolio_projects) AS total_portfolio_views,
    (SELECT COUNT(*) FROM quotation_requests WHERE DATE(created_at) = CURDATE()) AS quotations_today,
    (SELECT COUNT(*) FROM quotation_requests WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())) AS quotations_this_month,
    (SELECT COUNT(*) FROM chat_messages WHERE sender_type = 'visitor' AND is_read = 0) AS unread_chat_messages,
    (SELECT COUNT(*) FROM chat_conversations WHERE status = 'open') AS open_chat_conversations,
    (SELECT COUNT(*) FROM support_tickets WHERE status IN ('open', 'in_progress', 'waiting_reply') AND is_read = 0) AS unread_tickets,
    (SELECT COUNT(*) FROM support_tickets WHERE status IN ('open', 'in_progress', 'waiting_reply')) AS open_tickets;

-- =====================================================
-- END CHAT & TICKETS SCHEMA
-- =====================================================
