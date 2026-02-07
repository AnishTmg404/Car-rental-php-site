<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';
require_once __DIR__ . '/../../config/database.php';

require_admin();

$page_title = 'Add User';
$current_user = get_current_user_data();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $role       = $_POST['role'];
    $address    = trim($_POST['address']);
    $phone      = trim($_POST['phone']);

    // Validation
    if (!$username || !$first_name || !$last_name || !$email || !$password || !$role || !$address || !$phone) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        // Check if username/email exists
        $exists = db_fetch("SELECT id FROM users WHERE username=? OR email=?", [$username, $email]);
        if ($exists) {
            $error = "Username or email already exists.";
        } else {
            // Handle profile image
            $image_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','gif'])) {
                    $error = "Invalid image type.";
                } else {
                    $new_name = uniqid('user_') . '.' . $ext;
                    $upload_dir = __DIR__ . '/../../uploads/users/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name);
                    $image_path = 'uploads/users/' . $new_name;
                }
            }
        }
    }

    if (!$error) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $inserted = db_insert('users', [
            'username'      => $username,
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'email'         => $email,
            'password_hash' => $hashed_password,
            'role'          => $role,
            'address'       => $address,
            'phone'         => $phone,
            'created_at'    => date('Y-m-d H:i:s')
        ]);



        if ($inserted) {
            $success = "User added successfully!";
        } else {
            $error = "Failed to add user.";
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
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="row">
                <div class="col mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                    </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Profile Image</label>
                <input type="file" name="image" class="form-control">
            </div>
            <button class="btn btn-primary">Add User</button>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
