<?php
    session_start();
    include __DIR__ . '/../config/db_connect.php';

    if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
        header("Location: ../login.php");
        exit();
    }

    $customer_id = (int)$_SESSION['user_id'];

    // Count completed orders
    $eligCheck = $conn->prepare("SELECT COUNT(*) AS cnt FROM tbl_orders WHERE customer_id = ? AND order_status = 'COMPLETED'");
    $eligCheck->bind_param('i', $customer_id);
    $eligCheck->execute();
    $completedOrders = (int)$eligCheck->get_result()->fetch_assoc()['cnt'];

    // Count existing reviews by this customer
    $rvCheck = $conn->prepare("SELECT COUNT(*) AS cnt FROM tbl_reviews WHERE customer_id = ?");
    $rvCheck->bind_param('i', $customer_id);
    $rvCheck->execute();
    $existingReviews = (int)$rvCheck->get_result()->fetch_assoc()['cnt'];

    // Can review if they have more completed orders than reviews submitted
    $canReview = $completedOrders > $existingReviews;

    // Fetch all reviews
    $allReviews = $conn->query("
        SELECT r.review_id, r.rating, r.review_text,
            DATE_FORMAT(r.review_date_posted, '%M %d, %Y') AS review_date,
            c.first_name, c.last_name, c.customer_type, c.customer_id AS reviewer_id
        FROM tbl_reviews r
        JOIN tbl_customer c ON r.customer_id = c.customer_id
        ORDER BY r.review_date_posted DESC
    ")->fetch_all(MYSQLI_ASSOC);

    $statsRow = $conn->query("SELECT COUNT(*) AS total, AVG(rating) AS avg FROM tbl_reviews")->fetch_assoc();
    $totalReviews = (int)$statsRow['total'];
    $avgRating    = $totalReviews > 0 ? round((float)$statsRow['avg'], 1) : 0;

    $distResult = $conn->query("SELECT rating, COUNT(*) AS cnt FROM tbl_reviews GROUP BY rating ORDER BY rating DESC");
    $dist = [];
    while ($row = $distResult->fetch_assoc()) $dist[(int)$row['rating']] = (int)$row['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews — RGA Frames</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include __DIR__ . '/../includes/customer_header.php'; ?>

    <div class="rv-page">
        <div class="rv-inner">
            <div class="rv-page-header">
                <a href="customer_dashboard.php" class="rv-back-btn"><i class="fas fa-arrow-left"></i></a>
                <h1 class="rv-page-title">Customer Reviews</h1>
            </div>

            <div class="rv-summary">
                <div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;min-width:100px;">
                    <div class="rv-big-score"><?= number_format($avgRating, 1) ?></div>
                    <div class="rv-stars-row">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= round($avgRating) ? 'rv-star-on' : 'rv-star-off' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="rv-total"><?= number_format($totalReviews) ?> review<?= $totalReviews !== 1 ? 's' : '' ?></div>
                </div>
                <div class="rv-summary-right">
                    <?php for ($star = 5; $star >= 1; $star--):
                        $pct = $totalReviews > 0 ? round((($dist[$star] ?? 0) / $totalReviews) * 100) : 0;
                    ?>
                    <div class="rv-dist-row">
                        <span class="rv-dist-num"><?= $star ?></span>
                        <div class="rv-dist-track"><div class="rv-dist-fill" style="width:<?= $pct ?>%"></div></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="rv-write-card">
                <div class="rv-write-title"><i class="fas fa-pen-to-square" style="color:#0f3d33;"></i> Write a Review</div>

                <?php if ($completedOrders === 0): ?>
                    <div class="rv-locked-note">
                        <i class="fas fa-lock" style="margin-top:2px;flex-shrink:0;"></i>
                        <span>You can leave a review after completing your first order. Place an order and we'll unlock this for you!</span>
                    </div>
                <?php elseif (!$canReview): ?>
                    <div class="rv-locked-note">
                        <i class="fas fa-info-circle" style="margin-top:2px;flex-shrink:0;"></i>
                        <span>You have submitted a review for each of your completed orders. Complete another order to leave a new review.</span>
                    </div>
                <?php else: ?>
                    <p style="font-size:0.82rem;color:#6b7280;margin-bottom:0.75rem;">How was your experience with RGA Frames?</p>
                    <div class="rv-star-picker" id="starPicker">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star rv-star-pick" data-value="<?= $i ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" id="selectedRating" value="0">
                    <textarea class="rv-textarea" id="reviewText" maxlength="1000"
                          placeholder="Share your experience — quality, service, delivery..."></textarea>
                    <div class="rv-char-count"><span id="charCount">0</span> / 1000</div>
                    <button class="rv-submit-btn" id="submitReviewBtn" onclick="submitReview()">
                        <i class="fas fa-paper-plane"></i> Submit Review
                    </button>
                <?php endif; ?>
            </div>

            <div class="rv-list-header"><i class="fas fa-comments" style="color:#f59e0b;"></i> All Reviews</div>

            <?php if (empty($allReviews)): ?>
                <div class="rv-empty">
                    <i class="fas fa-star"></i>
                    <p>No reviews yet. Be the first!</p>
                </div>
            <?php else: ?>
            <div class="rv-list">
                <?php foreach ($allReviews as $r):
                    $initial  = strtoupper(substr($r['first_name'], 0, 1));
                    $fullName = htmlspecialchars($r['first_name'] . ' ' . $r['last_name']);
                    $isMe     = (int)$r['reviewer_id'] === $customer_id;
                    $isPhotog = strtoupper($r['customer_type']) === 'PHOTOGRAPHER';
                ?>
                <div class="rv-card">
                    <div class="rv-card-top">
                        <div class="rv-avatar"><?= $initial ?></div>
                        <div>
                            <div class="rv-card-name">
                                <?= $fullName ?>
                                <?php if ($isMe):    ?><span class="rv-you-badge">You</span><?php endif; ?>
                                <?php if ($isPhotog): ?><span class="rv-photog-badge"><i class="fas fa-camera"></i></span><?php endif; ?>
                            </div>
                        </div>
                        <span class="rv-rating-badge">
                            <i class="fas fa-star rv-star-on" style="font-size:0.75rem;"></i> <?= $r['rating'] ?>
                        </span>
                    </div>
                    <p class="rv-card-text"><?= htmlspecialchars($r['review_text']) ?></p>
                    <div class="rv-card-date"><i class="fas fa-clock"></i> <?= $r['review_date'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

<?php include __DIR__ . '/../includes/idx_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.rv-star-pick').forEach(star => {
        star.addEventListener('mouseenter', () => highlightStars(+star.dataset.value));
        star.addEventListener('mouseleave', () => highlightStars(+document.getElementById('selectedRating').value));
        star.addEventListener('click', () => {
            document.getElementById('selectedRating').value = star.dataset.value;
            highlightStars(+star.dataset.value);
        });
    });

    function highlightStars(n) {
        document.querySelectorAll('.rv-star-pick').forEach(s => {
            s.classList.toggle('active', +s.dataset.value <= n);
        });
    }

    const reviewText = document.getElementById('reviewText');
    if (reviewText) {
        reviewText.addEventListener('input', () => {
            document.getElementById('charCount').textContent = reviewText.value.length;
        });
    }

    async function submitReview() {
        const rating = +document.getElementById('selectedRating').value;
        const text   = reviewText?.value.trim() ?? '';

        if (rating < 1) { return Swal.fire('Select a rating', 'Please click on a star to rate.', 'warning'); }
        if (text.length < 5) { return Swal.fire('Too short', 'Please write at least 5 characters.', 'warning'); }

        const btn = document.getElementById('submitReviewBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting…';

        try {
            const res  = await fetch('../process/review_process.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'add', rating, review_text: text })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Thank you!', text: data.message,
                    confirmButtonColor: '#0f3d33', timer: 1800, showConfirmButton: false })
                .then(() => location.reload());
            } else {
                Swal.fire('Could not submit', data.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Review';
            }
        } catch {
            Swal.fire('Error', 'Network error. Please try again.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Review';
        }
    }
</script>
</body>
</html>