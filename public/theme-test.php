<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Toggle Test - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        .test-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .test-card {
            max-width: 500px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card test-card">
                        <div class="card-header">
                            <h4 class="mb-0">
                                <i class="bi bi-palette me-2"></i>Theme Toggle Test
                            </h4>
                        </div>
                        <div class="card-body">
                            <p class="card-text">This page tests the theme toggle functionality.</p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span>Toggle Theme:</span>
                                <button class="btn btn-outline-secondary" id="themeToggle">
                                    🌙
                                </button>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Click the theme toggle button to switch between light and dark modes.
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <i class="bi bi-car-front fs-1"></i>
                                            <h6 class="mt-2">Primary Card</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <i class="bi bi-check-circle fs-1"></i>
                                            <h6 class="mt-2">Success Card</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <a href="index.php" class="btn btn-primary">
                                    <i class="bi bi-house me-2"></i>Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/theme-toggle.js"></script>
</body>
</html>
