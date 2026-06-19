-- ==========================================
-- COMMUNITY CONNECT - PHASE 1 UPGRADE SCRIPT
-- Run this against an EXISTING community_connect database
-- (adds Mood Tracker + Crisis Detection / Sentiment columns)
-- ==========================================

USE `community_connect`;

ALTER TABLE `posts`
    ADD COLUMN `is_urgent` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `risk_level` ENUM('none', 'low', 'medium', 'high') NOT NULL DEFAULT 'none',
    ADD COLUMN `sentiment_label` ENUM('positive', 'neutral', 'negative', 'critical') NOT NULL DEFAULT 'neutral',
    ADD COLUMN `sentiment_score` FLOAT NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS `moods` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `mood` ENUM('Happy', 'Neutral', 'Sad', 'Stressed', 'Depressed') NOT NULL,
    `note` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
