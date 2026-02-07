<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../public/url.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . base_url('index.php'));
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
        $user = db_fetch("SELECT * FROM users WHERE email = ? AND status = 'active'", [$email]);
        
        if ($user && verify_password($password, $user['password_hash'])) {
            login_user($user);
            set_flash_message('success', 'Welcome back, ' . $user['first_name'] . '!');
            header('Location: ' . base_url('index.php'));
            exit;
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
    <title>Login - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset_url('css/auth.css') ?>">
    <style>
        .password-input {
            position: relative;
        }

        .password-input input {
            padding-right: 3rem; /* More padding to the right to make space for toggle */
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 0.75rem;  /* adjust horizontal position */
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            color: #6c757d;
            font-size: 1.2rem;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }


    </style>
</head>
<body class="auth-body">
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-car-front-fill"></i>
                <span>CarRental</span>
            </div>
            <h2 class="auth-title">Welcome Back</h2>
            <p class="auth-subtitle">Sign in to your account</p>
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

            <div class="form-group mb-3">
                <label for="email" class="form-label">
                    <i class="bi bi-envelope me-2"></i>Email Address
                </label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>

            <div class="form-group mb-3 password-input">
                <label for="password" class="form-label">
                    <i class="bi bi-lock me-2"></i>Password
                </label>
                <input type="password" class="form-control" id="password" name="password" required>
                <button type="button" class="password-toggle" onclick="togglePassword()">
                    <i class="bi bi-eye" id="password-icon"></i>
                </button>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check d-flex align-items-center mb-0">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label ms-2 mb-0" for="remember">Remember me</label>
                </div>
                <a href="<?= base_url('auth/forgot_password.php') ?>" class="small text-primary text-decoration-none">
                    Forgot Password?
                </a>
            </div>


            <button type="submit" class="btn btn-primary auth-btn w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <div class="auth-footer mt-4">
            <p class="text-center mb-3">
                Don't have an account? 
                <a href="<?= base_url('auth/register.php') ?>" class="auth-link">Sign up here</a>
            </p>
            <div class="auth-divider"><span>or</span></div>
            <div class="d-flex justify-content-center gap-2 mt-3">
                <a href="<?= base_url('index.php') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-house me-2"></i>Back to Home
                </a>
            </div>
        </div>

        <div class="mt-4 p-3 bg-light rounded">
            <h6 class="text-muted mb-2">Test Credentials:</h6>
            <div class="small text-muted">
                <strong>User:</strong> user@example.com / password123<br>
                <strong>Admin:</strong> admin@carrental.com / password<br>
                <small class="text-primary">
                    Admin login: <a href="<?= base_url('auth/admin.php') ?>" class="text-decoration-none">admin.php</a>
                </small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
