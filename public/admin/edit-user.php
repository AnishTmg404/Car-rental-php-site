<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

require_admin();

$page_title = 'Edit User';
$current_user = get_current_user_data();

$error = '';
$success = '';

if (!isset($_GET['id'])) {
    header('Location: ' . base_url('admin/manage-users.php'));
    exit;
}

$user_id = intval($_GET['id']);
$user = db_fetch("SELECT * FROM users WHERE id=?", [$user_id]);

if (!$user) {
    header('Location: ' . base_url('admin/manage-users.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $role       = $_POST['role'];

    // Basic validation
    if (!$username || !$first_name || !$last_name || !$email || !$role) {
        $error = "Username, name, email, and role are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email.";
    } else {
        // Check for duplicates
        $exists = db_fetch(
            "SELECT id FROM users WHERE (username=? OR email=?) AND id!=?",
            [$username, $email, $user_id]
        );
        if ($exists) $error = "Username or email already exists.";
    }

    // If no errors, proceed
    if (!$error) {
        // Handle image upload
        $image_path = $user['profile_image'] ?? null;

        if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error = "Invalid image type (jpg, jpeg, png, gif only).";
            } else {
                $upload_dir = __DIR__ . '/../../uploads/users/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                $new_name = uniqid('user_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name);
                $image_path = 'uploads/users/' . $new_name;
            }
        }
    }

    if (!$error) {
        // Prepare data for update
        $data = [
            'username'      => $username,
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'email'         => $email,
            'role'          => $role,
            'profile_image' => $image_path,
            'updated_at'    => date('Y-m-d H:i:s')
        ];

        if (!empty($password)) {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Short db_update function
        $updated = db_update('users', $data, ['id' => $user_id]);

        if ($updated) {
            $success = "User updated successfully!";
            $user = array_merge($user, $data); // refresh user data
        } else {
            $error = "Failed to update user.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('css/styles.css') ?>">
</head>
<body>

<?php include __DIR__ . '/../navbar.php'; ?>

<main class="py-4">
    <div class="container">
        <h1 class="h3 mb-4"><?= $page_title ?></h1>
        <a href="<?= base_url('admin/manage-users.php') ?>" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left me-2"></i>Back to Users
        </a>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control"
                       value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>

            <div class="row">
                <div class="col mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control"
                           value="<?= htmlspecialchars($user['first_name']) ?>" required>
                </div>
                <div class="col mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control"
                           value="<?= htmlspecialchars($user['last_name']) ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password (leave blank to keep unchanged)</label>
                <input type="password" name="password" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="user"  <?= $user['role']=='user'?'selected':'' ?>>User</option>
                    <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Profile Image</label>

                <?php if (!empty($user['profile_image'])): ?>
                    <div class="mb-2">
                        <img src="<?= base_url($user['profile_image']) ?>" alt="Profile"
                             width="100" class="rounded">
                    </div>
                <?php endif; ?>

                <input type="file" name="image" class="form-control">
            </div>

            <button class="btn btn-primary">Update User</button>

        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
