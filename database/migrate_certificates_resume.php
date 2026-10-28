<?php
/**
 * Safe, Idempotent Database Migration
 * Adds certificates table and resume fields to portfolio_db
 */
require_once __DIR__ . '/../config/db.php';

if (!$db_connected || !$pdo) {
    die("Database connection failed. Make sure MySQL is running.\n");
}

try {
    echo "Starting database migration...\n";

    // 1. Create certificates table if not exists
    $sqlCertificates = "
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
    ";
    $pdo->exec($sqlCertificates);
    echo "[OK] `certificates` table verified/created.\n";

    // 2. Add resume_path to profile table if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM `profile` LIKE 'resume_path'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `profile` ADD COLUMN `resume_path` VARCHAR(500) DEFAULT NULL AFTER `code_commits`");
        echo "[OK] Added `resume_path` column to `profile` table.\n";
    } else {
        echo "[OK] `resume_path` column already exists in `profile`.\n";
    }

    // 3. Add resume_updated_at to profile table if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM `profile` LIKE 'resume_updated_at'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `profile` ADD COLUMN `resume_updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `resume_path`");
        echo "[OK] Added `resume_updated_at` column to `profile` table.\n";
    } else {
        echo "[OK] `resume_updated_at` column already exists in `profile`.\n";
    }

    // 4. Update profile title to 'Information Technology Graduate | Aspiring Web Developer & IT Support Professional'
    $updateTitle = $pdo->prepare("UPDATE `profile` SET `title` = :t WHERE `id` = 1");
    $updateTitle->execute([':t' => 'Information Technology Graduate | Aspiring Web Developer & IT Support Professional']);
    echo "[OK] Updated profile title to 'Information Technology Graduate | Aspiring Web Developer & IT Support Professional'.\n";

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}
