<?php 
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Require admin access
require_admin();

$page_title = 'Admin Dashboard';
$current_user = get_current_user_data();

// Handle booking status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? '';
    $new_status = $_POST['new_status'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('error', 'Invalid request. Please try again.');
    } elseif ($booking_id && in_array($new_status, ['approved', 'rejected'])) {
        $booking = db_fetch("SELECT * FROM bookings WHERE id = ?", [$booking_id]);
        if ($booking) {
            $updated = db_update('bookings', ['status' => $new_status], 'id = ?', [$booking_id]);
            if ($updated) {
                log_admin_action('update_booking_status', 'bookings', $booking_id, ['status' => $booking['status']], ['status' => $new_status]);
                set_flash_message('success', "Booking #$booking_id status updated to $new_status.");
            } else {
                set_flash_message('error', 'Failed to update booking status.');
            }
        } else {
            set_flash_message('error', 'Booking not found.');
        }
    }
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

// Dashboard statistics
$total_cars = db_fetch("SELECT COUNT(*) as count FROM cars")['count'];
$total_users = db_fetch("SELECT COUNT(*) as count FROM users WHERE role = 'user'")['count'];
$total_bookings = db_fetch("SELECT COUNT(*) as count FROM bookings")['count'];
$pending_bookings = db_fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")['count'];

// Revenue data
$total_revenue = db_fetch("SELECT SUM(total_amount) as total FROM bookings WHERE status IN ('approved','active','completed')")['total'] ?? 0;
$monthly_revenue = db_fetch("SELECT SUM(total_amount) as total FROM bookings WHERE status IN ('approved','active','completed') AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")['total'] ?? 0;

// Recent bookings
$recent_bookings = db_fetch_all("
    SELECT b.*, u.first_name, u.last_name, u.email, c.brand, c.model, c.images 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN cars c ON b.car_id = c.id 
    ORDER BY b.created_at DESC 
    LIMIT 10
");

// Car status overview
// --- Fetching Car Status Counts ---
$car_status_counts = db_fetch_all("SELECT status, COUNT(*) as count FROM cars GROUP BY status");

// Helper function to pick colors for the badges
function getStatusColor($status) {
    return match($status) {
        'available'   => 'success',
        'rented'      => 'primary',
        'maintenance' => 'warning text-dark',
        'unavailable' => 'danger',
        default       => 'secondary'
    };
}

// Pending cancellation requests
$pending_cancels = db_fetch("SELECT COUNT(*) as count FROM booking_cancellations WHERE status='pending'")['count'] ?? 0;

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
<style>
.stats-card { background: linear-gradient(135deg, #0d6efd, #6610f2); color: #fff; }
.actions-btn-group .btn { margin-right: -1px; }
.actions-btn-group .btn:last-child { margin-right: 0; }
.table-img { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; }
</style>
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<main class="py-4">
<div class="container-fluid">

<?php if (has_flash_message('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars(get_flash_message('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (has_flash_message('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= htmlspecialchars(get_flash_message('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Header -->
<div class="row mb-4">
<div class="col-12 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-1">Admin Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($current_user['first_name']) ?>!</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/add-car.php') ?>" class="btn btn-success"><i class="bi bi-plus-lg me-2"></i>Add Car</a>
        <a href="<?= base_url('admin/manage-cars.php') ?>" class="btn btn-outline-primary"><i class="bi bi-car-front me-2"></i>Manage Cars</a>
        <a href="<?= base_url('admin/manage-booking.php') ?>" class="btn btn-primary"><i class="bi bi-calendar-check me-2"></i>Manage Bookings</a>
    </div>
</div>
</div>

<!-- Statistics cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-car-front fs-2"></i>
                <div class="ms-3">
                    <h6 class="card-title text-white-50 mb-1">Total Cars</h6>
                    <h3 class="card-text text-white mb-0"><?= $total_cars ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card h-100" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-people fs-2"></i>
                <div class="ms-3">
                    <h6 class="card-title text-white-50 mb-1">Total Users</h6>
                    <h3 class="card-text text-white mb-0"><?= $total_users ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card h-100" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-calendar-check fs-2"></i>
                <div class="ms-3">
                    <h6 class="card-title text-white-50 mb-1">Total Bookings</h6>
                    <h3 class="card-text text-white mb-0"><?= $total_bookings ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card h-100" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-clock fs-2"></i>
                <div class="ms-3">
                    <h6 class="card-title text-white-50 mb-1">Pending Bookings</h6>
                    <h3 class="card-text text-white mb-0"><?= $pending_bookings ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-6 col-md-6">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-currency-dollar fs-2 text-success"></i>
                <div class="ms-3">
                    <h6 class="card-title text-muted mb-1">Total Revenue</h6>
                    <h3 class="card-text text-success mb-0">Rs.<?= number_format($total_revenue,2) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-md-6">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-graph-up fs-2 text-primary"></i>
                <div class="ms-3">
                    <h6 class="card-title text-muted mb-1">This Month's Revenue</h6>
                    <h3 class="card-text text-primary mb-0">Rs.<?= number_format($monthly_revenue,2) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Bookings Table -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Bookings</h5>
                <a href="<?= base_url('admin/manage-booking.php') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                        <tr>
                            <th>Car</th>
                            <th>Customer</th>
                            <th>Dates</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $booking): 
                                // 1. Decode the JSON string
                                $car_images = json_decode($booking['images'], true);
                                
                                // 2. Get the first image path/URL
                                $raw_path = (is_array($car_images) && !empty($car_images[0])) ? $car_images[0] : '';

                                if (!empty($raw_path)) {
                                    // 3. Check if it's an external URL or local path
                                    if (filter_var($raw_path, FILTER_VALIDATE_URL)) {
                                        $car_image = $raw_path;
                                    } else {
                                        $car_image = base_url($raw_path);
                                    }
                                } else {
                                    // 4. THE FIX: Use a professional car thumbnail from the web instead of a placeholder
                                    $car_image = "https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=200&h=150&fit=crop";
                                }
                            ?>
                                <tr>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center" style="gap: 12px;">
                                            <img src="<?= htmlspecialchars($car_image) ?>" alt="Car" class="table-img" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px; flex-shrink: 0;">
                                            <span class="text-nowrap"><?= htmlspecialchars($booking['brand'].' '.$booking['model']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($booking['first_name'].' '.$booking['last_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($booking['email']) ?></small>
                                    </td>
                                    <td>
                                        <div class="small"><?= format_date($booking['pickup_date']) ?> → <?= format_date($booking['return_date']) ?></div>
                                    </td>
                                    <td class="fw-semibold">Rs.<?= number_format($booking['total_amount'],2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= match($booking['status']) {
                                            'pending'=>'warning',
                                            'approved'=>'success',
                                            'rejected'=>'danger',
                                            'active'=>'primary',
                                            'completed'=>'secondary',
                                            'cancelled'=>'dark',
                                            default=>'secondary'
                                        } ?>"><?= ucfirst($booking['status']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= base_url('admin/booking-details.php?id=' . $booking['id']) ?>" class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <a href="<?= base_url('admin/approve-booking.php?id=' . $booking['id']) ?>" class="btn btn-outline-success">
                                                    <i class="bi bi-check"></i>
                                                </a>
                                                <a href="<?= base_url('admin/reject-booking.php?id=' . $booking['id']) ?>" class="btn btn-outline-danger">
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

    <!-- Quick Links & Admin Cancellations -->
    <div class="col-xl-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="card text-white bg-danger">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50">Pending Cancellation Requests</h6>
                            <h3 class="card-text text-white mb-0"><?= $pending_cancels ?></h3>
                        </div>
                        <i class="bi bi-x-circle fs-1"></i>
                    </div>
                    <a href="<?= base_url('admin/admin-cancellations.php') ?>" class="card-footer text-white text-decoration-none">
                        View All <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-12">
                <div class="d-grid gap-2">
                    <a href="<?= base_url('admin/manage-cars.php') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-car-front me-2"></i>Manage Cars</a>
                    <a href="<?= base_url('admin/manage-users.php') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-people me-2"></i>Manage Users</a>
                    <a href="<?= base_url('admin/reports.php') ?>" class="btn btn-outline-info btn-sm"><i class="bi bi-graph-up me-2"></i>Reports</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Car status overview -->
<div class="row g-4 mt-4">
    <div class="col-xl-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0 fw-bold">Car Status Overview</h5>
            </div>
            <div class="card-body d-flex flex-wrap gap-3">
                <?php 
                $total = array_sum(array_column($car_status_counts, 'count')); 
                ?>
                <div class="badge bg-dark fs-6 p-3">Total Fleet: <?= $total ?></div>

                <?php foreach ($car_status_counts as $status): ?>
                    <div class="badge bg-<?= getStatusColor($status['status']) ?> fs-6 p-3">
                        <?= ucfirst($status['status']) ?>: <?= $status['count'] ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
