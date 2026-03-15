<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/url.php';

// Require user login
require_login();

$page_title = 'Cancel Booking';
$current_user = get_current_user_data();

$admin_contact = [
    'phone' => '+977-9812345678',
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

// Check if a request already exists to prevent duplicates
$existing_request = db_fetch("SELECT id FROM booking_cancellations WHERE booking_id = ? AND status = 'pending'", [$booking_id]);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cancel_reason = sanitize_input($_POST['cancel_reason'] ?? '');

    if ($existing_request) {
        $error = 'You already have a pending cancellation request for this booking.';
    } elseif (empty($cancel_reason)) {
        $error = 'Please provide a reason for cancellation.';
    } else {
        // We removed the immediate db_update logic here.
        // Now, all statuses (pending, approved, active) go through the same request process.
        if (in_array($booking['status'], ['pending', 'approved', 'active'])) {
            
            $inserted = db_insert('booking_cancellations', [
                'booking_id' => $booking_id,
                'user_id' => $current_user['id'],
                'cancel_reason' => $cancel_reason,
                'status' => 'pending', // Always pending now
                'requested_at' => date('Y-m-d H:i:s')
            ]);

            if ($inserted) {
                $success = 'Your cancellation request has been submitted and is awaiting admin approval.';
            } else {
                $error = 'Something went wrong while submitting your request.';
            }
        } else {
            $error = 'This booking is already ' . $booking['status'] . ' and cannot be cancelled.';
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
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-danger"><i class="bi bi-x-octagon me-2"></i>Cancel Booking #<?= $booking['id'] ?></h5>
                    </div>
                    <div class="card-body">

                        <?php if ($error): ?>
                            <div class="alert alert-danger border-0 shadow-sm small"><?= $error ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success border-0 shadow-sm small">
                                <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
                            </div>
                            <div class="d-grid">
                                <a href="<?= base_url('my-booking.php') ?>" class="btn btn-primary">Back to My Bookings</a>
                            </div>
                        <?php else: ?>
                            
                            <div class="alert alert-info border-0 small">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                All cancellation requests are reviewed by our team. You will be notified once the request is processed.
                            </div>

                            <form method="POST">
                                <div class="mb-3">
                                    <label for="cancel_reason" class="form-label fw-bold">Reason for Cancellation *</label>
                                    <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="4" placeholder="Please tell us why you want to cancel..." required><?= htmlspecialchars($_POST['cancel_reason'] ?? '') ?></textarea>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-danger py-2">
                                        Submit Cancellation Request
                                    </button>
                                    <a href="<?= base_url('booking-details.php?id=' . $booking['id']) ?>" class="btn btn-light text-muted">
                                        Nevermind, keep my booking
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>