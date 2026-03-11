<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Require admin login
require_login();
if (!is_admin()) {
    set_flash_message('error', 'Access denied.');
    header('Location: ' . base_url('index.php'));
    exit;
}

$page_title = 'Manage Cancellation Requests';

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = intval($_POST['request_id'] ?? 0);
    $admin_note = sanitize_input($_POST['admin_note'] ?? '');

    $request = db_fetch("SELECT * FROM booking_cancellations WHERE id = ? AND status = 'pending'", [$request_id]);

    if ($request) {
        $update_data = [
            'status' => ($action === 'approve') ? 'approved' : 'rejected',
            'decided_at' => date('Y-m-d H:i:s'),
            'admin_note' => $admin_note
        ];

        db_update('booking_cancellations', $update_data, ['id' => $request_id]);

        // If approved, update booking status to cancelled
        if ($action === 'approve') {
            db_update('bookings', ['status' => 'cancelled'], ['id' => $request['booking_id']]);
            set_flash_message('success', 'Cancellation request approved and booking cancelled.');
        } else {
            set_flash_message('success', 'Cancellation request rejected.');
        }
    } else {
        set_flash_message('error', 'Cancellation request not found or already processed.');
    }

    header('Location: ' . base_url('admin-cancellations.php'));
    exit;
}

// Fetch pending cancellation requests with user and booking info
$requests = db_fetch_all("
    SELECT bc.*, b.car_id, b.pickup_date, b.return_date, b.total_amount, b.status AS booking_status,
           c.brand, c.model,
           u.first_name, u.last_name, u.username
    FROM booking_cancellations bc
    JOIN bookings b ON bc.booking_id = b.id
    JOIN users u ON bc.user_id = u.id
    JOIN cars c ON b.car_id = c.id
    WHERE bc.status = 'pending'
    ORDER BY bc.requested_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
</head>
<body>
<?php include '../navbar.php'; ?>

<main class="py-4">
    <div class="container">
        <h1 class="mb-4"><?= $page_title ?></h1>

        <!-- Flash Messages -->
        <?php if ($msg = get_flash_message('success')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= $msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($msg = get_flash_message('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= $msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <?php if (empty($requests)): ?>
            <div class="alert alert-info">No pending cancellation requests.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Car</th>
                            <th>Booking Dates</th>
                            <th>Total</th>
                            <th>Reason</th>
                            <th>Requested At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $i => $r): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name'] . ' (' . $r['username'] . ')') ?></td>
                                <td><?= htmlspecialchars($r['brand'] . ' ' . $r['model']) ?></td>
                                <td><?= htmlspecialchars($r['pickup_date'] . ' → ' . $r['return_date']) ?></td>
                                <td>$<?= number_format($r['total_amount'], 2) ?></td>
                                <td><?= nl2br(htmlspecialchars($r['cancel_reason'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($r['requested_at'])) ?></td>
                                <td>
                                    <!-- Modal Trigger -->
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#actionModal<?= $r['id'] ?>">
                                        Take Action
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="actionModal<?= $r['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form method="POST" class="modal-content">
                                                <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Action on Request #<?= $r['id'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Admin Note (optional)</label>
                                                        <textarea class="form-control" name="admin_note" rows="3"></textarea>
                                                    </div>
                                                    <p>Do you want to approve or reject this cancellation request?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="action" value="approve" class="btn btn-success">
                                                        Approve
                                                    </button>
                                                    <button type="submit" name="action" value="reject" class="btn btn-danger">
                                                        Reject
                                                    </button>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

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
