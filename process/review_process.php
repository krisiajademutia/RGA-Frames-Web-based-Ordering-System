<?php
session_start();
include __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    echo json_encode(['success' => false, 'message' => 'Please log in to continue.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$customer_id = (int)$_SESSION['user_id'];
$action      = trim($_POST['action'] ?? '');

// ── ADD REVIEW 
if ($action === 'add') {
    $rating      = (int)($_POST['rating']      ?? 0);
    $review_text = trim($_POST['review_text'] ?? '');

    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Please select a rating from 1 to 5.']);
        exit();
    }
    if (mb_strlen($review_text) < 5) {
        echo json_encode(['success' => false, 'message' => 'Review must be at least 5 characters.']);
        exit();
    }
    if (mb_strlen($review_text) > 1000) {
        echo json_encode(['success' => false, 'message' => 'Review must not exceed 1000 characters.']);
        exit();
    }

    // Only customers with at least one completed order may review
    $chk = $conn->prepare("
        SELECT COUNT(*) AS cnt FROM tbl_orders
        WHERE customer_id = ? AND order_status = 'COMPLETED'
    ");
    $chk->bind_param('i', $customer_id);
    $chk->execute();
    if ((int)$chk->get_result()->fetch_assoc()['cnt'] === 0) {
        echo json_encode(['success' => false, 'message' => 'You need at least one completed order to leave a review.']);
        exit();
    }

    // One review per customer
    $dup = $conn->prepare("SELECT review_id FROM tbl_reviews WHERE customer_id = ? LIMIT 1");
    $dup->bind_param('i', $customer_id);
    $dup->execute();
    if ($dup->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You have already submitted a review. Delete it first to submit a new one.']);
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO tbl_reviews (customer_id, rating, review_text)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param('iis', $customer_id, $rating, $review_text);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Thank you for your review!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit review. Please try again.']);
    }
    exit();
}

// ── DELETE REVIEW 
if ($action === 'delete') {
    $review_id = (int)($_POST['review_id'] ?? 0);

    if ($review_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid review.']);
        exit();
    }

    // customer may only delete their own review
    $stmt = $conn->prepare("
        DELETE FROM tbl_reviews
        WHERE review_id = ? AND customer_id = ?
    ");
    $stmt->bind_param('ii', $review_id, $customer_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Your review has been deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not delete review.']);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
exit();