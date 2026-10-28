<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

// Handle Password / Account Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_account'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation (CSRF) failed. Please try again.';
    } else {
        $current_pass = trim($_POST['current_password'] ?? '');
        $new_username = trim($_POST['new_username'] ?? '');
        $new_pass     = trim($_POST['new_password'] ?? '');
        $confirm_pass = trim($_POST['confirm_password'] ?? '');

        $admin_user = $_SESSION['admin_username'] ?? '';

        if (empty($admin_user)) {
            $error = 'Session expired. Please log in again.';
        } elseif (empty($current_pass)) {
            $error = 'Please enter your current password to make changes.';
        } elseif (!$db_connected || !$pdo) {
            $error = 'Database is offline.';
        } else {
            try {
                // Verify current password against database for the authenticated user
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :u LIMIT 1");
                $stmt->execute([':u' => $admin_user]);
                $user_row = $stmt->fetch();

                if (!$user_row || !password_verify($current_pass, $user_row['password_hash'])) {
                    $error = 'Current password is incorrect.';
                } else {
                    $final_username = !empty($new_username) ? $new_username : $admin_user;

                    // Prevent duplicate username collision
                    if ($final_username !== $admin_user) {
                        $dup_stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = :u AND id != :id");
                        $dup_stmt->execute([':u' => $final_username, ':id' => $user_row['id']]);
                        if ((int)$dup_stmt->fetchColumn() > 0) {
                            $error = 'The username "' . htmlspecialchars($final_username) . '" is already in use.';
                        }
                    }

                    if (empty($error)) {
                        if (!empty($new_pass)) {
                            if (strlen($new_pass) < 6) {
                                $error = 'New password must be at least 6 characters long.';
                            } elseif ($new_pass !== $confirm_pass) {
                                $error = 'New password and confirmation do not match.';
                            } else {
                                $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
                                $up_stmt = $pdo->prepare("UPDATE admin_users SET username = :new, password_hash = :p WHERE id = :id");
                                $up_stmt->execute([':new' => $final_username, ':p' => $new_hash, ':id' => $user_row['id']]);
                                $_SESSION['admin_username'] = $final_username;
                                $success = 'Account credentials updated successfully!';
                            }
                        } else {
                            if ($final_username !== $admin_user) {
                                $up_stmt = $pdo->prepare("UPDATE admin_users SET username = :new WHERE id = :id");
                                $up_stmt->execute([':new' => $final_username, ':id' => $user_row['id']]);
                                $_SESSION['admin_username'] = $final_username;
                                $success = 'Admin username updated successfully!';
                            } else {
                                $error = 'No changes were specified to update.';
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                error_log('Failed to update credentials: ' . $e->getMessage());
                $error = 'Failed to update credentials. Please try again.';
            }
        }
    }
}

// Handle Profile Information Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_profile'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation (CSRF) failed. Please try again.';
    } else {
        $full_name    = trim($_POST['full_name'] ?? '');
        $title        = trim($_POST['title'] ?? '');
        $tagline      = trim($_POST['tagline'] ?? '');
        $bio          = trim($_POST['bio'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $phone        = trim($_POST['phone'] ?? '');
        $location     = trim($_POST['location'] ?? '');
        $availability = trim($_POST['availability'] ?? '');

        if (empty($full_name) || empty($email)) {
            $error = 'Full name and email cannot be empty.';
        } elseif ($db_connected && $pdo) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE profile SET
                        full_name = :name,
                        title = :title,
                        tagline = :tagline,
                        bio = :bio,
                        email = :email,
                        phone = :phone,
                        location = :loc,
                        availability = :avail
                    WHERE id = 1
                ");
                $stmt->execute([
                    ':name'    => $full_name,
                    ':title'   => $title,
                    ':tagline' => $tagline,
                    ':bio'     => $bio,
                    ':email'   => $email,
                    ':phone'   => $phone,
                    ':loc'     => $location,
                    ':avail'   => $availability
                ]);
                $success = 'Public profile information saved to MySQL!';
            } catch (Exception $e) {
                error_log('Failed to update profile: ' . $e->getMessage());
                $error = 'Failed to update profile. Please try again.';
            }
        }
    }
}

$profile = get_profile_data($pdo, $db_connected);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings | Portfolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">

    <?php include __DIR__ . '/admin_header.php'; ?>

    <main class="admin-main">
        <div style="margin-bottom: 28px;">
            <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.5px;">System &amp; Account Settings</h1>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">Configure your administrator credentials and update public portfolio profile details.</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert-banner success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert-banner error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Admin Credentials -->
            <div class="table-card" style="margin-bottom: 0;">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 8px;">Security &amp; Password</h2>
                <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 24px;">Update admin login username and password (hashed with Bcrypt).</p>

                <form method="POST">
                    <input type="hidden" name="action_account" value="1">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">

                    <div class="form-group">
                        <label class="form-label">Current Username</label>
                        <input type="text" class="form-input" value="<?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?>" disabled style="opacity: 0.6;">
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Username (Optional)</label>
                        <input type="text" name="new_username" class="form-input" placeholder="Leave blank to keep current">
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Password (Optional)</label>
                        <input type="password" name="new_password" class="form-input" placeholder="Minimum 6 characters">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-input" placeholder="Repeat new password">
                    </div>

                    <div class="form-group" style="padding-top: 14px; border-top: 1px solid var(--border-subtle);">
                        <label class="form-label">Current Password * (Required for verification)</label>
                        <input type="password" name="current_password" class="form-input" placeholder="Current password" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Update Credentials</button>
                </form>
            </div>

            <!-- Profile Info -->
            <div class="table-card" style="margin-bottom: 0;">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 8px;">Public Portfolio Profile</h2>
                <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 24px;">Directly updates the information displayed on your public portfolio.</p>

                <form method="POST">
                    <input type="hidden" name="action_profile" value="1">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">

                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-input" value="<?= htmlspecialchars($profile['full_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Title / Degree *</label>
                        <input type="text" name="title" class="form-input" value="<?= htmlspecialchars($profile['title']) ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($profile['email']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number *</label>
                            <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($profile['phone']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-input" value="<?= htmlspecialchars($profile['location']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Availability Status</label>
                            <input type="text" name="availability" class="form-input" value="<?= htmlspecialchars($profile['availability']) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tagline</label>
                        <input type="text" name="tagline" class="form-input" value="<?= htmlspecialchars($profile['tagline']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Bio Description</label>
                        <textarea name="bio" class="form-textarea" style="min-height: 90px;"><?= htmlspecialchars($profile['bio']) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Save Profile Information</button>
                </form>
            </div>
        </div>

        <!-- Quick Resume Management Card -->
        <?php $resume_quick = get_resume_info($pdo, $db_connected); ?>
        <div class="table-card" style="margin-top: 30px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; border-radius: 8px; background: rgba(86, 128, 233, 0.15); color: var(--color-sky); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    </div>
                    <div>
                        <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0 0 4px 0;">Recruiter Resume Document</h2>
                        <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">
                            Status: <strong style="color: <?= $resume_quick['exists'] ? '#34d399' : '#fbbf24' ?>;"><?= $resume_quick['exists'] ? 'Active PDF Uploaded' : 'No Resume Uploaded Yet' ?></strong>
                            <?php if ($resume_quick['exists']): ?>
                                &bull; <?= number_format($resume_quick['size'] / 1024, 1) ?> KB
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <?php if ($resume_quick['exists']): ?>
                        <a href="../download-resume.php?view=1" target="_blank" class="btn btn-secondary" style="padding: 9px 16px; font-size: 13px;">Preview PDF &nearr;</a>
                    <?php endif; ?>
                    <a href="resume.php" class="btn btn-primary" style="padding: 9px 18px; font-size: 13px;">Manage / Upload Resume &rarr;</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
