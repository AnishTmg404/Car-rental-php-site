<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Require admin access
require_admin();

$page_title = 'Manage Bookings';
$current_user = get_current_user_data();

// Handle booking status updates
$message = $error = null;
if (isset($_GET['action'], $_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    $action = $_GET['action'];

    if (in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $updated = db_execute("UPDATE bookings SET status = ? WHERE id = ?", [$status, $booking_id]);

        if ($updated) {
            if ($status === 'approved') {
                $car_id = db_fetch("SELECT car_id FROM bookings WHERE id = ?", [$booking_id])['car_id'];
                db_execute("UPDATE cars SET status = 'rented' WHERE id = ?", [$car_id]);
            }
            $message = "Booking #$booking_id successfully $status.";
        } else {
            $error = "Failed to update booking status for #$booking_id.";
        }
    }
}

// Fetch all bookings with car and user info
$bookings = db_fetch_all("
    SELECT b.*, u.first_name, u.last_name, u.email, c.make, c.model, c.images
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_id = c.id
    ORDER BY b.created_at DESC
");
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
<style>
    .table-hover tbody tr:hover { background-color: #f8f9fa; }
    .car-img { width: 60px; height: 40px; object-fit: cover; border-radius: 0.25rem; }
</style>
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<main class="py-4">
<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Manage Bookings</h1>
    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-house-door me-1"></i>Back to Dashboard</a>
</div>

<?php if($message): ?>
<div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if($error): ?>
<div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Car</th>
                    <th>Customer</th>
                    <th>Dates</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($bookings as $b): 
                $images = json_decode($b['images'], true);
                $img = $images[0] ?? '';
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
                <td><?= $b['id'] ?></td>
                <td>
                    <div class="d-flex align-items-center">
                        <?php if($img): ?>
                        <img src="<?= htmlspecialchars($img) ?>" alt="Car Image" class="car-img me-2">
                        <?php endif; ?>
                        <span><?= htmlspecialchars($b['make'].' '.$b['model']) ?></span>
                    </div>
                </td>
                <td>
                    <div class="fw-semibold"><?= htmlspecialchars($b['first_name'].' '.$b['last_name']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($b['email']) ?></small>
                </td>
                <td>
                    <div class="small"><?= format_date($b['pickup_date']) ?> - <?= format_date($b['return_date']) ?></div>
                </td>
                <td class="fw-semibold">Rs.<?= number_format($b['total_amount'], 2) ?></td>
                <td><span class="badge bg-<?= $status_class ?>"><?= ucfirst($b['status']) ?></span></td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="<?= base_url('admin/booking-details.php?id='.$b['id']) ?>" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>
                        <?php if($b['status'] === 'pending'): ?>
                        <a href="<?= base_url('admin/manage-booking.php?action=approve&id='.$b['id']) ?>" class="btn btn-outline-success" onclick="return confirm('Approve booking #<?= $b['id'] ?>?');"><i class="bi bi-check"></i></a>
                        <a href="<?= base_url('admin/manage-booking.php?action=reject&id='.$b['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Reject booking #<?= $b['id'] ?>?');"><i class="bi bi-x"></i></a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($bookings)): ?>
                <tr><td colspan="7" class="text-center text-muted">No bookings found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
