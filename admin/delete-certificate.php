<?php
/**
 * Admin Delete Certificate Controller
 * Secure server-side POST deletion with CSRF protection
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Disallow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: certificates.php?err=' . urlencode('Invalid request method. Deletion must be submitted via POST.'));
    exit;
}

// CSRF check
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    header('Location: certificates.php?err=' . urlencode('Security check failed (CSRF token invalid).'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id > 0 && $db_connected && $pdo) {
    try {
        // Fetch certificate to check if an uploaded file should be unlinked
        $stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $cert = $stmt->fetch();

        if ($cert) {
            $file_field = $cert['file_path'] ?? '';
            // If local upload, safely remove file
            if (!empty($file_field) && !preg_match('#^https?://#i', $file_field)) {
                $file_path = __DIR__ . '/../' . ltrim($file_field, '/\\');
                $real_uploads_dir = realpath(__DIR__ . '/../uploads/certificates');
                $real_file_path = realpath($file_path);

                // Prevent directory traversal: verify file is strictly inside uploads/certificates/
                if ($real_uploads_dir && $real_file_path && str_starts_with($real_file_path, $real_uploads_dir)) {
                    @unlink($real_file_path);
                }
            }

            // Delete database row
            $del_stmt = $pdo->prepare("DELETE FROM certificates WHERE id = :id");
            $del_stmt->execute([':id' => $id]);

            header("Location: certificates.php?msg=deleted");
            exit;
        } else {
            header("Location: certificates.php?err=" . urlencode('Certificate record not found.'));
            exit;
        }
    } catch (Exception $e) {
        error_log("Error in admin/delete-certificate.php: " . $e->getMessage());
        header("Location: certificates.php?err=" . urlencode('Error deleting certificate record. Please try again.'));
        exit;
    }
}

header("Location: certificates.php");
exit;
