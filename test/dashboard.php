<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Require admin access
require_admin();

$page_title = 'Admin Dashboard';
$current_user = get_current_user_data();

// Get dashboard statistics
$total_cars = db_fetch("SELECT COUNT(*) as count FROM cars")['count'];
$total_users = db_fetch("SELECT COUNT(*) as count FROM users WHERE role = 'user'")['count'];
$total_bookings = db_fetch("SELECT COUNT(*) as count FROM bookings")['count'];
$pending_bookings = db_fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")['count'];

// Get revenue data
$total_revenue = db_fetch("SELECT SUM(total_amount) as total FROM bookings WHERE status IN ('approved', 'active', 'completed')")['total'] ?? 0;
$monthly_revenue = db_fetch("SELECT SUM(total_amount) as total FROM bookings WHERE status IN ('approved', 'active', 'completed') AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")['total'] ?? 0;

// Get recent bookings
$recent_bookings = db_fetch_all("
    SELECT b.*, u.first_name, u.last_name, u.email, c.make, c.model 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN cars c ON b.car_id = c.id 
    ORDER BY b.created_at DESC 
    LIMIT 10
");

// Get car status counts
$car_status_counts = db_fetch_all("SELECT status, COUNT(*) as count FROM cars GROUP BY status");
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

<?php include __DIR__ . '/../navbar.php'; ?>

<main class="py-4">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1">Admin Dashboard</h1>
                        <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($current_user['first_name']) ?>!</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('admin/manage-cars.php') ?>" class="btn btn-outline-primary">
                            <i class="bi bi-car-front me-2"></i>Manage Cars
                        </a>
                        <a href="<?= base_url('admin/manage-bookings.php') ?>" class="btn btn-primary">
                            <i class="bi bi-calendar-check me-2"></i>Manage Bookings
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
                                <i class="bi bi-car-front fs-2"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-white-50 mb-1">Total Cars</h6>
                                <h3 class="card-text text-white mb-0"><?= $total_cars ?></h3>
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
                                <i class="bi bi-people fs-2"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-white-50 mb-1">Total Users</h6>
                                <h3 class="card-text text-white mb-0"><?= $total_users ?></h3>
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
                <div class="card stats-card h-100" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-clock fs-2"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-white-50 mb-1">Pending Bookings</h6>
                                <h3 class="card-text text-white mb-0"><?= $pending_bookings ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-6 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-currency-dollar fs-2 text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-muted mb-1">Total Revenue</h6>
                                <h3 class="card-text text-success mb-0">$<?= number_format($total_revenue, 2) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-graph-up fs-2 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title text-muted mb-1">This Month's Revenue</h6>
                                <h3 class="card-text text-primary mb-0">$<?= number_format($monthly_revenue, 2) ?></h3>
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
                        <a href="<?= base_url('admin/manage-bookings.php') ?>" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Car</th>
                                        <th>Dates</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <div class="fw-semibold"><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($booking['email']) ?></small>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?></td>
                                        <td>
                                            <div>
                                                <div class="small"><?= format_date($booking['pickup_date']) ?></div>
                                                <div class="small text-muted">to <?= format_date($booking['return_date']) ?></div>
                                            </div>
                                        </td>
                                        <td class="fw-semibold">$<?= number_format($booking['total_amount'], 2) ?></td>
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
                                                <a href="<?= base_url('admin/booking-details.php?id=' . $booking['id']) ?>" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if ($booking['status'] === 'pending'): ?>
                                                    <a href="<?= base_url('admin/approve-booking.php?id=' . $booking['id']) ?>" class="btn btn-outline-success btn-sm">
                                                        <i class="bi bi-check"></i>
                                                    </a>
                                                    <a href="<?= base_url('admin/reject-booking.php?id=' . $booking['id']) ?>" class="btn btn-outline-danger btn-sm">
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
                    </div>
                </div>
            </div>

            <!-- Car Status Overview -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Car Status Overview</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($car_status_counts as $status): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="fw-semibold"><?= ucfirst($status['status']) ?></span>
                            </div>
                            <div>
                                <span class="badge bg-primary"><?= $status['count'] ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <a href="<?= base_url('admin/manage-cars.php') ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-car-front me-2"></i>Manage Cars
                            </a>
                            <a href="<?= base_url('admin/manage-users.php') ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-people me-2"></i>Manage Users
                            </a>
                            <a href="<?= base_url('admin/reports.php') ?>" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-graph-up me-2"></i>View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../footer.php'; ?>


</body>
</html>
