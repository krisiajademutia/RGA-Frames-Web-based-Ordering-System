<?php
// process/upload_receipt.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_connect.php';

/* =========================================================================
   1. INTERFACES (Interface Segregation & Dependency Inversion Principles)
   ========================================================================= */

// Interface for Authentication
interface AuthInterface {
    public function authenticate(): int;
}

// Interface for File Storage
interface FileStorageInterface {
    public function store(array $fileData, int $identifier): string;
    public function delete(string $filePath): void;
}

// Interface for Database Operations
interface ReceiptRepositoryInterface {
    public function verifyOwnership(int $orderId, int $customerId): bool;
    public function saveReceipt(int $paymentId, string $filePath, float $amount): bool;
}

/* =========================================================================
   2. CONCRETE IMPLEMENTATIONS (Single Responsibility Principle)
   ========================================================================= */

// SRP: Only handles session-based authentication
class CustomerSessionAuth implements AuthInterface {
    public function authenticate(): int {
        if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
            throw new Exception('Unauthorized access. Please log in.');
        }
        return (int)$_SESSION['user_id'];
    }
}

// SRP: Only handles saving files locally and validating image types
class LocalImageStorage implements FileStorageInterface {
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(string $uploadDir) {
        $this->uploadDir = $uploadDir;
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function store(array $file, int $identifier): string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Please upload a valid receipt image.');
        }

        if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
            throw new Exception('File size exceeds 10MB limit.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $this->allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and WEBP are allowed.');
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'receipt_' . $identifier . '_' . time() . '_' . uniqid() . '.' . $ext;
        $targetPath = rtrim($this->uploadDir, '/') . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to move uploaded file to server.');
        }

        return 'uploads/receipts/' . $filename; // Return the path for the database
    }

    public function delete(string $filePath): void {
        // Converts DB path back to absolute path to delete if DB insertion fails
        $absolutePath = realpath(__DIR__ . '/../' . $filePath);
        if ($absolutePath && file_exists($absolutePath)) {
            unlink($absolutePath);
        }
    }
}

// SRP: Only handles querying and inserting into the database
class MySQLReceiptRepository implements ReceiptRepositoryInterface {
    private $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function verifyOwnership(int $orderId, int $customerId): bool {
        $stmt = $this->conn->prepare("SELECT order_id FROM tbl_orders WHERE order_id = ? AND customer_id = ?");
        $stmt->bind_param("ii", $orderId, $customerId);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function saveReceipt(int $paymentId, string $filePath, float $amount): bool {
        // Using your exact table name: tbl_payment_proof_uploads
        $stmt = $this->conn->prepare("
            INSERT INTO tbl_payment_proof_uploads 
            (payment_id, payment_proof, uploaded_amount, verification_status, upload_date) 
            VALUES (?, ?, ?, 'Pending', NOW())
        ");
        $stmt->bind_param("isd", $paymentId, $filePath, $amount);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}

/* =========================================================================
   3. MAIN CONTROLLER (Open/Closed & Dependency Inversion)
   ========================================================================= */

class ReceiptUploadController {
    private $auth;
    private $storage;
    private $repository;

    // We depend on Abstractions (Interfaces) here, not the concrete classes!
    public function __construct(
        AuthInterface $auth, 
        FileStorageInterface $storage, 
        ReceiptRepositoryInterface $repository
    ) {
        $this->auth = $auth;
        $this->storage = $storage;
        $this->repository = $repository;
    }

    public function handleRequest(array $post, array $files): array {
        try {
            // 1. Authenticate user
            $customerId = $this->auth->authenticate();

            // 2. Validate basic input
            $orderId = (int)($post['order_id'] ?? 0);
            $paymentId = (int)($post['payment_id'] ?? 0);
            $amount = (float)($post['uploaded_amount'] ?? 0);

            if ($orderId <= 0 || $paymentId <= 0 || $amount <= 0) {
                throw new Exception('Invalid order, payment, or amount.');
            }

            // 3. Verify the user actually owns this order
            if (!$this->repository->verifyOwnership($orderId, $customerId)) {
                throw new Exception('Order not found or unauthorized.');
            }

            // 4. Validate and Store File to the server folder
            if (!isset($files['receipt'])) {
                throw new Exception('No receipt file provided.');
            }
            $filePath = $this->storage->store($files['receipt'], $orderId);

            // 5. Save the file path to the Database
            if (!$this->repository->saveReceipt($paymentId, $filePath, $amount)) {
                // If DB fails, rollback by deleting the uploaded file
                $this->storage->delete($filePath);
                throw new Exception('Database error. Failed to save receipt record.');
            }

            return ['success' => true, 'message' => 'Receipt uploaded successfully.'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

/* =========================================================================
   4. EXECUTION
   ========================================================================= */

// Create the dependencies
$authService = new CustomerSessionAuth();
$fileStorage = new LocalImageStorage(__DIR__ . '/../uploads/receipts/');
$dbRepository = new MySQLReceiptRepository($conn);

// Inject dependencies into the Controller
$controller = new ReceiptUploadController($authService, $fileStorage, $dbRepository);

// Execute and output JSON for your frontend JavaScript
$response = $controller->handleRequest($_POST, $_FILES);
echo json_encode($response);

$conn->close();
?>