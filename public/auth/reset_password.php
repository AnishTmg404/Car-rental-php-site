<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Check if reset token exists and is valid
if (!isset($_SESSION['reset_user_id'], $_SESSION['reset_token'], $_SESSION['reset_expires'])
    || time() > $_SESSION['reset_expires']) {
    set_flash_message('error', 'Password reset session expired. Please try again.');
    header('Location: ' . base_url('auth/forgot_password.php'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!validate_password($password)) {
        $error = 'Password must be at least 8 characters.';
    } else {
        // Update user password
        $hashed = hash_password($password);
        db_update('users', ['password_hash' => $hashed], ['id' => $_SESSION['reset_user_id']]);

        // Clear reset session
        unset($_SESSION['reset_user_id'], $_SESSION['reset_token'], $_SESSION['reset_expires']);

        $success = 'Password updated successfully. You can now <a href="' . base_url('auth/login.php') . '">login</a>.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - CarRental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
<div class="card p-4 shadow" style="width: 400px;">
    <h3 class="mb-3 text-center">Reset Password</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php else: ?>
        <form method="POST">
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
