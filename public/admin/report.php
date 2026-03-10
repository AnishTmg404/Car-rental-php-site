<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../url.php';

// Require admin access
require_admin();

$page_title = 'Reports';
$current_user = get_current_user_data();

// Date range filter (optional)
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Revenue data per month (last 12 months)
$monthly_revenue_data = db_fetch_all("
    SELECT DATE_FORMAT(created_at,'%Y-%m') as month, SUM(total_amount) as total
    FROM bookings
    WHERE status IN ('approved','active','completed')
    GROUP BY month
    ORDER BY month ASC
");

// Booking status counts
$booking_status_counts = db_fetch_all("
    SELECT status, COUNT(*) as count
    FROM bookings
    GROUP BY status
");

// Top 5 most booked cars
$top_cars = db_fetch_all("
    SELECT c.make, c.model, COUNT(b.id) as total_bookings
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    GROUP BY b.car_id
    ORDER BY total_bookings DESC
    LIMIT 5
");

// User stats
$new_users_this_month = db_fetch("
    SELECT COUNT(*) as count
    FROM users
    WHERE role='user' AND MONTH(created_at)=MONTH(CURRENT_DATE()) AND YEAR(created_at)=YEAR(CURRENT_DATE())
")['count'] ?? 0;

$total_users = db_fetch("SELECT COUNT(*) as count FROM users WHERE role='user'")['count'];

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-card {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            color: #fff;
        }

        .chart-container {
            height: 300px;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="py-4">
        <div class="container-fluid">

            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1">Reports</h1>
                        <p class="text-muted mb-0">Analytics and statistics of your car rental system</p>
                    </div>
                    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <h6>Total Users</h6>
                            <h3><?= $total_users ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card h-100" style="background: linear-gradient(135deg, #28a745, #20c997);">
                        <div class="card-body">
                            <h6>New Users This Month</h6>
                            <h3><?= $new_users_this_month ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card h-100" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                        <div class="card-body">
                            <h6>Total Bookings</h6>
                            <h3><?= array_sum(array_column($booking_status_counts, 'count')) ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Chart -->
            <div class="row g-4 mb-4">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Monthly Revenue</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" class="chart-container"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Booking Status Chart -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Booking Status Overview</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="bookingStatusChart" class="chart-container"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Cars -->
            <div class="row g-4">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Top 5 Most Booked Cars</h5>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Car</th>
                                        <th>Total Bookings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_cars as $car): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></td>
                                            <td><?= $car['total_bookings'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($monthly_revenue_data, 'month')) ?>,
                datasets: [{
                    label: 'Revenue (Rs.)',
                    data: <?= json_encode(array_map(fn($m) => floatval($m['total']), $monthly_revenue_data)) ?>,
                    backgroundColor: 'rgba(13,110,253,0.2)',
                    borderColor: 'rgba(13,110,253,1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Booking Status Pie
        const statusCtx = document.getElementById('bookingStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($booking_status_counts, 'status')) ?>,
                datasets: [{
                    data: <?= json_encode(array_map(fn($s) => intval($s['count']), $booking_status_counts)) ?>,
                    backgroundColor: [
                        '#ffc107', '#0d6efd', '#dc3545', '#20c997', '#6610f2', '#fd7e14'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>