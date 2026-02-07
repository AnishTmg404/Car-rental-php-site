<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Require admin login
require_login();
if (!is_admin()) {
    header('Location: ' . base_url('index.php'));
    exit;
}

$page_title = 'Booking Cancellation Requests';

// Fetch all pending cancellation requests
$cancellations = db_fetch_all("
    SELECT bc.*, b.user_id, b.car_id, b.pickup_date, b.return_date, b.total_amount, b.status AS booking_status,
           u.first_name, u.last_name, u.username,
           c.make, c.model
    FROM booking_cancellations bc
    JOIN bookings b ON bc.booking_id = b.id
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_id = c.id
    WHERE bc.status = 'pending'
    ORDER BY bc.requested_at DESC
", []);

$statuses = ['approved' => 'success', 'rejected' => 'danger', 'pending' => 'secondary'];

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cancel_id = intval($_POST['cancel_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $admin_notes = sanitize_input($_POST['admin_notes'] ?? '');

    $cancel = db_fetch("SELECT * FROM booking_cancellations WHERE id = ?", [$cancel_id]);
    if (!$cancel) {
        set_flash_message('error', 'Cancellation request not found.');
    } elseif (!in_array($action, ['approved', 'rejected'])) {
        set_flash_message('error', 'Invalid action.');
    } else {
        // Update cancellation request
        db_update('booking_cancellations', ['status' => $action, 'admin_notes' => $admin_notes], "id = ?", [$cancel_id]);

        // If approved, update booking status to cancelled
        if ($action === 'approved') {
            db_update('bookings', ['status' => 'cancelled'], "id = ?", [$cancel['booking_id']]);
        }

        set_flash_message('success', 'Cancellation request has been ' . $action . '.');
        header('Location: ' . base_url('admin/cancellation-requests.php'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
</head>
<body>
<?php include '../public/navbar.php'; ?>

<main class="py-4">
<div class="container">
    <h1 class="h3 mb-4"><?= $page_title ?></h1>

    <!-- Flash messages -->
    <?php if ($msg = get_flash_message('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = get_flash_message('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= $msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <?php if (empty($cancellations)): ?>
        <p class="text-muted">No pending cancellation requests.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Car</th>
                        <th>Booking Dates</th>
                        <th>Total Amount</th>
                        <th>Cancel Reason</th>
                        <th>Requested At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cancellations as $i => $c): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?><br><small class="text-muted"><?= htmlspecialchars($c['username']) ?></small></td>
                            <td><?= htmlspecialchars($c['make'] . ' ' . $c['model']) ?></td>
                            <td><?= date('d/m/Y', strtotime($c['pickup_date'])) ?> - <?= date('d/m/Y', strtotime($c['return_date'])) ?></td>
                            <td>$<?= number_format($c['total_amount'], 2) ?></td>
                            <td><?= nl2br(htmlspecialchars($c['cancel_reason'])) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($c['requested_at'])) ?></td>
                            <td>
                                <form method="POST" class="d-flex flex-column gap-1">
                                    <input type="hidden" name="cancel_id" value="<?= $c['id'] ?>">
                                    <textarea name="admin_notes" class="form-control form-control-sm mb-1" placeholder="Admin notes..."></textarea>
                                    <div class="d-flex gap-1">
                                        <button type="submit" name="action" value="approved" class="btn btn-sm btn-success">Approve</button>
                                        <button type="submit" name="action" value="rejected" class="btn btn-sm btn-danger">Reject</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
