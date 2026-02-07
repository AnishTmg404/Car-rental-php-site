<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . base_url('index.php'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = sanitize_input($_POST['first_name'] ?? '');
    $last_name = sanitize_input($_POST['last_name'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $error = 'Please fill in all required fields.';
    } elseif (!validate_email($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (!validate_password($password)) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!empty($phone) && !validate_phone($phone)) {
        $error = 'Please enter a valid phone number.';
    } else {
        // Check if username or email already exists
        $existing_user = db_fetch("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
        
        if ($existing_user) {
            $error = 'Username or email already exists.';
        } else {
            // Create new user
            $user_data = [
                'username' => $username,
                'email' => $email,
                'password_hash' => hash_password($password),
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'role' => 'user',
                'status' => 'active'
            ];
            
            $user_id = db_insert('users', $user_data);
            
            if ($user_id) {
                set_flash_message('success', 'Account created successfully! Please sign in.');
                header('Location: ' . base_url('auth/login.php'));
                exit;
            } else {
                $error = 'Failed to create account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset_url('css/auth.css') ?>">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="bi bi-car-front-fill"></i>
                    <span>CarRental</span>
                </div>
                <h2 class="auth-title">Create Account</h2>
                <p class="auth-subtitle">Join our car rental community</p>
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
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="first_name" class="form-label">
                                <i class="bi bi-person me-2"></i>First Name *
                            </label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?= htmlspecialchars($first_name ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="last_name" class="form-label">
                                <i class="bi bi-person me-2"></i>Last Name *
                            </label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?= htmlspecialchars($last_name ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="bi bi-at me-2"></i>Username *
                    </label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?= htmlspecialchars($username ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-2"></i>Email Address *
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= htmlspecialchars($email ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">
                        <i class="bi bi-telephone me-2"></i>Phone Number
                    </label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           value="<?= htmlspecialchars($phone ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-2"></i>Password *
                    </label>
                    <div class="password-input">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="bi bi-eye" id="password-icon"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted">Minimum 8 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">
                        <i class="bi bi-lock-fill me-2"></i>Confirm Password *
                    </label>
                    <div class="password-input">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="bi bi-eye" id="confirm_password-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="#" class="auth-link">Terms of Service</a> and <a href="#" class="auth-link">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary auth-btn">
                    <i class="bi bi-person-plus me-2"></i>Create Account
                </button>
            </form>

            <div class="auth-footer">
                <p class="text-center mb-3">
                    Already have an account? 
                    <a href="<?= base_url('auth/login.php') ?>" class="auth-link">Sign in here</a>
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
    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const passwordIcon = document.getElementById(fieldId + '-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'bi bi-eye';
            }
        }

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            // You can add visual feedback here
        });

        function calculatePasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            return strength;
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
