<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

if (is_logged_in()) {
    header('Location: ' . base_url('index.php'));
    exit;
}

$fields = [
    'username' => '',
    'email' => '',
    'password' => '',
    'confirm_password' => '',
    'first_name' => '',
    'last_name' => '',
    'phone' => ''
];

$errors = array_fill_keys(array_keys($fields), '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($fields as $key => &$value) {
        $value = sanitize_input($_POST[$key] ?? '');
    }
    $csrf_token = $_POST['csrf_token'] ?? '';

    // CSRF validation
    if (!verify_csrf_token($csrf_token)) {
        $errors['username'] = "Invalid request. Refresh page.";
    }

    // Field validations
    if (empty($fields['username'])) $errors['username'] = "Username is required.";
    if (empty($fields['email'])) $errors['email'] = "Email is required.";
    elseif (!validate_email($fields['email'])) $errors['email'] = "Enter a valid email address.";

    if (empty($fields['password'])) $errors['password'] = "Password is required.";
    elseif (!validate_password($fields['password'])) $errors['password'] = "Password must be at least 8 characters.";

    if (empty($fields['confirm_password'])) $errors['confirm_password'] = "Confirm your password.";
    elseif ($fields['password'] !== $fields['confirm_password']) $errors['confirm_password'] = "Passwords do not match.";

    if (empty($fields['first_name'])) $errors['first_name'] = "First name is required.";
    if (empty($fields['last_name'])) $errors['last_name'] = "Last name is required.";
    if (!empty($fields['phone']) && !validate_phone($fields['phone'])) $errors['phone'] = "Enter a valid phone number.";

    // Only proceed if no errors
    if (!array_filter($errors)) {
        $existing_user = db_fetch("SELECT id FROM users WHERE username = ? OR email = ?", [$fields['username'], $fields['email']]);
        if ($existing_user) {
            $errors['username'] = "Username or email already exists.";
        } else {
            $user_data = [
                'username' => $fields['username'],
                'email' => $fields['email'],
                'password_hash' => hash_password($fields['password']),
                'first_name' => $fields['first_name'],
                'last_name' => $fields['last_name'],
                'phone' => $fields['phone'],
                'role' => 'user',
                'status' => 'active'
            ];

            $user_id = db_insert('users', $user_data);
            if ($user_id) {
                set_flash_message('success', 'Account created successfully! Please sign in.');
                header('Location: ' . base_url('auth/login.php'));
                exit;
            } else {
                $errors['username'] = "Failed to create account. Please try again.";
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

            <form method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" class="form-control <?= $errors['first_name'] ? 'is-invalid' : '' ?>" name="first_name" id="first_name" value="<?= htmlspecialchars($fields['first_name']) ?>">
                        <?php if ($errors['first_name']): ?><div class="text-danger mt-1 small"><?= $errors['first_name'] ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" class="form-control <?= $errors['last_name'] ? 'is-invalid' : '' ?>" name="last_name" id="last_name" value="<?= htmlspecialchars($fields['last_name']) ?>">
                        <?php if ($errors['last_name']): ?><div class="text-danger mt-1 small"><?= $errors['last_name'] ?></div><?php endif; ?>
                    </div>
                </div>

                <label for="username" class="form-label">Username *</label>
                <input type="text" class="form-control <?= $errors['username'] ? 'is-invalid' : '' ?>" name="username" id="username" value="<?= htmlspecialchars($fields['username']) ?>">
                <?php if ($errors['username']): ?><div class="text-danger mt-1 small"><?= $errors['username'] ?></div><?php endif; ?>

                <label for="email" class="form-label">Email Address *</label>
                <input type="email" class="form-control <?= $errors['email'] ? 'is-invalid' : '' ?>" name="email" id="email" value="<?= htmlspecialchars($fields['email']) ?>">
                <?php if ($errors['email']): ?><div class="text-danger mt-1 small"><?= $errors['email'] ?></div><?php endif; ?>

                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control <?= $errors['phone'] ? 'is-invalid' : '' ?>" name="phone" id="phone" value="<?= htmlspecialchars($fields['phone']) ?>">
                <?php if ($errors['phone']): ?><div class="text-danger mt-1 small"><?= $errors['phone'] ?></div><?php endif; ?>

                <label for="password" class="form-label">Password *</label>
                <div class="password-input">
                    <input type="password" class="form-control <?= $errors['password'] ? 'is-invalid' : '' ?>" name="password" id="password">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')"><i class="bi bi-eye" id="password-icon"></i></button>
                </div>
                <?php if ($errors['password']): ?><div class="text-danger mt-1 small"><?= $errors['password'] ?></div><?php endif; ?>
                <small class="form-text text-muted mb-3">Minimum 8 characters</small>

                <label for="confirm_password" class="form-label">Confirm Password *</label>
                <div class="password-input">
                    <input type="password" class="form-control <?= $errors['confirm_password'] ? 'is-invalid' : '' ?>" name="confirm_password" id="confirm_password">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')"><i class="bi bi-eye" id="confirm_password-icon"></i></button>
                </div>
                <?php if ($errors['confirm_password']): ?><div class="text-danger mt-1 small"><?= $errors['confirm_password'] ?></div><?php endif; ?>

                <div class="form-check my-3">
                    <input type="checkbox" class="form-check-input" id="terms" required>
                    <label class="form-check-label" for="terms">I agree to the <a href="#" class="auth-link">Terms of Service</a> and <a href="#" class="auth-link">Privacy Policy</a></label>
                </div>

                <button type="submit" class="btn btn-primary auth-btn w-100">Create Account</button>
            </form>

            <div class="auth-footer mt-3 text-center">
                Already have an account? <a href="<?= base_url('auth/login.php') ?>" class="auth-link">Sign in here</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = document.getElementById(id + '-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
    </script>
</body>

</html>