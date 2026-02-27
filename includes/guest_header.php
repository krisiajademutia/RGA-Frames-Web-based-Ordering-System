<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">

<header class="gst-hdr-container">
    <div class="gst-hdr-left">
        <a href="index.php" class="gst-hdr-brand-link">
            <div class="gst-hdr-logo">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="gst-hdr-brand">
                <h1>RGA Frames</h1>
                <p>Custom Framing & Printing</p>
            </div>
        </a>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="gst-mobile-menu-toggle" id="gstMobileMenuToggle" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Navigation -->
    <nav class="gst-hdr-nav" id="gstMainNav">
        <a href="index.php" class="gst-hdr-nav-link">
            <i class="fas fa-home"></i> 
            <span>Home</span>
        </a>
        
        <div class="gst-hdr-divider"></div>
        
        <a href="login.php" class="gst-hdr-nav-link gst-hdr-btn-login">
            <i class="fas fa-sign-in-alt"></i> 
            <span>Login</span>
        </a>
        <a href="register.php" class="gst-hdr-nav-link gst-hdr-btn-register">
            <i class="fas fa-user-plus"></i> 
            <span>Register</span>
        </a>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div class="gst-mobile-overlay" id="gstMobileOverlay"></div>
</header>

<script>
// Mobile menu functionality for guest header
(function() {
    const mobileMenuToggle = document.getElementById('gstMobileMenuToggle');
    const mainNav = document.getElementById('gstMainNav');
    const mobileOverlay = document.getElementById('gstMobileOverlay');
    const body = document.body;

    function toggleMobileMenu() {
        mobileMenuToggle.classList.toggle('active');
        mainNav.classList.toggle('active');
        mobileOverlay.classList.toggle('active');
        body.style.overflow = mainNav.classList.contains('active') ? 'hidden' : '';
    }

    function closeMobileMenu() {
        mobileMenuToggle.classList.remove('active');
        mainNav.classList.remove('active');
        mobileOverlay.classList.remove('active');
        body.style.overflow = '';
    }

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', toggleMobileMenu);
    }

    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMobileMenu);
    }

    // Close menu when clicking nav links
    document.querySelectorAll('.gst-hdr-nav-link').forEach(link => {
        link.addEventListener('click', closeMobileMenu);
    });

    // Close menu on window resize to desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            closeMobileMenu();
        }
    });
})();
</script>