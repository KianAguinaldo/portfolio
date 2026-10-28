<?php
/**
 * Admin Delete Project Controller
 * Secure server-side POST deletion with CSRF protection
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Disallow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: projects.php?err=' . urlencode('Invalid request method. Deletion must be submitted via POST.'));
    exit;
}

// CSRF check
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    header('Location: projects.php?err=' . urlencode('Security check failed (CSRF token invalid).'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$allowed_redirects = ['projects.php', 'index.php'];
$redirect = in_array($_POST['ref'] ?? '', $allowed_redirects, true) ? $_POST['ref'] : 'projects.php';

if ($id > 0 && $db_connected && $pdo) {
    try {
        // Fetch project to check if local image should be unlinked
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $project = $stmt->fetch();

        if ($project) {
            $image_field = $project['image'] ?? $project['image_url'] ?? '';
            // If local upload, safely remove file
            if (!empty($image_field) && !preg_match('#^https?://#i', $image_field)) {
                $file_path = __DIR__ . '/../' . ltrim($image_field, '/\\');
                // Ensure file is within uploads/projects directory before unlinking
                $real_uploads_dir = realpath(__DIR__ . '/../uploads/projects');
                $real_file_path = realpath($file_path);
                if ($real_uploads_dir && $real_file_path && str_starts_with($real_file_path, $real_uploads_dir)) {
                    @unlink($real_file_path);
                }
            }

            // Delete database row
            $del_stmt = $pdo->prepare("DELETE FROM projects WHERE id = :id");
            $del_stmt->execute([':id' => $id]);

            header("Location: {$redirect}?msg=deleted");
            exit;
        } else {
            header("Location: {$redirect}?err=" . urlencode('Project record not found.'));
            exit;
        }
    } catch (Exception $e) {
        error_log("Error in admin/delete-project.php: " . $e->getMessage());
        header("Location: {$redirect}?err=" . urlencode('Failed to delete project. Please try again.'));
        exit;
    }
}

header("Location: {$redirect}");
exit;
