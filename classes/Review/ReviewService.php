<?php
// classes/Review/ReviewService.php

require_once __DIR__ . '/ReviewRepository.php';

class ReviewService {
    private $repo;
    private $notifService;

    public function __construct(ReviewRepository $repo, $notifService = null) {
        $this->repo = $repo;
        $this->notifService = $notifService;
    }

    public function submitReview(int $customer_id, int $rating, string $review_text): array {
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'Please select a rating from 1 to 5.'];
        }
        if (mb_strlen($review_text) < 5) {
            return ['success' => false, 'message' => 'Review must be at least 5 characters.'];
        }
        if (mb_strlen($review_text) > 1000) {
            return ['success' => false, 'message' => 'Review must not exceed 1000 characters.'];
        }

        // Check if customer has unreviewed completed orders
        $completedOrders = $this->repo->getCompletedOrderCount($customer_id);
        if ($completedOrders === 0) {
            return ['success' => false, 'message' => 'You need at least one completed order to leave a review.'];
        }

        $existingReviews = $this->repo->getReviewCount($customer_id);
        if ($existingReviews >= $completedOrders) {
            return ['success' => false, 'message' => 'You have already used all your review slots. Complete another order to leave a new review.'];
        }

        $success = $this->repo->createReview($customer_id, $rating, $review_text);

        if ($success) {
            if ($this->notifService) {
                $stars   = str_repeat('⭐', $rating);
                $preview = mb_substr($review_text, 0, 40) . (mb_strlen($review_text) > 40 ? '...' : '');

                $this->notifService->notifyAdmin(
                    0,
                    "New Review! {$stars}",
                    "A customer just left a {$rating}-star review: \"{$preview}\""
                );
            }
            return ['success' => true, 'message' => 'Thank you for your review!'];
        }

        return ['success' => false, 'message' => 'Failed to submit review. Please try again.'];
    }

    public function getAllReviews(int $rating = 0, string $search = ''): array {
        return $this->repo->getAllReviews($rating, $search);
    }

    public function getReviewStatistics(): array {
        return $this->repo->getReviewStatistics();
    }
}
?>
