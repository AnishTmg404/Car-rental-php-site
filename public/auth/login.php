<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . base_url('index.php'));
    exit;
}

$email = '';
$password = '';

$errors = [
    'email' => '',
    'password' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // CSRF validation
    if (!verify_csrf_token($csrf_token)) {
        $errors['email'] = "Invalid request. Please refresh the page.";
    }

    // Email validation
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!validate_email($email)) {
        $errors['email'] = "Enter a valid email address.";
    }

    // Password validation
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }

    // If no validation errors
    if (!array_filter($errors)) {

        $user = db_fetch(
            "SELECT * FROM users WHERE email = ? AND status = 'active'",
            [$email]
        );

        if ($user && verify_password($password, $user['password_hash'])) {

            login_user($user);
            set_flash_message('success', 'Welcome back, ' . $user['first_name'] . '!');
            header('Location: ' . base_url('index.php'));
            exit;
        } else {

            $errors['password'] = "Invalid email or password.";
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

            <form method="POST" class="auth-form">

                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <!-- EMAIL -->

                <div class="form-group mb-3">

                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-2"></i>Email Address
                    </label>

                    <input
                        type="email"
                        class="form-control <?= $errors['email'] ? 'is-invalid' : '' ?>"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($email) ?>">

                    <?php if ($errors['email']): ?>
                        <div class="invalid-feedback d-block">
                            <?= $errors['email'] ?>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- PASSWORD -->

                <div class="form-group mb-3">

                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-2"></i>Password
                    </label>

                    <div class="password-input">

                        <input
                            type="password"
                            class="form-control <?= $errors['password'] ? 'is-invalid' : '' ?>"
                            id="password"
                            name="password">

                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="bi bi-eye" id="password-icon"></i>
                        </button>

                    </div>

                    <?php if ($errors['password']): ?>
                        <div class="invalid-feedback d-block">
                            <?= $errors['password'] ?>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="form-group form-check mb-3">

                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>

                </div>

                <button type="submit" class="btn btn-primary auth-btn w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>

            </form>

            <div class="auth-footer">

                <p class="text-center mb-3">
                    Don't have an account?
                    <a href="<?= base_url('auth/register.php') ?>" class="auth-link">
                        Sign up here
                    </a>
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
    </script>

</body>

</html>