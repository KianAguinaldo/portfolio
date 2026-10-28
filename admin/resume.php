<?php
/**
 * Admin Resume Management Controller
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$feedback = '';
$feedback_type = 'success';

// Handle File Upload or Replacement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_upload_resume'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $feedback = "Security validation (CSRF) failed. Please try again.";
        $feedback_type = 'error';
    } elseif (!$db_connected || !$pdo) {
        $feedback = "Database connection is offline.";
        $feedback_type = 'error';
    } elseif (!isset($_FILES['resume_file']) || $_FILES['resume_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $feedback = "Please select a PDF file to upload.";
        $feedback_type = 'error';
    } else {
        $file = $_FILES['resume_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $feedback = "Upload failed with error code: " . (int)$file['error'];
            $feedback_type = 'error';
        } else {
            $max_size = 10 * 1024 * 1024; // 10MB
            if ($file['size'] > $max_size) {
                $feedback = "Resume PDF must not exceed 10MB.";
                $feedback_type = 'error';
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if ($ext !== 'pdf' || $mime_type !== 'application/pdf') {
                    $feedback = "Invalid file type. Only PDF documents (.pdf) are allowed.";
                    $feedback_type = 'error';
                } else {
                    $target_dir = __DIR__ . '/../uploads/resume/';
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }

                    // Safe filename
                    $safe_filename = 'resume_kian_aguinaldo_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.pdf';
                    $target_file = $target_dir . $safe_filename;
                    $rel_path = 'uploads/resume/' . $safe_filename;

                    if (move_uploaded_file($file['tmp_name'], $target_file)) {
                        // Retrieve old resume to clean up if replacement
                        $old_info = get_resume_info($pdo, $db_connected);

                        try {
                            $stmt = $pdo->prepare("
                                UPDATE profile SET
                                    resume_path = :path,
                                    resume_updated_at = NOW()
                                WHERE id = 1
                            ");
                            $stmt->execute([':path' => $rel_path]);

                            // Safely remove old file if it existed
                            if ($old_info['exists'] && !empty($old_info['full_path'])) {
                                $real_dir = realpath($target_dir);
                                $real_old = realpath($old_info['full_path']);
                                if ($real_dir && $real_old && str_starts_with($real_old, $real_dir) && $real_old !== realpath($target_file)) {
                                    @unlink($real_old);
                                }
                            }

                            $feedback = "Resume uploaded successfully! The public download button is now live.";
                            $feedback_type = 'success';
                        } catch (Exception $e) {
                            // If DB update failed, delete the newly moved file to avoid orphan
                            @unlink($target_file);
                            error_log("Database error in admin/resume.php: " . $e->getMessage());
                            $feedback = "Database error while updating resume record. Please try again.";
                            $feedback_type = 'error';
                        }
                    } else {
                        $feedback = "Failed to save file. Check directory write permissions.";
                        $feedback_type = 'error';
                    }
                }
            }
        }
    }
}

// Handle Delete / Remove Resume
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_remove_resume'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $feedback = "Security validation (CSRF) failed. Please try again.";
        $feedback_type = 'error';
    } elseif ($db_connected && $pdo) {
        $old_info = get_resume_info($pdo, $db_connected);
        try {
            $stmt = $pdo->prepare("UPDATE profile SET resume_path = NULL, resume_updated_at = NULL WHERE id = 1");
            $stmt->execute();

            if ($old_info['exists'] && !empty($old_info['full_path'])) {
                $real_dir = realpath(__DIR__ . '/../uploads/resume');
                $real_old = realpath($old_info['full_path']);
                if ($real_dir && $real_old && str_starts_with($real_old, $real_dir)) {
                    @unlink($real_old);
                }
            }
            $feedback = "Resume removed. Public download button will be hidden until a new PDF is uploaded.";
            $feedback_type = 'success';
        } catch (Exception $e) {
            error_log("Failed to remove resume: " . $e->getMessage());
            $feedback = "Failed to remove resume record. Please try again.";
            $feedback_type = 'error';
        }
    }
}

$resume_info = get_resume_info($pdo, $db_connected);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resume | Portfolio Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">

    <?php include __DIR__ . '/admin_header.php'; ?>

    <main class="admin-main">
        <?php if (!empty($feedback)): ?>
            <div class="alert-banner <?= $feedback_type ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span><?= htmlspecialchars($feedback) ?></span>
            </div>
        <?php endif; ?>

        <div style="margin-bottom: 28px;">
            <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.5px;">Resume Management</h1>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">Upload or update your professional resume PDF for recruiters and employers.</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Current Status Card -->
            <div class="table-card" style="margin-bottom: 0;">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 8px;">Current Active Resume</h2>
                <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 24px;">Status of the PDF served to visitors clicking "Download Resume".</p>

                <?php if ($resume_info['exists']): ?>
                    <div style="background: rgba(86, 128, 233, 0.08); border: 1px solid rgba(86, 128, 233, 0.25); border-radius: var(--radius-md); padding: 22px; margin-bottom: 24px;">
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                            <div style="width: 48px; height: 48px; border-radius: 8px; background: rgba(239, 68, 68, 0.15); color: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            </div>
                            <div>
                                <strong style="display: block; font-size: 1rem; color: #ffffff;">Kian_Aguinaldo_Resume.pdf</strong>
                                <span style="font-size: 12px; color: var(--color-sky);">
                                    <?= number_format($resume_info['size'] / 1024, 1) ?> KB &bull; Last updated <?= $resume_info['updated_at'] ? date('M d, Y h:i A', strtotime($resume_info['updated_at'])) : 'Recently' ?>
                                </span>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <a href="../download-resume.php?view=1" target="_blank" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <span>Preview in Tab &nearr;</span>
                            </a>
                            <a href="../download-resume.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                <span>Test Download</span>
                            </a>
                        </div>
                    </div>

                    <form method="POST" onsubmit="return confirm('Are you sure you want to remove the active resume? The download button on your public portfolio will be hidden until a new file is uploaded.');">
                        <input type="hidden" name="action_remove_resume" value="1">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                        <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.12); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); padding: 8px 16px; font-size: 13px;">
                            Remove Active Resume
                        </button>
                    </form>
                <?php else: ?>
                    <div style="background: rgba(245, 158, 11, 0.08); border: 1px dashed rgba(245, 158, 11, 0.3); border-radius: var(--radius-md); padding: 32px 20px; text-align: center;">
                        <div style="display: flex; justify-content: center; margin-bottom: 12px; color: #fcd34d;">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        </div>
                        <strong style="display: block; font-size: 1rem; color: #fcd34d; margin-bottom: 6px;">No Resume Currently Uploaded</strong>
                        <p style="font-size: 13px; color: var(--text-muted); margin: 0 auto; max-width: 340px;">
                            The "Download Resume" button on your public portfolio is safely hidden until you upload a PDF file using the form on the right.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Upload / Replace Form -->
            <div class="table-card" style="margin-bottom: 0;">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 8px;">
                    <?= $resume_info['exists'] ? 'Replace Current Resume' : 'Upload New Resume' ?>
                </h2>
                <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 24px;">
                    Select your latest resume in PDF format. A sanitized server-side filename will be created automatically.
                </p>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action_upload_resume" value="1">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">
                            Choose PDF Document *
                        </label>
                        <input type="file" name="resume_file" accept=".pdf,application/pdf" required class="form-input" style="padding: 10px; background: rgba(7, 9, 15, 0.7); border: 1px dashed var(--border-subtle); border-radius: var(--radius-sm); width: 100%; box-sizing: border-box; color: var(--text-primary);">
                        <span style="font-size: 12px; color: var(--text-muted); margin-top: 6px; display: block;">
                            Allowed format: <strong>.PDF</strong> &bull; Maximum file size: <strong>10MB</strong>
                        </span>
                    </div>

                    <div style="background: rgba(12, 17, 30, 0.6); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 20px; font-size: 12px; color: var(--text-secondary);">
                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                            <span style="color: var(--color-sky); font-weight: bold;">[i]</span>
                            <div>
                                <strong>Professional Download Guarantee:</strong> When recruiters click "Download Resume", it is always served with the clean, professional filename: <code style="color: var(--color-cerulean);">Kian_Aguinaldo_Resume.pdf</code>.
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 14px; font-weight: 700;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        <span><?= $resume_info['exists'] ? 'Upload & Replace Resume' : 'Upload Resume PDF' ?></span>
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
