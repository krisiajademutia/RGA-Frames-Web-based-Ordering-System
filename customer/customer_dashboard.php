<?php
session_start();
include __DIR__ . '/../config/db_connect.php';

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch User Name
$user_id = $_SESSION['user_id'];
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --forest-green: #004236;
            --soft-white: #F9F9F9;
            --text-dark: #1F2937;
            --accent-gold: #C19A5F;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: white;
            color: var(--text-dark);
            margin: 0;
            padding-top: 80px;
        }

        .hero-banner {
            position: relative;
            height: 450px;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), 
                        url('../assets/img/frame_index2.jpg'); /* Using your existing asset */
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            margin-bottom: 4rem;
        }

        .hero-content h1 {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }

        .hero-content p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3.5rem;
        }

        .section-header h2 {
            font-weight: 700;
            color: var(--forest-green);
            font-size: 2.2rem;
            position: relative;
            display: inline-block;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--accent-gold);
        }

        .services-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
        }

        .figma-card {
            text-decoration: none;
            color: inherit;
        }

        .card-img-wrapper {
            width: 100%;
            height: 280px;
            border-radius: 24px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            position: relative;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .figma-card:hover .card-img-wrapper {
            transform: scale(1.03);
        }

        .card-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-info h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--forest-green);
        }

        .card-info p {
            color: #6B7280;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .pill-btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 28px;
            background-color: var(--forest-green);
            color: white;
            border-radius: 100px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .figma-card:hover .pill-btn {
            background-color: var(--accent-gold);
            padding-right: 35px;
        }

        .payment-banner {
            background-color: #F3F4F6;
            padding: 4rem 0;
            margin-top: 6rem;
        }

        .payment-flex {
            display: flex;
            justify-content: center;
            gap: 4rem;
            flex-wrap: wrap;
        }

        .payment-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .payment-icon {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--forest-green);
            font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .payment-text strong {
            display: block;
            font-size: 1.1rem;
        }

        .payment-text span {
            font-size: 0.9rem;
            color: #6B7280;
        }
    </style>
</head>
<body>

    <?php include '../includes/customer_header.php'; ?>

    <section class="hero-banner">
        <div class="hero-content">
            <h1>Hello, <?php echo htmlspecialchars($first_name); ?>!</h1>
            <p>Time to transform your memories into art.</p>
        </div>
    </section>

    <div class="container mb-5">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 text-center">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
    </div>

    <section class="section-header">
        <h2>What are you looking for?</h2>
    </section>

    <section class="services-container">
        <a href="customer_shop_readymade.php" class="figma-card">
            <div class="card-img-wrapper">
                <img src="../assets/img/frame_index.png" alt="Ready-made">
            </div>
            <div class="card-info text-center">
                <h3>Ready-Made Frames</h3>
                <p>Beautiful ready made frames in various sizes, ready for immediate use.</p>
                <div class="pill-btn">Shop Now <i class="fas fa-arrow-right ms-2"></i></div>
            </div>
        </a>

        <a href="customer_shop_custom.php" class="figma-card">
            <div class="card-img-wrapper">
                <img src="../assets/img/frame_index3.jpg" alt="Custom">
            </div>
            <div class="card-info text-center">
                <h3>Custom Framing</h3>
                <p>Design your own frame with custom sizes, materials, and mounting options. </p>
                <div class="pill-btn">Start Designing <i class="fas fa-arrow-right ms-2"></i></div>
            </div>
        </a>

        <a href="customer_shop_printing.php" class="figma-card">
            <div class="card-img-wrapper">
                <img src="../assets/img/frames.png" alt="Printing">
            </div>
            <div class="card-info text-center">
                <h3>Printing Services</h3>
                <p>High-quality printing on canvas or photo paper for your cherished images.</p>
                <div class="pill-btn">Upload Photos <i class="fas fa-arrow-right ms-2"></i></div>
            </div>
        </a>
    </section>

    <section class="payment-banner">
        <div class="container">
            <div class="payment-flex">
                <div class="payment-item">
                    <div class="payment-icon"><i class="fas fa-truck"></i></div>
                    <div class="payment-text">
                        <strong>COD Available</strong>
                        <span>Pay upon pickup or delivery</span>
                    </div>
                </div>
                <div class="payment-item">
                    <div class="payment-icon"><i class="fas fa-mobile-screen-button"></i></div>
                    <div class="payment-text">
                        <strong>GCash Payments</strong>
                        <span>Secure 50% upfront payment</span>
                    </div>
                </div>
                <div class="payment-item">
                    <div class="payment-icon"><i class="fas fa-shield-check"></i></div>
                    <div class="payment-text">
                        <strong>Quality Check</strong>
                        <span>100% satisfaction guaranteed</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/idx_footer.php'; ?>

</body>
</html>