<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/url.php';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top border-bottom" id="navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= base_url('index.php') ?>">
            <i class="bi bi-car-front fs-4 text-primary me-2"></i>
            <span class="fw-semibold fs-5">CarRental</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto">
                <a class="nav-link <?= route_match('/') ? 'active' : '' ?>" href="<?= base_url('index.php') ?>">Home</a>
                <a class="nav-link <?= route_match('/cars') ? 'active' : '' ?>" href="<?= base_url('cars.php') ?>">Cars</a>
                <?php if (is_logged_in()): ?>
                    <?php if (is_admin()): ?>
                        <a class="nav-link <?= route_match('/admin/dashboard') ? 'active' : '' ?>" href="<?= base_url('admin/dashboard.php') ?>">Dashboard</a>
                    <?php else: ?>
                        <a class="nav-link <?= route_match('/dashboard') ? 'active' : '' ?>" href="<?= base_url('dashboard.php') ?>">Dashboard</a>
                    <?php endif; ?>
                <?php endif; ?>

            </div>

            <div class="d-flex align-items-center ms-3">
                <?php if (is_logged_in()): ?>
                    <!-- Admin User -->
                    <?php if (is_admin()): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-shield-lock me-1"></i>
                                <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= base_url('admin/dashboard.php') ?>">
                                    <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="<?= base_url('admin/manage-cars.php') ?>">
                                    <i class="bi bi-car-front-fill me-2"></i>Manage Cars
                                </a></li>
                                <li><a class="dropdown-item" href="<?= base_url('admin/add-car.php') ?>">
                                    <i class="bi bi-plus-circle me-2"></i>Add Car
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= base_url('auth/logout.php') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                    <!-- Regular User -->
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= base_url('profile.php') ?>">
                                    <i class="bi bi-person me-2"></i>Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?= base_url('my-bookings.php') ?>">
                                    <i class="bi bi-calendar-check me-2"></i>My Bookings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= base_url('auth/logout.php') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Login Button -->
                    <div id="authButtons">
                        <a href="<?= base_url('auth/login.php') ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <!-- <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Test
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Action 1</a></li>
                    <li><a class="dropdown-item" href="#">Action 2</a></li>
                </ul> -->
            </div>
        </div>
    </div>
</nav>
