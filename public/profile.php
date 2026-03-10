<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/url.php';

// Require login
require_login();

$page_title = 'My Profile';
$current_user = get_current_user_data();

$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $first_name = sanitize_input($_POST['first_name'] ?? '');
    $last_name = sanitize_input($_POST['last_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Verify CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        if (empty($username) || empty($first_name) || empty($last_name) || empty($email)) {
            $error = 'Username, first name, last name, and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } else {
            // Password update logic
            $password_hash = null;

            // User is trying to change password?
            if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {

                // Get stored password if needed
                if (!isset($current_user['password'])) {
                    $row = db_select_one("SELECT password FROM users WHERE id = ?", [$current_user['id']]);
                    $stored_hash = $row['password'];
                } else {
                    $stored_hash = $current_user['password'];
                }

                if (empty($current_password)) {
                    $error = 'Current password is required.';
                } elseif (!password_verify($current_password, $stored_hash)) {
                    $error = 'Current password is incorrect.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New password and confirmation do not match.';
                } else {
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }



            // Handle profile image upload
            $profile_image = $current_user['profile_image'] ?? '';

            if (!empty($_FILES['profile_image']['name'])) {

                $upload_dir = __DIR__ . '/assets/uploads/profiles/';
                $db_path = 'assets/uploads/profiles/';

                // Create directory if missing
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $tmp = $_FILES['profile_image']['tmp_name'];
                $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($ext, $allowed)) {
                    $error = 'Invalid image format. Allowed: jpg, jpeg, png, gif.';
                } elseif (getimagesize($tmp) === false) {
                    $error = 'The file is not a valid image.';
                } else {
                    $new_name = 'user_' . $current_user['id'] . '_' . time() . '.' . $ext;
                    $target = $upload_dir . $new_name;

                    if (!move_uploaded_file($tmp, $target)) {
                        $error = 'Failed to upload profile image.';
                    } else {
                        // Delete old image if exists and is not default
                        if (
                            !empty($current_user['profile_image']) &&
                            $current_user['profile_image'] !== 'https://via.placeholder.com/120' &&
                            file_exists(__DIR__ . '/../' . $current_user['profile_image'])
                        ) {
                            unlink(__DIR__ . '/../' . $current_user['profile_image']);
                        }

                        // Save DB path
                        $profile_image = $db_path . $new_name;
                    }
                }
            }



            // Update user if no errors
            if (!$error) {
                $update_data = [
                    'username' => $username,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'profile_image' => $profile_image,
                    'updated_at' => date('Y-m-d H:i:s') // Add updatedAt
                ];
                if ($password_hash !== null) {
                    $update_data['password'] = $password_hash;
                }

                $updated = db_update('users', $update_data, ['id' => $current_user['id']]);
                if ($updated) {
                    $success = 'Profile updated successfully!';
                    $current_user = get_current_user_data(); // Refresh data
                } else {
                    $error = 'Failed to update profile. Please try again.';
                }
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
    <title><?= $page_title ?> - Car Rental</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset_url('css/styles.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/theme.css') ?>">
</head>

<body>

    <?php include '../public/navbar.php'; ?>

    <main class="py-4">
        <div class="container">
            <div class="row g-4">
                <!-- Sidebar Quick Actions -->
                <div class="col-lg-3">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <img src="<?= htmlspecialchars($current_user['profile_image'] ?? 'https://via.placeholder.com/120') ?>"
                                class="rounded-circle mb-2" style="width:120px; aspect-ratio:1/1; object-fit:cover;" alt="Profile Image">
                            <h6 class="mb-0"><?= htmlspecialchars(($current_user['first_name'] ?? '') . ' ' . ($current_user['last_name'] ?? '')) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($current_user['username'] ?? '') ?></small>
                        </div>
                    </div>
                    <div class="list-group">
                        <a href="<?= base_url('profile.php') ?>" class="list-group-item list-group-item-action active">
                            <i class="bi bi-person-circle me-2"></i> My Profile
                        </a>
                        <a href="<?= base_url('my-booking.php') ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-card-checklist me-2"></i> My Bookings
                        </a>
                        <a href="<?= base_url('cars.php') ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-car-front-fill me-2"></i> Browse Cars
                        </a>
                        <a href="<?= base_url('auth/logout.php') ?>" class="list-group-item list-group-item-action text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </div>
                </div>

                <!-- Profile Form -->
                <div class="col-lg-9">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Edit Profile</h5>
                        </div>
                        <div class="card-body">

                            <!-- Flash messages -->
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>
                            <?php if ($success): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                            <?php endif; ?>

                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                                <div class="mb-3 text-center">
                                    <input class="form-control mt-2" type="file" name="profile_image" accept="image/*">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($current_user['username'] ?? '') ?>" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($current_user['first_name'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($current_user['last_name'] ?? '') ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($current_user['email'] ?? '') ?>" required>
                                </div>

                                <hr>
                                <h6>Change Password (optional)</h6>

                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control">
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../public/footer.php'; ?>

</body>

</html>