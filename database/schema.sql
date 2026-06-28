-- PantauKu Database Schema
-- Tables: users, events, blacklist_domains, settings

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `events` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('app_open', 'browser_access') NOT NULL,
  `value` VARCHAR(255) NOT NULL,
  `is_suspicious` BOOLEAN DEFAULT FALSE,
  `device_id` VARCHAR(100) DEFAULT NULL,
  `occurred_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_type` (`type`),
  INDEX `idx_is_suspicious` (`is_suspicious`),
  INDEX `idx_occurred_at` (`occurred_at`),
  INDEX `idx_device_id` (`device_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `blacklist_domains` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `domain` VARCHAR(255) NOT NULL UNIQUE,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed default admin user: syahrul@example.com / ngd987hj56
INSERT IGNORE INTO `users` (`name`, `email`, `password`) 
VALUES ('Syahrul', 'syahrul@example.com', '$2y$12$PLACEHOLDER_HASH');

-- Seed sample blacklist domains (common judol sites)
INSERT IGNORE INTO `blacklist_domains` (`domain`, `notes`) VALUES
('mpo777.com', 'Situs judi online'),
('slot88.com', 'Situs judi slot'),
('poker88.com', 'Poker online'),
('togel.com', 'Togel online'),
('sbobet.com', 'Sports betting'),
('maxbet.com', 'Casino online'),
('judi123.com', 'Situs judi'),
('qqslot.com', 'Slot online'),
('dewa poker.com', 'Poker judi'),
('bola88.com', 'Judi bola'),
('casino online.com', 'Casino'),
('idnplay.com', 'IDN Play judi'),
('joker123.com', 'Joker slot judi'),
('ion casino.com', 'Ion casino'),
('cmd368.com', 'Sportsbook judi');

-- Default settings
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES
('telegram_bot_token', ''),
('telegram_chat_id', ''),
('api_token', 'pantauku_api_token_2026_secure_random_key');
