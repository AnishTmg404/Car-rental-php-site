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
    // Basic Info
    $username = sanitize_input($_POST['username'] ?? '');
    $first_name = sanitize_input($_POST['first_name'] ?? '');
    $last_name = sanitize_input($_POST['last_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? ''); 
    $address = sanitize_input($_POST['address'] ?? '');
    
    // License Info
    $license_number = sanitize_input($_POST['license_number'] ?? '');

    // Password Info
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // 1. Security Check
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        // 2. Required Fields Validation
        if (empty($username) || empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
            $error = 'All basic fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } elseif (!preg_match('/^[0-9+ ]+$/', $phone)) {
            $error = 'Invalid phone number format.';
        } 
        
        // 3. Uniqueness Check (Username/Email)
        if (!$error) {
            $existing = db_fetch("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?", [$email, $username, $current_user['id']]);
            if ($existing) {
                $error = 'Username or Email is already taken by another user.';
            }
        }

        if (!$error) {
            $password_hash = null;

            // 4. Password Change Validation
            if (!empty($new_password)) {
                $row = db_fetch("SELECT password FROM users WHERE id = ?", [$current_user['id']]);
                if (empty($current_password)) {
                    $error = 'Please enter your current password to authorize changes.';
                } elseif (!password_verify($current_password, $row['password'])) {
                    $error = 'The current password you entered is incorrect.';
                } elseif (strlen($new_password) < 8) {
                    $error = 'New password must be at least 8 characters long.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'The new password confirmation does not match.';
                } else {
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }

            // 5. Handle File Uploads (Profile Image & License Image)
            $profile_image = $current_user['profile_image'] ?? '';
            $license_image = $current_user['license_image'] ?? '';

            // Process Profile Image
            if (!$error && !empty($_FILES['profile_image']['name'])) {
                $res = handle_file_upload($_FILES['profile_image'], 'profiles', $current_user['profile_image']);
                if (isset($res['error'])) { $error = $res['error']; } else { $profile_image = $res['path']; }
            }

            // Process License Image
            if (!$error && !empty($_FILES['license_image']['name'])) {
                $res = handle_file_upload($_FILES['license_image'], 'licenses', $current_user['license_image'], ['jpg', 'jpeg', 'png', 'pdf']);
                if (isset($res['error'])) { $error = $res['error']; } else { $license_image = $res['path']; }
            }

            // 6. Update Database
            if (!$error) {
                $update_data = [
                    'username' => $username,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'license_number' => $license_number,
                    'profile_image' => $profile_image,
                    'license_image' => $license_image,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($password_hash !== null) $update_data['password'] = $password_hash;

                if (db_update('users', $update_data, ['id' => $current_user['id']])) {
                    $success = 'Profile updated successfully!';
                    $current_user = get_current_user_data(); 
                } else {
                    $error = 'No changes were made to your profile.';
                }
            }
        }
    }
}

/**
 * Helper function for handling uploads within the same logic flow
 */
function handle_file_upload($file, $subfolder, $old_path, $allowed = ['jpg', 'jpeg', 'png', 'gif']) {
    $upload_dir = __DIR__ . "/assets/uploads/$subfolder/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return ['error' => "Invalid file format for $subfolder."];
    
    $new_name = $subfolder . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
    if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_name)) {
        // Delete old file if it's not a placeholder
        if (!empty($old_path) && strpos($old_path, 'placeholder') === false && file_exists(__DIR__ . '/../' . $old_path)) {
            @unlink(__DIR__ . '/../' . $old_path);
        }
        return ['path' => "assets/uploads/$subfolder/" . $new_name];
    }
    return ['error' => "Upload failed for $subfolder."];
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
</head>
<body>
    <?php include '../public/navbar.php'; ?>
    <main class="py-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <img src="<?= htmlspecialchars($current_user['profile_image'] ?: 'https://via.placeholder.com/120') ?>"
                                 class="rounded-circle mb-2" style="width:120px; aspect-ratio:1/1; object-fit:cover;">
                            <h6 class="mb-0"><?= htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($current_user['username']) ?></small>
                        </div>
                    </div>
                    <div class="list-group">
                        <a href="profile.php" class="list-group-item list-group-item-action active"><i class="bi bi-person-circle me-2"></i> My Profile</a>
                        <a href="my-booking.php" class="list-group-item list-group-item-action"><i class="bi bi-card-checklist me-2"></i> My Bookings</a>
                        <a href="<?= base_url('auth/logout.php') ?>" class="list-group-item list-group-item-action text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="card shadow-sm">
                        <div class="card-header"><h5 class="mb-0">Account Settings</h5></div>
                        <div class="card-body">
                            <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                                <div class="mb-4 text-center">
                                    <label class="form-label d-block">Change Profile Photo</label>
                                    <input type="file" name="profile_image" class="form-control" accept="image/*">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($current_user['username']) ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($current_user['email']) ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($current_user['first_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($current_user['last_name']) ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($current_user['phone'] ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Residential Address</label>
                                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($current_user['address'] ?? '') ?></textarea>
                                </div>

                                <hr class="my-4">
                                <h6 class="text-primary"><i class="bi bi-card-heading me-2"></i>Driving License</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">License Number</label>
                                        <input type="text" name="license_number" class="form-control" value="<?= htmlspecialchars($current_user['license_number'] ?? '') ?>" placeholder="e.g. DL-12345678">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">License Document (Image/PDF)</label>
                                        <input type="file" name="license_image" class="form-control" accept="image/*,.pdf">
                                        <?php if (!empty($current_user['license_image'])): ?>
                                            <small class="text-success"><i class="bi bi-file-earmark-check"></i> Document is on file.</small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h6>Security (Leave blank to keep current password)</h6>
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 shadow-sm">Update Profile</button>
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