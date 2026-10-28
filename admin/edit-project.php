<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: projects.php');
    exit;
}

$project = get_project_by_id($pdo, $id);
if (!$project) {
    header('Location: projects.php?err=Project+not+found');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed (CSRF token invalid). Please try again.';
    } else {
        $title        = trim($_POST['title'] ?? '');
    $category     = trim($_POST['category'] ?? 'fullstack');
    $technologies = trim($_POST['technologies'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $github_url   = trim($_POST['github_url'] ?? '');
    $live_url     = trim($_POST['live_url'] ?? '');
    $status       = trim($_POST['status'] ?? 'published');
    $image_url_input = trim($_POST['image_url'] ?? '');

    // Normalize URLs to NULL if empty
    $github_url = empty($github_url) ? null : $github_url;
    $live_url   = empty($live_url) ? null : $live_url;

    if (empty($title) || empty($description) || empty($technologies)) {
        $error = 'Please fill out all required fields: Title, Description, and Technologies.';
    } elseif (!$db_connected || !$pdo) {
        $error = 'MySQL database is not connected.';
    } else {
        // Keep existing image by default
        $image_path = $project['image'] ?? $project['image_url'] ?? '';

        // Check if user entered a new image URL
        if (!empty($image_url_input)) {
            $image_path = $image_url_input;
        }

        // Check if a new file upload is present
        if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['screenshot'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Upload failed with error code: ' . $file['error'];
            } else {
                $max_size = 5 * 1024 * 1024;
                if ($file['size'] > $max_size) {
                    $error = 'Screenshot file size must not exceed 5MB.';
                } else {
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);

                    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];

                    if (!in_array($ext, $allowed_exts) || !in_array($mime_type, $allowed_mimes)) {
                        $error = 'Invalid image format. Allowed: JPG, PNG, WEBP.';
                    } else {
                        $new_filename = 'proj_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                        $target_dir = __DIR__ . '/../uploads/projects/';

                        if (!is_dir($target_dir)) {
                            mkdir($target_dir, 0755, true);
                        }

                        $target_file = $target_dir . $new_filename;
                        if (move_uploaded_file($file['tmp_name'], $target_file)) {
                            // Optionally remove old local file safely with directory traversal check
                            $old_img = $project['image'] ?? '';
                            if (!empty($old_img) && !preg_match('#^https?://#i', $old_img)) {
                                $old_file_path = __DIR__ . '/../' . ltrim($old_img, '/\\');
                                $real_uploads_dir = realpath(__DIR__ . '/../uploads/projects');
                                $real_old_path = realpath($old_file_path);
                                if ($real_uploads_dir && $real_old_path && str_starts_with($real_old_path, $real_uploads_dir) && file_exists($real_old_path)) {
                                    @unlink($real_old_path);
                                }
                            }

                            $image_path = 'uploads/projects/' . $new_filename;
                        } else {
                            $error = 'Failed to move uploaded file.';
                        }
                    }
                }
            }
        }

        // Update database record
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE projects
                    SET title = :title,
                        category = :cat,
                        description = :desc,
                        technologies = :tech,
                        image = :img,
                        github_url = :git,
                        live_url = :live,
                        status = :status,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':title'   => $title,
                    ':cat'     => $category,
                    ':desc'    => $description,
                    ':tech'    => $technologies,
                    ':img'     => $image_path,
                    ':git'     => $github_url,
                    ':live'    => $live_url,
                    ':status'  => $status,
                    ':id'      => $id
                ]);

                header('Location: projects.php?msg=updated');
                exit;
            } catch (Exception $e) {
                error_log("Database update failed in admin/edit-project.php: " . $e->getMessage());
                $error = 'Database update failed. Please try again.';
            }
        }
    }
}
}

// Current image source for preview
$current_img_src = get_project_image_src($project['image'] ?? '', '../');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project #<?= $id ?> | Portfolio Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">

    <?php include __DIR__ . '/admin_header.php'; ?>

    <main class="admin-main">
        <div class="form-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h1 style="font-size: 1.65rem; font-weight: 800; letter-spacing: -0.5px;">Edit Project</h1>
                    <p style="font-size: 13px; color: var(--text-secondary);">Editing: <strong><?= htmlspecialchars($project['title']) ?></strong> (ID #<?= $id ?>)</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="../index.php#projects" target="_blank" class="btn btn-secondary" style="padding: 8px 14px; font-size: 13px;">View on Live Site &nearr;</a>
                    <a href="projects.php" class="btn btn-secondary" style="padding: 8px 14px; font-size: 13px;">&larr; Back to Projects</a>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-banner error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Project Title *</label>
                        <input type="text" name="title" class="form-input" value="<?= htmlspecialchars($_POST['title'] ?? $project['title']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <?php $selected_cat = $_POST['category'] ?? $project['category'] ?? 'fullstack'; ?>
                        <select name="category" class="form-input" style="cursor: pointer;">
                            <option value="fullstack" <?= $selected_cat === 'fullstack' ? 'selected' : '' ?>>Full Stack</option>
                            <option value="frontend" <?= $selected_cat === 'frontend' ? 'selected' : '' ?>>Frontend / UI</option>
                            <option value="backend" <?= $selected_cat === 'backend' ? 'selected' : '' ?>>Backend &amp; Systems</option>
                            <option value="networking" <?= $selected_cat === 'networking' ? 'selected' : '' ?>>Networking / Infrastructure</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Technologies (comma-separated) *</label>
                    <input type="text" name="technologies" class="form-input" value="<?= htmlspecialchars($_POST['technologies'] ?? $project['technologies']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Project Description *</label>
                    <textarea name="description" class="form-textarea" required><?= htmlspecialchars($_POST['description'] ?? $project['description']) ?></textarea>
                </div>

                <!-- Current Image & Replacement -->
                <div class="form-group">
                    <label class="form-label">Current Screenshot</label>
                    <div class="image-preview-box">
                        <img src="<?= htmlspecialchars($current_img_src) ?>" alt="Current preview">
                        <div>
                            <div style="font-size: 13px; color: var(--text-primary); font-family: var(--font-mono); word-break: break-all;">
                                <?= htmlspecialchars($project['image'] ?? 'None') ?>
                            </div>
                            <small style="color: var(--text-muted); font-size: 12px;">Upload a new image below or change the URL to replace it.</small>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Upload New Screenshot</label>
                        <input type="file" name="screenshot" class="form-input" accept="image/png, image/jpeg, image/webp" style="padding: 9px 14px;">
                        <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">Leave empty to keep current image.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">OR Change Image URL</label>
                        <input type="url" name="image_url" class="form-input" placeholder="https://..." value="<?= htmlspecialchars($_POST['image_url'] ?? (preg_match('#^https?://#i', $project['image'] ?? '') ? $project['image'] : '')) ?>">
                        <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">External image URL fallback.</small>
                    </div>
                </div>

                <!-- Links & Status -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">GitHub Repository URL</label>
                        <input type="url" name="github_url" class="form-input" placeholder="https://github.com/..." value="<?= htmlspecialchars($_POST['github_url'] ?? $project['github_url'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Live Demo URL</label>
                        <input type="url" name="live_url" class="form-input" placeholder="https://..." value="<?= htmlspecialchars($_POST['live_url'] ?? $project['live_url'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Project Status *</label>
                    <?php $curr_status = $_POST['status'] ?? $project['status'] ?? 'published'; ?>
                    <div style="display: flex; gap: 24px; margin-top: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-primary); font-size: 14px;">
                            <input type="radio" name="status" value="published" <?= $curr_status === 'published' ? 'checked' : '' ?> style="accent-color: var(--color-cerulean);">
                            <span><strong>Published</strong> (Visible on public portfolio)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-secondary); font-size: 14px;">
                            <input type="radio" name="status" value="draft" <?= $curr_status === 'draft' ? 'checked' : '' ?> style="accent-color: var(--color-cerulean);">
                            <span><strong>Draft</strong> (Hidden from public portfolio)</span>
                        </label>
                    </div>
                </div>

                <div style="margin-top: 36px; display: flex; gap: 14px; border-top: 1px solid var(--border-subtle); padding-top: 24px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px 24px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        <span>Update Project in MySQL</span>
                    </button>
                    <a href="projects.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
