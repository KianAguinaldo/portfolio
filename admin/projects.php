<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$feedback = '';
$feedback_type = 'success';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') $feedback = "Project permanently deleted.";
    if ($_GET['msg'] === 'added')   $feedback = "New project published successfully!";
    if ($_GET['msg'] === 'updated') $feedback = "Project updated successfully!";
}
if (isset($_GET['err'])) {
    $feedback = "Error: " . htmlspecialchars($_GET['err']);
    $feedback_type = 'error';
}

$all_projects = get_all_projects_admin($pdo, $db_connected);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects | Portfolio Admin</title>
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

        <div class="card-header-flex">
            <div>
                <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.5px;">All Projects</h1>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    Total of <?= count($all_projects) ?> project(s) stored in MySQL database <code>portfolio_db.projects</code>.
                </p>
            </div>
            <a href="add-project.php" class="btn btn-primary" style="padding: 10px 22px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                <span>+ Add New Project</span>
            </a>
        </div>

        <div class="table-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Project</th>
                        <th>Title &amp; Links</th>
                        <th>Category</th>
                        <th>Technologies Used</th>
                        <th>Status</th>
                        <th>Date Added</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_projects)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 48px; color: var(--text-muted);">
                                No projects recorded in database. Click "+ Add New Project" to create one.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($all_projects as $proj):
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
                                    <div style="margin-top: 4px; display: flex; gap: 8px; font-size: 11px;">
                                        <?php if (!empty($proj['github_url'])): ?>
                                            <a href="<?= htmlspecialchars($proj['github_url']) ?>" target="_blank" style="color: var(--color-sky); text-decoration: none;">GitHub &nearr;</a>
                                        <?php endif; ?>
                                        <?php if (!empty($proj['live_url'])): ?>
                                            <a href="<?= htmlspecialchars($proj['live_url']) ?>" target="_blank" style="color: var(--color-cerulean); text-decoration: none;">Live Demo &nearr;</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 12px; color: var(--color-sky); text-transform: uppercase; font-weight: 600;">
                                        <?= htmlspecialchars($proj['category'] ?? 'Full Stack') ?>
                                    </span>
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
                                    <a href="edit-project.php?id=<?= $proj['id'] ?>" class="action-btn edit" style="margin-right: 6px;">
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
    </main>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="confirm-modal-overlay">
        <div class="confirm-modal-box">
            <div style="width: 52px; height: 52px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); color: #ef4444; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </div>
            <h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 8px;">Confirm Permanent Deletion</h3>
            <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 24px;">
                Are you sure you want to permanently delete <strong id="modalProjectTitle" style="color: #ffffff;"></strong>? This project will immediately disappear from your public portfolio.
            </p>
            <form method="POST" action="delete-project.php">
                <input type="hidden" name="id" id="modalProjectId" value="">
                <input type="hidden" name="ref" value="projects.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()" style="flex: 1;">Cancel</button>
                    <button type="submit" class="btn" style="flex: 1; background: #ef4444; color: #fff; border: none; cursor: pointer;">Yes, Delete Project</button>
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

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeDeleteModal();
        });
        document.getElementById('deleteModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('deleteModal')) closeDeleteModal();
        });
    </script>
</body>
</html>
