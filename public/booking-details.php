<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/url.php';

// Require login
require_login();

$page_title = 'Booking Details';
$current_user = get_current_user_data();

$booking_id = intval($_GET['id'] ?? 0);
if (!$booking_id) {
    set_flash_message('error', 'Invalid booking request.');
    header('Location: ' . base_url('my-booking.php'));
    exit;
}

// Fetch booking & join with car data
$booking = db_fetch("
    SELECT b.*, 
           c.make, c.model, c.year, c.daily_rate, c.images 
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    WHERE b.id = ? AND b.user_id = ?
", [$booking_id, $current_user['id']]);

if (!$booking) {
    set_flash_message('error', 'Booking not found.');
    header('Location: ' . base_url('my-booking.php'));
    exit;
}

$car_images = json_decode($booking['images'], true) ?? [];
$car_image = $car_images[0] ?? 'https://via.placeholder.com/400x250';

// Fetch cancellation request if exists
$cancel_request = db_fetch("SELECT * FROM booking_cancellations WHERE booking_id = ? AND user_id = ?", [$booking_id, $current_user['id']]);

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
<link rel="stylesheet" href="<?= asset_url('css/theme.css') ?>">
</head>
<body>

<?php include '../public/navbar.php'; ?>

<main class="py-4">
<div class="container">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Booking Details</h1>
            <p class="text-muted mb-0">Booking #<?= $booking['id'] ?></p>
        </div>
        <a href="<?= base_url('my-booking.php') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to My Bookings
        </a>
    </div>

    <!-- Status -->
    <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="bi bi-clock-history fs-5 me-2"></i>
        <div>
            Current Status: <strong><?= ucfirst($booking['status']) ?></strong>
        </div>
    </div>

    <div class="row g-4">

        <!-- Car Information -->
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <img src="<?= htmlspecialchars($car_image) ?>" 
                     class="card-img-top" 
                     alt="<?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?>">
                <div class="card-body">
                    <h4><?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?></h4>
                    <p class="text-muted">
                        <?= $booking['year'] ?> • $<?= number_format($booking['daily_rate'], 2) ?>/day
                    </p>

                    <a href="<?= base_url('car-details.php?id=' . $booking['car_id']) ?>" 
                       class="btn btn-outline-primary w-100">
                        View Car Details
                    </a>
                </div>
            </div>
        </div>

        <!-- Booking Information -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Booking Information</h5>
                </div>
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Pickup Date:</strong>
                            <p><?= htmlspecialchars($booking['pickup_date']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Return Date:</strong>
                            <p><?= htmlspecialchars($booking['return_date']) ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Pickup Location:</strong>
                            <p><?= ucfirst(str_replace('_', ' ', $booking['pickup_location'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Return Location:</strong>
                            <p><?= ucfirst(str_replace('_', ' ', $booking['return_location'])) ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Total Days:</strong>
                            <p><?= $booking['total_days'] ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Total Amount:</strong>
                            <p class="fw-bold text-success">Rs.<?= number_format($booking['total_amount'], 2) ?></p>
                        </div>
                    </div>

                    <?php if (!empty($booking['user_notes'])): ?>
                        <div class="mb-3">
                            <strong>Special Notes:</strong>
                            <p><?= nl2br(htmlspecialchars($booking['user_notes'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Cancellation Status -->
                    <?php if ($cancel_request): ?>
                        <div class="alert alert-<?=
                            $cancel_request['status'] === 'approved' ? 'success' :
                            ($cancel_request['status'] === 'rejected' ? 'danger' : 'info')
                        ?> mt-3">
                            <strong>Cancellation Request:</strong> <?= ucfirst($cancel_request['status']) ?><br>
                            <strong>Reason:</strong> <?= nl2br(htmlspecialchars($cancel_request['cancel_reason'])) ?><br>
                            <?php if (!empty($cancel_request['admin_note'])): ?>
                                <strong>Admin Note:</strong> <?= nl2br(htmlspecialchars($cancel_request['admin_note'])) ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Cancel Button / Active Booking Info -->
                    <?php if (!$cancel_request && !in_array($booking['status'], ['rejected', 'cancelled'])): ?>
                        <?php if ($booking['status'] === 'active'): ?>
                            <div class="alert alert-warning mt-3">
                                Booking is currently active. To request cancellation, please contact the admin directly:<br>
                                Email: <a href="mailto:admin@example.com">admin@example.com</a><br>
                                Phone: 9800000000
                            </div>
                        <?php else: ?>
                            <a href="<?= base_url('cancel-booking.php?id=' . $booking['id']) ?>" 
                               class="btn btn-danger w-100 mt-3"
                               onclick="return confirm('Are you sure you want to request cancellation for this booking?');">
                                Cancel Booking
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>
</main>

<?php include '../public/footer.php'; ?>
</body>
</html>
