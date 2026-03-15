<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        header('Location: ' . base_url('admin/dashboard.php'));
    } else {
        header('Location: ' . base_url('dashboard.php'));
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!validate_email($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check user credentials
        $user = db_fetch("SELECT * FROM users WHERE email = ?", [$email]);

        if ($user && verify_password($password, $user['password_hash'])) {
            
            // 1. Check Role First
            if ($user['role'] !== 'admin') {
                $error = 'Access denied. You do not have administrator privileges.';
            } 
            // 2. Check Status
            elseif ($user['status'] !== 'active') {
                $error = 'This admin account is currently ' . $user['status'] . '.';
            } 
            // 3. All good
            else {
                login_user($user);
                set_flash_message('success', 'Welcome back, ' . $user['first_name'] . '!');
                header('Location: ' . base_url('admin/dashboard.php'));
                exit;
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset_url('css/auth.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/theme.css') ?>">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Admin Panel</span>
                </div>
                <h2 class="auth-title">Admin Access</h2>
                <p class="auth-subtitle">Secure admin login portal</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-2"></i>Email Address
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= htmlspecialchars($email ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-2"></i>Password
                    </label>
                    <div class="password-input">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="bi bi-eye" id="password-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary auth-btn">
                    <i class="bi bi-shield-check me-2"></i>Access Admin Panel
                </button>
            </form>

            <div class="auth-footer">
                <p class="text-center mb-3">
                    <a href="<?= base_url('auth/login.php') ?>" class="auth-link">Regular User Login</a>
                </p>
                <div class="auth-divider">
                    <span>or</span>
                </div>
                <div class="auth-links">
                    <a href="<?= base_url('index.php') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-house me-2"></i>Back to Home
                    </a>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset_url('js/theme-toggle.js') ?>"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'bi bi-eye';
            }
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
