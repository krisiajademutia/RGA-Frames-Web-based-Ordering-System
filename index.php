<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RGA Frames - Custom Framing & Printing</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Georgia:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --brand-brown:    #4a2c18;
            --brand-gold:     #c19a5f;
            --brand-cream:    #fffdf7;
            --text-dark:      #1a0f09;
            --text-muted:     #4a3c32;
        }

        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: var(--brand-cream);
            color: var(--text-dark);
            font-size: 1.125rem;
            line-height: 1.65;
            padding-top: 80px;              /* Desktop / tablet */
        }

        @media (max-width: 991px) {
            body {
                padding-top: 140px;         /* Mobile – clears taller collapsed navbar */
            }
        }

        @media (max-width: 576px) {
            body {
                padding-top: 130px;
            }
        }

        /* Thinner navbar */
        .navbar {
            padding: 0.6rem 0 !important;
            min-height: 60px;
            border-bottom: 3px solid var(--brand-gold);
            background-color: white !important;
        }

        .navbar-brand {
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--brand-brown);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .navbar-toggler {
            padding: 0.35rem 0.65rem;
            border: none;
        }

        .nav-link, .btn {
            font-size: 1.1rem;
            padding: 0.45rem 1rem;
        }

        .btn-outline-brown {
            border: 2px solid var(--brand-brown);
            color: var(--brand-brown);
            font-weight: 600;
        }

        .btn-outline-brown:hover {
            background-color: var(--brand-brown);
            color: white;
        }

        h1, h2, h3 {
            font-family: 'Georgia', serif;
            font-weight: 700;
            color: var(--brand-brown);
        }

        .hero {
            background-color: white;
            padding: 6rem 1rem 5rem;
            text-align: center;
            margin-top: 1rem;
        }

        .hero h1 {
            font-size: clamp(2.6rem, 7vw, 4.5rem);
            margin-bottom: 1.8rem;
        }

        .hero p {
            font-size: 1.35rem;
            max-width: 800px;
            margin: 0 auto 2.8rem;
            color: var(--text-muted);
        }

        .btn-cta {
            font-size: 1.4rem;
            padding: 1.1rem 2.5rem;
            background-color: var(--brand-gold);
            color: white;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(193,154,95,0.3);
        }

        .section-title {
            font-size: 2.6rem;
            text-align: center;
            margin: 4rem 0 3rem;
        }

        .card {
            border: 2px solid #e8d9c5;
            border-radius: 16px;
            padding: 2rem;
            background: white;
            height: 100%;
        }

        .card-title {
            font-size: 1.65rem;
            margin-bottom: 1.4rem;
        }

        .card-text, .card ul li {
            font-size: 1.15rem;
            color: var(--text-muted);
        }

        .service-icon {
            font-size: 3.8rem;
            color: var(--brand-gold);
            margin-bottom: 1.5rem;
        }

        .list-unstyled li {
            margin-bottom: 1.1rem;
            font-size: 1.15rem;
        }

        .list-unstyled li::before {
            content: "✔";
            color: var(--brand-gold);
            margin-right: 12px;
            font-size: 1.3rem;
        }

        .payment-badge {
            background-color: #f9f4ec;
            color: var(--brand-brown);
            font-size: 1.15rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin: 0.8rem 0;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .hero {
                padding: 4.5rem 1rem 3.5rem;
            }
            .section-title {
                font-size: 2.2rem;
            }
            .btn {
                width: 100%;
                margin-bottom: 1.2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Thinner Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-box-open"></i>
                RGA Frames
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center gap-4">
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-brown px-4 py-2" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-brown px-4 py-2" href="register.php">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
        <div class="container">
            <h1>Turn Your Photos into Beautiful<br>Framed Art</h1>
            <p>We help you preserve your favorite memories with high-quality printing and custom framing — easy and made just for you.</p>
            <a href="login.php" class="btn btn-cta">
                <i class="fas fa-shopping-cart me-2"></i> Start Your Order
            </a>
        </div>
    </section>

    <!-- Services -->
    <section class="py-5">
        <div class="container">
            <h2 class="section-title">Our Services</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card text-center">
                        <div class="service-icon"><i class="fas fa-print"></i></div>
                        <h3 class="card-title">Photo Printing</h3>
                        <p class="card-text">Clear, long-lasting prints on canvas or photo paper — perfect for your special pictures.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card text-center">
                        <div class="service-icon"><i class="fas fa-image"></i></div>
                        <h3 class="card-title">Custom Frames</h3>
                        <p class="card-text">Choose size, color, and style — we make the frame exactly the way you want.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card text-center">
                        <div class="service-icon"><i class="fas fa-box"></i></div>
                        <h3 class="card-title">Ready-Made Frames</h3>
                        <p class="card-text">Beautiful frames already prepared — ready to hang on your wall today.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card text-center">
                        <div class="service-icon"><i class="fas fa-shopping-basket"></i></div>
                        <h3 class="card-title">Print + Frame Combo</h3>
                        <p class="card-text">We print your photo and put it in a nice frame — ready to display or give as a gift.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Info -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <h3 class="card-title"><i class="fas fa-palette me-2"></i> You Can Choose</h3>
                        <ul class="list-unstyled">
                            <li>Different frame sizes</li>
                            <li>Wall hanging or standing frame</li>
                            <li>Canvas or photo paper</li>
                            <li>Upload your own photo</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <h3 class="card-title"><i class="fas fa-boxes me-2"></i> Big Orders</h3>
                        <ul class="list-unstyled">
                            <li>30+ frames = special price</li>
                            <li>20% discount per frame</li>
                            <li>Free delivery by us (owners)</li>
                            <li>100% quality guarantee</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <h3 class="card-title"><i class="fas fa-credit-card me-2"></i> How to Pay</h3>
                        <p>Pick what is easiest for you:</p>
                        <div class="payment-badge">Cash – pay when you pick up or when we deliver</div>
                        <div class="payment-badge">GCash – 50% down payment</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <h3 class="card-title"><i class="fas fa-truck me-2"></i> Delivery</h3>
                        <p>
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