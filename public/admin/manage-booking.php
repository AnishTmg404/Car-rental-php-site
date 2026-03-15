<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Require admin access
require_admin();

$page_title = 'Manage Bookings';
$current_user = get_current_user_data();

// --- Handle booking status updates ---
$message = $error = null;
if (isset($_GET['action'], $_GET['id'])) {
    $booking_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $action = $_GET['action'];

    if (!$booking_id) {
        $error = "Invalid Booking ID.";
    } elseif (in_array($action, ['approve', 'reject'])) {
        $current_booking = db_fetch("SELECT status, car_id FROM bookings WHERE id = ?", [$booking_id]);
        
        if (!$current_booking) {
            $error = "Booking #$booking_id not found.";
        } elseif ($current_booking['status'] !== 'pending') {
            $error = "This booking is already " . $current_booking['status'] . ".";
        } else {
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $updated = db_execute("UPDATE bookings SET status = ? WHERE id = ?", [$status, $booking_id]);

            if ($updated) {
                if ($status === 'approved') {
                    db_execute("UPDATE cars SET status = 'rented' WHERE id = ?", [$current_booking['car_id']]);
                }
                $message = "Booking #$booking_id successfully $status.";
            } else {
                $error = "Failed to update booking status for #$booking_id.";
            }
        }
    }
}

// --- Pagination & View Limit Logic ---
// 1. Get the limit from URL, default to 10
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 10;
// 2. Get current page
$page = isset($_GET['p']) && is_numeric($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $limit;

// Fetch total count for pagination calculation
$total_bookings = db_fetch("SELECT COUNT(*) as count FROM bookings")['count'] ?? 0;
$total_pages = max(1, ceil($total_bookings / $limit));

// Fetch paginated bookings
$bookings = db_fetch_all("
    SELECT b.*, u.first_name, u.last_name, u.email, c.brand, c.model, c.images
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_id = c.id
    ORDER BY b.created_at DESC
    LIMIT $limit OFFSET $offset
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset_url('css/styles.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/theme.css') ?>">
    <style>
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        .car-img { width: 60px; height: 40px; object-fit: cover; border-radius: 0.25rem; flex-shrink: 0; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<main class="py-4">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Manage Bookings</h1>
                <p class="text-muted small mb-0">Showing <?= count($bookings) ?> of <?= $total_bookings ?> total bookings</p>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <form method="GET" class="d-flex align-items-center gap-2">
                    <label class="small text-muted text-nowrap">View:</label>
                    <select name="limit" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                        <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                        <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </form>
                <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-house-door me-1"></i>Dashboard
                </a>
            </div>
        </div>

        <?php if($message): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Car</th>
                                <th>Customer</th>
                                <th>Dates</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($bookings as $b): 
                            $images = json_decode($b['images'], true);
                            $raw_path = (is_array($images) && !empty($images[0])) ? $images[0] : '';
                            $img_src = (!empty($raw_path) && filter_var($raw_path, FILTER_VALIDATE_URL)) ? $raw_path : (!empty($raw_path) ? base_url($raw_path) : "https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=200&h=150&fit=crop");

                            $status_class = match($b['status']) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'active' => 'primary',
                                'completed' => 'secondary',
                                'cancelled' => 'dark',
                                default => 'secondary'
                            };
                        ?>
                        <tr>
                            <td class="text-muted">#<?= $b['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center" style="gap: 12px;">
                                    <img src="<?= htmlspecialchars($img_src) ?>" alt="Car" class="car-img">
                                    <span class="text-nowrap fw-medium"><?= htmlspecialchars($b['brand'].' '.$b['model']) ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($b['first_name'].' '.$b['last_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($b['email']) ?></small>
                            </td>
                            <td>
                                <div class="small"><?= format_date($b['pickup_date']) ?></div>
                                <div class="small text-muted">to <?= format_date($b['return_date']) ?></div>
                            </td>
                            <td class="fw-semibold text-nowrap">Rs. <?= number_format($b['total_amount'], 2) ?></td>
                            <td><span class="badge bg-<?= $status_class ?>"><?= ucfirst($b['status']) ?></span></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('admin/booking-details.php?id='.$b['id']) ?>" class="btn btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if($b['status'] === 'pending'): ?>
                                        <a href="<?= base_url('admin/manage-booking.php?action=approve&id='.$b['id'].'&limit='.$limit.'&p='.$page) ?>" 
                                           class="btn btn-outline-success" 
                                           onclick="return confirm('Approve booking #<?= $b['id'] ?>?');">
                                            <i class="bi bi-check"></i>
                                        </a>
                                        <a href="<?= base_url('admin/manage-booking.php?action=reject&id='.$b['id'].'&limit='.$limit.'&p='.$page) ?>" 
                                           class="btn btn-outline-danger" 
                                           onclick="return confirm('Reject booking #<?= $b['id'] ?>?');">
                                            <i class="bi bi-x"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($bookings)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">No bookings available.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white border-top-0 py-3">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?limit=<?= $limit ?>&p=<?= $page - 1 ?>">Previous</a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                <a class="page-link" href="?limit=<?= $limit ?>&p=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?limit=<?= $limit ?>&p=<?= $page + 1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>