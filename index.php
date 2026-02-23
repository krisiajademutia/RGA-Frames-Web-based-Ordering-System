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

    <!-- Header with scroll effect -->
    <header class="idx-hdr-container" id="mainHeader">
        <div class="idx-hdr-left">
            <a href="index.php" style="text-decoration: none; display: flex; align-items: center; gap: 0.8rem;">
                <div class="idx-hdr-logo">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="idx-hdr-brand">
                    <h1>RGA Frames</h1>
                    <p>Custom Framing & Printing</p>
                </div>
            </a>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="idx-mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Navigation -->
        <nav class="idx-hdr-nav" id="mainNav">
            <a href="#services" class="idx-hdr-nav-link idx-scroll-link">
                <i class="fas fa-concierge-bell"></i> Services
            </a>
            <a href="#features" class="idx-hdr-nav-link idx-scroll-link">
                <i class="fas fa-star"></i> Features
            </a>
            <a href="login.php" class="idx-hdr-nav-link idx-hdr-btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="register.php" class="idx-hdr-nav-link idx-hdr-btn-register">
                <i class="fas fa-user-plus"></i> Register
            </a>
        </nav>

        <!-- Mobile Menu Overlay -->
        <div class="idx-mobile-overlay" id="mobileOverlay"></div>
    </header>

    <!-- Hero Section with Animation -->
    <section class="idx-hero">
        <div class="idx-hero-overlay"></div>
        <div class="container">
            <div class="idx-hero-content">
                <h1 class="idx-hero-title animate-fade-in-up">
                    Turn Your Photos into Beautiful<br>Framed Art
                </h1>
                <p class="idx-hero-text animate-fade-in-up animation-delay-1">
                    We help you preserve your favorite memories with high-quality printing and custom framing — easy and made just for you.
                </p>
                <div class="idx-hero-buttons animate-fade-in-up animation-delay-2">
                    <a href="login.php" class="btn idx-btn-cta">
                        <i class="fas fa-shopping-cart me-2"></i> Start Your Order
                    </a>
                    <a href="#services" class="btn idx-btn-secondary idx-scroll-link">
                        <i class="fas fa-info-circle me-2"></i> Learn More
                    </a>
                </div>
            </div>
            
            <!-- Scroll indicator -->
            <div class="idx-scroll-indicator animate-bounce">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </section>

    <!-- Trust Indicators Section -->
    <section class="idx-trust-section">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="idx-trust-item">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Quality Guaranteed</h4>
                        <p>Professional printing and framing with attention to detail</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="idx-trust-item">
                        <i class="fas fa-clock"></i>
                        <h4>Fast Turnaround</h4>
                        <p>Quick processing without compromising quality</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="idx-trust-item">
                        <i class="fas fa-hand-holding-heart"></i>
                        <h4>Personal Service</h4>
                        <p>Direct communication with the owners for your peace of mind</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5" id="services">
        <div class="container">
            <div class="idx-section-header">
                <span class="idx-section-subtitle">What We Offer</span>
                <h2 class="idx-section-title">Our Services</h2>
                <p class="idx-section-description">Professional framing and printing services tailored to your needs</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card idx-service-card">
                        <div class="idx-service-icon-wrapper">
                            <div class="idx-service-icon">
                                <i class="fas fa-print"></i>
                            </div>
                        </div>
                        <h3 class="idx-card-title">Photo Printing</h3>
                        <p class="idx-card-text">Clear, long-lasting prints on canvas or photo paper — perfect for your special pictures.</p>
                        <a href="login.php" class="idx-service-link">
                            Get Started <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card idx-service-card">
                        <div class="idx-service-icon-wrapper">
                            <div class="idx-service-icon">
                                <i class="fas fa-image"></i>
                            </div>
                        </div>
                        <h3 class="idx-card-title">Custom Frames</h3>
                        <p class="idx-card-text">Choose size, color, and style — we make the frame exactly the way you want.</p>
                        <a href="login.php" class="idx-service-link">
                            Get Started <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card idx-service-card">
                        <div class="idx-service-icon-wrapper">
                            <div class="idx-service-icon">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <h3 class="idx-card-title">Ready-Made Frames</h3>
                        <p class="idx-card-text">Beautiful frames already prepared — ready to hang on your wall today.</p>
                        <a href="login.php" class="idx-service-link">
                            Get Started <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card idx-service-card">
                        <div class="idx-service-icon-wrapper">
                            <div class="idx-service-icon">
                                <i class="fas fa-shopping-basket"></i>
                            </div>
                        </div>
                        <h3 class="idx-card-title">Print + Frame Combo</h3>
                        <p class="idx-card-text">We print your photo and put it in a nice frame — ready to display or give as a gift.</p>
                        <a href="login.php" class="idx-service-link">
                            Get Started <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 idx-bg-white" id="features">
        <div class="container">
            <div class="idx-section-header">
                <span class="idx-section-subtitle">Why Choose Us</span>
                <h2 class="idx-section-title">Everything You Need</h2>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card idx-feature-card">
                        <div class="idx-feature-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h3 class="idx-card-title">You Can Choose</h3>
                        <ul class="idx-list-unstyled">
                            <li><i class="fas fa-check-circle me-2"></i>Different frame sizes</li>
                            <li><i class="fas fa-check-circle me-2"></i>Wall hanging or standing frame</li>
                            <li><i class="fas fa-check-circle me-2"></i>Canvas or photo paper</li>
                            <li><i class="fas fa-check-circle me-2"></i>Upload your own photo</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card idx-feature-card">
                        <div class="idx-feature-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h3 class="idx-card-title">Big Orders</h3>
                        <ul class="idx-list-unstyled">
                            <li><i class="fas fa-check-circle me-2"></i>30+ frames = special price</li>
                            <li><i class="fas fa-check-circle me-2"></i>20% discount per frame</li>
                            <li><i class="fas fa-check-circle me-2"></i>Free delivery by us (owners)</li>
                            <li><i class="fas fa-check-circle me-2"></i>100% quality guarantee</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card idx-feature-card">
                        <div class="idx-feature-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3 class="idx-card-title">How to Pay</h3>
                        <p class="idx-card-text mb-3">Pick what is easiest for you:</p>
                        <div class="idx-payment-badge">
                            <i class="fas fa-money-bill-wave me-2"></i>Cash – pay on pickup/delivery
                        </div>
                        <div class="idx-payment-badge">
                            <i class="fas fa-mobile-alt me-2"></i>GCash – 50% down payment
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="idx-card idx-feature-card">
                        <div class="idx-feature-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3 class="idx-card-title">Delivery</h3>
                        <p class="idx-card-text">
                            Order 30+ frames → <strong>free delivery</strong> straight to your home by the owners.<br><br>
                            Smaller orders → ready for pickup.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="idx-cta-section">
        <div class="container">
            <div class="idx-cta-content">
                <h2 class="idx-cta-title">Ready to Frame Your Memories?</h2>
                <p class="idx-cta-text">Join hundreds of satisfied customers and start creating beautiful framed art today.</p>
                <a href="register.php" class="btn idx-btn-cta-large">
                    <i class="fas fa-user-plus me-2"></i> Create Your Account
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="idx-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="idx-footer-brand">
                        <div class="idx-hdr-logo mb-3">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3>RGA Frames</h3>
                        <p>Custom Framing & Printing</p>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-2">&copy; 2024 RGA Frames. All rights reserved.</p>
                    <p class="text-muted">Preserving your memories with quality and care.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/animations.js"></script>
</body>
</html>