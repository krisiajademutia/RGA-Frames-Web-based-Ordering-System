<?php
session_start();
include __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/UserRepository.php';
require_once __DIR__ . '/../classes/Review/ReviewRepository.php';
require_once __DIR__ . '/../classes/Review/ReviewService.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$userRepo = new UserRepository($conn);
$userProfile = $userRepo->getCustomerProfile($user_id);
$first_name = $userProfile ? $userProfile['first_name'] : "Customer";

$reviewRepo = new ReviewRepository($conn);
$reviewService = new ReviewService($reviewRepo);

// ── Reviews preview (3 most recent) ─────────────────────────
$allReviews = $reviewService->getAllReviews();
$previewReviews = array_slice($allReviews, 0, 3);

$stats = $reviewService->getReviewStatistics();
$totalReviews = $stats['total'];
$avgRating    = $totalReviews > 0 ? round($stats['avg_rating'], 1) : 0;
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
    <link rel="stylesheet" href="../assets/css/style.css">
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
                <p style="margin:0;font-size:0.9rem;">No reviews yet. <a href="customer_reviews.php" style="color:var(--dash-forest-green);font-weight:700;">Be the first!</a></p>
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