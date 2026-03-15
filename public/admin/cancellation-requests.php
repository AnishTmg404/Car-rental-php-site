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

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cancel_id = intval($_POST['cancel_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $admin_notes = sanitize_input($_POST['admin_notes'] ?? '');

    // 1. Fetch the request to make sure it exists and get the associated booking_id
    $cancel = db_fetch("SELECT * FROM booking_cancellations WHERE id = ?", [$cancel_id]);
    
    if (!$cancel) {
        set_flash_message('error', 'Cancellation request not found.');
    } elseif (!in_array($action, ['approved', 'rejected'])) {
        set_flash_message('error', 'Invalid action.');
    } else {
        // 2. Update cancellation request table
        // Note: Using 'admin_note' as it's the more common naming convention, 
        // change to 'admin_notes' if that is exactly what is in your DB.
        db_update('booking_cancellations', [
            'status' => $action, 
            'admin_note' => $admin_notes,
            'decided_at' => date('Y-m-d H:i:s')
        ], ['id' => $cancel_id]);

        // 3. If approved, update the main bookings table status
        if ($action === 'approved') {
            db_update('bookings', ['status' => 'cancelled'], ['id' => $cancel['booking_id']]);
            set_flash_message('success', 'Request approved. Booking #' . $cancel['booking_id'] . ' is now cancelled.');
        } else {
            set_flash_message('success', 'Cancellation request rejected.');
        }

        header('Location: ' . base_url('admin/cancellation-requests.php'));
        exit;
    }
}

// 4. Optimized Fetch: Using LEFT JOIN ensures requests show up even if user/car data has issues
$cancellations = db_fetch_all("
    SELECT bc.*, 
           b.pickup_date, b.return_date, b.total_amount, b.status AS booking_status,
           u.first_name, u.last_name, u.username,
           c.brand, c.model
    FROM booking_cancellations bc
    INNER JOIN bookings b ON bc.booking_id = b.id
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN cars c ON b.car_id = c.id
    WHERE bc.status = 'pending'
    ORDER BY bc.requested_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <style>
        .card-table { border-radius: 12px; border: none; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1); }
        .reason-box { max-width: 200px; font-size: 0.85rem; color: #666; }
    </style>
</head>
<body class="bg-light">

<?php include '../navbar.php'; ?>

<main class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0"><?= $page_title ?></h1>
            <span class="badge bg-danger rounded-pill"><?= count($cancellations) ?> Pending</span>
        </div>

        <?php if ($msg = get_flash_message('success')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= $msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        
        <?php if ($msg = get_flash_message('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= $msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card card-table">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">User</th>
                            <th>Car</th>
                            <th>Dates</th>
                            <th>Total</th>
                            <th>Reason</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cancellations)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No pending requests found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cancellations as $c): ?>
                                <tr>
                                    <td class="ps-3">
                                        <strong><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></strong><br>
                                        <small class="text-muted">@<?= htmlspecialchars($c['username']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($c['brand'] . ' ' . $c['model']) ?></td>
                                    <td>
                                        <small><?= date('d M', strtotime($c['pickup_date'])) ?> - <?= date('d M', strtotime($c['return_date'])) ?></small>
                                    </td>
                                    <td class="text-success fw-bold">Rs.<?= number_format($c['total_amount'], 2) ?></td>
                                    <td>
                                        <div class="reason-box text-truncate" title="<?= htmlspecialchars($c['cancel_reason']) ?>">
                                            <?= htmlspecialchars($c['cancel_reason']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal<?= $c['id'] ?>">Review</button>

                                        <div class="modal fade" id="modal<?= $c['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <form method="POST" class="modal-content">
                                                    <input type="hidden" name="cancel_id" value="<?= $c['id'] ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Process Request #<?= $c['id'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="mb-2 small"><strong>User Reason:</strong> <?= htmlspecialchars($c['cancel_reason']) ?></p>
                                                        <label class="form-label small fw-bold">Admin Notes</label>
                                                        <textarea name="admin_notes" class="form-control mb-3" rows="3" placeholder="Reason for approval/rejection..."></textarea>
                                                    </div>
                                                    <div class="modal-footer justify-content-between">
                                                        <button type="submit" name="action" value="rejected" class="btn btn-outline-danger">Reject Request</button>
                                                        <button type="submit" name="action" value="approved" class="btn btn-success">Approve & Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>