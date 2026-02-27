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
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#ffff" viewBox="2 2 20 20">
                        <path d="M3 16c0 .34.18.67.47.85l8 5a1.01 1.01 0 0 0 1.06 0l8-5c.29-.18.47-.5.47-.85V8c0-.34-.18-.67-.47-.85l-8-5c-.32-.2-.74-.2-1.06 0l-8 5c-.29.18-.47.5-.47.85zm2-6.53 6 3.6v6.13l-6-3.75zm8 9.73v-6.13l6-3.6v5.98zM12 4.18l5.84 3.65-5.84 3.5-5.84-3.5z"></path>
                    </svg>
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

<!-- Navigation -->
        <nav class="idx-hdr-nav" id="mainNav">
            <a href="login.php" class="idx-hdr-nav-link idx-hdr-btn-login">Login</a>
            <a href="register.php" class="idx-hdr-nav-link idx-hdr-btn-register">Register</a>
        </nav>

        <div class="idx-mobile-overlay" id="mobileOverlay"></div>


    </header>