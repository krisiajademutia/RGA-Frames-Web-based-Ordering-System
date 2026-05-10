<?php
// classes/Dashboard/CustomerDashboardController.php

require_once __DIR__ . '/../UserRepository.php';
require_once __DIR__ . '/../Review/ReviewRepository.php';
require_once __DIR__ . '/../Review/ReviewService.php';

class CustomerDashboardController {
    private $userRepo;
    private $reviewService;

    // Inject dependencies via constructor
    public function __construct(UserRepository $userRepo, ReviewService $reviewService) {
        $this->userRepo = $userRepo;
        $this->reviewService = $reviewService;
    }

    public function getDashboardData(int $user_id): array {
        // Fetch User Profile
        $userProfile = $this->userRepo->getCustomerProfile($user_id);
        $first_name = $userProfile ? $userProfile['first_name'] : "Customer";

        // Fetch Reviews Data
        $allReviews = $this->reviewService->getAllReviews();
        $previewReviews = array_slice($allReviews, 0, 3);
        $stats = $this->reviewService->getReviewStatistics();
        
        $totalReviews = $stats['total'];
        $avgRating = $totalReviews > 0 ? round($stats['avg_rating'], 1) : 0;

        return [
            'first_name' => $first_name,
            'previewReviews' => $previewReviews,
            'totalReviews' => $totalReviews,
            'avgRating' => $avgRating
        ];
    }
}
?>