<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Require admin access
require_admin();

$page_title = 'Manage Cars';
$current_user = get_current_user_data();

function car_image_url($image)
{
    if (empty($image)) {
        return 'https://via.placeholder.com/80x60';
    }

    // If already a full URL (Unsplash etc.)
    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    // Normalize slashes
    $image = ltrim($image, '/');

    // If path already contains uploads
    if (str_contains($image, 'uploads/')) {
        return base_url($image);
    }

    // Default uploaded car image location
    return base_url('assets/uploads/cars/' . $image);
}



// Handle car actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $car_id = $_POST['car_id'] ?? '';
    
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Invalid request. Please try again.');
    } elseif ($action === 'delete' && $car_id) {
        // Check if car has active bookings
        $active_bookings = db_fetch("SELECT COUNT(*) as count FROM bookings WHERE car_id = ? AND status IN ('pending', 'approved', 'active')", [$car_id]);
        
        if ($active_bookings['count'] > 0) {
            set_flash_message('error', 'Cannot delete car with active bookings.');
        } else {
            $deleted = db_delete('cars', 'id = ?', [$car_id]);
            if ($deleted) {
                log_admin_action('delete_car', 'cars', $car_id);
                set_flash_message('success', 'Car deleted successfully.');
            } else {
                set_flash_message('error', 'Failed to delete car.');
            }
        }
    }elseif ($action === 'toggle_status' && $car_id) {
    $car = db_fetch("SELECT * FROM cars WHERE id = ?", [$car_id]);
    if ($car) {
        $new_status = $car['status'] === 'available' ? 'unavailable' : 'available';
        // Always run the update
        $updated = db_query("UPDATE cars SET status = ? WHERE id = ?", [$new_status, $car_id]);
        if ($updated !== false) { // db_query returns true even if 0 rows affected
            log_admin_action('update_car_status', 'cars', $car_id, ['status' => $car['status']], ['status' => $new_status]);
            set_flash_message('success', 'Car status updated successfully.');
        } else {
            set_flash_message('error', 'Failed to update car status.');
        }
    }
}


    
    header('Location: ' . base_url('admin/manage-cars.php'));
    exit;
}

// Get cars with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(brand LIKE ? OR model LIKE ? OR license_plate LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($category_filter) {
    $where_conditions[] = "category = ?";
    $params[] = $category_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$cars = db_fetch_all("SELECT * FROM cars $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset", $params);
$total_cars = db_fetch("SELECT COUNT(*) as count FROM cars $where_clause", $params)['count'];
$total_pages = ceil($total_cars / $limit);

// Get car categories and statuses for filters
$categories = db_fetch_all("SELECT DISTINCT category FROM cars ORDER BY category");
$statuses = db_fetch_all("SELECT DISTINCT status FROM cars ORDER BY status");
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

<?php include '../../public/navbar.php'; ?>

<main class="py-4">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1">Manage Cars</h1>
                        <p class="text-muted mb-0">Add, edit, and manage your car fleet</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (has_flash_message('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?= htmlspecialchars(get_flash_message('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (has_flash_message('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars(get_flash_message('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Cars</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= htmlspecialchars($search) ?>" placeholder="brand, model, or license plate">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= $status['status'] ?>" <?= $status_filter === $status['status'] ? 'selected' : '' ?>>
                                    <?= ucfirst($status['status']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['category'] ?>" <?= $category_filter === $category['category'] ? 'selected' : '' ?>>
                                    <?= ucfirst($category['category']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cars Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Cars (<?= $total_cars ?> total)</h5>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('admin/add-car.php') ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus me-1"></i>Add Car
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Car Details</th>
                                <th>Specifications</th>
                                <th>Rate</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cars as $car): ?>
                                <?php
                                $features = json_decode($car['features'], true) ?? [];
                                $images = json_decode($car['images'], true) ?? [];
                                ?>
                                <tr>
                                    <td>
                                        <div class="car-thumbnail">
                                            <img src="<?= htmlspecialchars(car_image_url($images[0] ?? null)) ?>" 
                                                alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>"
                                                class="img-thumbnail"
                                                style="width: 80px; height: 60px; object-fit: cover;">
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($car['year']) ?> • <?= htmlspecialchars($car['color']) ?></small>
                                            <div class="small text-muted">License: <?= htmlspecialchars($car['license_plate']) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><?= htmlspecialchars($car['seats']) ?> seats • <?= htmlspecialchars($car['doors']) ?> doors</div>
                                            <div><?= htmlspecialchars($car['fuel_type']) ?> • <?= htmlspecialchars($car['transmission']) ?></div>
                                            <div class="badge bg-secondary"><?= htmlspecialchars($car['category']) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary">Rs.<?= number_format($car['daily_rate'], 2) ?></div>
                                        <small class="text-muted">per day</small>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = match($car['status']) {
                                            'available' => 'success',
                                            'rented' => 'primary',
                                            'maintenance' => 'warning',
                                            'unavailable' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $status_class ?>"><?= ucfirst($car['status']) ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('admin/edit-car.php?id=' . $car['id']) ?>" 
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= base_url('car-details.php?id=' . $car['id']) ?>" 
                                               class="btn btn-outline-info" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-<?= $car['status'] === 'available' ? 'warning' : 'success' ?>" 
                                                    onclick="toggleCarStatus(<?= $car['id'] ?>, '<?= $car['status'] ?>')" 
                                                    title="<?= $car['status'] === 'available' ? 'Mark Unavailable' : 'Mark Available' ?>">
                                                <i class="bi bi-<?= $car['status'] === 'available' ? 'pause' : 'play' ?>"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteCar(<?= $car['id'] ?>, '<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>')" 
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Cars pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&category=<?= urlencode($category_filter) ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&category=<?= urlencode($category_filter) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&category=<?= urlencode($category_filter) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this car?</p>
                <p class="fw-semibold" id="carName"></p>
                <p class="text-danger small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="car_id" id="deleteCarId">
                    <button type="submit" class="btn btn-danger">Delete Car</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Status Toggle Form -->
<form method="POST" id="statusForm" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <input type="hidden" name="action" value="toggle_status">
    <input type="hidden" name="car_id" id="statusCarId">
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function deleteCar(carId, carName) {
    document.getElementById('carName').textContent = carName;
    document.getElementById('deleteCarId').value = carId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function toggleCarStatus(carId, currentStatus) {
    if (confirm(`Are you sure you want to ${currentStatus === 'available' ? 'mark as unavailable' : 'mark as available'}?`)) {
        document.getElementById('statusCarId').value = carId;
        document.getElementById('statusForm').submit();
    }
}

// Auto-dismiss alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

</body>
</html>
