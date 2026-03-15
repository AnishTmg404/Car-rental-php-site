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
    } else {
        $current_booking = db_fetch("SELECT status, car_id FROM bookings WHERE id = ?", [$booking_id]);
        
        if (!$current_booking) {
            $error = "Booking #$booking_id not found.";
        } else {
            $success = false;
            
            // Logic for workflow transitions
            if ($action === 'approve' && $current_booking['status'] === 'pending') {
                $success = db_execute("UPDATE bookings SET status = 'approved' WHERE id = ?", [$booking_id]);
            } 
            elseif ($action === 'reject' && $current_booking['status'] === 'pending') {
                $success = db_execute("UPDATE bookings SET status = 'rejected' WHERE id = ?", [$booking_id]);
            } 
            elseif ($action === 'pickup' && $current_booking['status'] === 'approved') {
                $success = db_execute("UPDATE bookings SET status = 'active' WHERE id = ?", [$booking_id]);
                if ($success) {
                    db_execute("UPDATE cars SET status = 'rented' WHERE id = ?", [$current_booking['car_id']]);
                }
            } 
            elseif ($action === 'return' && $current_booking['status'] === 'active') {
                $success = db_execute("UPDATE bookings SET status = 'completed' WHERE id = ?", [$booking_id]);
                if ($success) {
                    db_execute("UPDATE cars SET status = 'available' WHERE id = ?", [$current_booking['car_id']]);
                }
            }

            if ($success) {
                $message = "Booking #$booking_id successfully updated.";
            } else {
                $error = "Failed to update booking status or action not allowed.";
            }
        }
    }
}

// --- Filter & Search Logic ---
$filter_status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where_clauses = [];
$params = [];

if (!empty($filter_status)) {
    $where_clauses[] = "b.status = ?";
    $params[] = $filter_status;
}

if (!empty($search)) {
    $where_clauses[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

// --- Pagination ---
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['p']) && is_numeric($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $limit;

$total_count_res = db_fetch("SELECT COUNT(*) as count FROM bookings b JOIN users u ON b.user_id = u.id $where_sql", $params);
$total_bookings = $total_count_res['count'] ?? 0;
$total_pages = max(1, ceil($total_bookings / $limit));

$bookings = db_fetch_all("
    SELECT b.*, u.first_name, u.last_name, u.email, c.brand, c.model, c.images
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_id = c.id
    $where_sql
    ORDER BY b.created_at DESC
    LIMIT $limit OFFSET $offset
", $params);

$query_string = "&search=" . urlencode($search) . "&status=" . urlencode($filter_status) . "&limit=" . $limit;
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
        /* Late badge subtle animation */
        .badge-late { background-color: #dc3545; color: white; margin-left: 5px; }
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
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="limit" value="<?= $limit ?>">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Search Customer</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Name or email..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= $filter_status == 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="active" <?= $filter_status == 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="completed" <?= $filter_status == 'completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>

                    <div class="col-md-5 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-4">Filter</button>
                        <a href="manage-booking.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

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
                            // RESTORED ORIGINAL IMAGE LOGIC
                            $images = json_decode($b['images'], true);
                            $raw_path = (is_array($images) && !empty($images[0])) ? $images[0] : '';
                            $img_src = (!empty($raw_path) && filter_var($raw_path, FILTER_VALIDATE_URL)) ? $raw_path : (!empty($raw_path) ? base_url($raw_path) : "https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=200&h=150&fit=crop");

                            $status_class = match($b['status']) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'active' => 'primary',
                                'completed' => 'secondary',
                                default => 'secondary'
                            };

                            // LATE LOGIC
                            $is_late = ($b['status'] === 'active' && strtotime($b['return_date']) < time());
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
                                <div class="small"><?= date('M d, Y', strtotime($b['pickup_date'])) ?></div>
                                <div class="small text-muted">to <?= date('M d, Y', strtotime($b['return_date'])) ?></div>
                            </td>
                            <td class="fw-semibold text-nowrap">Rs. <?= number_format($b['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $status_class ?>"><?= ucfirst($b['status']) ?></span>
                                <?php if($is_late): ?>
                                    <span class="badge badge-late"><i class="bi bi-clock"></i> LATE</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('admin/booking-details.php?id='.$b['id']) ?>" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>
                                    
                                    <?php if($b['status'] === 'pending'): ?>
                                        <a href="?action=approve&id=<?= $b['id'] ?><?= $query_string ?>&p=<?= $page ?>" class="btn btn-outline-success" onclick="return confirm('Approve?');"><i class="bi bi-check"></i></a>
                                        <a href="?action=reject&id=<?= $b['id'] ?><?= $query_string ?>&p=<?= $page ?>" class="btn btn-outline-danger" onclick="return confirm('Reject?');"><i class="bi bi-x"></i></a>
                                    
                                    <?php elseif($b['status'] === 'approved'): ?>
                                        <a href="?action=pickup&id=<?= $b['id'] ?><?= $query_string ?>&p=<?= $page ?>" class="btn btn-success"><i class="bi bi-key"></i> Pickup</a>
                                    
                                    <?php elseif($b['status'] === 'active'): ?>
                                        <a href="?action=return&id=<?= $b['id'] ?><?= $query_string ?>&p=<?= $page ?>" class="btn btn-primary"><i class="bi bi-arrow-return-left"></i> Return</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white border-top-0 py-3">
                <nav><ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?p=<?= $page - 1 ?><?= $query_string ?>">Previous</a></li>
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $page == $i ? 'active' : '' ?>"><a class="page-link" href="?p=<?= $i ?><?= $query_string ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>"><a class="page-link" href="?p=<?= $page + 1 ?><?= $query_string ?>">Next</a></li>
                </ul></nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>