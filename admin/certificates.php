<?php
/**
 * Admin Certificates Management Page
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$feedback = '';
$feedback_type = 'success';

// Handle Action Messages from Redirects
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') $feedback = "Certificate permanently deleted.";
    if ($_GET['msg'] === 'added')   $feedback = "New certificate successfully recorded!";
    if ($_GET['msg'] === 'updated') $feedback = "Certificate updated successfully!";
    if ($_GET['msg'] === 'toggled') $feedback = "Certificate status updated!";
}
if (isset($_GET['err'])) {
    $feedback = "Error: " . htmlspecialchars($_GET['err']);
    $feedback_type = 'error';
}

// Handle Quick Status Toggle via POST with CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_toggle_status'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $feedback = "CSRF security check failed.";
        $feedback_type = 'error';
    } elseif ($db_connected && $pdo) {
        $id = (int)($_POST['id'] ?? 0);
        $new_status = $_POST['new_status'] === 'published' ? 'published' : 'draft';
        try {
            $stmt = $pdo->prepare("UPDATE certificates SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $new_status, ':id' => $id]);
            header("Location: certificates.php?msg=toggled");
            exit;
        } catch (Exception $e) {
            error_log("Failed to update status in admin/certificates.php: " . $e->getMessage());
            $feedback = "Failed to update certificate status. Please try again.";
            $feedback_type = 'error';
        }
    }
}

// Fetch all certificates (published + draft)
$all_certificates = get_certificates($pdo, $db_connected, false);

// Filter by status or search keyword if requested
$filter_status = trim($_GET['status'] ?? 'all');
$search_query  = trim($_GET['q'] ?? '');

$filtered_certificates = array_filter($all_certificates, function($cert) use ($filter_status, $search_query) {
    if ($filter_status !== 'all' && ($cert['status'] ?? 'published') !== $filter_status) {
        return false;
    }
    if (!empty($search_query)) {
        $haystack = strtolower($cert['title'] . ' ' . $cert['organization'] . ' ' . ($cert['description'] ?? ''));
        if (strpos($haystack, strtolower($search_query)) === false) {
            return false;
        }
    }
    return true;
});

// Counts
$total_count = count($all_certificates);
$published_count = 0;
$draft_count = 0;
foreach ($all_certificates as $c) {
    if (($c['status'] ?? 'published') === 'published') {
        $published_count++;
    } else {
        $draft_count++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Certificates | Portfolio Admin</title>
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

        <!-- Header -->
        <div class="card-header-flex">
            <div>
                <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.5px;">Certificates &amp; Achievements</h1>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">
                    Manage training credentials, verified certifications, and workshop completions displayed on your portfolio.
                </p>
            </div>
            <a href="add-certificate.php" class="btn btn-primary" style="padding: 10px 22px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                <span>+ Add Certificate</span>
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <div class="table-card" style="padding: 18px 24px; margin-bottom: 24px;">
            <form method="GET" style="display: flex; gap: 16px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <a href="certificates.php" class="admin-tab <?= $filter_status === 'all' && empty($search_query) ? 'active' : '' ?>" style="padding: 6px 14px; font-size: 12px;">
                        All (<?= $total_count ?>)
                    </a>
                    <a href="certificates.php?status=published" class="admin-tab <?= $filter_status === 'published' ? 'active' : '' ?>" style="padding: 6px 14px; font-size: 12px;">
                        Published (<?= $published_count ?>)
                    </a>
                    <a href="certificates.php?status=draft" class="admin-tab <?= $filter_status === 'draft' ? 'active' : '' ?>" style="padding: 6px 14px; font-size: 12px;">
                        Drafts (<?= $draft_count ?>)
                    </a>
                </div>

                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search certificates..." class="form-input" style="padding: 7px 14px; font-size: 13px; width: 220px; background: rgba(7, 9, 15, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: var(--text-primary);">
                    <?php if ($filter_status !== 'all'): ?>
                        <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-secondary" style="padding: 7px 14px; font-size: 13px;">Search</button>
                    <?php if (!empty($search_query) || $filter_status !== 'all'): ?>
                        <a href="certificates.php" class="btn" style="padding: 7px 12px; font-size: 13px; color: var(--text-muted);">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Certificates Table -->
        <div class="table-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 75px;">Preview</th>
                        <th>Title &amp; Organization</th>
                        <th>Date Issued</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($filtered_certificates)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 54px 20px; color: var(--text-muted);">
                                <div style="font-size: 38px; margin-bottom: 12px;">&#127941;</div>
                                <strong style="display: block; font-size: 1.05rem; color: #f8fafc; margin-bottom: 6px;">
                                    <?= empty($all_certificates) ? 'No Certificates Added Yet' : 'No Certificates Match Your Filter' ?>
                                </strong>
                                <p style="font-size: 13px; max-width: 400px; margin: 0 auto 18px auto;">
                                    <?= empty($all_certificates)
                                        ? 'Click "+ Add Certificate" to upload and showcase your first verified certificate or training credential.'
                                        : 'Try clearing your search query or switching tabs to view all records.' ?>
                                </p>
                                <?php if (empty($all_certificates)): ?>
                                    <a href="add-certificate.php" class="btn btn-primary" style="padding: 8px 18px; font-size: 13px;">+ Add Your First Certificate</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($filtered_certificates as $cert):
                            $status = $cert['status'] ?? 'published';
                            $file_src = get_certificate_file_src($cert['file_path'] ?? '', '../');
                            $file_type = $cert['file_type'] ?? 'image';
                            $has_file = !empty($cert['file_path']);
                            $date_formatted = !empty($cert['issue_date']) ? date('M Y', strtotime($cert['issue_date'])) : 'N/A';
                        ?>
                            <tr>
                                <td>
                                    <?php if ($has_file && $file_type === 'image'): ?>
                                        <a href="<?= htmlspecialchars($file_src) ?>" target="_blank" title="View Full Image">
                                            <img src="<?= htmlspecialchars($file_src) ?>" alt="cert-thumb" class="project-thumb">
                                        </a>
                                    <?php elseif ($has_file && $file_type === 'pdf'): ?>
                                        <a href="<?= htmlspecialchars($file_src) ?>" target="_blank" title="Open PDF Document" style="display: flex; align-items: center; justify-content: center; width: 60px; height: 42px; border-radius: 6px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; text-decoration: none;">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                        </a>
                                    <?php else: ?>
                                        <div style="display: flex; align-items: center; justify-content: center; width: 60px; height: 42px; border-radius: 6px; background: rgba(86, 128, 233, 0.1); border: 1px solid var(--border-subtle); color: var(--color-sky);">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"></circle><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"></path></svg>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color: var(--text-primary); font-size: 0.95rem; display: block;"><?= htmlspecialchars($cert['title']) ?></strong>
                                    <div style="margin-top: 3px; font-size: 13px; color: var(--color-sky);">
                                        <?= htmlspecialchars($cert['organization']) ?>
                                    </div>
                                    <?php if (!empty($cert['description'])): ?>
                                        <p style="font-size: 12px; color: var(--text-muted); margin: 4px 0 0 0; max-width: 450px;">
                                            <?= htmlspecialchars(mb_strimwidth($cert['description'], 0, 110, '...')) ?>
                                        </p>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 13px; color: var(--text-secondary); white-space: nowrap;">
                                    <?= htmlspecialchars($date_formatted) ?>
                                </td>
                                <td style="font-family: var(--font-mono); font-size: 13px; color: var(--color-silver);">
                                    #<?= (int)($cert['display_order'] ?? 0) ?>
                                </td>
                                <td>
                                    <!-- Status Badge with Toggle Form -->
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="action_toggle_status" value="1">
                                        <input type="hidden" name="id" value="<?= $cert['id'] ?>">
                                        <input type="hidden" name="new_status" value="<?= $status === 'published' ? 'draft' : 'published' ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                                        <button type="submit" title="Click to toggle status" style="background: none; border: none; padding: 0; cursor: pointer;">
                                            <span class="admin-badge <?= $status === 'published' ? 'badge-published' : 'badge-draft' ?>">
                                                <?= $status === 'published' ? '&bull; Published' : '&cir; Draft' ?>
                                            </span>
                                        </button>
                                    </form>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <?php if ($has_file): ?>
                                        <a href="<?= htmlspecialchars($file_src) ?>" target="_blank" class="action-btn" style="margin-right: 6px; background: rgba(86, 128, 233, 0.12); color: var(--color-sky); border-color: rgba(86, 128, 233, 0.3);">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            <span>View</span>
                                        </a>
                                    <?php endif; ?>
                                    <a href="edit-certificate.php?id=<?= $cert['id'] ?>" class="action-btn edit" style="margin-right: 6px;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        <span>Edit</span>
                                    </a>
                                    <button type="button" class="action-btn delete" onclick="confirmDeleteCert(<?= $cert['id'] ?>, '<?= htmlspecialchars(addslashes($cert['title'])) ?>')">
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

    <!-- Delete Confirmation Modal (Secure POST with CSRF) -->
    <div id="deleteCertModal" class="confirm-modal-overlay">
        <div class="confirm-modal-box">
            <div style="width: 52px; height: 52px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); color: #ef4444; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </div>
            <h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 8px;">Confirm Certificate Deletion</h3>
            <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 24px;">
                Are you sure you want to permanently delete <strong id="modalCertTitle" style="color: #ffffff;"></strong>? Associated certificate files will also be safely removed.
            </p>
            <form method="POST" action="delete-certificate.php">
                <input type="hidden" name="id" id="modalCertId" value="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteCertModal()" style="flex: 1;">Cancel</button>
                    <button type="submit" class="btn" style="flex: 1; background: #ef4444; color: #fff; border: none; cursor: pointer;">Yes, Delete Certificate</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmDeleteCert(id, title) {
            document.getElementById('modalCertTitle').textContent = `"${title}"`;
            document.getElementById('modalCertId').value = id;
            document.getElementById('deleteCertModal').classList.add('active');
        }

        function closeDeleteCertModal() {
            document.getElementById('deleteCertModal').classList.remove('active');
        }

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeDeleteCertModal();
        });
        document.getElementById('deleteCertModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('deleteCertModal')) closeDeleteCertModal();
        });
    </script>
</body>
</html>
