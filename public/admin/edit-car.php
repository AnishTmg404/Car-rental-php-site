<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Require admin access
require_admin();

$page_title = 'Edit Car';
$current_user = get_current_user_data();

$car_id = intval($_GET['id'] ?? 0);
$car = db_fetch("SELECT * FROM cars WHERE id = ?", [$car_id]);
if (!$car) {
    set_flash_message('error', 'Car not found.');
    header('Location: ' . base_url('admin/manage-cars.php'));
    exit;
}

$existing_images = json_decode($car['images'], true) ?? [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Invalid CSRF token.');
        header('Location: ' . base_url('admin/edit-car.php?id=' . $car_id));
        exit;
    }

    $make = $_POST['make'] ?? '';
    $model = $_POST['model'] ?? '';
    $year = $_POST['year'] ?? '';
    $color = $_POST['color'] ?? '';
    $license_plate = $_POST['license_plate'] ?? '';
    $daily_rate = floatval($_POST['daily_rate'] ?? 0);
    $seats = intval($_POST['seats'] ?? 0);
    $doors = intval($_POST['doors'] ?? 0);
    $fuel_type = $_POST['fuel_type'] ?? '';
    $transmission = $_POST['transmission'] ?? '';
    $category = $_POST['category'] ?? '';
    $status = $_POST['status'] ?? 'available';
    $features = $_POST['features'] ?? [];
    $remove_images = $_POST['remove_images'] ?? [];

    // Remove selected existing images
    foreach ($remove_images as $img) {
        if (($key = array_search($img, $existing_images)) !== false) {
            $file_path = __DIR__ . '/../assets/uploads/cars' . $img; // Correct path
            if (file_exists($file_path)) unlink($file_path);
            unset($existing_images[$key]);
        }
    }
    $existing_images = array_values($existing_images);

    // Handle uploaded files
    if (!empty($_FILES['images']['name'][0])) {
        $upload_dir = __DIR__ . '/../assets/uploads/cars/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);

        foreach ($_FILES['images']['name'] as $idx => $filename) {
            $tmp_name = $_FILES['images']['tmp_name'][$idx];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','gif'])) continue; // skip invalid types
            $new_name = uniqid('car_') . '.' . $ext;
            if (move_uploaded_file($tmp_name, $upload_dir . $new_name)) {
                $existing_images[] = 'assets/uploads/cars/' . $new_name;
            }
        }
    }

    $update_data = [
        'make' => $make,
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
        'status' => $status,
        'features' => json_encode($features),
        'images' => json_encode($existing_images),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    if (db_update('cars', $update_data, ['id' => $car_id])) {
        set_flash_message('success', 'Car updated successfully.');
        header('Location: ' . base_url('admin/manage-cars.php'));
        exit;
    } else {
        set_flash_message('error', 'Failed to update car.');
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
<style>
    .img-thumb-wrapper { position: relative; width: 120px; }
    .img-thumb-wrapper img { width: 100%; height: auto; border-radius: 5px; }
    .img-thumb-wrapper .form-check { position: absolute; top: 0; right: 0; }
</style>
</head>
<body>

<?php include '../../public/navbar.php'; ?>

<main class="py-4">
<div class="container">
    <h1 class="mb-4">Edit Car: <?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></h1>

    <?php if (has_flash_message('success')): ?>
        <div class="alert alert-success"><?= htmlspecialchars(get_flash_message('success')) ?></div>
    <?php endif; ?>
    <?php if (has_flash_message('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars(get_flash_message('error')) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

        <!-- Car Details -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Make</label>
                <input type="text" class="form-control" name="make" value="<?= htmlspecialchars($car['make']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Model</label>
                <input type="text" class="form-control" name="model" value="<?= htmlspecialchars($car['model']) ?>" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Year</label>
                <input type="number" class="form-control" name="year" value="<?= htmlspecialchars($car['year']) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Color</label>
                <input type="text" class="form-control" name="color" value="<?= htmlspecialchars($car['color']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">License Plate</label>
                <input type="text" class="form-control" name="license_plate" value="<?= htmlspecialchars($car['license_plate']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Daily Rate ($)</label>
                <input type="number" step="0.01" class="form-control" name="daily_rate" value="<?= htmlspecialchars($car['daily_rate']) ?>" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Seats</label>
                <input type="number" class="form-control" name="seats" value="<?= htmlspecialchars($car['seats']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Doors</label>
                <input type="number" class="form-control" name="doors" value="<?= htmlspecialchars($car['doors']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fuel Type</label>
                <input type="text" class="form-control" name="fuel_type" value="<?= htmlspecialchars($car['fuel_type']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Transmission</label>
                <input type="text" class="form-control" name="transmission" value="<?= htmlspecialchars($car['transmission']) ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Category</label>
                <input type="text" class="form-control" name="category" value="<?= htmlspecialchars($car['category']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="available" <?= $car['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="unavailable" <?= $car['status'] === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                    <option value="maintenance" <?= $car['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    <option value="rented" <?= $car['status'] === 'rented' ? 'selected' : '' ?>>Rented</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Features (comma separated)</label>
                <input type="text" class="form-control" name="features[]" value="<?= htmlspecialchars(implode(',', json_decode($car['features'], true) ?? [])) ?>">
            </div>
        </div>

        <!-- Existing Images -->
        <div class="mb-3">
            <label class="form-label">Existing Images</label>
            <div class="d-flex flex-wrap gap-2" id="existing-images">
                <?php foreach ($existing_images as $img): ?>
                    <div class="img-thumb-wrapper">
                        <?php
                        // If the image is an external URL, use it directly; otherwise, prepend base_url
                        $img_src = preg_match('/^https?:\/\//', $img) ? $img : base_url($img);
                        ?>
                        <img src="<?= htmlspecialchars($img_src) ?>" class="img-thumbnail" style="width:120px;height:90px;object-fit:cover;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remove_images[]" value="<?= htmlspecialchars($img) ?>" title="Remove">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Upload New Images -->
        <div class="mb-3">
            <label class="form-label">Upload New Images</label>
            <input type="file" class="form-control" name="images[]" multiple accept="image/*" id="new-images-input">
            <small class="text-muted">You can upload multiple images. Allowed: jpg, jpeg, png, gif</small>
            <div class="mt-2 d-flex flex-wrap gap-2" id="new-images-preview"></div>
        </div>

        <button type="submit" class="btn btn-primary">Update Car</button>
        <a href="<?= base_url('admin/manage-cars.php') ?>" class="btn btn-secondary">Back to Manage Cars</a>
    </form>
</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Preview new images
document.getElementById('new-images-input').addEventListener('change', function(event){
    const previewContainer = document.getElementById('new-images-preview');
    previewContainer.innerHTML = '';
    Array.from(event.target.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.style.width = '120px';
            div.innerHTML = `<img src="${e.target.result}" class="img-thumbnail">`;
            previewContainer.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
});
</script>

</body>
</html>
