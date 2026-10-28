<?php
/**
 * Admin Edit Certificate Page
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$certificate = get_certificate_by_id($pdo, $id);

if (!$certificate) {
    header('Location: certificates.php?err=' . urlencode('Certificate record not found.'));
    exit;
}

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
        $remove_file   = isset($_POST['remove_current_file']) && $_POST['remove_current_file'] === '1';

        // Normalize empty values
        $issue_date  = empty($issue_date) ? null : $issue_date;
        $description = empty($description) ? null : $description;
        $status      = in_array($status, ['published', 'draft']) ? $status : 'published';

        if (empty($title) || empty($organization)) {
            $error = 'Please fill out all required fields: Certificate Title and Issuing Organization.';
        } elseif (!$db_connected || !$pdo) {
            $error = 'Database connection error.';
        } else {
            $new_file_path = $certificate['file_path'];
            $new_file_type = $certificate['file_type'];
            $uploaded_new = false;
            $old_file_to_unlink = null;

            // Check if replacement file uploaded
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
                                $old_file_to_unlink = $certificate['file_path'];
                                $new_file_path = 'uploads/certificates/' . $safe_filename;
                                $new_file_type = ($ext === 'pdf') ? 'pdf' : 'image';
                                $uploaded_new = true;
                            } else {
                                $error = 'Failed to save uploaded certificate file. Check write permissions.';
                            }
                        }
                    }
                }
            } elseif ($remove_file) {
                $old_file_to_unlink = $certificate['file_path'];
                $new_file_path = null;
                $new_file_type = null;
            }

            // Save updates to MySQL
            if (empty($error)) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE certificates SET
                            title = :title,
                            organization = :org,
                            issue_date = :issue_date,
                            description = :desc,
                            file_path = :file_path,
                            file_type = :file_type,
                            status = :status,
                            display_order = :order,
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':title'       => $title,
                        ':org'         => $organization,
                        ':issue_date'  => $issue_date,
                        ':desc'        => $description,
                        ':file_path'   => $new_file_path,
                        ':file_type'   => $new_file_type,
                        ':status'      => $status,
                        ':order'       => $display_order,
                        ':id'          => $id
                    ]);

                    // If file was replaced or removed, safely unlink the old file
                    if (!empty($old_file_to_unlink)) {
                        $real_dir = realpath(__DIR__ . '/../uploads/certificates');
                        $real_old = realpath(__DIR__ . '/../' . ltrim($old_file_to_unlink, '/\\'));
                        if ($real_dir && $real_old && str_starts_with($real_old, $real_dir)) {
                            @unlink($real_old);
                        }
                    }

                    header("Location: certificates.php?msg=updated");
                    exit;
                } catch (Exception $e) {
                    // If DB save failed and a new file was moved, delete it to prevent orphaned files
                    if ($uploaded_new && !empty($target_file) && file_exists($target_file)) {
                        @unlink($target_file);
                    }
                    error_log("Database error in admin/edit-certificate.php: " . $e->getMessage());
                    $error = 'Database error while updating certificate. Please try again.';
                }
            }
        }
    }
}

// Refresh certificate data if re-rendering on validation error
$curr_cert = get_certificate_by_id($pdo, $id);
$has_file = !empty($curr_cert['file_path']);
$file_src = get_certificate_file_src($curr_cert['file_path'] ?? '', '../');
$file_type = $curr_cert['file_type'] ?? 'image';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Certificate | Portfolio Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">

    <?php include __DIR__ . '/admin_header.php'; ?>

    <main class="admin-main">
        <div style="margin-bottom: 24px;">
            <a href="certificates.php" style="color: var(--color-sky); text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                &larr; Back to All Certificates
            </a>
            <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.5px; margin: 0;">Edit Certificate</h1>
            <p style="color: var(--text-secondary); font-size: 0.95rem; margin-top: 4px;">Update credential details, replace files, or change publication status.</p>
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
                    <input type="text" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? $curr_cert['title']) ?>" class="form-input" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary);">
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                    <div class="form-group">
                        <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Issuing Organization *
                        </label>
                        <input type="text" name="organization" required value="<?= htmlspecialchars($_POST['organization'] ?? $curr_cert['organization']) ?>" class="form-input" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary);">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Date Issued / Earned (Optional)
                        </label>
                        <input type="date" name="issue_date" value="<?= htmlspecialchars($_POST['issue_date'] ?? $curr_cert['issue_date'] ?? '') ?>" class="form-input" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary);">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        Brief Description / Highlights (Optional)
                    </label>
                    <textarea name="description" rows="3" class="form-textarea" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary); resize: vertical;"><?= htmlspecialchars($_POST['description'] ?? $curr_cert['description'] ?? '') ?></textarea>
                </div>

                <!-- Current File Preview Box -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">
                        Current Attached File
                    </label>

                    <?php if ($has_file): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: rgba(7, 9, 15, 0.6); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); margin-bottom: 10px; flex-wrap: wrap; gap: 12px;">
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <?php if ($file_type === 'image'): ?>
                                    <img src="<?= htmlspecialchars($file_src) ?>" alt="current-cert" style="width: 60px; height: 42px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-subtle);">
                                <?php else: ?>
                                    <div style="width: 44px; height: 42px; border-radius: 4px; background: rgba(239, 68, 68, 0.15); color: #ef4444; display: flex; align-items: center; justify-content: center;">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <strong style="font-size: 13px; color: #ffffff;"><?= htmlspecialchars(basename($curr_cert['file_path'])) ?></strong>
                                    <span style="font-size: 11px; color: var(--color-sky); display: block; text-transform: uppercase;">
                                        Type: <?= htmlspecialchars($file_type) ?>
                                    </span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <a href="<?= htmlspecialchars($file_src) ?>" target="_blank" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                    View File &nearr;
                                </a>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #fca5a5; cursor: pointer;">
                                    <input type="checkbox" name="remove_current_file" value="1">
                                    <span>Remove File</span>
                                </label>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="padding: 12px; background: rgba(7, 9, 15, 0.4); border: 1px dashed var(--border-subtle); border-radius: var(--radius-sm); font-size: 12px; color: var(--text-muted);">
                            No file currently attached. The public card will display a credential badge.
                        </div>
                    <?php endif; ?>

                    <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-top: 12px; margin-bottom: 6px;">
                        Upload Replacement File (Optional)
                    </label>
                    <input type="file" name="certificate_file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="form-input" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px dashed var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary);">
                    <span style="font-size: 12px; color: var(--text-muted); margin-top: 4px; display: block;">
                        Leave empty to keep the existing file intact. Allowed: PDF, JPG, JPEG, PNG (Max: 10MB).
                    </span>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 26px;">
                    <div class="form-group">
                        <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Publication Status
                        </label>
                        <select name="status" class="form-input" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary);">
                            <option value="published" <?= (($curr_cert['status'] ?? 'published') === 'published') ? 'selected' : '' ?>>Published (Live on Public Portfolio)</option>
                            <option value="draft" <?= (($curr_cert['status'] ?? '') === 'draft') ? 'selected' : '' ?>>Draft (Saved Privately in Admin Only)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Display Order (Optional)
                        </label>
                        <input type="number" name="display_order" value="<?= htmlspecialchars($_POST['display_order'] ?? $curr_cert['display_order'] ?? '0') ?>" min="0" step="1" class="form-input" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: rgba(7, 9, 15, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary);">
                    </div>
                </div>

                <div style="display: flex; gap: 12px; align-items: center;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 14px; font-weight: 700;">
                        Save Changes
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
