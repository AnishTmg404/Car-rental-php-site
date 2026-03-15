<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// 1. Validation & Access Control
require_login();
if (!is_admin()) {
    set_flash_message('error', 'Access denied.');
    header('Location: ' . base_url('index.php'));
    exit;
}

$page_title = 'Admin - Booking Details';
$booking_id = intval($_GET['id'] ?? 0);

if (!$booking_id) {
    header('Location: ' . base_url('admin/manage-booking.php'));
    exit;
}

// 2. Fetch Detailed Data
$booking = db_fetch("
    SELECT b.*, 
           c.brand, c.model, c.year, c.daily_rate, c.images,
           u.first_name, u.last_name, u.email, u.phone, u.address, u.username
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    JOIN users u ON b.user_id = u.id
    WHERE b.id = ?
", [$booking_id]);

if (!$booking) {
    die("Booking not found.");
}

// Image handling
$car_images = json_decode($booking['images'], true) ?? [];
$raw_path = $car_images[0] ?? '';
$car_image = (!empty($raw_path) && filter_var($raw_path, FILTER_VALIDATE_URL)) ? $raw_path : base_url($raw_path);

// 3. Fetch Cancellation Request (using your column: requested_at)
$cancel_request = db_fetch("SELECT * FROM booking_cancellations WHERE booking_id = ?", [$booking_id]);
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
</head>
<body class="bg-light">

<?php include __DIR__ . '/../navbar.php'; ?>

<main class="py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="manage-booking.php">Bookings</a></li>
                        <li class="breadcrumb-item active">#<?= $booking['id'] ?></li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0">Review Booking Details</h1>
            </div>
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-printer me-2"></i>Print
                </button>
                <a href="manage-booking.php" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">Customer Information</div>
                    <div class="card-body">
                        <p class="mb-1 fw-bold"><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></p>
                        <p class="text-muted small mb-3">@<?= htmlspecialchars($booking['username']) ?></p>
                        <p class="text-muted small mb-1"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($booking['email']) ?></p>
                        <p class="text-muted small mb-0"><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($booking['phone']) ?></p>
                        <hr>
                        <p class="text-muted small mb-0"><strong>Address:</strong><br><?= nl2br(htmlspecialchars($booking['address'])) ?></p>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <img src="<?= htmlspecialchars($car_image) ?>" class="card-img-top" alt="Car" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($booking['brand'] . ' ' . $booking['model']) ?></h5>
                        <p class="text-muted small"><?= $booking['year'] ?> • Rs. <?= number_format($booking['daily_rate'], 2) ?>/day</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Rental Schedule & Payment</h5>
                        <span class="badge bg-<?= ($booking['status'] == 'pending') ? 'warning' : (($booking['status'] == 'cancelled') ? 'danger' : 'success') ?> text-uppercase p-2">
                            <?= $booking['status'] ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-4">
                            <div class="col-6 border-end">
                                <label class="text-muted small d-block">PICKUP</label>
                                <span class="fw-bold fs-5"><?= date('M d, Y', strtotime($booking['pickup_date'])) ?></span>
                                <p class="small text-secondary"><?= htmlspecialchars($booking['pickup_location']) ?></p>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small d-block">RETURN</label>
                                <span class="fw-bold fs-5"><?= date('M d, Y', strtotime($booking['return_date'])) ?></span>
                                <p class="small text-secondary"><?= htmlspecialchars($booking['return_location']) ?></p>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Duration:</span>
                                <strong><?= $booking['total_days'] ?> Days</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total Amount:</span>
                                <strong class="text-success fs-4">Rs. <?= number_format($booking['total_amount'], 2) ?></strong>
                            </div>
                        </div>

                        <?php if($booking['status'] === 'pending' && !$cancel_request): ?>
                            <div class="alert alert-info border-0 shadow-sm d-flex justify-content-between align-items-center">
                                <span>New booking request requires action.</span>
                                <div>
                                    <a href="manage-booking.php?action=approve&id=<?= $booking['id'] ?>" class="btn btn-success btn-sm">Approve</a>
                                    <a href="manage-booking.php?action=reject&id=<?= $booking['id'] ?>" class="btn btn-danger btn-sm">Reject</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($cancel_request): ?>
                    <div class="card border-danger shadow-sm mt-4">
                        <div class="card-header bg-danger text-white">Cancellation Request Filed</div>
                        <div class="card-body">
                            <p><strong>Reason:</strong> <?= nl2br(htmlspecialchars($cancel_request['cancel_reason'])) ?></p>
                            <p class="small text-muted mb-1">Requested on: <?= date('M d, Y H:i', strtotime($cancel_request['requested_at'])) ?></p>
                            
                            <?php if ($cancel_request['status'] !== 'pending' && !empty($cancel_request['decided_at'])): ?>
                                <p class="small text-muted">Decided on: <?= date('M d, Y H:i', strtotime($cancel_request['decided_at'])) ?></p>
                            <?php endif; ?>

                            <?php if ($cancel_request['status'] === 'pending'): ?>
                                <hr>
                                <form action="cancellation-requests.php" method="POST">
                                    <input type="hidden" name="cancel_id" value="<?= $cancel_request['id'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Admin Response Note</label>
                                        <textarea name="admin_note" class="form-control" rows="2" placeholder="Provide a reason for the customer..."></textarea>
                                    </div>
                                    <button name="action" value="approved" class="btn btn-danger btn-sm">Confirm & Cancel Booking</button>
                                    <button name="action" value="rejected" class="btn btn-outline-secondary btn-sm">Reject Request</button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-<?= ($cancel_request['status'] == 'approved') ? 'success' : 'secondary' ?> mt-2 mb-0">
                                    <strong>Status:</strong> <?= ucfirst($cancel_request['status']) ?><br>
                                    <strong>Admin Note:</strong> <?= htmlspecialchars($cancel_request['admin_note'] ?? 'None') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>