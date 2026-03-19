<?php
// includes/idx_footer.php
?>

<style>
    .idx-ftr {
        background-color: #001611;
        color: #ffffff;
        padding: 4rem 0 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .idx-ftr-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 3rem;
        margin-bottom: 3rem;
    }

    .idx-ftr-brand h2 {
        color: #ffffff;
        font-weight: 700;
        margin-bottom: 1.2rem;
        letter-spacing: -0.5px;
    }

    .idx-ftr-brand p {
        color: #D1D5DB;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    .idx-ftr-heading {
        font-size: 1rem;
        font-weight: 700;
        color: #C19A5F;
        margin-bottom: 1.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .idx-ftr-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .idx-ftr-links li {
        margin-bottom: 0.8rem;
    }

    .idx-ftr-links a {
        color: #E5E7EB;
        text-decoration: none;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .idx-ftr-links a:hover {
        color: #C19A5F;
        padding-left: 5px;
    }

    .idx-ftr-contact-info {
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
    }

    .idx-ftr-contact-item {
        display: flex;
        gap: 0.85rem;
        color: #E5E7EB;
        font-size: 0.95rem;
    }

    .idx-ftr-contact-item i {
        color: #C19A5F;
        font-size: 1.1rem;
        margin-top: 0.2rem;
        flex-shrink: 0;
    }

    .idx-ftr-contact-text strong {
        color: #FFFFFF;
        display: block;
        margin-bottom: 2px;
    }

    .idx-ftr-bottom {
        padding-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
        color: #9CA3AF;
        font-size: 0.85rem;
    }

    /* ── Mobile ── */
    @media (max-width: 768px) {
        .idx-ftr {
            padding: 2.5rem 0 1.5rem;
        }

        .idx-ftr-grid {
            grid-template-columns: 1fr !important;
            gap: 1.75rem;
            text-align: center;
        }

        .idx-ftr-brand h2 {
            font-size: 1.4rem;
            margin-bottom: 0.75rem;
        }

        .idx-ftr-brand p {
            font-size: 0.875rem;
        }

        .idx-ftr-heading {
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .idx-ftr-links li {
            margin-bottom: 0.6rem;
        }

        .idx-ftr-links a {
            font-size: 0.875rem;
        }

        .idx-ftr-links a:hover {
            padding-left: 0;
        }

        .idx-ftr-contact-info {
            gap: 1rem;
            align-items: center;
        }

        .idx-ftr-contact-item {
            justify-content: center;
            flex-direction: row;
            align-items: flex-start;
            text-align: left;
            max-width: 280px;
            margin: 0 auto;
        }

        .idx-ftr-contact-text {
            text-align: left;
        }

        .idx-ftr-contact-text strong {
            font-size: 0.875rem;
        }

        .idx-ftr-bottom {
            font-size: 0.78rem;
            padding-top: 1.25rem;
        }
    }

    @media (max-width: 400px) {
        .idx-ftr-brand h2 {
            font-size: 1.2rem;
        }

        .idx-ftr-contact-item {
            max-width: 100%;
        }

        .idx-ftr-bottom {
            font-size: 0.72rem;
        }
    }
</style>

<footer class="idx-ftr">
    <div class="container">
        <div class="idx-ftr-grid">
            <div class="idx-ftr-brand">
                <h2>RGA FRAMES</h2>
                <p>
                    Premium artisan framing services dedicated to preserving your most valued memories with quality wood products and professional care.
                </p>
            </div>

            <div>
                <h4 class="idx-ftr-heading">Our Services</h4>
                <ul class="idx-ftr-links">
                    <li><a href="customer_shop_readymade.php">Ready-Made Frames</a></li>
                    <li><a href="customer_shop_custom.php">Custom Frames</a></li>
                    <li><a href="customer_shop_printing.php">Printing Services</a></li>
                    <li><a href="customer_orders.php">Track Your Orders</a></li>
                </ul>
            </div>

            <div>
                <h4 class="idx-ftr-heading">Visit Our Shop</h4>
                <div class="idx-ftr-contact-info">
                    <div class="idx-ftr-contact-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <div class="idx-ftr-contact-text">
                            <strong>Physical Address</strong>
                            Mabini Street, Tagum City,<br>
                            Davao del Norte, Philippines 8100
                        </div>
                    </div>
                    <div class="idx-ftr-contact-item">
                        <i class="fa-solid fa-phone"></i>
                        <div class="idx-ftr-contact-text">
                            <strong>Call Us</strong>
                            +63 9XX XXX XXXX
                        </div>
                    </div>
                    <div class="idx-ftr-contact-item">
                        <i class="fa-solid fa-envelope"></i>
                        <div class="idx-ftr-contact-text">
                            <strong>Email Inquiries</strong>
                            support@rgaframes.com
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="idx-ftr-bottom">
            <p>&copy; <?php echo date("Y"); ?> RGA Frames & Wood Products. Crafted with quality in Tagum City.</p>
        </div>
    </div>
</footer>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">