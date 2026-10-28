<?php
/**
 * Database Configuration & Helper Utilities (PDO)
 * Default XAMPP settings: Host=127.0.0.1, Port=3306, User=root, Pass="", DB=portfolio_db
 */

// Load environment configuration if available
$env_file = __DIR__ . '/../.env';
$db_host = '127.0.0.1';
$db_port = '3306';
$db_name = 'portfolio_db';
$db_user = 'root';
$db_pass = '';

if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            // Remove surrounding quotes if present
            $val = trim($val, "\"'");
            switch ($key) {
                case 'DB_HOST': $db_host = $val; break;
                case 'DB_PORT': $db_port = $val; break;
                case 'DB_NAME': $db_name = $val; break;
                case 'DB_USER': $db_user = $val; break;
                case 'DB_PASS': $db_pass = $val; break;
            }
        }
    }
}

$pdo = null;
$db_connected = false;
$db_error_message = '';

try {
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 3,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    $db_connected = true;
} catch (PDOException $e) {
    $db_connected = false;
    $db_error_message = $e->getMessage();
}

/**
 * Fetch projects for the public portfolio.
 * Only returns projects with status = 'published'.
 * Directly reflects MySQL database records.
 */
function get_projects($pdo, $db_connected) {
    if ($db_connected && $pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM projects WHERE status = 'published' ORDER BY id DESC");
            $projects = $stmt->fetchAll();
            return array_map('normalize_project_record', $projects);
        } catch (Exception $e) {
            error_log("Database error in get_projects: " . $e->getMessage());
            return [];
        }
    }
    return [];
}

/**
 * Fetch all projects for the Admin Dashboard (including drafts).
 */
function get_all_projects_admin($pdo, $db_connected) {
    if ($db_connected && $pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM projects ORDER BY id DESC");
            $projects = $stmt->fetchAll();
            return array_map('normalize_project_record', $projects);
        } catch (Exception $e) {
            error_log("Database error in get_all_projects_admin: " . $e->getMessage());
            return [];
        }
    }
    return [];
}

/**
 * Fetch a single project by ID.
 */
function get_project_by_id($pdo, $id) {
    if (!$pdo) return null;
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => (int)$id]);
    $project = $stmt->fetch();
    return $project ? normalize_project_record($project) : null;
}

/**
 * Normalizes project fields (backward compatibility between `image` and `image_url`)
 */
function normalize_project_record($row) {
    if (!isset($row['image']) && isset($row['image_url'])) {
        $row['image'] = $row['image_url'];
    } elseif (isset($row['image']) && !isset($row['image_url'])) {
        $row['image_url'] = $row['image'];
    }
    if (!isset($row['status'])) {
        $row['status'] = 'published';
    }
    return $row;
}

/**
 * Formats image path for frontend/admin rendering
 */
function get_project_image_src($image_field, $base_prefix = '') {
    $img = trim((string)$image_field);
    if (empty($img)) {
        return 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&q=80';
    }
    if (preg_match('#^https?://#i', $img)) {
        return $img;
    }
    // Relative upload path
    $clean_path = ltrim($img, '/\\');
    if (!str_starts_with($clean_path, 'uploads/')) {
        $clean_path = 'uploads/projects/' . $clean_path;
    }
    return $base_prefix . $clean_path;
}

/**
 * Helper to fetch site profile data
 */
function get_profile_data($pdo, $db_connected) {
    if ($db_connected && $pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM profile LIMIT 1");
            $profile = $stmt->fetch();
            if ($profile) {
                return $profile;
            }
        } catch (Exception $e) {
            // Fall through
        }
    }

    return [
        'full_name' => 'Kian Lee Raian M. Aguinaldo',
        'title' => 'Information Technology Graduate | Aspiring Web Developer & IT Support Professional',
        'tagline' => 'Information Technology graduate interested in IT support, troubleshooting, and practical technology solutions.',
        'bio' => "I'm an Information Technology graduate interested in IT support, troubleshooting, and practical technology solutions. Through my internship and personal projects, I've gained exposure to computer setup, software installation, basic networking, and web technologies. I'm eager to continue learning, strengthen my technical skills, and contribute to a professional IT team.",
        'email' => 'kianaguinaldo7@gmail.com',
        'phone' => '09762022723',
        'location' => 'Lucena City, Philippines',
        'availability' => 'Available for Entry-Level IT Roles',
        'github' => '',
        'linkedin' => '',
        'twitter' => '',
        'years_experience' => 'Fresh Graduate',
        'projects_completed' => '',
        'satisfied_clients' => '',
        'code_commits' => '',
        'resume_path' => null,
        'resume_updated_at' => null
    ];
}

/**
 * Fetch certificates for public portfolio or admin dashboard.
 * Supports published-only filter and ordering.
 */
function get_certificates($pdo, $db_connected, $published_only = true) {
    if ($db_connected && $pdo) {
        try {
            $sql = "SELECT * FROM certificates";
            if ($published_only) {
                $sql .= " WHERE status = 'published'";
            }
            $sql .= " ORDER BY display_order ASC, issue_date DESC, id DESC";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Database error in get_certificates: " . $e->getMessage());
            return [];
        }
    }
    return [];
}

/**
 * Fetch a single certificate by ID.
 */
function get_certificate_by_id($pdo, $id) {
    if (!$pdo) return null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch() ?: null;
    } catch (Exception $e) {
        error_log("Database error in get_certificate_by_id: " . $e->getMessage());
        return null;
    }
}

/**
 * Formats certificate file path for public/admin rendering.
 */
function get_certificate_file_src($file_field, $base_prefix = '') {
    $file = trim((string)$file_field);
    if (empty($file)) {
        return '';
    }
    if (preg_match('#^https?://#i', $file)) {
        return $file;
    }
    $clean_path = ltrim($file, '/\\');
    return $base_prefix . $clean_path;
}

/**
 * Retrieves resume information and verifies local file existence.
 */
function get_resume_info($pdo, $db_connected) {
    $profile = get_profile_data($pdo, $db_connected);
    $resume_rel = $profile['resume_path'] ?? '';
    if (!empty($resume_rel)) {
        $candidate_path = __DIR__ . '/../' . ltrim($resume_rel, '/\\');
        $real_dir = realpath(__DIR__ . '/../uploads/resume');
        $real_file = realpath($candidate_path);
        if ($real_dir && $real_file && str_starts_with($real_file, $real_dir) && file_exists($real_file)) {
            return [
                'exists' => true,
                'relative_path' => $resume_rel,
                'full_path' => $real_file,
                'updated_at' => $profile['resume_updated_at'] ?? (filemtime($real_file) ? date('Y-m-d H:i:s', filemtime($real_file)) : null),
                'size' => filesize($real_file),
                'filename' => basename($real_file)
            ];
        }
    }
    return [
        'exists' => false,
        'relative_path' => '',
        'full_path' => '',
        'updated_at' => null,
        'size' => 0,
        'filename' => ''
    ];
}