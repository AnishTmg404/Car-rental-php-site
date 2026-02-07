<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/url.php';

// Require user login
require_login();

// Redirect admin to admin dashboard
if (is_admin()) {
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

$page_title = 'User Dashboard';
$current_user = get_current_user_data();

// Get user statistics
$total_bookings = db_fetch("SELECT COUNT(*) as count FROM bookings WHERE user_id = ?", [$current_user['id']])['count'];
$active_bookings = db_fetch("SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND status IN ('pending', 'approved', 'active')", [$current_user['id']])['count'];
$completed_bookings = db_fetch("SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND status = 'completed'", [$current_user['id']])['count'];
$total_spent = db_fetch("SELECT SUM(total_amount) as total FROM bookings WHERE user_id = ? AND status IN ('approved', 'active', 'completed')", [$current_user['id']])['total'] ?? 0;

// Get recent bookings
$recent_bookings = db_fetch_all("
    SELECT b.*, c.make, c.model, c.images, c.daily_rate 
    FROM bookings b 
    JOIN cars c ON b.car_id = c.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC 
    LIMIT 5
", [$current_user['id']]);

// Get favorite cars (most booked by user)
$favorite_cars = db_fetch_all("
    SELECT c.*, COUNT(b.id) as booking_count
    FROM cars c
    JOIN bookings b ON c.id = b.car_id
    WHERE b.user_id = ? AND b.status IN ('completed', 'approved', 'active')
    GROUP BY c.id
    ORDER BY booking_count DESC
    LIMIT 3
", [$current_user['id']]);
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
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1">Welcome back, <?= htmlspecialchars($current_user['first_name']) ?>!</h1>
                        <p class="text-muted mb-0">Manage your bookings and explore our fleet</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('cars.php') ?>" class="btn btn-primary">
                            <i class="bi bi-car-front me-2"></i>Browse Cars
                        </a>
                        <a href="<?= base_url('profile.php') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-person me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-calendar-check fs-2"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-white-50 mb-1">Total Bookings</h6>
                                <h3 class="card-text text-white mb-0"><?= $total_bookings ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card h-100" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-clock fs-2"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-white-50 mb-1">Active Bookings</h6>
                                <h3 class="card-text text-white mb-0"><?= $active_bookings ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card h-100" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-check-circle fs-2"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-white-50 mb-1">Completed</h6>
                                <h3 class="card-text text-white mb-0"><?= $completed_bookings ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card h-100" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-currency-dollar fs-2"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-white-50 mb-1">Total Spent</h6>
                                <h3 class="card-text text-white mb-0">Rs.<?= number_format($total_spent, 2) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Bookings -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Bookings</h5>
                        <a href="<?= base_url('my-booking.php') ?>" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recent_bookings)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-calendar-x fs-1 text-muted mb-3"></i>
                                <h5 class="text-muted">No bookings yet</h5>
                                <p class="text-muted">Start exploring our car fleet and make your first booking!</p>
                                <a href="<?= base_url('cars.php') ?>" class="btn btn-primary">Browse Cars</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Car</th>
                                            <th>Dates</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_bookings as $booking): ?>
                                            <?php
                                            $images = json_decode($booking['images'], true) ?? [];
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?= htmlspecialchars($images[0] ?? 'https://via.placeholder.com/60x40') ?>" 
                                                             alt="<?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?>"
                                                             class="img-thumbnail me-3" style="width: 60px; height: 40px; object-fit: cover;">
                                                        <div>
                                                            <div class="fw-semibold"><?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?></div>
                                                            <small class="text-muted">Booking #<?= $booking['id'] ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="small"><?= format_date($booking['pickup_date']) ?></div>
                                                        <div class="small text-muted">to <?= format_date($booking['return_date']) ?></div>
                                                    </div>
                                                </td>
                                                <td class="fw-semibold">Rs.<?= number_format($booking['total_amount'], 2) ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = match($booking['status']) {
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'active' => 'primary',
                                                        'completed' => 'secondary',
                                                        'cancelled' => 'dark',
                                                        default => 'secondary'
                                                    };
                                                    ?>
                                                    <span class="badge bg-<?= $status_class ?>"><?= ucfirst($booking['status']) ?></span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?= base_url('booking-details.php?id=' . $booking['id']) ?>" class="btn btn-outline-primary">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <?php if ($booking['status'] === 'pending'): ?>
                                                            <a href="<?= base_url('cancel-booking.php?id=' . $booking['id']) ?>" class="btn btn-outline-danger">
                                                                <i class="bi bi-x"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
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

            <!-- Quick Actions & Favorites -->
            <div class="col-xl-4">
                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?= base_url('cars.php') ?>" class="btn btn-primary">
                                <i class="bi bi-car-front me-2"></i>Browse Cars
                            </a>
                            <a href="<?= base_url('my-booking.php') ?>" class="btn btn-outline-primary">
                                <i class="bi bi-calendar-check me-2"></i>My Bookings
                            </a>
                            <a href="<?= base_url('profile.php') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-person me-2"></i>Edit Profile
                            </a>
                            <a href="<?= base_url('auth/logout.php') ?>" class="btn btn-outline-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Favorite Cars -->
                <?php if (!empty($favorite_cars)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Your Favorite Cars</h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($favorite_cars as $car): ?>
                                <?php
                                $images = json_decode($car['images'], true) ?? [];
                                ?>
                                <div class="d-flex align-items-center mb-3">
                                    <img src="<?= htmlspecialchars($images[0] ?? 'https://via.placeholder.com/50x35') ?>" 
                                         alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>"
                                         class="img-thumbnail me-3" style="width: 50px; height: 35px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small"><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></div>
                                        <div class="text-muted small">Booked <?= $car['booking_count'] ?> time<?= $car['booking_count'] > 1 ? 's' : '' ?></div>
                                    </div>
                                    <a href="<?= base_url('car-details.php?id=' . $car['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Account Info -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Account Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Member since</small>
                            <div class="fw-semibold"><?= format_date($current_user['created_at']) ?></div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Email</small>
                            <div class="fw-semibold"><?= htmlspecialchars($current_user['email']) ?></div>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted">Phone</small>
                            <div class="fw-semibold"><?= htmlspecialchars($current_user['phone'] ?: 'Not provided') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../public/footer.php'; ?>

<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset_url('js/main.js') ?>"></script>
<script src="<?= asset_url('js/theme-toggle.js') ?>"></script> -->

</body>
</html>
