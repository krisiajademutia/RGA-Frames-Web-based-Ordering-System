<?php
// includes/idx_footer.php
?>

<style>
    .main-footer {
        background-color: #001611;
        color: #ffffff;
        padding: 4rem 0 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 3rem;
        margin-bottom: 3rem;
    }

    .footer-brand h2 {
        color: #ffffff;
        font-weight: 700;
        margin-bottom: 1.2rem;
        letter-spacing: -0.5px;
    }

    .footer-brand p {
        color: #D1D5DB;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    .footer-heading {
        font-size: 1rem;
        font-weight: 700;
        color: #C19A5F;
        margin-bottom: 1.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 0.8rem;
    }

    .footer-links a {
        color: #E5E7EB;
        text-decoration: none;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .footer-links a:hover {
        color: #C19A5F;
        padding-left: 5px;
    }

    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
    }

    .contact-item {
        display: flex;
        gap: 0.85rem;
        color: #E5E7EB;
        font-size: 0.95rem;
    }

    .contact-item i {
        color: #C19A5F; 
        font-size: 1.1rem;
        margin-top: 0.2rem;
    }

    .contact-text strong {
        color: #FFFFFF;
        display: block;
        margin-bottom: 2px;
    }

    .footer-bottom {
        padding-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
        color: #9CA3AF;
        font-size: 0.85rem;
    }

    @media (max-width: 768px) {
        .footer-grid {
            text-align: center;
        }
        .contact-item {
            justify-content: center;
        }
    }
</style>

<footer class="main-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <h2>RGA FRAMES</h2>
                <p>
                    Premium artisan framing services dedicated to preserving your most valued memories with quality wood products and professional care.
                </p>
            </div>

            <div>
                <h4 class="footer-heading">Our Services</h4>
                <ul class="footer-links">
                    <li><a href="customer_shop_readymade.php">Ready-Made Frames</a></li>
                    <li><a href="customer_shop_custom.php">Custom Frames</a></li>
                    <li><a href="customer_shop_printing.php">Printing Services</a></li>
                    <li><a href="customer_orders.php">Track Your Orders</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-heading">Visit Our Shop</h4>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <div class="contact-text">
                            <strong>Physical Address</strong>
                            Mabini Street, Tagum City,<br>
                            Davao del Norte, Philippines 8100
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fa-solid fa-phone"></i>
                        <div class="contact-text">
                            <strong>Call Us</strong>
                            +63 9XX XXX XXXX
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fa-solid fa-envelope"></i>
                        <div class="contact-text">
                            <strong>Email Inquiries</strong>
                            support@rgaframes.com
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> RGA Frames & Wood Products. Crafted with quality in Tagum City.</p>
        </div>
    </div>
</footer>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">