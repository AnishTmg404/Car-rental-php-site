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

    // Check if request exists and is still pending
    $request = db_fetch("SELECT * FROM booking_cancellations WHERE id = ? AND status = 'pending'", [$request_id]);

    if ($request) {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        
        $update_data = [
            'status' => $status,
            'decided_at' => date('Y-m-d H:i:s'),
            'admin_note' => $admin_note
        ];

        $updated = db_update('booking_cancellations', $update_data, ['id' => $request_id]);

        if ($updated) {
            if ($action === 'approve') {
                // If approved, update the main booking status to 'cancelled'
                db_update('bookings', ['status' => 'cancelled'], ['id' => $request['booking_id']]);
                set_flash_message('success', 'Cancellation request approved. Booking #' . $request['booking_id'] . ' is now cancelled.');
            } else {
                set_flash_message('success', 'Cancellation request #' . $request_id . ' has been rejected.');
            }
        } else {
            set_flash_message('error', 'Failed to update the database.');
        }
    } else {
        set_flash_message('error', 'Request not found, already processed, or invalid ID.');
    }

    header('Location: ' . base_url('admin/admin-cancellations.php')); // Ensure path is correct
    exit;
}

// FETCHING DATA - Using LEFT JOIN to prevent records from disappearing if a join fails
$requests = db_fetch_all("
    SELECT bc.*, 
           b.pickup_date, b.return_date, b.total_amount, b.status AS booking_status,
           c.brand, c.model,
           u.first_name, u.last_name, u.username
    FROM booking_cancellations bc
    INNER JOIN bookings b ON bc.booking_id = b.id
    LEFT JOIN users u ON bc.user_id = u.id
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
        .table-card { border-radius: 15px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.05); }
        .badge-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    </style>
</head>
<body class="bg-light">

<?php include '../navbar.php'; ?>

<main class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800"><?= $page_title ?></h1>
            <span class="badge bg-primary rounded-pill"><?= count($requests) ?> Pending</span>
        </div>

        <?php if ($msg = get_flash_message('success')): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
                <i class="bi bi-check-circle-fill me-2"></i><?= $msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($msg = get_flash_message('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($requests)): ?>
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="mt-3 text-muted">No pending cancellation requests found.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm table-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Request #</th>
                                <th>User</th>
                                <th>Car Details</th>
                                <th>Booking Info</th>
                                <th>Reason for Cancellation</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $r): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">#<?= $r['id'] ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></div>
                                        <small class="text-muted">@<?= htmlspecialchars($r['username']) ?></small>
                                    </td>
                                    <td>
                                        <div class="small fw-bold text-dark"><?= htmlspecialchars($r['brand'] . ' ' . $r['model']) ?></div>
                                        <small class="text-muted">Booking ID: #<?= $r['booking_id'] ?></small>
                                    </td>
                                    <td>
                                        <div class="small"><i class="bi bi-calendar-range me-1"></i><?= date('M d', strtotime($r['pickup_date'])) ?> - <?= date('M d', strtotime($r['return_date'])) ?></div>
                                        <div class="fw-bold text-success">Rs.<?= number_format($r['total_amount'], 2) ?></div>
                                    </td>
                                    <td>
                                        <p class="small mb-0 text-wrap" style="max-width: 250px;">
                                            <i class="bi bi-chat-left-dots me-1"></i> "<?= htmlspecialchars($r['cancel_reason']) ?>"
                                        </p>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#actionModal<?= $r['id'] ?>">
                                            Review
                                        </button>

                                        <div class="modal fade" id="actionModal<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <form method="POST" class="modal-content border-0 shadow">
                                                    <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                                    <div class="modal-header border-0 bg-light">
                                                        <h5 class="modal-title fw-bold">Review Request #<?= $r['id'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3 text-center">
                                                            <p class="text-muted small">Are you sure you want to process this cancellation? This action cannot be undone.</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold small">Admin Response / Notes</label>
                                                            <textarea class="form-control" name="admin_note" rows="3" placeholder="Explain the decision to the user..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 bg-light justify-content-between">
                                                        <button type="submit" name="action" value="reject" class="btn btn-outline-danger px-4 rounded-pill">Reject Request</button>
                                                        <button type="submit" name="action" value="approve" class="btn btn-success px-4 rounded-pill">Approve & Cancel</button>
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
            </div>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        let alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            new bootstrap.Alert(alert).close();
        });
    }, 5000);
</script>
</body>
</html>