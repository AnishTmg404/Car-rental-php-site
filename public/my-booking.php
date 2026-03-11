<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/url.php';

require_login();

$page_title = 'My Bookings';
$current_user = get_current_user_data();

// Fetch all bookings with potential cancellation requests
$bookings = db_fetch_all("
    SELECT b.*, c.brand, c.model, c.daily_rate,
           bc.status AS cancel_status
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    LEFT JOIN booking_cancellations bc 
           ON bc.booking_id = b.id AND bc.user_id = ?
    WHERE b.user_id = ? 
    ORDER BY b.pickup_date DESC
", [$current_user['id'], $current_user['id']]);


function booking_status_badge($status) {
    return match ($status) {
        'pending' => 'secondary',
        'approved' => 'primary',
        'active' => 'warning',
        'completed' => 'success',
        'cancelled' => 'danger',
        'rejected' => 'danger',
        default => 'secondary',
    };
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
<link rel="stylesheet" href="<?= asset_url('css/styles.css') ?>">
<link rel="stylesheet" href="<?= asset_url('css/theme.css') ?>">
</head>
<body>

<?php include '../public/navbar.php'; ?>

<main class="py-4">
<div class="container">
    <div class="row g-4">
        <div class="col-lg-3">
            <div class="card shadow-sm mb-4 text-center">
                <div class="card-body">
                    <img src="<?= htmlspecialchars($current_user['profile_image'] ?? 'https://via.placeholder.com/120') ?>" 
                         class="rounded-circle mb-2" width="120" height="120" alt="Profile Image">
                    <h6 class="mb-0"><?= htmlspecialchars(($current_user['first_name'] ?? '') . ' ' . ($current_user['last_name'] ?? '')) ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($current_user['username'] ?? '') ?></small>
                </div>
            </div>
            <div class="list-group">
                <a href="<?= base_url('profile.php') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-person-circle me-2"></i> My Profile
                </a>
                <a href="<?= base_url('my-booking.php') ?>" class="list-group-item list-group-item-action active">
                    <i class="bi bi-card-checklist me-2"></i> My Bookings
                </a>
                <a href="<?= base_url('cars.php') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-car-front-fill me-2"></i> Browse Cars
                </a>
                <a href="<?= base_url('logout.php') ?>" class="list-group-item list-group-item-action text-danger">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">My Bookings</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($bookings)): ?>
                        <p class="text-muted">You have no bookings yet. <a href="<?= base_url('cars.php') ?>">Book a car now</a>.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Car</th>
                                        <th>Pickup</th>
                                        <th>Return</th>
                                        <th>Total Days</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Cancellation</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $i => $b): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($b['brand'] . ' ' . $b['model']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($b['pickup_date'])) ?></td>
                                            <td><?= date('d/m/Y', strtotime($b['return_date'])) ?></td>
                                            <td><?= intval($b['total_days']) ?></td>
                                            <td>Rs.<?= number_format($b['total_amount'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= booking_status_badge($b['status']) ?>">
                                                    <?= ucfirst($b['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($b['cancel_status']): ?>
                                                    <span class="badge bg-<?= $b['cancel_status'] === 'approved' ? 'success' : ($b['cancel_status'] === 'rejected' ? 'danger' : 'info') ?>">
                                                        <?= ucfirst($b['cancel_status']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('booking-details.php?id=' . $b['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
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
