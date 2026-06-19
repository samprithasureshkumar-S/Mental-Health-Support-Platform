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
