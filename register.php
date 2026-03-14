<?php
session_start();

$errors = $_SESSION['errors'] ?? [];
$old    = $_SESSION['old_input'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RGA Frames</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="reg-body-reset">
     <?php include 'includes/guest_header.php'; ?>

    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            
            <div class="col-lg-6 d-flex flex-column p-4 p-md-5">
                
                <div class="reg-header-area">
                    <div class="reg-brand-wrapper">
                        <i class="fas fa-box-open me-2"></i> 
                        <span class="reg-brand-text"> </span>
                    </div>
                </div>

                <div class="reg-form-container my-auto mx-auto">
                    <div class="mb-4">
                        <h1 class="reg-title">Create Your Account</h1>
                        <p class="reg-subtitle">Register to access custom framing options, professional printing services, and ready-made frames.</p>
                    </div>

                    <form id="regForm" action="process/register_process.php" method="POST" novalidate>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="reg-label">First Name</label>
                                <div class="reg-input-wrapper">
                                    <span class="reg-field-icon"><i class="fas fa-user"></i></span>
                                    <input type="text" name="first_name" 
                                           class="form-control reg-input <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                                           placeholder="Enter your first name" 
                                           value="<?php echo htmlspecialchars($old['first_name'] ?? ''); ?>" required>
                                </div>
                                <?php if (isset($errors['first_name'])): ?>
                                    <div class="text-danger small mt-1">
                                        <?php echo htmlspecialchars($errors['first_name']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="reg-label">Last Name</label>
                                <div class="reg-input-wrapper">
                                    <span class="reg-field-icon"><i class="fas fa-user"></i></span>
                                    <input type="text" name="last_name" 
                                           class="form-control reg-input <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                                           placeholder="Enter your last name" 
                                           value="<?php echo htmlspecialchars($old['last_name'] ?? ''); ?>" required>
                                </div>
                                <?php if (isset($errors['last_name'])): ?>
                                    <div class="text-danger small mt-1">
                                        <?php echo htmlspecialchars($errors['last_name']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="reg-label">Username</label>
                            <div class="reg-input-wrapper">
                                <span class="reg-field-icon">@</span>
                                <input type="text" name="username" 
                                       class="form-control reg-input <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                                       placeholder="Enter your username" 
                                       value="<?php echo htmlspecialchars($old['username'] ?? ''); ?>" required>
                            </div>
                            <?php if (isset($errors['username'])): ?>
                                <div class="text-danger small mt-1">
                                    <?php echo htmlspecialchars($errors['username']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="reg-label">Gmail</label>
                            <div class="reg-input-wrapper">
                                <span class="reg-field-icon"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" id="email" 
                                       class="form-control reg-input <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       placeholder="example@gmail.com" 
                                       value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" required>
                            </div>
                            <?php if (isset($errors['email'])): ?>
                                <div class="text-danger small mt-1">
                                    <?php echo htmlspecialchars($errors['email']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="reg-label">Phone Number</label>
                            <div class="reg-input-wrapper">
                                <span class="reg-field-icon"><i class="fas fa-phone"></i></span>
                                <input type="tel" name="phone_number" id="phone_number"
                                       class="form-control reg-input <?php echo isset($errors['phone_number']) ? 'is-invalid' : ''; ?>" 
                                       placeholder="09xxxxxxxxx" 
                                       value="<?php echo htmlspecialchars($old['phone_number'] ?? ''); ?>"required>
                            </div>
                            <?php if (isset($errors['phone_number'])): ?>
                                <div class="text-danger small mt-1">
                                    <?php echo htmlspecialchars($errors['phone_number']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="reg-label">Password</label>
                            <div class="reg-input-wrapper">
                                <span class="reg-field-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="registerPassword" 
                                       class="form-control reg-input <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                       placeholder="Enter your password" required>
                                <button class="reg-eye-toggle" type="button" onclick="togglePassword('registerPassword', this)">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="text-danger small mt-1">
                                    <?php echo htmlspecialchars($errors['password']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input <?php echo isset($errors['data_privacy']) ? 'is-invalid' : ''; ?>" id="data_privacy" name="data_privacy" required>
                            <label class="form-check-label text-muted small" for="data_privacy" style="font-size: 0.85rem;">
                                I have read and agree to the 
                                <a href="#" onclick="document.getElementById('privacy-modal').style.display='flex'; return false;" style="color: var(--forest-dark); font-weight: 600; text-decoration: underline;">
                                    Data Privacy Policy
                                </a>.
                            </label>
                            <?php if (isset($errors['data_privacy'])): ?>
                                <div class="text-danger small mt-1">
                                    <?php echo htmlspecialchars($errors['data_privacy']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" name="register_btn" class="reg-btn-submit">Register Account</button>
                    </form>

                    <p class="reg-footer-text mt-4 text-center">
                        Already have an account? <a href="login.php" class="reg-login-link">Log-in Here</a>
                    </p>
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-block reg-image-side">
            </div>

        </div>
    </div>
    
    <?php
    unset($_SESSION['errors']);
    unset($_SESSION['old_input']);
    ?>
    <div id="privacy-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: #fff; padding: 30px; border-radius: 10px; max-width: 600px; width: 100%; max-height: 85vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="color: #0F473A; margin-top: 0; font-weight: bold; border-bottom: 2px solid #eee; padding-bottom: 10px;">RGA Frames Data Privacy Policy</h3>
        <p style="font-size: 0.9rem; color: #555;">Effective Date: March 2026</p>
        
        <p class="small text-muted">In compliance with the <strong>Data Privacy Act of 2012 (RA 10173)</strong>, RGA Frames is committed to protecting your personal data.</p>
        
        <h5 style="color: #0F473A; margin-top: 20px; font-size: 1rem;">1. What Information We Collect</h5>
        <p class="small text-muted">When you register and use our system, we collect: your First and Last Name, Gmail Address, 11-digit Phone Number, Delivery Addresses, uploaded personal photos (for the Frame & Print service), and GCash payment receipts.</p>
        
        <h5 style="color: #0F473A; margin-top: 15px; font-size: 1rem;">2. How We Use Your Data</h5>
        <ul class="small text-muted">
            <li>To create and secure your RGA Frames account.</li>
            <li>To contact you regarding order updates, pick-ups, or delivery.</li>
            <li><strong>Photo Uploads:</strong> Your uploaded images are strictly used to print and assemble your custom frame. They will NEVER be posted publicly, sold, or shared without your explicit consent.</li>
            <li><strong>Payment Proofs:</strong> GCash receipts are collected solely by our admin for payment verification.</li>
        </ul>
        
        <h5 style="color: #0F473A; margin-top: 15px; font-size: 1rem;">3. Data Protection & Security</h5>
        <p class="small text-muted">Your passwords are encrypted securely in our database. Only authorized RGA Frames administrators have access to your uploaded images, addresses, and receipts.</p>
        
        <button type="button" onclick="document.getElementById('data_privacy').checked = true; document.getElementById('privacy-modal').style.display='none';" style="background: #0F473A; color: #fff; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; margin-top: 20px; width: 100%; font-weight: bold; font-size: 1rem;">
            I Understand and Accept
        </button>
    </div>
</div>
    <script src="assets/js/main.js"></script>
</body>
</html>