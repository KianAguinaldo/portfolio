<?php
/**
 * Public Resume Download & View Controller
 * Safely serves Kian's resume with a professional filename
 */
require_once __DIR__ . '/config/db.php';

$resume = get_resume_info($pdo, $db_connected);

if (!$resume['exists'] || !file_exists($resume['full_path'])) {
    // If not found, show a recruiter-friendly notice or redirect
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resume Not Available | Kian Aguinaldo</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <style>
            body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #07090f; color: #f8fafc; font-family: 'Inter', sans-serif; margin: 0; padding: 20px; }
            .notice-card { background: #0c111e; border: 1px solid rgba(193, 200, 196, 0.15); border-radius: 12px; padding: 36px; max-width: 480px; text-align: center; }
            .notice-card h2 { margin-top: 0; color: #5680E9; }
            .notice-card p { color: #8e9a97; line-height: 1.6; margin-bottom: 24px; }
        </style>
    </head>
    <body>
        <div class="notice-card">
            <h2>Resume Temporarily Unavailable</h2>
            <p>The resume document is currently being updated by the candidate. Please check back shortly or reach out directly through the contact section.</p>
            <a href="index.php" class="btn btn-primary" style="display: inline-block;">Return to Portfolio</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$full_path = $resume['full_path'];
$download_name = 'Kian_Aguinaldo_Resume.pdf';
$disposition = isset($_GET['view']) && $_GET['view'] == '1' ? 'inline' : 'attachment';

// Clear output buffering
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: ' . $disposition . '; filename="' . $download_name . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($full_path));

readfile($full_path);
exit;
