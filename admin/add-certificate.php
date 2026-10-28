<?php
/**
 * Admin Add Certificate Page
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation (CSRF) failed. Please try again.';
    } else {
        $title         = trim($_POST['title'] ?? '');
        $organization  = trim($_POST['organization'] ?? '');
        $issue_date    = trim($_POST['issue_date'] ?? '');
        $description   = trim($_POST['description'] ?? '');
        $status        = trim($_POST['status'] ?? 'published');
        $display_order = (int)($_POST['display_order'] ?? 0);

        // Normalize empty values
        $issue_date  = empty($issue_date) ? null : $issue_date;
        $description = empty($description) ? null : $description;
        $status      = in_array($status, ['published', 'draft']) ? $status : 'published';

        // Validate Required Fields
        if (empty($title) || empty($organization)) {
            $error = 'Please fill out all required fields: Certificate Title and Issuing Organization.';
        } elseif (!$db_connected || !$pdo) {
            $error = 'MySQL database is not connected. Please ensure MySQL is running in XAMPP.';
        } else {
            $file_path = null;
            $file_type = null;

            // Handle File Upload if provided
            if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['certificate_file'];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $error = 'File upload error code: ' . $file['error'];
                } else {
                    $max_size = 10 * 1024 * 1024; // 10MB
                    if ($file['size'] > $max_size) {
                        $error = 'Uploaded certificate file must not exceed 10MB.';
                    } else {
                        $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $file['tmp_name']);
                        finfo_close($finfo);

                        $allowed_mimes = [
                            'application/pdf',
                            'image/jpeg',
                            'image/png'
                        ];

                        if (!in_array($ext, $allowed_exts) || !in_array($mime_type, $allowed_mimes)) {
                            $error = 'Invalid certificate file format. Allowed formats: PDF, JPG, JPEG, PNG.';
                        } else {
                            $target_dir = __DIR__ . '/../uploads/certificates/';
                            if (!is_dir($target_dir)) {
                                mkdir($target_dir, 0755, true);
                            }

                            $safe_filename = 'cert_' . date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
                            $target_file = $target_dir . $safe_filename;

                            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                                $file_path = 'uploads/certificates/' . $safe_filename;
                                $file_type = ($ext === 'pdf') ? 'pdf' : 'image';
                            } else {
                                $error = 'Failed to move uploaded certificate file. Check write permissions.';
                            }
                        }
                    }
                }
            }

            // Save to MySQL
            if (empty($error)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO certificates (title, organization, issue_date, description, file_path, file_type, status, display_order, created_at, updated_at)
                        VALUES (:title, :org, :issue_date, :desc, :file_path, :file_type, :status, :order, NOW(), NOW())
                    ");
                    $stmt->execute([
                        ':title'       => $title,
                        ':org'         => $organization,
                        ':issue_date'  => $issue_date,
                        ':desc'        => $description,
                        ':file_path'   => $file_path,
                        ':file_type'   => $file_type,
                        ':status'      => $status,
                        ':order'       => $display_order
                    ]);

                    header("Location: certificates.php?msg=added");
                    exit;
                } catch (Exception $e) {
                    if (!empty($target_file) && file_exists($target_file)) {
                        @unlink($target_file);
                    }
                    error_log("Database error in admin/add-certificate.php: " . $e->getMessage());
                    $error = 'Database error while saving certificate. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Certificate | Portfolio Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">

    <?php include __DIR__ . '/admin_header.php'; ?>

    <main class="admin-main">
        <div style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <a href="certificates.php" style="color: var(--color-sky); text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                    &larr; Back to All Certificates
                </a>
                <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.5px; margin: 0;">Add New Certificate</h1>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-banner error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        Certificate Title *
                    </label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" placeholder="e.g. IT Support Specialist Certificate" class="form-input" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary);">
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                    <div class="form-group">
                        <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Issuing Organization *
                        </label>
                        <input type="text" name="organization" required value="<?= htmlspecialchars($_POST['organization'] ?? '') ?>" placeholder="e.g. Commission on Elections (COMELEC) / Cisco / Google" class="form-input" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary);">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Date Issued / Earned (Optional)
                        </label>
                        <input type="date" name="issue_date" value="<?= htmlspecialchars($_POST['issue_date'] ?? '') ?>" class="form-input" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary);">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        Brief Description / Highlights (Optional)
                    </label>
                    <textarea name="description" rows="3" placeholder="Brief summary of skills verified, training content, or credential scope..." class="form-textarea" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary); resize: vertical;"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        Certificate File (Optional &bull; PDF, JPG, JPEG, or PNG)
                    </label>
                    <input type="file" name="certificate_file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="form-input" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px dashed var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary);">
                    <span style="font-size: 12px; color: var(--text-muted); margin-top: 6px; display: block;">
                        Supported formats: <strong>PDF, JPG, JPEG, PNG</strong> (Max: 10MB). If no file is provided, a clean credential badge will be shown.
                    </span>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 26px;">
                    <div class="form-group">
                        <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Publication Status
                        </label>
                        <select name="status" class="form-input" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary);">
                            <option value="published" <?= (($_POST['status'] ?? 'published') === 'published') ? 'selected' : '' ?>>Published (Live on Public Portfolio)</option>
                            <option value="draft" <?= (($_POST['status'] ?? '') === 'draft') ? 'selected' : '' ?>>Draft (Saved Privately in Admin Only)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Display Order (Optional)
                        </label>
                        <input type="number" name="display_order" value="<?= htmlspecialchars($_POST['display_order'] ?? '0') ?>" min="0" step="1" class="form-input" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary);">
                        <span style="font-size: 12px; color: var(--text-muted); margin-top: 4px; display: block;">
                            Lower numbers appear first on the public grid.
                        </span>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; align-items: center;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 14px; font-weight: 700;">
                        Save Certificate Record
                    </button>
                    <a href="certificates.php" class="btn btn-secondary" style="padding: 12px 20px; font-size: 14px;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
