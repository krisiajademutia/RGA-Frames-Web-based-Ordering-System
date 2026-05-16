<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'RGA Frames - Custom Framing & Printing'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Georgia:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="idx-body">

    <header class="idx-hdr-container" id="mainHeader">
        <div class="idx-hdr-left">
            <a href="index.php" style="text-decoration: none; display: flex; align-items: center; gap: 0.3rem;">
                <div class="idx-hdr-logo">
                     <img 
                        src="assets/img/rga_logo.png" 
                        alt="RGA Frames and Photo Studio Logo"
                    >
                </div>
                <div class="idx-hdr-brand">
                    <h1>RGA Frames</h1>
                </div>
            </a>
        </div>

 <button class="idx-mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
    <span></span>
    <span></span>
    <span></span>
</button>

        <nav class="idx-hdr-nav" id="mainNav">
            <a href="index.php" class="idx-hdr-nav-link idx-hdr-btn-home">
                <i class="fas fa-home"></i> <span>Home</span>
            </a>
            <a href="login.php" class="idx-hdr-nav-link idx-hdr-btn-login">
                <i class="fas fa-sign-in-alt"></i> <span>Login</span>
            </a>
            <a href="register.php" class="idx-hdr-nav-link idx-hdr-btn-register">
                <i class="fas fa-user-plus"></i> <span>Register</span>
            </a>
        </nav>

        <div class="idx-mobile-overlay" id="mobileOverlay"></div>


    </header>