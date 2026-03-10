<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Require admin access
require_admin();

$page_title = 'Add New Car';
$current_user = get_current_user_data();

$errors = [];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $make = sanitize_input($_POST['make'] ?? '');
    $model = sanitize_input($_POST['model'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $color = sanitize_input($_POST['color'] ?? '');
    $license_plate = sanitize_input($_POST['license_plate'] ?? '');
    $vin = sanitize_input($_POST['vin'] ?? '');
    $mileage = intval($_POST['mileage'] ?? 0);
    $fuel_type = $_POST['fuel_type'] ?? '';
    $transmission = $_POST['transmission'] ?? '';
    $seats = intval($_POST['seats'] ?? 0);
    $doors = intval($_POST['doors'] ?? 0);
    $category = $_POST['category'] ?? '';
    $daily_rate = floatval($_POST['daily_rate'] ?? 0);
    $description = sanitize_input($_POST['description'] ?? '');
    $features = $_POST['features'] ?? [];
    $csrf_token = $_POST['csrf_token'] ?? '';

    // CSRF
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Invalid request.';
    }

    // Required fields
    if (empty($make)) $errors['make'] = "Brand is required";
    if (empty($model)) $errors['model'] = "Model is required";
    if (empty($license_plate)) $errors['license_plate'] = "License plate required";
    if (empty($fuel_type)) $errors['fuel_type'] = "Select fuel type";
    if (empty($transmission)) $errors['transmission'] = "Select transmission";
    if (empty($category)) $errors['category'] = "Select category";

    // Numeric validations
    if ($year < 1900 || $year > date('Y') + 1) {
        $errors['year'] = "Enter valid year";
    }

    if ($seats < 1 || $seats > 9) {
        $errors['seats'] = "Seats must be between 1-9";
    }

    if ($doors < 2 || $doors > 5) {
        $errors['doors'] = "Doors must be between 2-5";
    }

    if ($daily_rate <= 0) {
        $errors['daily_rate'] = "Daily rate must be greater than 0";
    }

    // License plate check
    if (empty($errors['license_plate'])) {
        $existing = db_fetch("SELECT id FROM cars WHERE license_plate = ?", [$license_plate]);
        if ($existing) {
            $errors['license_plate'] = "License plate already exists";
        }
    }

    if (empty($errors)) {

        // IMAGE UPLOAD
        $uploaded_images = [];

        if (!empty($_FILES['images']['name'][0])) {

            $upload_dir = '../../public/assets/uploads/cars/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {

                if ($_FILES['images']['error'][$i] === 0) {

                    $file_size = $_FILES['images']['size'][$i];

                    if ($file_size > 5 * 1024 * 1024) {
                        continue;
                    }

                    $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));

                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                    if (in_array($ext, $allowed)) {

                        $filename = uniqid('car_') . "." . $ext;

                        $path = $upload_dir . $filename;

                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $path)) {

                            $uploaded_images[] = 'assets/uploads/cars/' . $filename;
                        }
                    }
                }
            }
        }

        // if (empty($uploaded_images)) {
        //     $uploaded_images[] = 'https://via.placeholder.com/400x250?text=Car+Image';
        // }

        $car_data = [
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'color' => $color,
            'license_plate' => $license_plate,
            'vin' => $vin,
            'mileage' => $mileage,
            'fuel_type' => $fuel_type,
            'transmission' => $transmission,
            'seats' => $seats,
            'doors' => $doors,
            'category' => $category,
            'daily_rate' => $daily_rate,
            'description' => $description,
            'features' => json_encode($features),
            'images' => json_encode($uploaded_images),
            'status' => 'available',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $car_id = db_insert('cars', $car_data);

        if ($car_id) {

            log_admin_action('add_car', 'cars', $car_id, null, $car_data);

            set_flash_message('success', 'Car added successfully');

            header('Location: ' . base_url('admin/manage-cars.php'));
            exit;
        }

        $error = "Failed to add car";
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
</head>

<body>

    <?php include '../../public/navbar.php'; ?>

    <main class="py-4">
        <div class="container">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1">Add New Car</h1>
                            <p class="text-muted mb-0">Add a new vehicle to your fleet</p>
                        </div>
                        <a href="<?= base_url('admin/manage-cars.php') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Cars
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

            <!-- Add Car Form -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Car Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" novalidate>
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                                <!-- Basic Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">Basic Information</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="make" class="form-label">Brand *</label>
                                        <input type="text"
                                            id="make"
                                            name="make"
                                            class="form-control <?= isset($errors['make']) ? 'is-invalid' : '' ?>"
                                            value="<?= htmlspecialchars($make ?? '') ?>">

                                        <?php if (!empty($errors['make'])): ?>
                                            <div class="invalid-feedback">
                                                <?= htmlspecialchars($errors['make']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="model" class="form-label">Model *</label>
                                        <input type="text" name="model"
                                            class="form-control <?= isset($errors['model']) ? 'is-invalid' : '' ?>"
                                            value="<?= htmlspecialchars($model ?? '') ?>">
                                        <?php if (isset($errors['model'])): ?>
                                            <div class="invalid-feedback"><?= $errors['model'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="year" class="form-label">Year *</label>
                                        <input type="number" name="year"
                                            class="form-control <?= isset($errors['year']) ? 'is-invalid' : '' ?>"
                                            value="<?= htmlspecialchars($year ?? '') ?>">
                                        <?php if (isset($errors['year'])): ?>
                                            <div class="invalid-feedback"><?= $errors['year'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="color" class="form-label">Color</label>
                                        <input type="text" class="form-control" id="color" name="color"
                                            value="<?= htmlspecialchars($color ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="mileage" class="form-label">Mileage</label>
                                        <input type="number" class="form-control" id="mileage" name="mileage"
                                            value="<?= htmlspecialchars($mileage ?? '') ?>" min="0">
                                    </div>
                                </div>

                                <!-- Vehicle Details -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">Vehicle Details</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="license_plate" class="form-label">License Plate *</label>
                                        <input type="text" name="license_plate"
                                            class="form-control <?= isset($errors['license_plate']) ? 'is-invalid' : '' ?>"
                                            value="<?= htmlspecialchars($license_plate ?? '') ?>">
                                        <?php if (isset($errors['license_plate'])): ?>
                                            <div class="invalid-feedback"><?= $errors['license_plate'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="vin" class="form-label">Chassie Number</label>
                                        <input type="text" class="form-control" id="vin" name="vin"
                                            value="<?= htmlspecialchars($vin ?? '') ?>" maxlength="17">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fuel_type" class="form-label">Fuel Type *</label>
                                        <select name="fuel_type" class="form-select <?= isset($errors['fuel_type']) ? 'is-invalid' : '' ?>">
                                            <option value="">Select fuel type</option>
                                            <option value="petrol" <?= ($fuel_type ?? '') === 'petrol' ? 'selected' : '' ?>>Petrol</option>
                                            <option value="diesel" <?= ($fuel_type ?? '') === 'diesel' ? 'selected' : '' ?>>Diesel</option>
                                            <option value="hybrid" <?= ($fuel_type ?? '') === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                                            <option value="electric" <?= ($fuel_type ?? '') === 'electric' ? 'selected' : '' ?>>Electric</option>
                                        </select>
                                        <?php if (isset($errors['fuel_type'])): ?>
                                            <div class="invalid-feedback"><?= $errors['fuel_type'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="transmission" class="form-label">Transmission *</label>
                                        <select name="transmission" class="form-select <?= isset($errors['transmission']) ? 'is-invalid' : '' ?>">
                                            <option value="">Select transmission</option>
                                            <option value="manual" <?= ($transmission ?? '') === 'manual' ? 'selected' : '' ?>>Manual</option>
                                            <option value="automatic" <?= ($transmission ?? '') === 'automatic' ? 'selected' : '' ?>>Automatic</option>
                                        </select>
                                        <?php if (isset($errors['transmission'])): ?>
                                            <div class="invalid-feedback"><?= $errors['transmission'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="seats" class="form-label">Number of Seats *</label>
                                        <input type="number" name="seats"
                                            class="form-control <?= isset($errors['seats']) ? 'is-invalid' : '' ?>"
                                            value="<?= htmlspecialchars($seats ?? '') ?>">
                                        <?php if (isset($errors['seats'])): ?>
                                            <div class="invalid-feedback"><?= $errors['seats'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="doors" class="form-label">Number of Doors *</label>
                                        <input type="number" name="doors"
                                            class="form-control <?= isset($errors['doors']) ? 'is-invalid' : '' ?>"
                                            value="<?= htmlspecialchars($doors ?? '') ?>">
                                        <?php if (isset($errors['doors'])): ?>
                                            <div class="invalid-feedback"><?= $errors['doors'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="category" class="form-label">Category *</label>
                                        <select name="category" class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>">
                                            <option value="">Select category</option>
                                            <option value="economy" <?= ($category ?? '') === 'economy' ? 'selected' : '' ?>>Economy</option>
                                            <option value="compact" <?= ($category ?? '') === 'compact' ? 'selected' : '' ?>>Compact</option>
                                            <option value="mid-size" <?= ($category ?? '') === 'mid-size' ? 'selected' : '' ?>>Mid-size</option>
                                            <option value="full-size" <?= ($category ?? '') === 'full-size' ? 'selected' : '' ?>>Full-size</option>
                                            <option value="luxury" <?= ($category ?? '') === 'luxury' ? 'selected' : '' ?>>Luxury</option>
                                            <option value="suv" <?= ($category ?? '') === 'suv' ? 'selected' : '' ?>>SUV</option>
                                            <option value="sports" <?= ($category ?? '') === 'sports' ? 'selected' : '' ?>>Sports</option>
                                        </select>
                                        <?php if (isset($errors['category'])): ?>
                                            <div class="invalid-feedback"><?= $errors['category'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Pricing -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">Pricing</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="daily_rate" class="form-label">Daily Rate *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rs.</span>
                                            <input type="number" name="daily_rate"
                                                class="form-control <?= isset($errors['daily_rate']) ? 'is-invalid' : '' ?>"
                                                value="<?= htmlspecialchars($daily_rate ?? '') ?>" step="0.01" min="0">
                                            <?php if (isset($errors['daily_rate'])): ?>
                                                <div class="invalid-feedback"><?= $errors['daily_rate'] ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">Description</h6>
                                    </div>
                                    <div class="col-12">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($description ?? '') ?></textarea>
                                    </div>
                                </div>

                                <!-- Features -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">Features</h6>
                                    </div>
                                    <div class="col-12">
                                        <div class="row">
                                            <?php
                                            $common_features = [
                                                'GPS Navigation',
                                                'Bluetooth',
                                                'Air Conditioning',
                                                'Leather Seats',
                                                'Sunroof',
                                                'Premium Sound',
                                                'USB Ports',
                                                'Cruise Control',
                                                'Backup Camera',
                                                'Heated Seats',
                                                'Keyless Entry',
                                                'Remote Start'
                                            ];
                                            foreach ($common_features as $feature):
                                            ?>
                                                <div class="col-md-4 col-sm-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="features[]"
                                                            value="<?= htmlspecialchars($feature) ?>"
                                                            id="feature_<?= strtolower(str_replace(' ', '_', $feature)) ?>"
                                                            <?= in_array($feature, $features ?? []) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="feature_<?= strtolower(str_replace(' ', '_', $feature)) ?>">
                                                            <?= htmlspecialchars($feature) ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Images -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">Images</h6>
                                    </div>
                                    <div class="col-12">
                                        <label for="images" class="form-label">Car Images</label>
                                        <input type="file" class="form-control" id="images" name="images[]"
                                            multiple accept="image/*">
                                        <div class="form-text">Upload multiple images (JPG, PNG, GIF, WebP). Max 5MB per image.</div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-plus-circle me-2"></i>Add Car
                                            </button>
                                            <a href="<?= base_url('admin/manage-cars.php') ?>" class="btn btn-outline-secondary">
                                                Cancel
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Tips</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Upload high-quality images for better presentation
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Set competitive daily rates based on market prices
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Include all relevant features to attract customers
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Ensure license plate is unique and valid
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview functionality
        document.getElementById('images').addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            const previewContainer = document.getElementById('imagePreview');

            if (!previewContainer) {
                const container = document.createElement('div');
                container.id = 'imagePreview';
                container.className = 'row mt-3';
                e.target.parentNode.appendChild(container);
            }

            const container = document.getElementById('imagePreview');
            container.innerHTML = '';

            files.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-md-4 mb-2';
                        col.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" style="height: 100px; object-fit: cover;">
                        <div class="card-body p-2">
                            <small class="text-muted">${file.name}</small>
                        </div>
                    </div>
                `;
                        container.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

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