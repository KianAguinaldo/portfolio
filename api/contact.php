<?php
/**
 * Contact Form API Endpoint
 * Handles AJAX submissions, validates data, and stores messages into MySQL
 */

header('Content-Type: application/json; charset=utf-8');

// Allow POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Support both form-data and JSON payload
$input = $_POST;
if (empty($input)) {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $input = $json;
        }
    }
}

$name    = trim($input['name'] ?? '');
$email   = trim($input['email'] ?? '');
$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');

// Validation
$errors = [];

if (empty($name) || mb_strlen($name) < 2) {
    $errors[] = 'Please enter your full name (minimum 2 characters).';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if (empty($subject) || mb_strlen($subject) < 3) {
    $errors[] = 'Please enter a subject (minimum 3 characters).';
}

if (empty($message) || mb_strlen($message) < 10) {
    $errors[] = 'Please enter a message (minimum 10 characters).';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => implode(' ', $errors)
    ]);
    exit;
}

// IP Address capture
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

// If Database is connected, store directly in MySQL
if ($db_connected && $pdo) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO messages (name, email, subject, message, is_read, ip_address, created_at)
            VALUES (:name, :email, :subject, :message, 0, :ip, NOW())
        ");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':subject' => $subject,
            ':message' => $message,
            ':ip' => $ip_address
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Thank you! Your message has been safely saved to the database. I will get back to you shortly.',
            'db_stored' => true
        ]);
        exit;
    } catch (Exception $e) {
        error_log("Database error in api/contact.php: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error while saving message. Please try again later.'
        ]);
        exit;
    }
} else {
    http_response_code(503);
    echo json_encode([
        'status' => 'error',
        'message' => 'Message service is temporarily unavailable because the database is offline. Please reach out directly via email.',
        'db_stored' => false
    ]);
    exit;
}
