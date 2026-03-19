<?php
session_start();
include __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name FROM tbl_customer WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$first_name = ($result->num_rows > 0) ? $result->fetch_assoc()['first_name'] : "Customer";
$stmt->close();

// ── Reviews preview (3 most recent) ─────────────────────────
$previewReviews = $conn->query("
    SELECT r.rating, r.review_text,
           DATE_FORMAT(r.review_date_posted, '%M %d, %Y') AS review_date,
           c.first_name, c.last_name, c.customer_type
    FROM tbl_reviews r
    JOIN tbl_customer c ON r.customer_id = c.customer_id
    ORDER BY r.review_date_posted DESC
    LIMIT 3
")->fetch_all(MYSQLI_ASSOC);

$statsRow    = $conn->query("SELECT COUNT(*) AS total, AVG(rating) AS avg FROM tbl_reviews")->fetch_assoc();
$totalReviews = (int)$statsRow['total'];
$avgRating    = $totalReviews > 0 ? round((float)$statsRow['avg'], 1) : 0;
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

        /* ── Hero ── */
        .hero-banner {
            position: relative;
            height: 450px;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)),
                        url('../assets/img/frame_index2.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            margin-bottom: 4rem;
        }
        .hero-content h1 { font-size: clamp(2.5rem, 5vw, 3.5rem); font-weight: 700; margin-bottom: 10px; letter-spacing: -1px; }
        .hero-content p  { font-size: 1.2rem; opacity: 0.9; }

        /* ── Section header ── */
        .section-header { text-align: center; margin-bottom: 3.5rem; }
        .section-header h2 {
            font-weight: 700; color: var(--forest-green); font-size: 2.2rem;
            position: relative; display: inline-block;
        }
        .section-header h2::after {
            content: ''; position: absolute; bottom: -10px; left: 50%;
            transform: translateX(-50%); width: 50px; height: 3px;
            background: var(--accent-gold);
        }

        /* ── Service cards ── */
        .services-container {
            max-width: 1200px; margin: 0 auto; padding: 0 20px;
            display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2.5rem;
        }
        .figma-card { text-decoration: none; color: inherit; }
        .card-img-wrapper {
            width: 100%; height: 280px; border-radius: 24px; overflow: hidden;
            margin-bottom: 1.5rem; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .figma-card:hover .card-img-wrapper { transform: scale(1.03); }
        .card-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .card-info h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; color: var(--forest-green); }
        .card-info p  { color: #6B7280; line-height: 1.6; margin-bottom: 1.5rem; }
        .pill-btn {
            display: inline-flex; align-items: center; padding: 12px 28px;
            background-color: var(--forest-green); color: white; border-radius: 100px;
            font-weight: 600; font-size: 0.95rem; transition: all 0.3s ease;
        }
        .figma-card:hover .pill-btn { background-color: var(--accent-gold); padding-right: 35px; }

        /* ── Payment banner ── */
        .payment-banner { background-color: #F3F4F6; padding: 4rem 0; margin-top: 6rem; }
        .payment-flex   { display: flex; justify-content: center; gap: 4rem; flex-wrap: wrap; }
        .payment-item   { display: flex; align-items: center; gap: 1rem; }
        .payment-icon {
            width: 50px; height: 50px; background: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: var(--forest-green); font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .payment-text strong { display: block; font-size: 1.1rem; }
        .payment-text span   { font-size: 0.9rem; color: #6B7280; }

        /* ── Reviews preview section ── */
        .rv-preview-section {
            max-width: 1200px;
            margin: 0 auto 5rem;
            padding: 0 20px;
        }
        .rv-preview-topbar {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .rv-preview-heading {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--forest-green);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.55rem;
        }
        .rv-preview-avg {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.9rem;
            color: #6b7280;
            font-weight: 600;
        }
        .rv-preview-avg .rv-star-on { color: #f59e0b; }
        .rv-see-more {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1.2rem;
            border: 1.5px solid var(--forest-green);
            border-radius: 999px;
            font-size: 0.84rem;
            font-weight: 700;
            color: var(--forest-green);
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
        }
        .rv-see-more:hover { background: var(--forest-green); color: #fff; }
        .rv-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.1rem;
        }
        .rv-preview-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 1.1rem 1.25rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            transition: box-shadow 0.15s, transform 0.15s;
        }
        .rv-preview-card:hover {
            box-shadow: 0 6px 20px rgba(0,66,54,0.1);
            transform: translateY(-2px);
        }
        .rv-preview-card-top {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.65rem;
        }
        .rv-preview-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, #0f3d33, #2d7a60);
            color: #fff; font-size: 0.88rem; font-weight: 800;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .rv-preview-name { font-size: 0.85rem; font-weight: 700; color: #111827; }
        .rv-preview-stars { display: flex; gap: 0.15rem; margin-top: 1px; }
        .rv-star-on  { color: #f59e0b; }
        .rv-star-off { color: #d1d5db; }
        .rv-preview-rating-badge {
            margin-left: auto;
            display: inline-flex; align-items: center; gap: 0.25rem;
            padding: 0.2rem 0.6rem;
            border: 1.5px solid #fde68a; border-radius: 999px;
            font-size: 0.75rem; font-weight: 800;
            color: #92400e; background: #fef9c3; flex-shrink: 0;
        }
        .rv-preview-text {
            font-size: 0.875rem; color: #374151; line-height: 1.6;
            margin: 0 0 0.6rem;
            display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .rv-preview-date { font-size: 0.73rem; color: #9ca3af; display: flex; align-items: center; gap: 0.3rem; }
        .rv-preview-empty {
            text-align: center; padding: 3rem 1rem; color: #9ca3af;
            background: #fff; border: 1px dashed #e5e7eb; border-radius: 14px;
        }
        .rv-preview-empty i { font-size: 2rem; display: block; margin-bottom: 0.5rem; color: #d1d5db; }
    </style>
</head>
<body>

    <?php include '../includes/customer_header.php'; ?>

    <!-- ── Hero ── -->
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

    <!-- ── Shop services ── -->
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
                <p>Design your own frame with custom sizes, materials, and mounting options.</p>
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

    <!-- ── Reviews preview ── -->
    <section class="rv-preview-section" style="margin-top:5rem;">
        <div class="rv-preview-topbar">
            <div>
                <h2 class="rv-preview-heading">
                    <i class="fas fa-star" style="color:#f59e0b;font-size:1.3rem;"></i>
                    Customer Reviews
                </h2>
                <?php if ($totalReviews > 0): ?>
                <div class="rv-preview-avg mt-1">
                    <i class="fas fa-star rv-star-on" style="font-size:0.82rem;"></i>
                    <strong><?= number_format($avgRating, 1) ?></strong>
                    <span>· <?= number_format($totalReviews) ?> review<?= $totalReviews !== 1 ? 's' : '' ?></span>
                </div>
                <?php endif; ?>
            </div>
            <a href="customer_reviews.php" class="rv-see-more">
                See all reviews <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <?php if (empty($previewReviews)): ?>
            <div class="rv-preview-empty">
                <i class="fas fa-star"></i>
                <p style="margin:0;font-size:0.9rem;">No reviews yet. <a href="customer_reviews.php" style="color:var(--forest-green);font-weight:700;">Be the first!</a></p>
            </div>
        <?php else: ?>
        <div class="rv-preview-grid">
            <?php foreach ($previewReviews as $r):
                $initial  = strtoupper(substr($r['first_name'], 0, 1));
                $fullName = htmlspecialchars($r['first_name'] . ' ' . $r['last_name']);
                $isPhotog = strtoupper($r['customer_type']) === 'PHOTOGRAPHER';
            ?>
            <div class="rv-preview-card">
                <div class="rv-preview-card-top">
                    <div class="rv-preview-avatar"><?= $initial ?></div>
                    <div>
                        <div class="rv-preview-name">
                            <?= $fullName ?>
                            <?php if ($isPhotog): ?>&nbsp;<i class="fas fa-camera" style="color:#f59e0b;font-size:0.72rem;" title="Photographer"></i><?php endif; ?>
                        </div>
                        <div class="rv-preview-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $r['rating'] ? 'rv-star-on' : 'rv-star-off' ?>" style="font-size:0.7rem;"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <span class="rv-preview-rating-badge">
                        <i class="fas fa-star rv-star-on" style="font-size:0.68rem;"></i> <?= $r['rating'] ?>
                    </span>
                </div>
                <p class="rv-preview-text"><?= htmlspecialchars($r['review_text']) ?></p>
                <div class="rv-preview-date"><i class="fas fa-clock"></i> <?= $r['review_date'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:1.25rem;">
            <a href="customer_reviews.php" class="rv-see-more">
                View all <?= $totalReviews ?> review<?= $totalReviews !== 1 ? 's' : '' ?> <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <?php endif; ?>
    </section>

    <!-- ── Payment banner ── -->
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