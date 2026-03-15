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

$page_title = 'Book a Car';
$current_user = get_current_user_data();

$error = '';
$success = '';

// Get car ID from URL
$car_id = intval($_GET['id'] ?? 0);
if (!$car_id) {
    header('Location: ' . base_url('cars.php'));
    exit;
}

// Get car details
$car = db_fetch("SELECT * FROM cars WHERE id = ? AND status = 'available'", [$car_id]);
if (!$car) {
    set_flash_message('error', 'Car not found or not available.');
    header('Location: ' . base_url('cars.php'));
    exit;
}

$car_images = json_decode($car['images'], true) ?? [];
$car_features = json_decode($car['features'], true) ?? [];

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_date = $_POST['pickup_date'] ?? '';
    $return_date = $_POST['return_date'] ?? '';
    $pickup_location = sanitize_input($_POST['pickup_location'] ?? '');
    $return_location = sanitize_input($_POST['return_location'] ?? '');
    $user_notes = sanitize_input($_POST['user_notes'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($pickup_date) || empty($return_date) || empty($pickup_location) || empty($return_location)) {
        $error = 'Please fill in all required fields.';
    } elseif (strtotime($pickup_date) < strtotime('today')) {
        $error = 'Pickup date cannot be in the past.';
    } elseif (strtotime($return_date) <= strtotime($pickup_date)) {
        $error = 'Return date must be after pickup date.';
    } else {
        // Check if car is available for the selected dates
        // A conflict exists if (Existing_Start <= Requested_End) AND (Existing_End >= Requested_Start)
        $conflict = db_fetch("
            SELECT COUNT(*) as count 
            FROM bookings 
            WHERE car_id = ? 
            AND status != 'cancelled'
            AND pickup_date <= ? 
            AND return_date >= ?
        ", [$car_id, $return_date, $pickup_date]);

        if ($conflict['count'] > 0) {
            $error = 'Sorry, this car was just booked by someone else for those dates. Please try different dates or another car.';
        } else {
            // Calculate total amount
            $total_days = calculate_total_days($pickup_date, $return_date);
            $total_amount = $total_days * $car['daily_rate'];
            
            // Create booking
            $booking_data = [
                'user_id' => $current_user['id'],
                'car_id' => $car_id,
                'pickup_date' => $pickup_date,
                'return_date' => $return_date,
                'pickup_location' => $pickup_location,
                'return_location' => $return_location,
                'total_days' => $total_days,
                'daily_rate' => $car['daily_rate'],
                'total_amount' => $total_amount,
                'status' => 'pending',
                'user_notes' => $user_notes
            ];
            
            $booking_id = db_insert('bookings', $booking_data);
            
            if ($booking_id) {
                set_flash_message('success', 'Booking request submitted successfully! You will be notified once it\'s approved.');
                header('Location: ' . base_url('booking-details.php?id=' . $booking_id));
                exit;
            } else {
                $error = 'Failed to create booking. Please try again.';
            }
        }
    }
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
    <link rel="stylesheet" href="<?= asset_url('css/cars.css') ?>">
</head>
<body>

<?php include '../public/navbar.php'; ?>

<main class="py-4">
    <div class="container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1">Book a Car</h1>
                        <p class="text-muted mb-0">Complete your booking for <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></p>
                    </div>
                    <a href="<?= base_url('car-details.php?id=' . $car['id']) ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Car Details
                    </a>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Car Details -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Car Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <img src="<?= htmlspecialchars($car_images[0] ?? 'https://via.placeholder.com/400x250') ?>" 
                                     alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>"
                                     class="img-fluid rounded mb-3">
                            </div>
                            <div class="col-md-6">
                                <h4><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h4>
                                <p class="text-muted"><?= htmlspecialchars($car['year']) ?> • <?= htmlspecialchars($car['fuel_type']) ?> • <?= htmlspecialchars($car['transmission']) ?></p>
                                
                                <div class="mb-3">
                                    <h5 class="text-primary mb-0">Rs.<?= number_format($car['daily_rate'], 2) ?></h5>
                                    <small class="text-muted">per day</small>
                                </div>
                                
                                <div class="car-features mb-3">
                                    <?php foreach (array_slice($car_features, 0, 4) as $feature): ?>
                                        <span class="badge bg-light text-dark me-1"><?= htmlspecialchars($feature) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="spec-item">
                                    <span class="spec-label">Seats:</span>
                                    <span class="spec-value"><?= $car['seats'] ?></span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-label">Doors:</span>
                                    <span class="spec-value"><?= $car['doors'] ?></span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-label">Category:</span>
                                    <span class="spec-value"><?= ucfirst($car['category']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="col-lg-6">
                <div class="card booking-form">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Booking Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="pickup_date" class="form-label">Pickup Date *</label>
                                    <input type="date" class="form-control" id="pickup_date" name="pickup_date" 
                                           value="<?= htmlspecialchars($pickup_date ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="return_date" class="form-label">Return Date *</label>
                                    <input type="date" class="form-control" id="return_date" name="return_date" 
                                           value="<?= htmlspecialchars($return_date ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="pickup_location" class="form-label">Pickup Location *</label>
                                    <select class="form-select" id="pickup_location" name="pickup_location" required>
                                        <option value="">Select location</option>
                                        <option value="downtown" <?= ($pickup_location ?? '') === 'downtown' ? 'selected' : '' ?>>Downtown</option>
                                        <option value="airport" <?= ($pickup_location ?? '') === 'airport' ? 'selected' : '' ?>>Airport</option>
                                        <option value="shopping_mall" <?= ($pickup_location ?? '') === 'shopping_mall' ? 'selected' : '' ?>>Shopping Mall</option>
                                        <option value="hotel" <?= ($pickup_location ?? '') === 'hotel' ? 'selected' : '' ?>>Hotel</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="return_location" class="form-label">Return Location *</label>
                                    <select class="form-select" id="return_location" name="return_location" required>
                                        <option value="">Select location</option>
                                        <option value="downtown" <?= ($return_location ?? '') === 'downtown' ? 'selected' : '' ?>>Downtown</option>
                                        <option value="airport" <?= ($return_location ?? '') === 'airport' ? 'selected' : '' ?>>Airport</option>
                                        <option value="shopping_mall" <?= ($return_location ?? '') === 'shopping_mall' ? 'selected' : '' ?>>Shopping Mall</option>
                                        <option value="hotel" <?= ($return_location ?? '') === 'hotel' ? 'selected' : '' ?>>Hotel</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="user_notes" class="form-label">Special Requests</label>
                                <textarea class="form-control" id="user_notes" name="user_notes" rows="3" 
                                          placeholder="Any special requests or notes..."><?= htmlspecialchars($user_notes ?? '') ?></textarea>
                            </div>

                            <!-- Booking Summary -->
                            <div class="booking-summary">
                                <h6 class="mb-3">Booking Summary</h6>
                                <div class="price-item">
                                    <span>Daily Rate:</span>
                                    <span>Rs.<?= number_format($car['daily_rate'], 2) ?></span>
                                </div>
                                <div class="price-item">
                                    <span>Duration:</span>
                                    <span id="duration">Select dates</span>
                                </div>
                                <div class="price-total">
                                    <span>Total:</span>
                                    <span id="total">Rs 0.00</span>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-calendar-check me-2"></i>Submit Booking Request
                                </button>
                                <a href="<?= base_url('cars.php') ?>" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
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

<script>
document.addEventListener('DOMContentLoaded', function() {

    const pickupDateInput = document.getElementById('pickup_date');
    const returnDateInput = document.getElementById('return_date');

    const durationSpan = document.getElementById('duration');
    const totalSpan = document.getElementById('total');

    const dailyRate = <?= $car['daily_rate'] ?>;

    // Force pickup date minimum = today
    const today = new Date().toISOString().split('T')[0];
    pickupDateInput.min = today;
    returnDateInput.min = today;

    // Main calculate function
    function calculateBooking() {
        const pickupVal = pickupDateInput.value;
        const returnVal = returnDateInput.value;

        if (!pickupVal || !returnVal) {
            durationSpan.textContent = 'Select dates';
            totalSpan.textContent = 'Rs 0.00';
            return;
        }

        const pickupDate = new Date(pickupVal);
        const returnDate = new Date(returnVal);

        // Invalid case
        if (returnDate <= pickupDate) {
            durationSpan.textContent = 'Invalid dates';
            totalSpan.textContent = '$0.00';
            return;
        }

        // Calculate days
        const diffDays = Math.ceil((returnDate - pickupDate) / (1000 * 60 * 60 * 24));
        durationSpan.textContent = diffDays + ' day' + (diffDays > 1 ? 's' : '');

        // Calculate total (no tax, no subtotal)
        const total = diffDays * dailyRate;
        totalSpan.textContent = 'Rs.' + total.toLocaleString(undefined, {minimumFractionDigits: 2});
    }

    pickupDateInput.addEventListener('change', function() {
        // Return date must be at least 1 day after pickup
        returnDateInput.min = this.value;
        calculateBooking();
    });

    returnDateInput.addEventListener('change', calculateBooking);

    // Auto dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            new bootstrap.Alert(alert).close();
        });
    }, 5000);

});
</script>




</body>
</html>
