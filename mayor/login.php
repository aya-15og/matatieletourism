<?php
declare(strict_types=1);

// Enable full error reporting (for debugging; disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php'; // provides generate_csrf_token(), validate_csrf_token(), sanitize_input()

if (session_status() === PHP_SESSION_NONE) session_start();

// Simple login throttle (per session)
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['login_lock_until'])) $_SESSION['login_lock_until'] = 0;
$now = time();
$locked = $_SESSION['login_lock_until'] > $now;

$error = null;
$prefill = ['username' => ''];

// Build absolute URL for logo
$logo_url = sprintf(
    "%s://%s/mayor/assets/images/matatiele-municipal-logo.jpg",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http',
    $_SERVER['HTTP_HOST']
);

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($locked) {
        $error = 'Too many attempts. Try again later.';
    } else {
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $token    = $_POST['csrf_token'] ?? '';

        // CSRF
        if (!validate_csrf_token($token)) {
            $error = 'Invalid request (CSRF).';
        } elseif (empty($username) || empty($password)) {
            $error = 'Please provide username and password.';
        } else {
            // Lookup user
            $stmt = $pdo->prepare('SELECT id, username, password, full_name, role FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Timing-safe check
            $pwHash = $user['password'] ?? password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            if (!empty($user) && password_verify($password, $pwHash)) {
                // Successful login
                session_regenerate_id(true); // prevent session fixation
                $_SESSION['user'] = [
                    'id'        => (int)$user['id'],
                    'username'  => $user['username'],
                    'full_name' => $user['full_name'] ?? $user['username'],
                    'role'      => $user['role'] ?? 'admin'
                ];
                $_SESSION['login_attempts'] = 0;
                $_SESSION['login_lock_until'] = 0;
                header('Location: /mayor/admin/dashboard.php');
                exit;
            } else {
                // Failed login
                $_SESSION['login_attempts']++;
                if ($_SESSION['login_attempts'] >= 6) {
                    $_SESSION['login_lock_until'] = time() + 300; // 5 min lock
                    $error = 'Too many attempts. Locked for 5 minutes.';
                } else {
                    $remaining = 6 - $_SESSION['login_attempts'];
                    $error = 'Invalid credentials. Attempts left: ' . $remaining;
                }
            }
        }
        $prefill['username'] = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — Mayoral Cup Tournament</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');

body {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 50%, #f8f9fa 100%);
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow-x: hidden;
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 50%, rgba(255, 215, 0, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(50, 205, 50, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 40% 20%, rgba(255, 69, 0, 0.06) 0%, transparent 50%),
        radial-gradient(circle at 90% 30%, rgba(30, 144, 255, 0.08) 0%, transparent 50%);
    pointer-events: none;
    z-index: 0;
}

.login-container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 480px;
    padding: 2rem;
}

.login-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    border: none;
    overflow: hidden;
}

.login-header {
    background: linear-gradient(90deg, #FFD700, #32CD32, #FF4500, #1E90FF);
    padding: 2.5rem 2rem;
    text-align: center;
    position: relative;
}

.logo-container {
    width: 100px;
    height: 100px;
    margin: 0 auto 1rem;
    position: relative;
}

.logo-container img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 4px solid white;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    object-fit: cover;
}

.login-title {
    color: white;
    font-weight: 700;
    font-size: 1.75rem;
    margin: 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.login-subtitle {
    color: rgba(255, 255, 255, 0.95);
    font-size: 0.95rem;
    margin-top: 0.5rem;
    font-weight: 500;
}

.login-body {
    padding: 2.5rem 2rem;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-label i {
    color: #6c757d;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #FFD700;
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.15);
}

.btn-login {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    border: none;
    padding: 0.875rem 2rem;
    font-weight: 700;
    font-size: 1.05rem;
    border-radius: 12px;
    color: #000;
    width: 100%;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
    background: linear-gradient(135deg, #FFA500 0%, #FFD700 100%);
}

.btn-login:active {
    transform: translateY(0);
}

.btn-login:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #6c757d;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    padding: 0.5rem;
    border-radius: 8px;
}

.back-link:hover {
    color: #FFD700;
    background: rgba(255, 215, 0, 0.1);
}

.alert {
    border: none;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-danger {
    background: linear-gradient(135deg, #fee 0%, #fdd 100%);
    color: #c00;
}

.alert-warning {
    background: linear-gradient(135deg, #fff4e6 0%, #ffe8cc 100%);
    color: #c77700;
}

.alert i {
    font-size: 1.25rem;
}

.credentials-box {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-radius: 12px;
    padding: 1.25rem;
    margin-top: 1.5rem;
    border-left: 4px solid #1E90FF;
}

.credentials-title {
    font-weight: 700;
    color: #1565c0;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
}

.credentials-info {
    background: white;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-family: 'Courier New', monospace;
    font-size: 0.95rem;
    color: #2c3e50;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.credentials-info:last-child {
    margin-bottom: 0;
}

.credential-label {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.85rem;
}

.credential-value {
    font-weight: 700;
    color: #2c3e50;
}

.divider {
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, #e9ecef 50%, transparent 100%);
    margin: 1.5rem 0;
}
</style>
</head>
<body>
<div class="login-container">
    <div class="card login-card">
        <div class="login-header">
            <div class="logo-container">
                <img src="<?= $logo_url ?>" alt="Matatiele Municipal Logo">
            </div>
            <h1 class="login-title">Admin Login</h1>
            <p class="login-subtitle">Mayoral Cup Tournament</p>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?=htmlspecialchars($error)?></span>
                </div>
            <?php endif; ?>

            <?php if ($locked): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-clock-fill"></i>
                    <span>Too many attempts. Try again at <?=date('H:i:s', $_SESSION['login_lock_until'])?></span>
                </div>
            <?php endif; ?>

            <form method="post" autocomplete="off" novalidate>
                <input type="hidden" name="csrf_token" value="<?=generate_csrf_token()?>">
                
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-person-fill"></i>
                        Username
                    </label>
                    <input name="username" class="form-control" value="<?=$prefill['username']?>" required <?=$locked ? 'disabled' : ''?>>
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        <i class="bi bi-lock-fill"></i>
                        Password
                    </label>
                    <input name="password" type="password" class="form-control" required <?=$locked ? 'disabled' : ''?>>
                </div>

                <button class="btn btn-login" type="submit" <?=$locked ? 'disabled' : ''?>>
                    <i class="bi bi-box-arrow-in-right"></i> Login to Dashboard
                </button>
            </form>

            <div class="divider"></div>

            <div class="text-center">
                <a class="back-link" href="/mayor/">
                    <i class="bi bi-arrow-left"></i>
                    Back to Tournament Site
                </a>
            </div>

            <div class="credentials-box">
                <div class="credentials-title">
                    <i class="bi bi-info-circle-fill"></i>
                    Test Login Credentials
                </div>
                <div class="credentials-info">
                    <span class="credential-label">Username:</span>
                    <span class="credential-value">admin</span>
                </div>
                <div class="credentials-info">
                    <span class="credential-label">Password:</span>
                    <span class="credential-value">20Testing@25</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>