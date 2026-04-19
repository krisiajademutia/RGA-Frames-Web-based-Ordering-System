<?php
session_start();
include __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Notification/NotificationRepository.php';
require_once __DIR__ . '/../classes/Notification/NotificationService.php';
require_once __DIR__ . '/../classes/Review/ReviewRepository.php';
require_once __DIR__ . '/../classes/Review/ReviewService.php';

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

if ($action === 'add') {
    $rating      = (int)($_POST['rating']      ?? 0);
    $review_text = trim($_POST['review_text'] ?? '');

    $notifRepo = new NotificationRepository($conn);
    $notifService = new NotificationService($notifRepo);
    
    $reviewRepo = new ReviewRepository($conn);
    $reviewService = new ReviewService($reviewRepo, $notifService);

    $response = $reviewService->submitReview($customer_id, $rating, $review_text);
    echo json_encode($response);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
exit();