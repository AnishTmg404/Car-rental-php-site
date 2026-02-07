<?php
require_once __DIR__ . '/url.php';
?>
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-car-front-fill fs-3 text-primary me-2"></i>
                        <h5 class="fw-bold mb-0">CarRental</h5>
                    </div>
                    <p class="text-light">Your trusted partner for premium car rental services. Experience luxury and comfort with our exclusive fleet of vehicles.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-twitter fs-5"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-linkedin fs-5"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h6 class="fw-semibold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= base_url('index.php') ?>" class="text-light text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?= base_url('cars.php') ?>" class="text-light text-decoration-none">Cars</a></li>
                        <?php if (is_logged_in()): ?>
                            <li class="mb-2"><a href="<?= base_url('dashboard.php') ?>" class="text-light text-decoration-none">Dashboard</a></li>
                            <li class="mb-2"><a href="<?= base_url('my-bookings.php') ?>" class="text-light text-decoration-none">My Bookings</a></li>
                        <?php else: ?>
                            <li class="mb-2"><a href="<?= base_url('auth/login.php') ?>" class="text-light text-decoration-none">Login</a></li>
                            <li class="mb-2"><a href="<?= base_url('auth/register.php') ?>" class="text-light text-decoration-none">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-semibold mb-3">Services</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Economy Cars</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Luxury Cars</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">SUV Rentals</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sports Cars</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-semibold mb-3">Contact Info</h6>
                    <div class="mb-3">
                        <i class="bi bi-telephone text-primary me-2"></i>
                        <span>+977 1234567890 </span>
                    </div>
                    <div class="mb-3">
                        <i class="bi bi-envelope text-primary me-2"></i>
                        <span>info@carrental.com</span>
                    </div>
                    <div class="mb-3">
                        <i class="bi bi-geo-alt text-primary me-2"></i>
                        <span>Taulung,Budhanilkantha-1,Kathmandu</span>
                    </div>
                    <div class="mb-3">
                        <i class="bi bi-clock text-primary me-2"></i>
                        <span>24/7 Support Available</span>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-light">&copy; <?= date('Y') ?> CarRental. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex justify-content-md-end gap-3">
                        <a href="#" class="text-light text-decoration-none small">Privacy Policy</a>
                        <a href="#" class="text-light text-decoration-none small">Terms of Service</a>
                        <a href="#" class="text-light text-decoration-none small">FAQ</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary position-fixed bottom-0 end-0 m-4 rounded-circle" style="display: none; z-index: 1000;" onclick="scrollToTop()">
        <i class="bi bi-arrow-up"></i>
    </button>

     <!-- JS: Bootstrap bundle (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>