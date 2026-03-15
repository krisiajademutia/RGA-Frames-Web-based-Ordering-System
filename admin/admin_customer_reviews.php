<?php
// admin/admin_customer_reviews.php
session_start();
include __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

$filter_rating = (int)($_GET['rating'] ?? 0);
$search        = trim($_GET['search'] ?? '');

$sql = "
    SELECT r.review_id, r.rating, r.review_text,
           DATE_FORMAT(r.review_date_posted, '%M %d, %Y') AS review_date,
           c.first_name, c.last_name, c.customer_type
    FROM tbl_reviews r
    JOIN tbl_customer c ON r.customer_id = c.customer_id
    WHERE 1=1
";
$params = []; $types = '';
if ($filter_rating > 0) { $sql .= " AND r.rating = ?"; $params[] = $filter_rating; $types .= 'i'; }
if (!empty($search)) {
    $sql .= " AND (c.first_name LIKE ? OR c.last_name LIKE ? OR r.review_text LIKE ?)";
    $like = "%$search%"; $params = array_merge($params, [$like, $like, $like]); $types .= 'sss';
}
$sql .= " ORDER BY r.review_date_posted DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$statsRow = $conn->query("SELECT COUNT(*) AS total, AVG(rating) AS avg_rating FROM tbl_reviews")->fetch_assoc();
$totalAll = (int)$statsRow['total'];
$avgAll   = $totalAll > 0 ? round((float)$statsRow['avg_rating'], 1) : 0;

$distResult = $conn->query("SELECT rating, COUNT(*) AS cnt FROM tbl_reviews GROUP BY rating ORDER BY rating DESC");
$dist = [];
while ($row = $distResult->fetch_assoc()) $dist[(int)$row['rating']] = (int)$row['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews — RGA Frames Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/admin_header.php'; ?>

<div class="rcv-page">

    <!-- ── Page header ── -->
    <div class="rcv-page-header">
        <a href="admin_customers.php" class="rcv-back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="rcv-page-title">Ratings &amp; Reviews</h1>
    </div>

    <!-- ── Rating summary ── -->
    <div class="rcv-summary">
        <!-- Left: big score -->
        <div class="rcv-summary-left">
            <div class="rcv-big-score"><?= number_format($avgAll, 1) ?></div>
            <div class="rcv-big-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?= $i <= round($avgAll) ? 'rcv-star-on' : 'rcv-star-off' ?>"></i>
                <?php endfor; ?>
            </div>
            <div class="rcv-total-count">(<?= number_format($totalAll) ?> review<?= $totalAll !== 1 ? 's' : '' ?>)</div>
        </div>
        <!-- Right: distribution bars -->
        <div class="rcv-summary-right">
            <?php for ($star = 5; $star >= 1; $star--):
                $pct = $totalAll > 0 ? round((($dist[$star] ?? 0) / $totalAll) * 100) : 0;
            ?>
            <div class="rcv-dist-row">
                <span class="rcv-dist-num"><?= $star ?></span>
                <div class="rcv-dist-track">
                    <div class="rcv-dist-fill" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- ── Search ── -->
    <div class="rcv-search-wrap">
        <form method="GET" action="admin_customer_reviews.php">
            <?php if ($filter_rating > 0): ?><input type="hidden" name="rating" value="<?= $filter_rating ?>"><?php endif; ?>
            <i class="fas fa-search rcv-search-icon"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                   class="rcv-search-input" placeholder="Search reviews...">
            <?php if ($search): ?>
            <a href="admin_customer_reviews.php<?= $filter_rating ? '?rating='.$filter_rating : '' ?>" class="rcv-search-clear">
                <i class="fas fa-times"></i>
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- ── Filter pills ── -->
    <div class="rcv-pills">
        <a href="admin_customer_reviews.php<?= $search ? '?search='.urlencode($search) : '' ?>"
           class="rcv-pill <?= $filter_rating === 0 ? 'active' : '' ?>">
            <i class="fas fa-star"></i> All
        </a>
        <?php for ($star = 5; $star >= 1; $star--): ?>
        <a href="admin_customer_reviews.php?rating=<?= $star ?><?= $search ? '&search='.urlencode($search) : '' ?>"
           class="rcv-pill <?= $filter_rating === $star ? 'active' : '' ?>">
            <i class="fas fa-star"></i> <?= $star ?>
            <span class="rcv-pill-count"><?= $dist[$star] ?? 0 ?></span>
        </a>
        <?php endfor; ?>
    </div>

    <!-- ── Results count ── -->
    <p class="rcv-results-count">
        <?= count($reviews) ?> review<?= count($reviews) !== 1 ? 's' : '' ?>
        <?= $filter_rating ? ' · ' . $filter_rating . ' star' : '' ?>
        <?= $search ? ' · "' . htmlspecialchars($search) . '"' : '' ?>
    </p>

    <!-- ── Review list ── -->
    <?php if (empty($reviews)): ?>
    <div class="rcv-empty">
        <i class="fas fa-star"></i>
        <p>No reviews found.</p>
    </div>
    <?php else: ?>
    <div class="rcv-list">
        <?php foreach ($reviews as $r):
            $initial  = strtoupper(substr($r['first_name'], 0, 1));
            $fullName = htmlspecialchars($r['first_name'] . ' ' . $r['last_name']);
            $isPhotog = strtoupper($r['customer_type']) === 'PHOTOGRAPHER';
        ?>
        <div class="rcv-card">
            <!-- Card top: avatar + name + rating badge -->
            <div class="rcv-card-top">
                <div class="rcv-card-avatar"><?= $initial ?></div>
                <div class="rcv-card-meta">
                    <div class="rcv-card-name">
                        <?= $fullName ?>
                        <?php if ($isPhotog): ?>
                        <span class="rcv-photog-badge"><i class="fas fa-camera"></i></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="rcv-card-rating-badge">
                    <i class="fas fa-star rcv-star-on"></i> <?= $r['rating'] ?>
                </div>
            </div>
            <!-- Review text -->
            <p class="rcv-card-text"><?= htmlspecialchars($r['review_text']) ?></p>
            <!-- Date -->
            <div class="rcv-card-date"><i class="fas fa-clock"></i> <?= $r['review_date'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>