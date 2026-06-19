-- ==========================================
-- COMMUNITY CONNECT - PHASE 2 UPGRADE SCRIPT
-- Run this against an EXISTING community_connect database
-- (adds Private Chat + Wellness Journal + Appointment Booking)
-- ==========================================

USE `community_connect`;

CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `volunteer_id` INT NOT NULL,
    `sender_id` INT NOT NULL,
    `content` TEXT NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX (`user_id`, `volunteer_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `journal_entries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `appointments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `volunteer_id` INT NOT NULL,
    `status` ENUM('pending', 'accepted', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `notes` VARCHAR(255) DEFAULT NULL,
    `response_note` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
