<?php
// update_order.php
session_start();
include 'db_connect.php';

// 1. Check if Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

// 2. Check if form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $order_id = $_POST['order_id'];
    $action = $_POST['action'];

    // --- HANDLE FILE UPLOAD FUNCTION ---
    function uploadReceipt($file) {
        $target_dir = "uploads/receipts/"; // Make sure this folder exists!
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $filename = time() . "_" . basename($file["name"]);
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $target_file;
        } else {
            return false;
        }
    }

    // --- LOGIC SWITCH ---
    if ($action == 'accept') {
        // ACTION: Move to Preparing + Upload Initial Receipt
        if (isset($_FILES['receipt_img']) && $_FILES['receipt_img']['error'] == 0) {
            $image_path = uploadReceipt($_FILES['receipt_img']);
            
            if ($image_path) {
                $sql = "UPDATE orders SET status = 'Preparing', initial_receipt_image = '$image_path' WHERE order_id = $order_id";
                if ($conn->query($sql)) {
                    header("Location: admin_orders.php?status=new_orders&msg=accepted");
                } else {
                    echo "Error updating record: " . $conn->error;
                }
            } else {
                echo "Error uploading file.";
            }
        } else {
            echo "Please select a receipt image.";
        }

    } elseif ($action == 'mark_done') {
        // ACTION: Move to Ready OR Delivery
        $next_status = $_POST['next_status']; // Passed from the hidden input
        
        $sql = "UPDATE orders SET status = '$next_status' WHERE order_id = $order_id";
        if ($conn->query($sql)) {
            header("Location: admin_orders.php?status=preparing&msg=done");
        }

    } elseif ($action == 'mark_sold') {
        // ACTION: Move to Completed + Upload Final Receipt + Mark Fully Paid
        if (isset($_FILES['receipt_img']) && $_FILES['receipt_img']['error'] == 0) {
            $image_path = uploadReceipt($_FILES['receipt_img']);
            
            if ($image_path) {
                // Update status AND mark payment as 'Paid'
                $sql = "UPDATE orders SET status = 'Completed', final_receipt_image = '$image_path', payment_status = 'Paid' WHERE order_id = $order_id";
                if ($conn->query($sql)) {
                    header("Location: admin_orders.php?status=sold&msg=completed");
                } else {
                    echo "Error updating record: " . $conn->error;
                }
            } else {
                echo "Error uploading file.";
            }
        } else {
            echo "Please select a final receipt image.";
        }
        
    } elseif ($action == 'reject') {
        // ACTION: Cancel Order
        $sql = "UPDATE orders SET status = 'Cancelled' WHERE order_id = $order_id";
        $conn->query($sql);
        header("Location: admin_orders.php?status=new_orders&msg=rejected");
    }
}
?>