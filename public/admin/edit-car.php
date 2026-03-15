<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

require_admin();

$page_title = 'Edit Car';
$car_id = intval($_GET['id'] ?? 0);
$car = db_fetch("SELECT * FROM cars WHERE id = ?", [$car_id]);

if (!$car) {
    set_flash_message('error', 'Car not found.');
    header('Location: ' . base_url('admin/manage-cars.php'));
    exit;
}

$existing_images = json_decode($car['images'], true) ?? [];
$errors = []; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    // 1. Data Collection & Sanitization
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $license_plate = trim($_POST['license_plate'] ?? '');
    $daily_rate = floatval($_POST['daily_rate'] ?? 0);
    $seats = intval($_POST['seats'] ?? 0);
    $doors = intval($_POST['doors'] ?? 0);
    $fuel_type = trim($_POST['fuel_type'] ?? '');
    $transmission = trim($_POST['transmission'] ?? '');
    $category = $_POST['category'] ?? '';
    $status = $_POST['status'] ?? 'available';
    $description = trim($_POST['description'] ?? '');
    $features_input = $_POST['features'] ?? '';

    // 2. Validation Logic
    if (empty($brand)) $errors['brand'] = "Brand name is required.";
    if (empty($model)) $errors['model'] = "Model name is required.";
    if ($year < 1900 || $year > date('Y') + 1) $errors['year'] = "Please enter a valid year.";
    if ($daily_rate <= 0) $errors['daily_rate'] = "Daily rate must be a positive number.";
    if (empty($license_plate)) $errors['license_plate'] = "License plate is required.";
    if (empty($category)) $errors['category'] = "Please select a category.";

    // 3. Image Handling (Delete + Upload)
    $updated_images = $existing_images;
    $remove_images = $_POST['remove_images'] ?? [];
    
    foreach ($remove_images as $img) {
        if (($key = array_search($img, $updated_images)) !== false) {
            $file_path = __DIR__ . '/../' . $img;
            if (file_exists($file_path)) unlink($file_path);
            unset($updated_images[$key]);
        }
    }
    $updated_images = array_values($updated_images);

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $idx => $filename) {
            $tmp_name = $_FILES['images']['tmp_name'][$idx];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $upload_dir = __DIR__ . '/../assets/uploads/cars/';
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);
                $new_name = uniqid('car_') . '.' . $ext;
                if (move_uploaded_file($tmp_name, $upload_dir . $new_name)) {
                    $updated_images[] = 'assets/uploads/cars/' . $new_name;
                }
            } else {
                $errors['images'] = "One or more files have an invalid format.";
            }
        }
    }

    // 4. Update Database if no errors
    if (empty($errors)) {
        $features_array = array_filter(array_map('trim', explode(',', $features_input)));
        
        $update_data = [
            'brand' => $brand,
            'model' => $model,
            'year' => $year,
            'color' => $color,
            'license_plate' => $license_plate,
            'daily_rate' => $daily_rate,
            'seats' => $seats,
            'doors' => $doors,
            'fuel_type' => $fuel_type,
            'transmission' => $transmission,
            'category' => $category,
            'description' => $description,
            'status' => $status,
            'features' => json_encode($features_array),
            'images' => json_encode($updated_images),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (db_update('cars', $update_data, ['id' => $car_id])) {
            set_flash_message('success', 'Car updated successfully.');
            header('Location: ' . base_url('admin/manage-cars.php'));
            exit;
        } else {
            set_flash_message('error', 'Failed to update database.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <style>
        .img-thumb-wrapper { position: relative; width: 120px; }
        .img-thumb-wrapper img { width: 100%; height: 90px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6; }
        .img-thumb-wrapper .form-check { position: absolute; top: 4px; right: 4px; background: white; border-radius: 4px; padding: 2px; }
        .is-invalid-label { color: #dc3545; font-size: 0.875em; }
    </style>
</head>
<body class="bg-light">

<?php include '../../public/navbar.php'; ?>

<main class="py-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Car: <span class="text-primary"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></span></h1>
        <a href="<?= base_url('admin/manage-cars.php') ?>" class="btn btn-outline-secondary">Back to List</a>
    </div>

    <form method="POST" enctype="multipart/form-data" class="card shadow-sm">
        <div class="card-body p-4">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <h5 class="mb-3 border-bottom pb-2">Basic Information</h5>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Brand *</label>
                    <input type="text" name="brand" class="form-control <?= isset($errors['brand']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['brand'] ?? $car['brand']) ?>">
                    <div class="invalid-feedback"><?= $errors['brand'] ?? '' ?></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Model *</label>
                    <input type="text" name="model" class="form-control <?= isset($errors['model']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['model'] ?? $car['model']) ?>">
                    <div class="invalid-feedback"><?= $errors['model'] ?? '' ?></div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Year *</label>
                    <input type="number" name="year" class="form-control <?= isset($errors['year']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['year'] ?? $car['year']) ?>">
                    <div class="invalid-feedback"><?= $errors['year'] ?? '' ?></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">License Plate *</label>
                    <input type="text" name="license_plate" class="form-control <?= isset($errors['license_plate']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['license_plate'] ?? $car['license_plate']) ?>">
                    <div class="invalid-feedback"><?= $errors['license_plate'] ?? '' ?></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Daily Rate ($) *</label>
                    <input type="number" step="0.01" name="daily_rate" class="form-control <?= isset($errors['daily_rate']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['daily_rate'] ?? $car['daily_rate']) ?>">
                    <div class="invalid-feedback"><?= $errors['daily_rate'] ?? '' ?></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Color</label>
                    <input type="text" name="color" class="form-control" value="<?= htmlspecialchars($_POST['color'] ?? $car['color']) ?>">
                </div>
            </div>

            <h5 class="mb-3 border-bottom pb-2">Specifications & Features</h5>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Seats</label>
                    <input type="number" name="seats" class="form-control" value="<?= htmlspecialchars($_POST['seats'] ?? $car['seats']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Doors</label>
                    <input type="number" name="doors" class="form-control" value="<?= htmlspecialchars($_POST['doors'] ?? $car['doors']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Fuel Type</label>
                    <input type="text" name="fuel_type" class="form-control" value="<?= htmlspecialchars($_POST['fuel_type'] ?? $car['fuel_type']) ?>" placeholder="e.g. Petrol, Diesel, Electric">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Transmission</label>
                    <input type="text" name="transmission" class="form-control" value="<?= htmlspecialchars($_POST['transmission'] ?? $car['transmission']) ?>" placeholder="e.g. Automatic, Manual">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Category *</label>
                    <select class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>" name="category">
                        <option value="">Select Category</option>
                        <?php foreach (['economy','compact','mid-size','full-size','luxury','suv','sports'] as $c): ?>
                            <option value="<?= $c ?>" <?= ($_POST['category'] ?? $car['category']) === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"><?= $errors['category'] ?? '' ?></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Status</label>
                    <select class="form-select" name="status">
                        <?php foreach (['available','unavailable','maintenance','rented'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($_POST['status'] ?? $car['status']) === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Features (comma separated)</label>
                    <input type="text" name="features" class="form-control" value="<?= htmlspecialchars($_POST['features'] ?? implode(', ', json_decode($car['features'], true) ?? [])) ?>" placeholder="GPS, Bluetooth, AC">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? $car['description'] ?? '') ?></textarea>
            </div>

            <h5 class="mb-3 border-bottom pb-2">Media Management</h5>
            <div class="mb-3">
                <label class="form-label d-block fw-bold">Existing Images (Check to delete)</label>
                <div class="d-flex flex-wrap gap-3 p-3 border rounded bg-light">
                    <?php if (empty($existing_images)): ?>
                        <span class="text-muted small">No images uploaded.</span>
                    <?php else: ?>
                        <?php foreach ($existing_images as $img): ?>
                            <div class="img-thumb-wrapper">
                                <img src="<?= htmlspecialchars(base_url($img)) ?>">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_images[]" value="<?= htmlspecialchars($img) ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Upload New Images</label>
                <input type="file" name="images[]" class="form-control <?= isset($errors['images']) ? 'is-invalid' : '' ?>" multiple accept="image/*">
                <div class="invalid-feedback"><?= $errors['images'] ?? '' ?></div>
            </div>

            <div class="pt-3 border-top">
                <button type="submit" class="btn btn-primary px-5">Save Car Details</button>
                <a href="<?= base_url('admin/manage-cars.php') ?>" class="btn btn-link text-decoration-none text-muted">Cancel</a>
            </div>
        </div>
    </form>
</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>