<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/url.php';

$page_title = 'Car Details';
$current_user = get_current_user_data();

// Get car ID from URL
$car_id = intval($_GET['id'] ?? 0);
if (!$car_id) {
    header('Location: ' . base_url('cars.php'));
    exit;
}

// Get car details
$car = db_fetch("SELECT * FROM cars WHERE id = ?", [$car_id]);
if (!$car) {
    set_flash_message('error', 'Car not found.');
    header('Location: ' . base_url('cars.php'));
    exit;
}

$car_images = json_decode($car['images'], true) ?? [];
$car_features = json_decode($car['features'], true) ?? [];

// Get similar cars (same category)
$similar_cars = db_fetch_all("
    SELECT * FROM cars 
    WHERE category = ? AND id != ? AND status = 'available' 
    ORDER BY created_at DESC 
    LIMIT 4
", [$car['category'], $car_id]);

// Get car reviews
$reviews = db_fetch_all("
    SELECT r.*, u.first_name, u.last_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.car_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 5
", [$car_id]);

$average_rating = db_fetch("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE car_id = ?", [$car_id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?> - Car Rental</title>
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
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('cars.php') ?>">Cars</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div id="carImageCarousel" class="carousel slide" data-bs-ride="carousel">
                        <?php if (count($car_images) > 1): ?>
                            <div class="carousel-indicators">
                                <?php foreach ($car_images as $i => $img): ?>
                                    <button type="button" data-bs-target="#carImageCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>"></button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="carousel-inner rounded-top">
                            <?php if (!empty($car_images)): ?>
                                <?php foreach ($car_images as $index => $image): ?>
                                    <?php $img_src = filter_var($image, FILTER_VALIDATE_URL) ? $image : base_url($image); ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= htmlspecialchars($img_src) ?>" class="d-block w-100" style="height: 480px; object-fit: cover;" alt="Car Image">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="carousel-item active">
                                    <img src="https://via.placeholder.com/800x500" class="d-block w-100" alt="No image available">
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (count($car_images) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carImageCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carImageCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mt-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Car Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h4>
                                <p class="text-muted mb-3"><?= htmlspecialchars($car['year']) ?> • <?= htmlspecialchars($car['color']) ?></p>
                                
                                <?php if ($car['description']): ?>
                                    <p class="mb-3"><?= htmlspecialchars($car['description']) ?></p>
                                <?php endif; ?>
                                
                                <div class="car-features mb-4">
                                    <h6 class="mb-2">Features:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php 
                                        $features_data = json_decode($car['features'], true);
                                        $final_list = [];
                                        if (is_array($features_data)) {
                                            foreach ($features_data as $item) {
                                                if (strpos($item, ',') !== false) {
                                                    $split_items = explode(',', $item);
                                                    foreach ($split_items as $s) $final_list[] = trim($s);
                                                } else {
                                                    $final_list[] = trim($item);
                                                }
                                            }
                                        }
                                        foreach (array_unique($final_list) as $feature): 
                                            if (empty($feature)) continue;
                                        ?>
                                            <span class="feature-pill"><?= htmlspecialchars($feature) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="car-specs">
                                    <div class="spec-item"><span class="spec-label">Fuel Type:</span> <span class="spec-value"><?= ucfirst($car['fuel_type']) ?></span></div>
                                    <div class="spec-item"><span class="spec-label">Transmission:</span> <span class="spec-value"><?= ucfirst($car['transmission']) ?></span></div>
                                    <div class="spec-item"><span class="spec-label">Seats:</span> <span class="spec-value"><?= $car['seats'] ?></span></div>
                                    <div class="spec-item"><span class="spec-label">Doors:</span> <span class="spec-value"><?= $car['doors'] ?></span></div>
                                    <div class="spec-item"><span class="spec-label">Category:</span> <span class="spec-value"><?= ucfirst($car['category']) ?></span></div>
                                    <div class="spec-item"><span class="spec-label">Mileage:</span> <span class="spec-value"><?= number_format($car['mileage']) ?> km</span></div>
                                    <div class="spec-item"><span class="spec-label">License Plate:</span> <span class="spec-value"><?= htmlspecialchars($car['license_plate']) ?></span></div>
                                    <div class="spec-item">
                                        <span class="spec-label">Status:</span>
                                        <?php $status_class = match($car['status']) {'available'=>'success','rented'=>'primary','maintenance'=>'warning','unavailable'=>'danger',default=>'secondary'}; ?>
                                        <span class="badge bg-<?= $status_class ?>"><?= ucfirst($car['status']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($reviews)): ?>
                    <div class="card mt-4 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Customer Reviews</h5>
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star-fill <?= $i <= round($average_rating['avg_rating']) ? 'text-warning' : 'text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="fw-semibold"><?= number_format($average_rating['avg_rating'], 1) ?></span>
                                <small class="text-muted ms-1">(<?= $average_rating['total_reviews'] ?> reviews)</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php foreach ($reviews as $review): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></div>
                                            <div class="text-muted small"><?= format_date($review['created_at']) ?></div>
                                        </div>
                                        <div>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star-fill <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <?php if ($review['comment']): ?><p class="mb-0"><?= htmlspecialchars($review['comment']) ?></p><?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="sticky-top" style="top: 20px; z-index: 10;">
                    
                    <div class="card booking-form shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Book This Car</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h3 class="text-primary mb-1">Rs.<?= number_format($car['daily_rate'], 2) ?></h3>
                                <small class="text-muted">per day</small>
                            </div>

                            <div class="d-grid gap-2">
                                <?php if ($car['status'] === 'available'): ?>
                                    <?php if (is_logged_in()): ?>
                                        <a href="<?= base_url('book-car.php?id=' . $car['id']) ?>" class="btn btn-primary btn-lg">
                                            <i class="bi bi-calendar-check me-2"></i>Book Now
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= base_url('auth/login.php') ?>" class="btn btn-primary btn-lg">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Login to Book
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-warning text-center small py-2">
                                        <i class="bi bi-exclamation-triangle me-2"></i>Unavailable
                                    </div>
                                <?php endif; ?>
                                <a href="<?= base_url('cars.php') ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Cars
                                </a>
                            </div>

                            <hr class="my-4">

                            <div class="text-center">
                                <h6 class="mb-3 small fw-bold">Why choose this car?</h6>
                                <div class="row g-0 text-center">
                                    <div class="col-4">
                                        <i class="bi bi-shield-check text-success fs-4"></i>
                                        <div class="small mt-1" style="font-size: 0.7rem;">Insured</div>
                                    </div>
                                    <div class="col-4">
                                        <i class="bi bi-clock text-primary fs-4"></i>
                                        <div class="small mt-1" style="font-size: 0.7rem;">24/7 Support</div>
                                    </div>
                                    <div class="col-4">
                                        <i class="bi bi-award text-warning fs-4"></i>
                                        <div class="small mt-1" style="font-size: 0.7rem;">Premium</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="card-title mb-0 fw-bold">
                                <i class="bi bi-headset me-2 text-primary"></i>Need Help?
                            </h6>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0 py-2">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-telephone text-primary me-3"></i>
                                    <div>
                                        <div class="fw-bold small">Call Us</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">+977 98XXXXXXXX</div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item border-0 py-2">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-envelope text-primary me-3"></i>
                                    <div>
                                        <div class="fw-bold small">Email Us</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">support@carrental.com</div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>

        <?php /*
        if (!empty($similar_cars)): ?>
            <div class="row mt-5">
                <div class="col-12">
                    <h4 class="mb-4">Similar Cars</h4>
                    <div class="row g-4">
                        <?php foreach ($similar_cars as $similar_car): ?>
                            <?php
                            $similar_images = json_decode($similar_car['images'], true) ?? [];
                            $similar_features = json_decode($similar_car['features'], true) ?? [];
                            ?>
                            <div class="col-lg-3 col-md-6">
                                <div class="card h-100 car-card shadow-sm">
                                    <div class="car-image-container">
                                        <img src="<?= htmlspecialchars($similar_images[0] ?? 'https://via.placeholder.com/400x250') ?>" 
                                             class="card-img-top car-image" alt="<?= htmlspecialchars($similar_car['brand'] . ' ' . $similar_car['model']) ?>">
                                        <div class="car-status"><span class="badge bg-success">Available</span></div>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title small fw-bold"><?= htmlspecialchars($similar_car['brand'] . ' ' . $similar_car['model']) ?></h5>
                                        <p class="card-text text-muted x-small mb-2">
                                            <?= htmlspecialchars($similar_car['year']) ?> • <?= htmlspecialchars($similar_car['fuel_type']) ?>
                                        </p>
                                        <div class="mt-auto pt-2 border-top">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-primary small">Rs.<?= number_format($similar_car['daily_rate'], 0) ?></span>
                                                <a href="<?= base_url('car-details.php?id=' . $similar_car['id']) ?>" class="btn btn-outline-primary btn-sm">View</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; */ ?>

    </div>
</main>

<?php include '../public/footer.php'; ?>

<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> -->
<script>
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