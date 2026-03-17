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
           c.brand, c.model, c.year, c.daily_rate, c.images 
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    WHERE b.id = ? AND b.user_id = ?
", [$booking_id, $current_user['id']]);

if (!$booking) {
    set_flash_message('error', 'Booking not found or access denied.');
    header('Location: ' . base_url('my-booking.php'));
    exit;
}

$car_images = json_decode($booking['images'], true) ?? [];
$car_image = $car_images[0] ?? 'https://via.placeholder.com/400x250';

// Fetch cancellation request if exists
$cancel_request = db_fetch("SELECT * FROM booking_cancellations WHERE booking_id = ? AND user_id = ?", [$booking_id, $current_user['id']]);

// Helper for status styling and messaging
function getStatusConfig($status) {
    switch ($status) {
        case 'pending':
            return ['class' => 'info', 'icon' => 'clock-history', 'msg' => 'Your booking request has been submitted successfully and is awaiting admin approval.'];
        case 'approved':
            return ['class' => 'success', 'icon' => 'check-circle', 'msg' => 'Congratulations! Your booking has been approved. Enjoy your ride!'];
        case 'active':
            return ['class' => 'primary', 'icon' => 'car-front', 'msg' => 'Your trip is currently in progress. Drive safely!'];
        case 'completed':
            return ['class' => 'secondary', 'icon' => 'flag', 'msg' => 'This booking is completed. Thank you for choosing us!'];
        case 'rejected':
            return ['class' => 'danger', 'icon' => 'x-circle', 'msg' => 'This booking request was declined. Please contact support for details.'];
        case 'cancelled':
            return ['class' => 'dark', 'icon' => 'trash', 'msg' => 'This booking has been cancelled.'];
        default:
            return ['class' => 'light', 'icon' => 'info-circle', 'msg' => 'Status: ' . ucfirst($status)];
    }
}

$statusConfig = getStatusConfig($booking['status']);
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
        
        <?php 
            $flash_error = get_flash_message('error');
            $flash_success = get_flash_message('success');
        ?>
        <?php if ($flash_error): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $flash_error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($flash_success): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= $flash_success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Booking Details</h1>
                <p class="text-muted mb-0">Order ID: #<?= $booking['id'] ?></p>
            </div>
            <a href="<?= base_url('my-booking.php') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to My Bookings
            </a>
        </div>

        <div class="alert alert-<?= $statusConfig['class'] ?> d-flex align-items-center p-3 shadow-sm" role="alert">
            <i class="bi bi-<?= $statusConfig['icon'] ?> fs-4 me-3"></i>
            <div>
                <div class="fw-bold">Status: <?= ucfirst($booking['status']) ?></div>
                <small><?= $statusConfig['msg'] ?></small>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 h-100">
                    <img src="<?= htmlspecialchars($car_image) ?>" 
                         class="card-img-top rounded-top" 
                         style="height: 250px; object-fit: cover;"
                         alt="<?= htmlspecialchars($booking['brand'] . ' ' . $booking['model']) ?>">
                    <div class="card-body text-center">
                        <h4 class="mb-1"><?= htmlspecialchars($booking['brand'] . ' ' . $booking['model']) ?></h4>
                        <p class="text-muted mb-4"><?= $booking['year'] ?> Model</p>
                        
                        <div class="d-grid">
                            <a href="<?= base_url('car-details.php?id=' . $booking['car_id']) ?>" class="btn btn-primary">
                                <i class="bi bi-info-circle me-2"></i>Re-view Car Info
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Summary</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-muted" width="40%">Pickup:</td>
                                    <td class="fw-semibold">
                                        <i class="bi bi-calendar-event me-2"></i><?= date('D, M jS, Y', strtotime($booking['pickup_date'])) ?><br>
                                        <small class="text-muted"><i class="bi bi-geo-alt me-2"></i><?= ucfirst(str_replace('_', ' ', $booking['pickup_location'])) ?></small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Return:</td>
                                    <td class="fw-semibold">
                                        <i class="bi bi-calendar-check me-2"></i><?= date('D, M jS, Y', strtotime($booking['return_date'])) ?><br>
                                        <small class="text-muted"><i class="bi bi-geo-alt me-2"></i><?= ucfirst(str_replace('_', ' ', $booking['return_location'])) ?></small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Rental Duration:</td>
                                    <td><?= $booking['total_days'] ?> Days</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Total Paid:</td>
                                    <td class="fs-5 fw-bold text-success">Rs.<?= number_format($booking['total_amount'], 2) ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <?php if (!empty($booking['user_notes'])): ?>
                            <hr>
                            <div class="bg-light p-3 rounded">
                                <small class="text-muted fw-bold d-block mb-1 text-uppercase">Your Special Requests:</small>
                                <p class="mb-0 fst-italic">"<?= nl2br(htmlspecialchars($booking['user_notes'])) ?>"</p>
                            </div>
                        <?php endif; ?>

                        <?php if ($cancel_request): ?>
                            <div class="card mt-4 border-<?= $cancel_request['status'] === 'rejected' ? 'danger' : 'info' ?> bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold"><i class="bi bi-shield-exclamation me-2"></i>Cancellation Review</h6>
                                    <p class="small mb-1"><strong>Reason:</strong> <?= htmlspecialchars($cancel_request['cancel_reason']) ?></p>
                                    <?php if ($cancel_request['admin_note']): ?>
                                        <p class="small mb-0 text-danger"><strong>Admin Response:</strong> <?= htmlspecialchars($cancel_request['admin_note']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4 pt-2">
                            <?php if (!$cancel_request && !in_array($booking['status'], ['rejected', 'cancelled', 'completed'])): ?>
                                <?php if ($booking['status'] === 'active'): ?>
                                    <div class="p-3 border rounded border-warning">
                                        <p class="mb-0 small text-dark">
                                            <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
                                            For emergency cancellations while the car is in your possession, please call our 24/7 support: 
                                            <strong>9800000000</strong>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <a href="<?= base_url('cancel-booking.php?id=' . $booking['id']) ?>" 
                                       class="btn btn-outline-danger w-100"
                                       onclick="return confirm('Request cancellation? This will need admin approval.');">
                                        Request Cancellation
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../public/footer.php'; ?>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> -->
</body>
</html>