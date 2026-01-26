<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Dummy Data
$total_posted = 0; $active_products = 0; $total_sold = 0; $total_earnings = 0.00;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Custom Frame Options</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    /* --- NEW COLOR PALETTE --- */
        :root {
            /* YOUR PALETTE */
            --color-green: #A7C957;
            --color-gold: #B89655;
            --color-brown: #795338;
            
            /* Standard UI Colors */
            --bg-light: #f8f9fa;
            --text-dark: #333;
            --text-grey: #666;
            --color-danger: #dc3545; /* Keep red for reject actions for safety */
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            color: var(--text-dark);
            padding-top: 100px;
        }
          .orders-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); overflow: hidden; border-top: 3px solid var(--color-gold); }
        /* --- CONTENT --- */
        .container { max-width: 1200px; margin: 0 auto; padding-bottom: 50px; }
        /* Use Brown for strong headings */
        .page-title { font-size: 24px; font-weight: bold; margin-bottom: 5px; color: var(--color-brown); }
        .page-subtitle { font-size: 14px; color: var(--text-grey); margin-bottom: 25px; }

        </style>
        
</head>
<body>

    <?php include 'admin_header.php'; ?>
 <div class="container">
   
            <h1 class="page-title">Add New Products</h1>
        <p class="page-subtitle">Manage Ready-Made Frames</p>
<div class="orders-card">
            
        </div>
        
</div>
</body>
</html>