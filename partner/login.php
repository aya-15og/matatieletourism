<?php
session_start();
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['partner_logged_in']) && $_SESSION['partner_logged_in'] === true) {
    header('Location: /kokstad/partner/dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM partner_users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $partner = $stmt->fetch();
            
            if ($partner && password_verify($password, $partner['password_hash'])) {
                // Login successful
                $_SESSION['partner_logged_in'] = true;
                $_SESSION['partner_user_id'] = $partner['id'];
                $_SESSION['partner_accommodation_id'] = $partner['accommodation_id'];
                $_SESSION['partner_name'] = $partner['full_name'];
                $_SESSION['partner_email'] = $partner['email'];
                
                // Update last login
                $updateStmt = $pdo->prepare("UPDATE partner_users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$partner['id']]);
                
                // Log activity
                $logStmt = $pdo->prepare("INSERT INTO partner_activity_log (partner_user_id, accommodation_id, action_type, action_description, ip_address) VALUES (?, ?, 'login', 'Partner logged in', ?)");
                $logStmt->execute([$partner['id'], $partner['accommodation_id'], $_SERVER['REMOTE_ADDR']]);
                
                header('Location: /kokstad/partner/dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            error_log('Partner login error: ' . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    } else {
        $error = 'Please enter both email and password.';
    }
}

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $email = trim($_POST['reset_email'] ?? '');
    
    if ($email) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM partner_users WHERE email = ?");
            $stmt->execute([$email]);
            $partner = $stmt->fetch();
            
            if ($partner) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $updateStmt = $pdo->prepare("UPDATE partner_users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
                $updateStmt->execute([$token, $expires, $partner['id']]);
                
                // Send reset email (implement your email sending logic)
                $reset_link = "https://kokstad.co.za/kokstad/partner/reset-password.php?token=$token";
                $subject = "Password Reset Request - Kokstad Tourism Partner";
                $message = "Click the link to reset your password: $reset_link (Valid for 1 hour)";
                
                // mail($email, $subject, $message);
                
                $success = 'Password reset instructions have been sent to your email.';
            } else {
                $success = 'If an account exists with this email, reset instructions will be sent.';
            }
        } catch (PDOException $e) {
            error_log('Password reset error: ' . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Login - Kokstad Tourism</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #2c5282 0%, #1e3a5f 100%);
            color: white;
            padding: 50px 40px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 800;
        }

        .login-header p {
            opacity: 0.95;
            font-size: 1rem;
        }

        .login-body {
            padding: 50px 40px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
        }

        .tab {
            flex: 1;
            padding: 15px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: #999;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }

        .tab.active {
            color: #2c5282;
            border-bottom-color: #2c5282;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 1px solid #fca5a5;
            color: #991b1b;
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 1px solid #6ee7b7;
            color: #065f46;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.2rem;
        }

        .form-group input {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2c5282;
            box-shadow: 0 0 0 4px rgba(44, 82, 130, 0.1);
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 25px;
        }

        .forgot-password a {
            color: #2c5282;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #2c5282 0%, #1e3a5f 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(44, 82, 130, 0.3);
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(44, 82, 130, 0.4);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button:disabled {
            background: #999;
            cursor: not-allowed;
        }

        .login-footer {
            text-align: center;
            padding: 30px 40px;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }

        .login-footer p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .login-footer a {
            color: #2c5282;
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .help-text {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: #f0f9ff;
            border-radius: 10px;
            font-size: 0.9rem;
            color: #0c4a6e;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-header {
                padding: 35px 25px;
            }

            .login-header h1 {
                font-size: 1.8rem;
            }

            .login-body {
                padding: 35px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🏨 Partner Portal</h1>
            <p>Manage your accommodation</p>
        </div>

        <div class="login-body">
            <div class="tabs">
                <button class="tab active" onclick="switchTab('login')">Sign In</button>
                <button class="tab" onclick="switchTab('reset')">Reset Password</button>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    ⚠️ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✓ <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <!-- Login Tab -->
            <div id="login-tab" class="tab-content active">
                <form method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <span class="input-icon">📧</span>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   required 
                                   autofocus
                                   placeholder="your@email.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required
                                   placeholder="Enter your password">
                        </div>
                    </div>

                    <div class="forgot-password">
                        <a href="#" onclick="switchTab('reset'); return false;">Forgot password?</a>
                    </div>

                    <button type="submit" name="login" class="login-button" id="loginBtn">
                        Sign In to Dashboard
                    </button>
                </form>

                <div class="help-text">
                    💡 <strong>New Partner?</strong> Contact the admin to set up your account
                </div>
            </div>

            <!-- Reset Password Tab -->
            <div id="reset-tab" class="tab-content">
                <form method="POST" id="resetForm">
                    <div class="form-group">
                        <label for="reset_email">Email Address</label>
                        <div class="input-wrapper">
                            <span class="input-icon">📧</span>
                            <input type="email" 
                                   id="reset_email" 
                                   name="reset_email" 
                                   required
                                   placeholder="your@email.com">
                        </div>
                    </div>

                    <button type="submit" name="reset_password" class="login-button">
                        Send Reset Link
                    </button>
                </form>

                <div class="help-text">
                    📬 We'll send password reset instructions to your email
                </div>
            </div>
        </div>

        <div class="login-footer">
            <p>Need help? <a href="mailto:partners@kokstadtourism.co.za">Contact Support</a></p>
            <p><a href="/kokstad">← Back to Main Website</a></p>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Update tabs
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            if (tab === 'login') {
                document.querySelector('.tab:first-child').classList.add('active');
                document.getElementById('login-tab').classList.add('active');
            } else {
                document.querySelector('.tab:last-child').classList.add('active');
                document.getElementById('reset-tab').classList.add('active');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.textContent = '⏳ Signing in...';
            btn.disabled = true;
        });
    </script>
</body>
</html>