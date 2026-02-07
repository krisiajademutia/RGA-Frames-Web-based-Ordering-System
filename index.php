<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RGA Frames - Custom Framing & Printing</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Georgia:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="idx-body">

    <nav class="navbar navbar-expand-lg fixed-top idx-navbar">
        <div class="container">
            <a class="navbar-brand idx-brand" href="index.php">
                <i class="fas fa-box-open"></i> RGA Frames
            </a>
            <button class="navbar-toggler idx-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center gap-4">
                    <li class="nav-item">
                        <a class="nav-link btn idx-btn-outline-brown px-4 py-2" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn idx-btn-outline-brown px-4 py-2" href="register.php">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="idx-hero">
        <div class="container">
            <h1 class="idx-hero-title">Turn Your Photos into Beautiful<br>Framed Art</h1>
            <p class="idx-hero-text">We help you preserve your favorite memories with high-quality printing and custom framing — easy and made just for you.</p>
            <a href="login.php" class="btn idx-btn-cta">
                <i class="fas fa-shopping-cart me-2"></i> Start Your Order
            </a>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h2 class="idx-section-title">Our Services</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card text-center">
                        <div class="idx-service-icon"><i class="fas fa-print"></i></div>
                        <h3 class="idx-card-title">Photo Printing</h3>
                        <p class="idx-card-text">Clear, long-lasting prints on canvas or photo paper — perfect for your special pictures.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card text-center">
                        <div class="idx-service-icon"><i class="fas fa-image"></i></div>
                        <h3 class="idx-card-title">Custom Frames</h3>
                        <p class="idx-card-text">Choose size, color, and style — we make the frame exactly the way you want.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card text-center">
                        <div class="idx-service-icon"><i class="fas fa-box"></i></div>
                        <h3 class="idx-card-title">Ready-Made Frames</h3>
                        <p class="idx-card-text">Beautiful frames already prepared — ready to hang on your wall today.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card text-center">
                        <div class="idx-service-icon"><i class="fas fa-shopping-basket"></i></div>
                        <h3 class="idx-card-title">Print + Frame Combo</h3>
                        <p class="idx-card-text">We print your photo and put it in a nice frame — ready to display or give as a gift.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 idx-bg-white">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card">
                        <h3 class="idx-card-title"><i class="fas fa-palette me-2"></i> You Can Choose</h3>
                        <ul class="idx-list-unstyled">
                            <li>Different frame sizes</li>
                            <li>Wall hanging or standing frame</li>
                            <li>Canvas or photo paper</li>
                            <li>Upload your own photo</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card">
                        <h3 class="idx-card-title"><i class="fas fa-boxes me-2"></i> Big Orders</h3>
                        <ul class="idx-list-unstyled">
                            <li>30+ frames = special price</li>
                            <li>20% discount per frame</li>
                            <li>Free delivery by us (owners)</li>
                            <li>100% quality guarantee</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card">
                        <h3 class="idx-card-title"><i class="fas fa-credit-card me-2"></i> How to Pay</h3>
                        <p class="idx-card-text">Pick what is easiest for you:</p>
                        <div class="idx-payment-badge">Cash – pay on pickup/delivery</div>
                        <div class="idx-payment-badge">GCash – 50% down payment</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card">
                        <h3 class="idx-card-title"><i class="fas fa-truck me-2"></i> Delivery</h3>
                        <p class="idx-card-text">
                            Order 30+ frames → <strong>free delivery</strong> straight to your home by the owners.<br>
                            Smaller orders → ready for pickup.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>