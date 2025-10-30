<?php
require_once 'url.php';
require_once '../config/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset_url('css/styles.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/theme.css') ?>">
</head>
<body>

<?php include 'navbar.php'; ?>

<main>
    <!-- Flash Messages -->
    <?php if (has_flash_message('success')): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= htmlspecialchars(get_flash_message('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (has_flash_message('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= htmlspecialchars(get_flash_message('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero-section bg-light">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold mb-4">Find Your Perfect Car on Rent</h1>
                    <p class="lead mb-5">Experience premium comfort with our exclusive collection of luxury vehicles</p>

                    <!-- Search Form -->
                    <div class="row justify-content-center mb-5">
                        <div class="col-lg-10">
                            <div class="card shadow-lg border-0 rounded-4 p-4">
                                <form action="<?= base_url('cars.php') ?>" method="GET" id="searchForm">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">Pickup Location</label>
                                            <select class="form-select" name="location">
                                                <option value="">Select location</option>
                                                <option value="downtown">Downtown</option>
                                                <option value="airport">Airport</option>
                                                <option value="shopping_mall">Shopping Mall</option>
                                                <option value="hotel">Hotel</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">Pick-up Date</label>
                                            <input type="date" class="form-control" name="pickup_date" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium">Return Date</label>
                                            <input type="date" class="form-control" name="return_date" required>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="bi bi-search me-2"></i>Search
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Car Image -->
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1676288176820-a5a954d81e6e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080"
                             alt="Luxury Car" class="img-fluid hero-car-img">
                        <div class="car-shadow"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-6 fw-bold mb-3">Why Choose CarRental?</h2>
                <p class="lead">Experience the best car rental service with premium features</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Fully Insured</h5>
                    <p class="text-muted">All our vehicles come with comprehensive insurance coverage</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-icon bg-success text-white rounded-circle mx-auto mb-3">
                        <i class="bi bi-clock"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">24/7 Support</h5>
                    <p class="text-muted">Round-the-clock customer support for your peace of mind</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-icon bg-warning text-white rounded-circle mx-auto mb-3">
                        <i class="bi bi-award"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Premium Fleet</h5>
                    <p class="text-muted">Latest model vehicles maintained to the highest standards</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Cars Section -->
    <section class="py-5 bg-light" id="featuredCars">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="display-6 fw-bold mb-2">Featured Cars</h2>
                    <p class="lead">Discover our most popular vehicles</p>
                </div>
                <a href="<?= base_url('cars.php') ?>" class="btn btn-outline-primary d-none d-md-block">View All Cars</a>
            </div>
            <div class="row g-4" id="featuredCarsGrid">
                <?php
                $featured_cars = db_fetch_all("SELECT * FROM cars WHERE status = 'available' ORDER BY created_at DESC LIMIT 6");
                foreach ($featured_cars as $car):
                    $features = json_decode($car['features'], true) ?? [];
                    $images = json_decode($car['images'], true) ?? [];
                ?>
                <div class="col-lg-4 col-md-6">
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
            <div class="text-center mt-4 d-md-none">
                <a href="<?= base_url('cars.php') ?>" class="btn btn-outline-primary">View All Cars</a>
            </div>
        </div>
    </section>

    <!-- Call To Action -->
    <section class="py-5 bg-primary text-white text-center">
        <div class="container">
            <h2 class="display-6 fw-bold mb-3">Ready to Hit the Road?</h2>
            <p class="lead mb-4">Join thousands of satisfied customers who trust CarRental</p>
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <a class="btn btn-light btn-lg" href="<?= base_url('cars.php') ?>">Browse Our Fleet</a>
                <?php if (!is_logged_in()): ?>
                    <a class="btn btn-outline-light btn-lg" href="<?= base_url('auth/register.php') ?>">Sign Up Today</a>
                <?php else: ?>
                    <a class="btn btn-outline-light btn-lg" href="<?= base_url('dashboard.php') ?>">Go to Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>

<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset_url('js/main.js') ?>"></script>
<script src="<?= asset_url('js/theme-toggle.js') ?>"></script> -->

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
    
    // Auto-dismiss alerts
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>

</body>
</html>
