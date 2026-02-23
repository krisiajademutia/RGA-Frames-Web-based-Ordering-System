<?php
session_start();
include __DIR__ . '/../config/db_connect.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); // Go back to main folder for login
    exit();
}

// 2. Fetch User Name
$user_id = $_SESSION['user_id'];

// 🔴 FIX: Changed 'tbl_users' to 'tbl_customer' and 'user_id' to 'customer_id'
$sql = "SELECT first_name FROM tbl_customer WHERE customer_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $first_name = $user['first_name'];
} else {
    $first_name = "Customer";
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RGA Frames</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Georgia:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
            padding-top: 80px;
        }

        @media (max-width: 991px) {
            body {
                padding-top: 140px;
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

        .nav-link, .btn {
            font-size: 1.1rem;
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
            color: var(--brand-brown);
            font-weight: 700;
        }

        .hero-section {
            background: white;
            padding: 5rem 1rem 4rem;
            text-align: center;
            border-radius: 0 0 20px 20px;
            margin-bottom: 3rem;
        }

        .hero-title {
            font-size: clamp(2.5rem, 6vw, 4rem);
            margin-bottom: 1.2rem;
        }

        .welcome-msg {
            font-size: 1.4rem;
            color: var(--brand-brown);
            font-weight: 600;
            margin-bottom: 3rem;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.8rem;
            margin-bottom: 4rem;
        }

        .service-card {
            background: white;
            border: 2px solid #e8d9c5;
            border-radius: 16px;
            padding: 2.5rem 1.8rem;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .service-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
            border-color: var(--brand-gold);
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background-color: var(--brand-gold);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2.2rem;
        }

        .card-title {
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        .card-desc {
            font-size: 1.05rem;
            color: var(--text-muted);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 1.8rem;
        }

        .info-box {
            padding: 2rem;
            border-radius: 12px;
            border: 2px solid #e8d9c5;
            background: white;
        }

        .box-title {
            font-size: 1.35rem;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .info-list li {
            font-size: 1.05rem;
            margin-bottom: 0.9rem;
        }

        .payment-section {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            margin-top: 4rem;
            border: 2px solid #e8d9c5;
        }

        .payment-title {
            font-size: 1.35rem;
            margin-bottom: 1.2rem;
        }

        .payment-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pay-badge {
            font-size: 1.1rem;
            padding: 0.9rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
        }

        .pay-cod {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #86efac;
        }

        .pay-gcash {
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
    </style>
</head>
<body>

    <?php include '../includes/customer_header.php'; ?>

    <div class="container mt-5 pt-3">
        <?php
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                    ' . htmlspecialchars($_SESSION['success']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                    ' . htmlspecialchars($_SESSION['error']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            unset($_SESSION['error']);
        }
        ?>
    </div>

    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Welcome, <?php echo htmlspecialchars($first_name); ?>!</h1>
            <p class="welcome-msg">Let's create something beautiful with your memories today</p>
        </div>
    </section>

    <section class="container py-5">
        <h2 class="text-center mb-5" style="font-size: 2.2rem;">Our Services</h2>
        <div class="services-grid">
            
            <a href="customer_shop_readymade.php" class="service-card">
                <div class="icon-box"><i class="fas fa-box"></i></div>
                <h3 class="card-title">Ready-Made Frames</h3>
                <p class="card-desc">Beautiful pre-designed frames in various sizes — ready to hang today.</p>
            </a>

            <a href="customer_shop_custom.php" class="service-card">
                <div class="icon-box"><i class="fas fa-crop-alt"></i></div>
                <h3 class="card-title">Custom Framing</h3>
                <p class="card-desc">Create your own frame with custom sizes, colors, and materials.</p>
            </a>

            <a href="customer_shop_printing.php" class="service-card">
                <div class="icon-box"><i class="fas fa-print"></i></div>
                <h3 class="card-title">Printing Services</h3>
                <p class="card-desc">High-quality printing on canvas or photo paper — your photos come to life.</p>
            </a>
        </div>
    </section>

    <section class="container py-5">
        <div class="info-grid">
            <div class="info-box">
                <div class="box-title">
                    <i class="fas fa-palette"></i> Customization Options
                </div>
                <ul class="list-unstyled">
                    <li>Choose frame size and style</li>
                    <li>Wall hanging or with stand</li>
                    <li>Canvas or photo paper</li>
                    <li>Upload your own photos</li>
                </ul>
            </div>

            <div class="info-box">
                <div class="box-title">
                    <i class="fas fa-boxes"></i> Bulk Order Benefits
                </div>
                <ul class="list-unstyled">
                    <li>Special pricing for 30+ frames</li>
                    <li>20% discount per frame</li>
                    <li>Free delivery by us (owners)</li>
                    <li>Quality guaranteed</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="container payment-section">
        <h2 class="payment-title text-center mb-4">
            <i class="fas fa-wallet"></i> Payment Methods
        </h2>
        <p class="text-center mb-4" style="font-size: 1.1rem; color: var(--text-muted);">
            Choose the option that works best for you:
        </p>
        <div class="payment-badges justify-content-center">
            <div class="pay-badge pay-cod">
                <i class="fas fa-money-bill-wave"></i> Cash on Delivery / Pickup
            </div>
            <div class="pay-badge pay-gcash">
                <i class="fas fa-mobile-alt"></i> GCash (50% upfront)
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>