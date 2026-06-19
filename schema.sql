-- ==========================================
-- COMMUNITY CONNECT DATABASE SCHEMA & SEED DATA
-- Database Name: community_connect
-- ==========================================

CREATE DATABASE IF NOT EXISTS `community_connect` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `community_connect`;

-- 1. Table: users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'volunteer', 'admin') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Table: posts
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `content` TEXT NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `is_urgent` TINYINT(1) NOT NULL DEFAULT 0,
    `risk_level` ENUM('none', 'low', 'medium', 'high') NOT NULL DEFAULT 'none',
    `sentiment_label` ENUM('positive', 'neutral', 'negative', 'critical') NOT NULL DEFAULT 'neutral',
    `sentiment_score` FLOAT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 2b. Table: moods
CREATE TABLE IF NOT EXISTS `moods` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `mood` ENUM('Happy', 'Neutral', 'Sad', 'Stressed', 'Depressed') NOT NULL,
    `note` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Table: replies
CREATE TABLE IF NOT EXISTS `replies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `volunteer_id` INT NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3b. Table: messages (private user <-> volunteer chat)
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

-- 3c. Table: journal_entries
CREATE TABLE IF NOT EXISTS `journal_entries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3d. Table: appointments
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

-- 3e. Table: user_achievements
CREATE TABLE IF NOT EXISTS `user_achievements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `badge_code` VARCHAR(50) NOT NULL,
    `earned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (`user_id`, `badge_code`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3f. Table: polls
CREATE TABLE IF NOT EXISTS `polls` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `question` VARCHAR(255) NOT NULL,
    `created_by` INT NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3g. Table: poll_options
CREATE TABLE IF NOT EXISTS `poll_options` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `poll_id` INT NOT NULL,
    `option_text` VARCHAR(100) NOT NULL,
    FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3h. Table: poll_votes
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

-- 3i. Table: ratings
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

-- 4. Table: resources
CREATE TABLE IF NOT EXISTS `resources` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `content` TEXT NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `author` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Table: helplines
CREATE TABLE IF NOT EXISTS `helplines` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `hours` VARCHAR(100) NOT NULL DEFAULT '24/7'
) ENGINE=InnoDB;

-- ==========================================
-- INSERT SEED DATA
-- Default password for all users: password123
-- (Bcrypt hash: $2y$10$e0MYzXy55.k1BPhh1JZ4Z.e099V1Zq6b2Rux.1N26pP0sL4H4zF7O)
-- ==========================================

-- Seed Users
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
('admin', 'admin@communityconnect.org', '$2y$12$kn5RaR87GAZv/ni/IMfucOZyK8h3M1ukAUxrf/4ubIA4CrSJbHLqy', 'admin'),
('volunteer', 'sarah@communityconnect.org', '$2y$12$kn5RaR87GAZv/ni/IMfucOZyK8h3M1ukAUxrf/4ubIA4CrSJbHLqy', 'volunteer'),
('user', 'user@communityconnect.org', '$2y$12$kn5RaR87GAZv/ni/IMfucOZyK8h3M1ukAUxrf/4ubIA4CrSJbHLqy', 'user');

-- Seed Resources
INSERT INTO `resources` (`title`, `content`, `category`, `author`) VALUES
('Understanding Stress and How to Manage It', 'Stress is a natural physical and mental reaction to life experiences. Learn to practice mindfulness, deep breathing, and set realistic goals to cope with daily stressors.', 'Stress Management', 'Mental Health NGO'),
('Building Resilience in College Life', 'Resilience helps you navigate obstacles. Stay connected with friends, follow a routine, seek help when overwhelmed, and view failures as opportunities to learn.', 'Personal Growth', 'Campus Counsellor'),
('5 Quick Breathing Exercises for Anxiety', 'Breathing exercises can help reduce acute anxiety. Try Box Breathing: Inhale for 4 seconds, hold for 4 seconds, exhale for 4 seconds, hold for 4 seconds. Repeat 5 times.', 'Anxiety Control', 'Wellbeing Team');

-- Seed Helplines
INSERT INTO `helplines` (`name`, `phone`, `description`, `hours`) VALUES
('National Suicide Prevention Lifeline', '988', 'Free and confidential support for people in distress or crisis.', '24/7'),
('Crisis Text Line', 'Text HOME to 741741', 'Connect with a crisis counsellor for support via text message.', '24/7'),
('Disaster Distress Helpline', '1-800-985-5990', 'Immediate crisis counselling for people experiencing disaster-related distress.', '24/7');
