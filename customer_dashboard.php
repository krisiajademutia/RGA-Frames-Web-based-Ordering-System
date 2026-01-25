<?php
session_start();
include 'db_connect.php'; // Ensure this file exists and connects to your DB

// 1. Security Check: Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Fetch User Name for the "Welcome" message
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name FROM users WHERE user_id = '$user_id'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $first_name = $user['first_name'];
} else {
    $first_name = "Customer";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RGA Frames</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* --- RGA FRAMES COLOR PALETTE --- */
        :root {
            --primary-brown: #795338;  /* Deep Wood Brown */
            --accent-gold: #B89655;    /* Metallic Gold */
            --fresh-green: #A7C957;    /* Nature Green */
            --bg-cream: #FFFBF0;       /* Light Cream Background */
            --text-dark: #333333;
            --text-muted: #666666;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-cream);
            color: var(--text-dark);
            margin: 0;
            padding-top: 80px; /* Space for your fixed Navbar */
        }

        /* CONTAINER */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* HERO SECTION */
        .hero-section {
            text-align: center;
            padding: 50px 20px 40px;
        }

        .hero-title {
            color: var(--primary-brown);
            font-size: 2.2rem;
            font-weight: 800;
            margin: 0 0 10px 0;
        }

        .hero-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            max-width: 600px;
            margin: 0 auto 25px;
            line-height: 1.5;
        }

        .welcome-msg {
            display: inline-block;
            color: #d97706; /* Darker orange/brown text */
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 40px;
        }

        /* SECTION HEADERS */
        .section-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .section-header h2 {
            color: var(--primary-brown);
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* SERVICES GRID (3 Cards) */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 60px;
        }

        .service-card {
            background: white;
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            color: inherit;
            border: 2px solid transparent;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
            border-color: var(--accent-gold);
        }

        .icon-box {
            width: 70px;
            height: 70px;
            background-color: #ff9f1c; /* Orange Background */
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
        }
        
        /* Custom Colors for Icons if desired */
        .icon-ready { background-color: #ff9f1c; } /* Orange */
        .icon-custom { background-color: #e65100; } /* Darker Orange */
        .icon-print { background-color: #f59e0b; } /* Gold/Yellow */

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #4a2c1d; /* Dark Brown */
            margin-bottom: 12px;
        }

        .card-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* INFO & BULK SECTION (2 Columns) */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .info-box {
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #eee;
        }

        .box-custom {
            background-color: #FFF8E7; /* Light Yellow/Gold tint */
            border-left: 5px solid #FFCA28;
        }

        .box-bulk {
            background-color: #F0FDF4; /* Light Green tint */
            border-left: 5px solid var(--fresh-green);
        }

        .box-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .text-brown { color: var(--primary-brown); }
        .text-green { color: #15803d; } /* Dark Green */

        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .info-list li {
            position: relative;
            padding-left: 20px;
            margin-bottom: 10px;
            color: #555;
            font-size: 0.95rem;
        }

        .info-list li::before {
            content: "•";
            color: var(--accent-gold);
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        /* PAYMENT METHODS SECTION */
        .payment-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            border: 1px solid #e5e7eb;
            margin-bottom: 60px;
        }

        .payment-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-brown);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-badges {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .pay-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .pay-cod {
            background-color: #f3f4f6;
            color: #1f2937;
            border: 1px solid #d1d5db;
        }
        
        .pay-gcash {
            background-color: #eff6ff;
            color: #1e40af; /* GCash Blue */
            border: 1px solid #bfdbfe;
        }

    </style>
</head>
<body>

    <?php include 'customer_header.php'; ?>


    <div class="hero-section">
        <h1 class="hero-title">Transform Your Memories into Art</h1>
        <p class="hero-subtitle">Professional framing and large printing services with custom designs tailored to your style</p>
        <div class="welcome-msg">Welcome, <?php echo htmlspecialchars($first_name); ?>!</div>
    </div>

    <div class="container">
        
        <div class="section-header">
            <h2>Our Services</h2>
        </div>

        <div class="services-grid">
            
            <a href="customer_shop_readymade.php" class="service-card">
                <div class="icon-box icon-ready">
                    <i class="fas fa-box"></i>
                </div>
                <h3 class="card-title">Ready-Made Frames</h3>
                <p class="card-desc">Beautiful pre-designed frames in various sizes, ready for immediate use.</p>
            </a>

            <a href="customer_shop_custom.php" class="service-card">
                <div class="icon-box icon-custom">
                    <i class="fas fa-crop-alt"></i>
                </div>
                <h3 class="card-title">Custom Framing</h3>
                <p class="card-desc">Design your own frame with custom sizes, materials, and mounting options.</p>
            </a>

            <a href="customer_shop_printing.php" class="service-card">
                <div class="icon-box icon-print">
                    <i class="fas fa-print"></i>
                </div>
                <h3 class="card-title">Printing Services</h3>
                <p class="card-desc">High-quality printing on canvas or photo paper for your cherished images.</p>
            </a>

        </div>

        <div class="info-grid">
            
            <div class="info-box box-custom">
                <div class="box-title text-brown">
                    <i class="fas fa-palette"></i> Customization Options
                </div>
                <ul class="info-list">
                    <li>Choose from various frame sizes and designs</li>
                    <li>Select mount type: Wall hanging or with stand</li>
                    <li>Pick paper quality: Canvas or photo paper</li>
                    <li>Upload your own images for printing</li>
                </ul>
            </div>

            <div class="info-box box-bulk">
                <div class="box-title text-green">
                    <i class="fas fa-boxes"></i> Bulk Order Benefits
                </div>
                <ul class="info-list">
                    <li>Order 30+ frames for special pricing</li>
                    <li>Get 20% discount on each frame</li>
                    <li>Free personal delivery by owners</li>
                    <li>Quality assurance guaranteed</li>
                </ul>
            </div>

        </div>

        <div class="payment-section">
            <div class="payment-title">
                <i class="fas fa-wallet"></i> Payment Methods
            </div>
            <p style="color: #666; font-size: 0.95rem;">We accept the following payment options:</p>
            
            <div class="payment-badges">
                <div class="pay-badge pay-cod">
                    <i class="fas fa-money-bill-wave" style="color: #10b981;"></i> Cash on Delivery
                </div>
                <div class="pay-badge pay-gcash">
                    <i class="fas fa-mobile-alt" style="color: #2563eb;"></i> GCash
                </div>
            </div>
        </div>

    </div>

</body>
</html>