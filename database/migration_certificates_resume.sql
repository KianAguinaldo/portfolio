-- ========================================================
-- Migration: Certificates and Resume Management
-- Database: portfolio_db
-- ========================================================

USE `portfolio_db`;

-- 1. Create certificates table
CREATE TABLE IF NOT EXISTS `certificates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `organization` VARCHAR(200) NOT NULL,
  `issue_date` DATE DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `file_path` VARCHAR(500) DEFAULT NULL,
  `file_type` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
  `display_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_cert_status_order` (`status`, `display_order`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add resume fields to profile table (if not exists)
ALTER TABLE `profile` ADD COLUMN IF NOT EXISTS `resume_path` VARCHAR(500) DEFAULT NULL AFTER `code_commits`;
ALTER TABLE `profile` ADD COLUMN IF NOT EXISTS `resume_updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `resume_path`;

-- 3. Update title
UPDATE `profile`
SET `title` = 'Information Technology Graduate | Aspiring Web Developer & IT Support Professional'
WHERE `id` = 1;
