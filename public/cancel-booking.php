<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/url.php';

// Require user login
require_login();

$page_title = 'Cancel Booking';
$current_user = get_current_user_data();

// Admin contact info (shown to users who try to cancel active bookings)
$admin_contact = [
    'phone' => '+1-800-123-4567',
    'email' => 'support@carrental.com'
];

$booking_id = intval($_GET['id'] ?? 0);
if (!$booking_id) {
    set_flash_message('error', 'Invalid booking ID.');
    header('Location: ' . base_url('my-booking.php'));
    exit;
}

// Fetch booking
$booking = db_fetch("SELECT * FROM bookings WHERE id = ? AND user_id = ?", [$booking_id, $current_user['id']]);
if (!$booking) {
    set_flash_message('error', 'Booking not found.');
    header('Location: ' . base_url('my-booking.php'));
    exit;
}

// Initialize
$error = '';
$success = '';
$is_requesting_admin = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cancel_reason = sanitize_input($_POST['cancel_reason'] ?? '');

    if (empty($cancel_reason)) {
        $error = 'Please provide a reason for cancellation.';
    } else {
        if (in_array($booking['status'], ['pending', 'approved'])) {
            // Immediate cancellation
            db_update('bookings', ['status' => 'cancelled'], ['id' => $booking_id]);

            // Insert cancellation record
            db_insert('booking_cancellations', [
                'booking_id' => $booking_id,
                'user_id' => $current_user['id'],
                'cancel_reason' => $cancel_reason,
                'status' => 'approved', // auto-approved for pending/approved bookings
                'requested_at' => date('Y-m-d H:i:s'),
                'decided_at' => date('Y-m-d H:i:s')
            ]);

            $success = 'Booking has been cancelled successfully.';
        } elseif ($booking['status'] === 'active') {
            // Request admin approval
            db_insert('booking_cancellations', [
                'booking_id' => $booking_id,
                'user_id' => $current_user['id'],
                'cancel_reason' => $cancel_reason,
                'status' => 'pending',
                'requested_at' => date('Y-m-d H:i:s')
            ]);

            $success = 'Your cancellation request has been sent to the admin. Please contact support if urgent.';
        } else {
            $error = 'This booking cannot be cancelled.';
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
</head>
<body>

<?php include '../public/navbar.php'; ?>

<main class="py-4">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-lg-6">

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Cancel Booking #<?= $booking['id'] ?></h5>
                    </div>
                    <div class="card-body">

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <?php if (!$success): ?>
                            <?php if (in_array($booking['status'], ['pending', 'approved', 'active'])): ?>
                                <?php if ($booking['status'] === 'active'): ?>
                                    <div class="alert alert-warning">
                                        This booking is currently <strong>active</strong>. Cancellation requires admin approval.<br>
                                        Please contact admin: <br>
                                        Phone: <?= htmlspecialchars($admin_contact['phone']) ?><br>
                                        Email: <?= htmlspecialchars($admin_contact['email']) ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="cancel_reason" class="form-label">Reason for Cancellation *</label>
                                        <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="4" required><?= htmlspecialchars($_POST['cancel_reason'] ?? '') ?></textarea>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-x-circle me-2"></i>Submit Cancellation
                                        </button>
                                        <a href="<?= base_url('booking-details.php?id=' . $booking['id']) ?>" class="btn btn-outline-secondary">
                                            Back to Booking Details
                                        </a>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    This booking cannot be cancelled (status: <?= htmlspecialchars($booking['status']) ?>).
                                </div>
                                <a href="<?= base_url('booking-details.php?id=' . $booking['id']) ?>" class="btn btn-outline-secondary">
                                    Back to Booking Details
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= base_url('my-booking.php') ?>" class="btn btn-primary mt-3">
                                Back to My Bookings
                            </a>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>

    </div>
</main>

</body>
</html>
