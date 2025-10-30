<?php
require_once '../config/auth.php';
require_once '../public/url.php';

$page_title = 'Our Car Fleet';
$current_user = get_current_user_data();

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$location = $_GET['location'] ?? '';
$pickup_date = $_GET['pickup_date'] ?? '';
$return_date = $_GET['return_date'] ?? '';
$category_filter = $_GET['category'] ?? '';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';
$fuel_type = $_GET['fuel_type'] ?? '';
$transmission = $_GET['transmission'] ?? '';

// Build query conditions
$where_conditions = ["status = 'available'"];
$params = [];

if ($search) {
    $where_conditions[] = "(make LIKE ? OR model LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $where_conditions[] = "category = ?";
    $params[] = $category_filter;
}

if ($fuel_type) {
    $where_conditions[] = "fuel_type = ?";
    $params[] = $fuel_type;
}

if ($transmission) {
    $where_conditions[] = "transmission = ?";
    $params[] = $transmission;
}

if ($price_min) {
    $where_conditions[] = "daily_rate >= ?";
    $params[] = floatval($price_min);
}

if ($price_max) {
    $where_conditions[] = "daily_rate <= ?";
    $params[] = floatval($price_max);
}

$where_clause = implode(' AND ', $where_conditions);

// Get cars with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$cars = db_fetch_all("SELECT * FROM cars WHERE $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset", $params);
$total_cars = db_fetch("SELECT COUNT(*) as count FROM cars WHERE $where_clause", $params)['count'];
$total_pages = ceil($total_cars / $limit);

// Get filter options
$categories = db_fetch_all("SELECT DISTINCT category FROM cars WHERE status = 'available' ORDER BY category");
$fuel_types = db_fetch_all("SELECT DISTINCT fuel_type FROM cars WHERE status = 'available' ORDER BY fuel_type");
$transmissions = db_fetch_all("SELECT DISTINCT transmission FROM cars WHERE status = 'available' ORDER BY transmission");

// Get price range
$price_range = db_fetch("SELECT MIN(daily_rate) as min_price, MAX(daily_rate) as max_price FROM cars WHERE status = 'available'");
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
    <link rel="stylesheet" href="<?= asset_url('css/cars.css') ?>">
</head>
<body>

<?php include '../public/navbar.php'; ?>

<main class="py-4">
    <div class="container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="text-center">
                    <h1 class="display-6 fw-bold mb-3">Our Car Fleet</h1>
                    <p class="lead text-muted">Find the perfect vehicle for your journey</p>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search Cars</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" placeholder="Make, model, or description">
                        </div>
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
                    <div class="col-md-3">
                        <label for="fuel_type" class="form-label">Fuel Type</label>
                        <select class="form-select" id="fuel_type" name="fuel_type">
                            <option value="">All Fuel Types</option>
                            <?php foreach ($fuel_types as $fuel): ?>
                                <option value="<?= $fuel['fuel_type'] ?>" <?= $fuel_type === $fuel['fuel_type'] ? 'selected' : '' ?>>
                                    <?= ucfirst($fuel['fuel_type']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="transmission" class="form-label">Transmission</label>
                        <select class="form-select" id="transmission" name="transmission">
                            <option value="">All Transmissions</option>
                            <?php foreach ($transmissions as $trans): ?>
                                <option value="<?= $trans['transmission'] ?>" <?= $transmission === $trans['transmission'] ? 'selected' : '' ?>>
                                    <?= ucfirst($trans['transmission']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="price_min" class="form-label">Price Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" class="form-control" id="price_min" name="price_min" 
                                       value="<?= htmlspecialchars($price_min) ?>" placeholder="Min" 
                                       min="<?= $price_range['min_price'] ?>" max="<?= $price_range['max_price'] ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control" id="price_max" name="price_max" 
                                       value="<?= htmlspecialchars($price_max) ?>" placeholder="Max" 
                                       min="<?= $price_range['min_price'] ?>" max="<?= $price_range['max_price'] ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="pickup_date" class="form-label">Pickup Date</label>
                        <input type="date" class="form-control" id="pickup_date" name="pickup_date" 
                               value="<?= htmlspecialchars($pickup_date) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="return_date" class="form-label">Return Date</label>
                        <input type="date" class="form-control" id="return_date" name="return_date" 
                               value="<?= htmlspecialchars($return_date) ?>">
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>Search Cars
                            </button>
                            <a href="<?= base_url('cars.php') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-2"></i>Clear Filters
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Header -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="mb-0">Showing <?= $total_cars ?> cars</h5>
                <?php if ($search || $category_filter || $fuel_type || $transmission || $price_min || $price_max): ?>
                    <small class="text-muted">Filtered results</small>
                <?php endif; ?>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary active" onclick="setViewMode('grid')">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setViewMode('list')">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Cars Grid -->
        <div class="row g-4" id="carsGrid">
            <?php foreach ($cars as $car): ?>
                <?php
                $features = json_decode($car['features'], true) ?? [];
                $images = json_decode($car['images'], true) ?? [];
                ?>
                <div class="col-lg-4 col-md-6 car-item">
                    <div class="card h-100 car-card">
                        <div class="car-image-container">
                            <img src="<?= htmlspecialchars($images[0] ?? 'https://via.placeholder.com/400x250') ?>" 
                                 class="card-img-top car-image" alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>">
                            <div class="car-status">
                                <span class="badge bg-success">Available</span>
                            </div>
                            <div class="car-rating">
                                <i class="bi bi-star-fill text-warning"></i>
                                <span class="ms-1">4.8</span>
                            </div>
                            <div class="car-overlay">
                                <a href="<?= base_url('car-details.php?id=' . $car['id']) ?>" class="btn btn-light btn-sm">
                                    <i class="bi bi-eye me-1"></i>Quick View
                                </a>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></h5>
                            <p class="card-text text-muted small">
                                <?= htmlspecialchars($car['year']) ?> • <?= htmlspecialchars($car['fuel_type']) ?> • <?= htmlspecialchars($car['transmission']) ?>
                            </p>
                            <div class="car-features mb-3">
                                <?php foreach (array_slice($features, 0, 3) as $feature): ?>
                                    <span class="badge bg-light text-dark me-1"><?= htmlspecialchars($feature) ?></span>
                                <?php endforeach; ?>
                                <?php if (count($features) > 3): ?>
                                    <span class="badge bg-light text-dark">+<?= count($features) - 3 ?> more</span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="car-price">
                                        <span class="h5 text-primary mb-0">$<?= number_format($car['daily_rate'], 2) ?></span>
                                        <small class="text-muted">/day</small>
                                    </div>
                                    <div class="car-actions">
                                        <a href="<?= base_url('car-details.php?id=' . $car['id']) ?>" class="btn btn-outline-primary btn-sm me-2">View Details</a>
                                        <?php if (is_logged_in()): ?>
                                            <a href="<?= base_url('book-car.php?id=' . $car['id']) ?>" class="btn btn-primary btn-sm">Book Now</a>
                                        <?php else: ?>
                                            <a href="<?= base_url('auth/login.php') ?>" class="btn btn-primary btn-sm">Login to Book</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- No Results -->
        <?php if (empty($cars)): ?>
            <div class="text-center py-5">
                <i class="bi bi-car-front fs-1 text-muted mb-3"></i>
                <h4 class="text-muted">No cars found</h4>
                <p class="text-muted">Try adjusting your search criteria or filters.</p>
                <a href="<?= base_url('cars.php') ?>" class="btn btn-primary">View All Cars</a>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Cars pagination" class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&fuel_type=<?= urlencode($fuel_type) ?>&transmission=<?= urlencode($transmission) ?>&price_min=<?= urlencode($price_min) ?>&price_max=<?= urlencode($price_max) ?>&pickup_date=<?= urlencode($pickup_date) ?>&return_date=<?= urlencode($return_date) ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&fuel_type=<?= urlencode($fuel_type) ?>&transmission=<?= urlencode($transmission) ?>&price_min=<?= urlencode($price_min) ?>&price_max=<?= urlencode($price_max) ?>&pickup_date=<?= urlencode($pickup_date) ?>&return_date=<?= urlencode($return_date) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&fuel_type=<?= urlencode($fuel_type) ?>&transmission=<?= urlencode($transmission) ?>&price_min=<?= urlencode($price_min) ?>&price_max=<?= urlencode($price_max) ?>&pickup_date=<?= urlencode($pickup_date) ?>&return_date=<?= urlencode($return_date) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</main>

<?php include '../public/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset_url('js/main.js') ?>"></script>
<script src="<?= asset_url('js/theme-toggle.js') ?>"></script>

<script>
// Set minimum date to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const pickupDateInput = document.querySelector('input[name="pickup_date"]');
    const returnDateInput = document.querySelector('input[name="return_date"]');
    
    if (pickupDateInput) {
        pickupDateInput.min = today;
        pickupDateInput.addEventListener('change', function() {
            const pickupDate = new Date(this.value);
            pickupDate.setDate(pickupDate.getDate() + 1);
            returnDateInput.min = pickupDate.toISOString().split('T')[0];
        });
    }
    
    if (returnDateInput) {
        returnDateInput.min = today;
    }
});

// View mode toggle
function setViewMode(mode) {
    const grid = document.getElementById('carsGrid');
    const buttons = document.querySelectorAll('.btn-group .btn');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    
    if (mode === 'grid') {
        grid.className = 'row g-4';
        buttons[0].classList.add('active');
    } else {
        grid.className = 'row g-3';
        buttons[1].classList.add('active');
        
        // Convert cards to list view
        const cards = grid.querySelectorAll('.car-item');
        cards.forEach(card => {
            card.className = 'col-12 car-item';
        });
    }
}

// Price range slider (if needed)
const priceMin = document.getElementById('price_min');
const priceMax = document.getElementById('price_max');

if (priceMin && priceMax) {
    priceMin.addEventListener('input', function() {
        if (parseFloat(this.value) > parseFloat(priceMax.value)) {
            priceMax.value = this.value;
        }
    });
    
    priceMax.addEventListener('input', function() {
        if (parseFloat(this.value) < parseFloat(priceMin.value)) {
            priceMin.value = this.value;
        }
    });
}
</script>

</body>
</html>
