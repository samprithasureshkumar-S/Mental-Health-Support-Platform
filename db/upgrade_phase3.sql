-- ==========================================
-- COMMUNITY CONNECT - PHASE 3 UPGRADE SCRIPT
-- Run this against an EXISTING community_connect database
-- (adds Achievements + Anonymous Polls + Resource Recommendations + Feedback Ratings + Admin Analytics)
-- ==========================================

USE `community_connect`;

CREATE TABLE IF NOT EXISTS `user_achievements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `badge_code` VARCHAR(50) NOT NULL,
    `earned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (`user_id`, `badge_code`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `polls` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `question` VARCHAR(255) NOT NULL,
    `created_by` INT NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `poll_options` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `poll_id` INT NOT NULL,
    `option_text` VARCHAR(100) NOT NULL,
    FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `poll_votes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `poll_id` INT NOT NULL,
    `option_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (`poll_id`, `user_id`),
    FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`option_id`) REFERENCES `poll_options` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `ratings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id` INT NOT NULL UNIQUE,
    `user_id` INT NOT NULL,
    `volunteer_id` INT NOT NULL,
    `rating` TINYINT NOT NULL,
    `comment` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
