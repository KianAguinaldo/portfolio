<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $authenticated = false;

        // Try database verification first
        if ($db_connected && $pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :u LIMIT 1");
                $stmt->execute([':u' => $username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    $authenticated = true;
                }
            } catch (Exception $e) {
                // Database check failed
            }
        }

        if ($authenticated) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Kian Lee Raian M. Aguinaldo Portfolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #5680E9;
            --color-sky: #84CEEB;
            --color-cerulean: #5AB9EA;
            --color-silver: #C1C8C4;
            --color-purple: #8860D0;
            --bg-deep: #07090f;
            --bg-card: rgba(12, 17, 30, 0.85);
            --border-glass: rgba(193, 200, 196, 0.16);
            --text-main: #f8fafc;
            --text-muted: #8d9894;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-deep);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            background-image: radial-gradient(rgba(193, 200, 196, 0.05) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .ambient-glow {
            position: fixed;
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(90, 185, 234, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-card {
            position: relative;
            z-index: 1;
            max-width: 420px;
            width: 100%;
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 25px 55px rgba(0, 0, 0, 0.75), 0 0 30px rgba(90, 185, 234, 0.08);
        }

        .logo-box {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .logo-badge {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-purple));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            box-shadow: 0 4px 14px rgba(86, 128, 233, 0.4);
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--color-silver);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            background: rgba(7, 9, 15, 0.85);
            border: 1px solid var(--border-glass);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            color: var(--text-main);
            outline: none;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            border-color: var(--color-cerulean);
            box-shadow: 0 0 15px rgba(90, 185, 234, 0.35);
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-purple));
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.25s ease;
            margin-top: 10px;
            box-shadow: 0 4px 20px rgba(86, 128, 233, 0.35);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 26px rgba(86, 128, 233, 0.5);
            filter: brightness(1.08);
        }

        .error-msg {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .hint-box {
            margin-top: 20px;
            padding: 12px;
            background: rgba(193, 200, 196, 0.05);
            border: 1px solid rgba(193, 200, 196, 0.1);
            border-radius: 8px;
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
        }

        .hint-box code {
            font-family: 'JetBrains Mono', monospace;
            color: var(--color-cerulean);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: var(--color-cerulean);
        }
    </style>
</head>
<body>
    <div class="ambient-glow"></div>

    <div class="login-card">
        <div class="logo-box">
            <div class="logo-badge">&lt;/&gt;</div>
            <div>
                <div style="font-weight: 700; font-size: 15px;">Portfolio Control Panel</div>
                <div style="font-size: 12px; color: var(--color-cerulean);">Kian Lee Raian M. Aguinaldo</div>
            </div>
        </div>

        <h1 class="title">Admin Login</h1>
        <p class="subtitle">Log in to manage projects and settings</p>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="Username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Password" required>
            </div>

            <button type="submit" class="btn-submit">Sign In to Dashboard</button>
        </form>

        <a href="../index.php" class="back-link">&larr; Return to Public Portfolio</a>
    </div>
</body>
</html>
