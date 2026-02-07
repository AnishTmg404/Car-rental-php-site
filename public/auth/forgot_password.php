<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');

    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!validate_email($email)) {
        $error = 'Please enter a valid email.';
    } else {
        // Check if user exists
        $user = db_fetch("SELECT * FROM users WHERE email = ? AND status = 'active'", [$email]);
        if (!$user || ($phone && $user['phone_number'] !== $phone)) {
            $error = 'User not found or phone number does not match.';
        } else {
            // Set a temporary session token
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['reset_token'] = bin2hex(random_bytes(16));
            $_SESSION['reset_expires'] = time() + 900; // 15 minutes

            // Redirect to reset page
            header('Location: ' . base_url('auth/reset_password.php'));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - CarRental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
<div class="card p-4 shadow" style="width: 400px;">
    <h3 class="mb-3 text-center">Forgot Password</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" id="email" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number (optional)</label>
            <input type="text" name="phone" class="form-control" id="phone">
        </div>
        <button type="submit" class="btn btn-primary w-100">Verify</button>
        <a href="<?= base_url('auth/login.php') ?>" class="btn btn-link w-100 mt-2">Back to Login</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
