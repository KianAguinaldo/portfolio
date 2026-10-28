<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$feedback = '';
$feedback_type = 'success';

// Handle Action Messages from Redirects
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') $feedback = "Project permanently deleted successfully.";
    if ($_GET['msg'] === 'added')   $feedback = "New project added successfully!";
    if ($_GET['msg'] === 'updated') $feedback = "Project updated successfully!";
}
if (isset($_GET['err'])) {
    $feedback = "Error: " . htmlspecialchars($_GET['err']);
    $feedback_type = 'error';
}

// Handle In-line Actions for Inquiries
if ($db_connected && $pdo) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_msg'])) {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $feedback = "Security validation (CSRF) failed. Please try again.";
            $feedback_type = "error";
        } else {
            $id = (int)($_POST['id'] ?? 0);
            $action = $_POST['action_msg'] ?? '';
            if ($action === 'mark_read' && $id > 0) {
                $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $feedback = "Message marked as read.";
            } elseif ($action === 'delete_msg' && $id > 0) {
                $stmt = $pdo->prepare("DELETE FROM messages WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $feedback = "Message deleted successfully.";
            }
        }
    }
}

// Fetch Messages
$messages = [];
$unread_count = 0;
if ($db_connected && $pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM messages ORDER BY id DESC LIMIT 10");
        $messages = $stmt->fetchAll();

        $unread_stmt = $pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0");
        $unread_count = (int)$unread_stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Error reading messages in admin/index.php: " . $e->getMessage());
        $feedback = "Error reading messages. Please try again.";
        $feedback_type = 'error';
    }
}

// Fetch Projects
$all_projects = get_all_projects_admin($pdo, $db_connected);
$published_count = 0;
foreach ($all_projects as $p) {
    if (($p['status'] ?? 'published') === 'published') {
        $published_count++;
    }
}
$recent_projects = array_slice($all_projects, 0, 5);

// Fetch Certificates & Resume
$all_certificates = get_certificates($pdo, $db_connected, false);
$published_certs_count = 0;
foreach ($all_certificates as $c) {
    if (($c['status'] ?? 'published') === 'published') {
        $published_certs_count++;
    }
}
$recent_certificates = array_slice($all_certificates, 0, 5);
$resume_info = get_resume_info($pdo, $db_connected);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | IT Portfolio Control Panel</title>
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

        <!-- Welcome Strip -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; flex-wrap: wrap; gap: 14px;">
            <div>
                <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.5px;">Portfolio Overview</h1>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">Manage showcase projects, inspect database status, and review visitor inquiries.</p>
            </div>
            <a href="add-project.php" class="btn btn-primary" style="padding: 10px 20px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                <span>+ Add Project</span>
            </a>
        </div>

        <!-- Metric Cards -->
        <div class="metric-cards" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));">
            <div class="metric-card">
                <div>
                    <span style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Projects</span>
                    <div class="metric-val"><?= count($all_projects) ?></div>
                    <span style="font-size: 12px; color: var(--color-sky);"><?= $published_count ?> Active on Site</span>
                </div>
                <div class="feature-icon" style="margin-bottom: 0;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                </div>
            </div>

            <div class="metric-card">
                <div>
                    <span style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Certificates</span>
                    <div class="metric-val"><?= count($all_certificates) ?></div>
                    <span style="font-size: 12px; color: var(--color-sky);"><?= $published_certs_count ?> Published</span>
                </div>
                <div class="feature-icon" style="margin-bottom: 0; background: rgba(90, 185, 234, 0.12); color: var(--color-cerulean);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"></circle><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"></path></svg>
                </div>
            </div>

            <div class="metric-card">
                <div>
                    <span style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Active Resume</span>
                    <div class="metric-val" style="font-size: 1.35rem; color: <?= $resume_info['exists'] ? '#10b981' : '#f59e0b' ?>;">
                        <?= $resume_info['exists'] ? 'Active PDF' : 'Not Set' ?>
                    </div>
                    <span style="font-size: 12px; color: var(--text-secondary);">
                        <a href="resume.php" style="color: var(--color-sky); text-decoration: none;">Manage PDF &rarr;</a>
                    </span>
                </div>
                <div class="feature-icon" style="margin-bottom: 0; background: <?= $resume_info['exists'] ? 'rgba(16, 185, 129, 0.12)' : 'rgba(245, 158, 11, 0.12)' ?>; color: <?= $resume_info['exists'] ? '#10b981' : '#f59e0b' ?>;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                </div>
            </div>

            <div class="metric-card">
                <div>
                    <span style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Inquiries</span>
                    <div class="metric-val" style="color: <?= $unread_count > 0 ? 'var(--color-cerulean)' : 'var(--text-muted)' ?>">
                        <?= $unread_count ?>
                    </div>
                    <span style="font-size: 12px; color: var(--text-secondary);"><?= count($messages) ?> Total</span>
                </div>
                <div class="feature-icon" style="margin-bottom: 0; background: rgba(136, 96, 208, 0.12); color: var(--color-purple);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
        </div>

        <!-- Recent Projects Section -->
        <div class="table-card">
            <div class="card-header-flex">
                <div>
                    <h2 style="font-size: 1.3rem; font-weight: 700;">Recent Projects</h2>
                    <p style="font-size: 13px; color: var(--text-secondary);">Latest additions to your portfolio showcase.</p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="projects.php" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px;">View All Projects (<?= count($all_projects) ?>)</a>
                    <a href="add-project.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">+ Add Project</a>
                </div>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Screenshot</th>
                        <th>Project Title</th>
                        <th>Technologies</th>
                        <th>Status</th>
                        <th>Date Added</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_projects)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                No projects in the database. Click "+ Add Project" to create your first portfolio entry!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_projects as $proj):
                            $img_src = get_project_image_src($proj['image'] ?? $proj['image_url'] ?? '', '../');
                            $status = $proj['status'] ?? 'published';
                            $created_date = !empty($proj['created_at']) ? date('M d, Y', strtotime($proj['created_at'])) : 'N/A';
                        ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($img_src) ?>" alt="thumb" class="project-thumb">
                                </td>
                                <td>
                                    <strong style="color: var(--text-primary); font-size: 0.95rem;"><?= htmlspecialchars($proj['title']) ?></strong>
                                    <?php if (!empty($proj['category'])): ?>
                                        <div style="font-size: 11px; color: var(--color-sky); text-transform: uppercase;"><?= htmlspecialchars($proj['category']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-family: var(--font-mono); font-size: 12px; color: var(--text-secondary); max-width: 280px;">
                                    <?= htmlspecialchars($proj['technologies']) ?>
                                </td>
                                <td>
                                    <span class="admin-badge <?= $status === 'published' ? 'badge-published' : 'badge-draft' ?>">
                                        <?= htmlspecialchars(ucfirst($status)) ?>
                                    </span>
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted); white-space: nowrap;">
                                    <?= htmlspecialchars($created_date) ?>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="edit-project.php?id=<?= $proj['id'] ?>" class="action-btn edit">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        <span>Edit</span>
                                    </a>
                                    <button type="button" class="action-btn delete" onclick="confirmDelete(<?= $proj['id'] ?>, '<?= htmlspecialchars(addslashes($proj['title'])) ?>')">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        <span>Delete</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Certificates Section -->
        <div class="table-card">
            <div class="card-header-flex">
                <div>
                    <h2 style="font-size: 1.3rem; font-weight: 700;">Recent Certificates &amp; Credentials</h2>
                    <p style="font-size: 13px; color: var(--text-secondary);">Verified credentials and training records published to your portfolio.</p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="certificates.php" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px;">Manage All Certificates (<?= count($all_certificates) ?>)</a>
                    <a href="add-certificate.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">+ Add Certificate</a>
                </div>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Preview</th>
                        <th>Title &amp; Organization</th>
                        <th>Date Issued</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_certificates)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                No certificates recorded in the database. Click "+ Add Certificate" to showcase your training and certifications!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_certificates as $cert):
                            $c_file = get_certificate_file_src($cert['file_path'] ?? '', '../');
                            $c_type = $cert['file_type'] ?? 'image';
                            $c_status = $cert['status'] ?? 'published';
                            $c_date = !empty($cert['issue_date']) ? date('M Y', strtotime($cert['issue_date'])) : 'N/A';
                        ?>
                            <tr>
                                <td>
                                    <?php if (!empty($cert['file_path']) && $c_type === 'image'): ?>
                                        <img src="<?= htmlspecialchars($c_file) ?>" alt="thumb" class="project-thumb">
                                    <?php elseif (!empty($cert['file_path']) && $c_type === 'pdf'): ?>
                                        <div style="display: flex; align-items: center; justify-content: center; width: 60px; height: 42px; border-radius: 6px; background: rgba(239, 68, 68, 0.15); color: #ef4444; font-size: 11px; font-weight: 700;">PDF</div>
                                    <?php else: ?>
                                        <div style="display: flex; align-items: center; justify-content: center; width: 60px; height: 42px; border-radius: 6px; background: rgba(86, 128, 233, 0.1); color: var(--color-sky);">&#127941;</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color: var(--text-primary); font-size: 0.95rem;"><?= htmlspecialchars($cert['title']) ?></strong>
                                    <div style="font-size: 12px; color: var(--color-sky);"><?= htmlspecialchars($cert['organization']) ?></div>
                                </td>
                                <td style="font-size: 12px; color: var(--text-secondary); white-space: nowrap;">
                                    <?= htmlspecialchars($c_date) ?>
                                </td>
                                <td>
                                    <span class="admin-badge <?= $c_status === 'published' ? 'badge-published' : 'badge-draft' ?>">
                                        <?= htmlspecialchars(ucfirst($c_status)) ?>
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="edit-certificate.php?id=<?= $cert['id'] ?>" class="action-btn edit">
                                        <span>Edit</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Inquiries / Messages Section -->
        <div class="table-card">
            <div class="card-header-flex">
                <div>
                    <h2 style="font-size: 1.3rem; font-weight: 700;">Client Inquiries &amp; Messages</h2>
                    <p style="font-size: 13px; color: var(--text-secondary);">Direct contact submissions saved into MySQL <code>portfolio_db.messages</code>.</p>
                </div>
            </div>

            <?php if (empty($messages)): ?>
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    No messages received yet. When visitors fill out your portfolio contact form, they will appear right here.
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Status</th>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($msg['is_read'])): ?>
                                        <span class="admin-badge" style="background: rgba(193, 200, 196, 0.1); color: var(--text-muted);">Read</span>
                                    <?php else: ?>
                                        <span class="admin-badge badge-published">NEW</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color: var(--text-primary);"><?= htmlspecialchars($msg['name']) ?></strong><br>
                                    <small style="color: var(--color-sky);"><?= htmlspecialchars($msg['email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($msg['subject']) ?></td>
                                <td style="max-width: 360px; color: var(--text-secondary); font-size: 13px;">
                                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted); white-space: nowrap;">
                                    <?= date('M d, Y H:i', strtotime($msg['created_at'])) ?>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <div style="display: inline-flex; gap: 6px; justify-content: flex-end;">
                                        <?php if (empty($msg['is_read'])): ?>
                                            <form method="POST" style="display:inline; margin:0;">
                                                <input type="hidden" name="action_msg" value="mark_read">
                                                <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                                                <button type="submit" class="action-btn edit" style="cursor:pointer; border:none; background:transparent;">Mark Read</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('Delete this message permanently?');">
                                            <input type="hidden" name="action_msg" value="delete_msg">
                                            <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                                            <button type="submit" class="action-btn delete" style="cursor:pointer; border:none; background:transparent;">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="confirm-modal-overlay">
        <div class="confirm-modal-box">
            <div style="width: 52px; height: 52px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); color: #ef4444; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </div>
            <h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 8px;">Confirm Project Deletion</h3>
            <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 24px;">
                Are you sure you want to permanently delete <strong id="modalProjectTitle" style="color: #ffffff;"></strong>? This action cannot be undone and will immediately remove the project from the public portfolio.
            </p>
            <form method="POST" action="delete-project.php">
                <input type="hidden" name="id" id="modalProjectId" value="">
                <input type="hidden" name="ref" value="index.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()" style="flex: 1;">Cancel</button>
                    <button type="submit" class="btn" style="flex: 1; background: #ef4444; color: #fff; border: none; cursor: pointer;">Yes, Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmDelete(id, title) {
            document.getElementById('modalProjectTitle').textContent = `"${title}"`;
            document.getElementById('modalProjectId').value = id;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // Close on escape or outside click
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeDeleteModal();
        });
        document.getElementById('deleteModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('deleteModal')) closeDeleteModal();
        });
    </script>
</body>
</html>