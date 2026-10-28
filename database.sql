-- ========================================================
-- Portfolio Database Schema & Seed Data
-- Owner: Kian Lee Raian M. Aguinaldo
-- Degree: Bachelor of Science in Information Technology
-- Target Database: portfolio_db
-- Compatibility: MySQL 5.7+ / MariaDB 10.4+ (XAMPP Default)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `portfolio_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `portfolio_db`;

-- --------------------------------------------------------
-- Table structure for `profile`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `profile` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(120) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `tagline` VARCHAR(255) NOT NULL,
  `bio` TEXT NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `location` VARCHAR(120) DEFAULT NULL,
  `availability` VARCHAR(100) DEFAULT 'Available for Opportunities',
  `github` VARCHAR(255) DEFAULT NULL,
  `linkedin` VARCHAR(255) DEFAULT NULL,
  `twitter` VARCHAR(255) DEFAULT NULL,
  `years_experience` VARCHAR(20) DEFAULT NULL,
  `projects_completed` VARCHAR(20) DEFAULT NULL,
  `satisfied_clients` VARCHAR(20) DEFAULT NULL,
  `code_commits` VARCHAR(20) DEFAULT NULL,
  `resume_path` VARCHAR(500) DEFAULT NULL,
  `resume_updated_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed profile data for Kian Lee Raian M. Aguinaldo
INSERT INTO `profile` (`full_name`, `title`, `tagline`, `bio`, `email`, `phone`, `location`, `availability`, `github`, `linkedin`, `twitter`, `years_experience`, `projects_completed`, `satisfied_clients`, `code_commits`, `resume_path`)
VALUES (
  'Kian Lee Raian M. Aguinaldo',
  'Information Technology Graduate | Aspiring Web Developer & IT Support Professional',
  'Information Technology graduate interested in IT support, troubleshooting, and practical technology solutions.',
  'I am an Information Technology graduate interested in IT support, troubleshooting, and practical technology solutions. Through my internship and personal projects, I have gained exposure to computer setup, software installation, basic networking, and web technologies. I am eager to continue learning, strengthen my technical skills, and contribute to a professional IT team.',
  'your-email@example.com',
  '09XXXXXXXXXX',
  'Lucena City, Philippines',
  'Available for Entry-Level IT Roles',
  '',
  '',
  '',
  'Fresh Graduate',
  '',
  '',
  '',
  NULL
);

-- --------------------------------------------------------
-- Table structure for `certificates`
-- --------------------------------------------------------
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

-- --------------------------------------------------------
-- Table structure for `projects`
-- Required fields: id, title, description, technologies, image, github_url, live_url, status, created_at, updated_at
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `technologies` VARCHAR(255) NOT NULL,
  `image` VARCHAR(500) NOT NULL,
  `github_url` VARCHAR(255) DEFAULT NULL,
  `live_url` VARCHAR(255) DEFAULT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'published',
  `category` ENUM('fullstack', 'frontend', 'backend', 'networking') NOT NULL DEFAULT 'fullstack',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed projects matching IT Graduate portfolio
INSERT INTO `projects` (`title`, `description`, `technologies`, `image`, `github_url`, `live_url`, `status`, `category`) VALUES
(
  'Personal Portfolio Website',
  'Created a personal portfolio website to present my education, technical skills, experience, and projects. Used Google Antigravity and AI-assisted development tools to help with implementation and refinement. Reviewed and tested the website\'s functionality while exploring PHP, MySQL, and web application structure. Development approach: AI-assisted development using Google Antigravity.',
  'PHP, MySQL, HTML, CSS, JavaScript, XAMPP, Git, GitHub',
  'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=800&q=80',
  NULL,
  NULL,
  'published',
  'fullstack'
);

-- --------------------------------------------------------
-- Table structure for `messages` (Contact Form Submissions)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact inquiries are created dynamically via the public contact form.
-- No sample or fictional messages are seeded in this release schema.

-- --------------------------------------------------------
-- Table structure for `admin_users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(60) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- No administrator account is seeded in this SQL file to prevent credentials from being committed to version control.
-- The administrator account must be created securely through the project's local setup process, which is not included in the public repository.
