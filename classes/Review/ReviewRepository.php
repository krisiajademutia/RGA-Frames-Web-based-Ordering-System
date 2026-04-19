<?php
// classes/Review/ReviewRepository.php

class ReviewRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getCompletedOrderCount(int $customer_id): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS cnt FROM tbl_orders WHERE customer_id = ? AND order_status = 'COMPLETED'");
        $stmt->bind_param('i', $customer_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    public function getReviewCount(int $customer_id): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS cnt FROM tbl_reviews WHERE customer_id = ?");
        $stmt->bind_param('i', $customer_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    public function createReview(int $customer_id, int $rating, string $review_text): bool {
        $stmt = $this->conn->prepare("INSERT INTO tbl_reviews (customer_id, rating, review_text) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $customer_id, $rating, $review_text);
        return $stmt->execute();
    }

    public function getAllReviews(int $rating = 0, string $search = ''): array {
        $sql = "SELECT r.review_id, r.rating, r.review_text,
                       DATE_FORMAT(r.review_date_posted, '%M %d, %Y') AS review_date,
                       c.first_name, c.last_name, c.customer_type, c.customer_id AS reviewer_id, r.review_date_posted as created_at
                FROM tbl_reviews r
                JOIN tbl_customer c ON r.customer_id = c.customer_id
                WHERE 1=1";
        
        $params = [];
        $types = '';
        
        if ($rating > 0) {
            $sql .= " AND r.rating = ?";
            $params[] = $rating;
            $types .= 'i';
        }
        
        if (!empty($search)) {
            $sql .= " AND (c.first_name LIKE ? OR c.last_name LIKE ? OR r.review_text LIKE ?)";
            $like = "%$search%";
            array_push($params, $like, $like, $like);
            $types .= 'sss';
        }
        
        $sql .= " ORDER BY r.review_date_posted DESC";
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
             $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getReviewStatistics(): array {
        $statsArray = [
            'total'      => 0,
            'avg_rating' => 0.0,
            'distribution' => [5=>0, 4=>0, 3=>0, 2=>0, 1=>0]
        ];

        $res1 = $this->conn->query("SELECT COUNT(*) AS total, AVG(rating) AS avg_rating FROM tbl_reviews");
        if ($res1 && $row = $res1->fetch_assoc()) {
            $statsArray['total'] = (int)$row['total'];
            $statsArray['avg_rating'] = (float)$row['avg_rating'];
        }

        $res2 = $this->conn->query("SELECT rating, COUNT(*) AS cnt FROM tbl_reviews GROUP BY rating");
        if ($res2) {
            while ($r = $res2->fetch_assoc()) {
                $statsArray['distribution'][(int)$r['rating']] = (int)$r['cnt'];
            }
        }
        return $statsArray;
    }
}
?>
