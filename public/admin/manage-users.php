<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Require admin access
require_admin();

$page_title = 'Manage Users';
$current_user = get_current_user_data();

$error = '';
$success = '';

// Handle Delete User
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    if ($delete_id === $current_user['id']) {
        $error = "You cannot delete your own account!";
    } else {
        $deleted = db_delete('users', 'id = ?', [$delete_id]);
        if ($deleted) {
            $success = "User deleted successfully!";
        } else {
            $error = "Failed to delete user.";
        }
    }
}

// Handle Status Toggle (Active/Inactive/Suspended)
if (isset($_GET['toggle_id'])) {
    $toggle_id = intval($_GET['toggle_id']);
    
    if ($toggle_id === $current_user['id']) {
        $error = "You cannot change your own admin account status!";
    } else {
        // Fetch current status
        $user_to_toggle = db_fetch("SELECT status FROM users WHERE id = ?", [$toggle_id]);
        
        if ($user_to_toggle) {
            // Logic: If active -> set inactive. If anything else -> set active.
            $new_status = ($user_to_toggle['status'] === 'active') ? 'inactive' : 'active';
            
            $updated = db_update('users', ['status' => $new_status], ['id' => $toggle_id]);
            
            if ($updated) {
                $success = "User status updated to " . ucfirst($new_status) . "!";
            } else {
                $error = "Failed to update user status.";
            }
        }
    }
}

// Get all users
$users = db_fetch_all("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset_url('css/styles.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/theme.css') ?>">
</head>
<body>

<?php include __DIR__ . '/../navbar.php'; ?>

<main class="py-4">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0"><?= $page_title ?></h1>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                    </a>
                    <a href="<?= base_url('admin/add-user.php') ?>" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Add User
                    </a>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Users Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $i => $user): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['first_name']) ?></td>
                                    <td><?= htmlspecialchars($user['last_name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= ucfirst($user['role']) ?></td>
                                    <td><?= $user['created_at'] ?></td>
                                    <td><?= $user['updated_at'] ?? '-' ?></td>
                                    <td>
                    <?php
                    $status = $user['status'] ?? 'active';
                    $badge_class = 'bg-success'; // Default active
                    if ($status === 'inactive') $badge_class = 'bg-secondary';
                    if ($status === 'suspended') $badge_class = 'bg-danger';
                    ?>
                    <span class="badge <?= $badge_class ?>">
                        <?= ucfirst($status) ?>
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="<?= base_url('admin/edit-user.php?id=' . $user['id']) ?>" class="btn btn-outline-primary" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <?php if ($user['id'] !== $current_user['id']): ?>
                            <?php if ($status === 'active'): ?>
                                <a href="?toggle_id=<?= $user['id'] ?>" class="btn btn-outline-secondary" title="Deactivate" onclick="return confirm('Deactivate this user?');">
                                    <i class="bi bi-pause-fill"></i>
                                </a>
                            <?php else: ?>
                                <a href="?toggle_id=<?= $user['id'] ?>" class="btn btn-outline-success" title="Activate" onclick="return confirm('Activate this user?');">
                                    <i class="bi bi-play-fill"></i>
                                </a>
                            <?php endif; ?>

                            <button class="btn btn-outline-danger" title="Suspend" onclick="suspendUser(<?= $user['id'] ?>)">
                                <i class="bi bi-slash-circle"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => new bootstrap.Alert(alert).close());
    }, 5000);
</script>
</body>
</html>
