<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

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

    // Normalize empty URLs to NULL
    $github_url = empty($github_url) ? null : $github_url;
    $live_url   = empty($live_url) ? null : $live_url;

    // Validate Required Fields
    if (empty($title) || empty($description) || empty($technologies)) {
        $error = 'Please fill out all required fields: Title, Description, and Technologies.';
    } elseif (!$db_connected || !$pdo) {
        $error = 'MySQL database is not connected. Please ensure MySQL is running in XAMPP.';
    } else {
        $image_path = '';

        // 1. Check if a screenshot file was uploaded
        if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['screenshot'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'File upload error code: ' . $file['error'];
            } else {
                // Size validation (Max 5MB)
                $max_size = 5 * 1024 * 1024;
                if ($file['size'] > $max_size) {
                    $error = 'Uploaded image must not exceed 5MB.';
                } else {
                    // Check file extension
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                    // MIME Type validation
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);

                    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];

                    if (!in_array($ext, $allowed_exts) || !in_array($mime_type, $allowed_mimes)) {
                        $error = 'Invalid image format. Allowed formats: JPG, PNG, WEBP.';
                    } else {
                        // Generate unique, sanitized filename
                        $new_filename = 'proj_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                        $target_dir = __DIR__ . '/../uploads/projects/';

                        if (!is_dir($target_dir)) {
                            mkdir($target_dir, 0755, true);
                        }

                        $target_file = $target_dir . $new_filename;
                        if (move_uploaded_file($file['tmp_name'], $target_file)) {
                            $image_path = 'uploads/projects/' . $new_filename;
                        } else {
                            $error = 'Failed to move uploaded file. Check directory write permissions.';
                        }
                    }
                }
            }
        }

        // 2. Fall back to external Image URL if no file uploaded
        if (empty($error) && empty($image_path)) {
            if (!empty($image_url_input)) {
                $image_path = $image_url_input;
            } else {
                // Default high-quality tech placeholder
                $image_path = 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&q=80';
            }
        }

        // 3. Save to MySQL
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO projects (title, category, description, technologies, image, github_url, live_url, status, created_at, updated_at)
                    VALUES (:title, :cat, :desc, :tech, :img, :git, :live, :status, NOW(), NOW())
                ");
                $stmt->execute([
                    ':title'   => $title,
                    ':cat'     => $category,
                    ':desc'    => $description,
                    ':tech'    => $technologies,
                    ':img'     => $image_path,
                    ':git'     => $github_url,
                    ':live'    => $live_url,
                    ':status'  => $status
                ]);

                header('Location: projects.php?msg=added');
                exit;
            } catch (Exception $e) {
                error_log("Database insertion failed in admin/add-project.php: " . $e->getMessage());
                $error = 'Database insertion failed. Please try again.';
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
    <title>Add New Project | Portfolio Admin</title>
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
                    <h1 style="font-size: 1.65rem; font-weight: 800; letter-spacing: -0.5px;">Add New Portfolio Project</h1>
                    <p style="font-size: 13px; color: var(--text-secondary);">Fill out the details below to publish a new project into MySQL.</p>
                </div>
                <a href="projects.php" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px;">&larr; Back to Projects</a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-banner error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Project Title *</label>
                        <input type="text" name="title" class="form-input" placeholder="e.g. IT Helpdesk &amp; Asset Portal" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category" class="form-input" style="cursor: pointer;">
                            <option value="fullstack" <?= ($_POST['category'] ?? '') === 'fullstack' ? 'selected' : '' ?>>Full Stack</option>
                            <option value="frontend" <?= ($_POST['category'] ?? '') === 'frontend' ? 'selected' : '' ?>>Frontend / UI</option>
                            <option value="backend" <?= ($_POST['category'] ?? '') === 'backend' ? 'selected' : '' ?>>Backend &amp; Systems</option>
                            <option value="networking" <?= ($_POST['category'] ?? '') === 'networking' ? 'selected' : '' ?>>Networking / Infrastructure</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Technologies Used (comma-separated) *</label>
                    <input type="text" name="technologies" class="form-input" placeholder="e.g. PHP 8, MySQL, JavaScript, HTML5/CSS3" value="<?= htmlspecialchars($_POST['technologies'] ?? '') ?>" required>
                    <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">These will appear as individual badges on the public project card.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Project Description *</label>
                    <textarea name="description" class="form-textarea" placeholder="Detail the problem solved, architecture choices, technical highlights, and impact..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <!-- Screenshot Upload & URL Fallback -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Upload Project Screenshot (File)</label>
                        <input type="file" name="screenshot" class="form-input" accept="image/png, image/jpeg, image/webp" style="padding: 9px 14px;">
                        <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">Supports JPG, PNG, WEBP (Max 5MB). Stored in <code>uploads/projects/</code></small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">OR Cover Image URL (Optional)</label>
                        <input type="url" name="image_url" class="form-input" placeholder="https://images.unsplash.com/..." value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>">
                        <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">Used if no file is uploaded above.</small>
                    </div>
                </div>

                <!-- Links & Status -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">GitHub Repository URL</label>
                        <input type="url" name="github_url" class="form-input" placeholder="https://github.com/user/project" value="<?= htmlspecialchars($_POST['github_url'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Live Demo URL</label>
                        <input type="url" name="live_url" class="form-input" placeholder="https://my-live-demo.com" value="<?= htmlspecialchars($_POST['live_url'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Project Status *</label>
                    <div style="display: flex; gap: 24px; margin-top: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-primary); font-size: 14px;">
                            <input type="radio" name="status" value="published" <?= ($_POST['status'] ?? 'published') === 'published' ? 'checked' : '' ?> style="accent-color: var(--color-cerulean);">
                            <span><strong>Published</strong> (Visible on public portfolio)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-secondary); font-size: 14px;">
                            <input type="radio" name="status" value="draft" <?= ($_POST['status'] ?? '') === 'draft' ? 'checked' : '' ?> style="accent-color: var(--color-cerulean);">
                            <span><strong>Draft</strong> (Hidden from public portfolio)</span>
                        </label>
                    </div>
                </div>

                <div style="margin-top: 36px; display: flex; gap: 14px; border-top: 1px solid var(--border-subtle); padding-top: 24px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px 24px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        <span>Save Project to MySQL</span>
                    </button>
                    <a href="projects.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
