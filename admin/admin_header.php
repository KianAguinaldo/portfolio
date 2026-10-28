<?php
/**
 * Shared Admin Navigation & Top Bar
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="admin-nav">
    <div class="admin-nav-brand">
        <a href="index.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit;">
            <div class="brand-icon">&lt;/&gt;</div>
            <div>
                <strong style="font-size: 15px; letter-spacing: -0.2px;">Portfolio Admin Control</strong>
                <div style="font-size: 12px; color: var(--color-sky);">Kian Lee Raian M. Aguinaldo</div>
            </div>
        </a>
    </div>

    <nav class="admin-nav-links">
        <a href="index.php" class="admin-tab <?= $current_page === 'index.php' ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            <span>Dashboard</span>
        </a>
        <a href="projects.php" class="admin-tab <?= in_array($current_page, ['projects.php', 'add-project.php', 'edit-project.php']) ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            <span>Projects</span>
        </a>
        <a href="certificates.php" class="admin-tab <?= in_array($current_page, ['certificates.php', 'add-certificate.php', 'edit-certificate.php']) ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"></circle><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"></path></svg>
            <span>Certificates</span>
        </a>
        <a href="resume.php" class="admin-tab <?= $current_page === 'resume.php' ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            <span>Resume</span>
        </a>
        <a href="settings.php" class="admin-tab <?= $current_page === 'settings.php' ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            <span>Settings</span>
        </a>
    </nav>

    <div class="admin-nav-actions">
        <a href="../index.php" target="_blank" class="btn btn-secondary" style="padding: 7px 14px; font-size: 13px;">
            <span>Live Portfolio &nearr;</span>
        </a>
        <a href="logout.php" class="btn btn-secondary" style="padding: 7px 14px; font-size: 13px; color: #fca5a5; border-color: rgba(239, 68, 68, 0.35);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span>Logout</span>
        </a>
    </div>
</header>
