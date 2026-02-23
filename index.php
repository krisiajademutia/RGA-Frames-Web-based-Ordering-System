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
            <a href="index.php" style="text-decoration: none; display: flex; align-items: center; gap: 0.3rem;">
                <div class="idx-hdr-logo">
                    <svg  xmlns="http://www.w3.org/2000/svg" width="24" height="24"  
fill="#004030" viewBox="2 2 20 20" >
<!--Boxicons v3.0.8 https://boxicons.com | License  https://docs.boxicons.com/free-->
<path d="M3 16c0 .34.18.67.47.85l8 5a1.01 1.01 0 0 0 1.06 0l8-5c.29-.18.47-.5.47-.85V8c0-.34-.18-.67-.47-.85l-8-5c-.32-.2-.74-.2-1.06 0l-8 5c-.29.18-.47.5-.47.85zm2-6.53 6 3.6v6.13l-6-3.75zm8 9.73v-6.13l6-3.6v5.98zM12 4.18l5.84 3.65-5.84 3.5-5.84-3.5z"></path>
</svg>
                </div>
                <div class="idx-hdr-brand">
                    <h1>RGA Frames</h1>
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
            <a href="login.php" class="idx-hdr-nav-link idx-hdr-btn-login">
               Login
            </a>
            <a href="register.php" class="idx-hdr-nav-link idx-hdr-btn-register">
             Register
            </a>
        </nav>

        <!-- Mobile Menu Overlay -->
        <div class="idx-mobile-overlay" id="mobileOverlay"></div>
    </header>

    <!-- Hero Section with Animation -->
    <section class="idx-hero">
        <div class="container">
            <div class="idx-hero-content">
                <h1 class="idx-hero-title animate-fade-in-up">
                    Transform Your Memories Into Art
                </h1>
                <p class="idx-hero-text animate-fade-in-up animation-delay-1">
                   Professional framing and large printing services with custom<br> designs tailored to your style.
                </p>
                <div class="idx-hero-buttons animate-fade-in-up animation-delay-2">
                    <a href="login.php" class="btn idx-btn-cta">
                        <i class="fas fa-shopping-cart me-2"></i> Order Now
                    </a>
                </div>
            </div>
            
        </div>
    </section>

   <section class="idx-gallery-section">
    <div class="container">
        <div class="idx-gallery-wrapper">
            <div class="idx-gallery-item item-1">
                <img src="assets/img/img_1.png" alt="Gallery Image">
            </div>
            <div class="idx-gallery-item item-2">
                <img src="assets/img/img_2.png" alt="Gallery Image">
            </div>
            <div class="idx-gallery-item item-3">
                <img src="assets/img/img_1.png" alt="Gallery Image">
            </div>
            <div class="idx-gallery-item item-4">
                <img src="assets/img/img_2.png" alt="Gallery Image">
            </div>
        </div>
    </div>
</section>

    <section class="idx-services-section" id="services">
    <div class="container">
        <div class="idx-section-header">
            <h2 class="idx-section-title">Our Services</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="idx-card idx-service-card">
                    <div class="idx-service-icon">
                        <i class="fas fa-print"></i>
                    </div>
                    <h3 class="idx-card-title">Printing Services</h3>
                    <p class="idx-card-text">High-quality printing on canvas or photo paper for your cherished images.</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="idx-card idx-service-card">
                    <div class="idx-service-icon">
                        <i class="fas fa-image"></i>
                    </div>
                    <h3 class="idx-card-title">Custom Framing</h3>
                    <p class="idx-card-text">Design your own frame with custom sizes, materials, and mounting options.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="idx-card idx-service-card">
                    <div class="idx-service-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3 class="idx-card-title">Ready-Made Frames</h3>
                    <p class="idx-card-text">Beautiful ready made frames in various sizes, ready for immediate use.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="idx-card idx-service-card">
                    <div class="idx-service-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3 class="idx-card-title">Print + Frame Packages</h3>
                    <p class="idx-card-text">Complete solution combining professional printing with elegant framing.</p>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Features Section -->
    <section class="idx-feature-gallery-section" id="features">
    <div class="container">
        <div class="idx-main-grid">
            
            <div class="idx-gallery-side">
                <div class="idx-stacked-wrapper">
                    <div class="idx-stack-item item-top-left">
                        <img src="assets/img/img_1.png" alt="Custom Frame 1">
                    </div>
                    <div class="idx-stack-item item-center">
                        <img src="assets/img/img_2.png" alt="Custom Frame 2">
                    </div>
                    <div class="idx-stack-item item-bottom-right">
                        <img src="assets/img/img_3.png" alt="Custom Frame 3">
                    </div>
                </div>
            </div>

            <div class="idx-content-side">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="idx-info-card">
                            <div class="idx-card-head">
                                <i class="fas fa-cog"></i>
                                <h3>Customization Options</h3>
                            </div>
                            <ul class="idx-card-list">
                                <li>Choose from various frame sizes and designs</li>
                                <li>Select mount type: Wall hanging or with stand</li>
                                <li>Pick paper quality: Canvas or Photo paper</li>
                                <li>Upload your own images for printing</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="idx-info-card">
                            <div class="idx-card-head">
                                <i class="fas fa-layer-group"></i>
                                <h3>Bulk Order Benefits</h3>
                            </div>
                            <ul class="idx-card-list">
                                <li>Order 30+ frames for special pricing</li>
                                <li>Get 20% discount on each frame</li>
                                <li>Free personal delivery by the owners</li>
                                <li>Quality assurance guaranteed</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="idx-info-card">
                            <div class="idx-card-head">
                                <i class="fas fa-credit-card"></i>
                                <h3>Payment Options</h3>
                            </div>
                            <p class="idx-card-intro">Choose the payment method that works best for you:</p>
                            <ul class="idx-card-list">
                                <li>Cash Payment (On Pickup / Delivery)</li>
                                <li>GCash (50% Upfront Payment)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="idx-info-card">
                            <div class="idx-card-head">
                                <i class="fas fa-truck"></i>
                                <h3>Delivery Information</h3>
                            </div>
                            <p class="idx-card-intro">
                                For bulk orders of 30+ frames, enjoy free personal delivery handled by the business owners to ensure quality. Standard orders available for pickup.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

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