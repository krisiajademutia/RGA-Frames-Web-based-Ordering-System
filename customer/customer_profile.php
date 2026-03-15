<?php
// customer/customer_profile.php
session_start();
include __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}

$customer_id = (int)$_SESSION['user_id'];

// Fetch customer
$stmt = $conn->prepare("
    SELECT customer_id, first_name, last_name, username, email, phone_number, customer_type, created_at
    FROM tbl_customer WHERE customer_id = ?
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    header("Location: ../login.php");
    exit();
}

// Order stats
$statsStmt = $conn->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN order_status = 'COMPLETED' THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN order_status = 'PENDING'   THEN 1 ELSE 0 END) AS pending
    FROM tbl_orders WHERE customer_id = ?
");
$statsStmt->bind_param("i", $customer_id);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();

$isPhotographer = strtoupper($customer['customer_type']) === 'PHOTOGRAPHER';
$initial        = strtoupper(substr($customer['first_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — RGA Frames</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include __DIR__ . '/../includes/customer_header.php'; ?>

<div class="cust-prof-page">
    <div class="cust-prof-layout">

        <!-- ── LEFT COLUMN — Identity card ── -->
        <div class="cust-prof-sidebar">

            <!-- Avatar -->
            <div class="cust-prof-card cust-prof-identity">
                <div class="cust-prof-avatar"><?= $initial ?></div>
                <div class="cust-prof-fullname">
                    <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                </div>
                <div class="cust-prof-username">@<?= htmlspecialchars($customer['username']) ?></div>

                <!-- Customer type badge -->
                <?php if ($isPhotographer): ?>
                <div class="cust-prof-type-badge photographer">
                    <i class="fas fa-camera"></i> Photographer
                </div>
                <?php else: ?>
                <div class="cust-prof-type-badge regular">
                    <i class="fas fa-user"></i> Regular Customer
                </div>
                <?php endif; ?>

                <?php if ($isPhotographer): ?>
                <div class="cust-prof-discount-note">
                    <i class="fas fa-tag"></i> 20% loyalty discount applied to your orders
                </div>
                <?php endif; ?>

                <div class="cust-prof-member-since">
                    <i class="fas fa-calendar-alt"></i>
                    Member since <?= date('F Y', strtotime($customer['created_at'])) ?>
                </div>
            </div>

            <!-- Order stats -->
            <div class="cust-prof-card cust-prof-stats">
                <div class="cust-prof-stats-title">Order Summary</div>
                <div class="cust-prof-stat-row">
                    <span class="cust-prof-stat-label">Total Orders</span>
                    <span class="cust-prof-stat-val"><?= (int)$stats['total'] ?></span>
                </div>
                <div class="cust-prof-stat-row">
                    <span class="cust-prof-stat-label">Completed</span>
                    <span class="cust-prof-stat-val cust-prof-stat-green"><?= (int)$stats['completed'] ?></span>
                </div>
                <div class="cust-prof-stat-row">
                    <span class="cust-prof-stat-label">Pending</span>
                    <span class="cust-prof-stat-val cust-prof-stat-amber"><?= (int)$stats['pending'] ?></span>
                </div>
                <a href="customer_orders.php" class="cust-prof-view-orders-btn">
                    <i class="fas fa-list-alt"></i> View My Orders
                </a>
            </div>

        </div>

        <!-- ── RIGHT COLUMN — Edit forms ── -->
        <div class="cust-prof-main">

            <!-- Personal Info -->
            <div class="cust-prof-card">
                <div class="cust-prof-card-header">
                    <i class="fas fa-user-edit"></i> Personal Information
                </div>
                <div class="cust-prof-card-body">
                    <form id="form-info">
                        <div class="cust-prof-field-grid">
                            <div class="cust-prof-field">
                                <label class="cust-prof-label">First Name</label>
                                <input type="text" class="cust-prof-input" name="first_name"
                                       value="<?= htmlspecialchars($customer['first_name']) ?>" required>
                            </div>
                            <div class="cust-prof-field">
                                <label class="cust-prof-label">Last Name</label>
                                <input type="text" class="cust-prof-input" name="last_name"
                                       value="<?= htmlspecialchars($customer['last_name']) ?>" required>
                            </div>
                            <div class="cust-prof-field">
                                <label class="cust-prof-label">Username</label>
                                <input type="text" class="cust-prof-input cust-prof-input-readonly"
                                       value="<?= htmlspecialchars($customer['username']) ?>" readonly>
                                <span class="cust-prof-hint">Username cannot be changed</span>
                            </div>
                            <div class="cust-prof-field">
                                <label class="cust-prof-label">Phone Number</label>
                                <input type="text" class="cust-prof-input" name="phone_number"
                                       value="<?= htmlspecialchars($customer['phone_number'] ?? '') ?>"
                                       placeholder="e.g. 09123456789">
                            </div>
                            <div class="cust-prof-field cust-prof-field-full">
                                <label class="cust-prof-label">Email Address</label>
                                <input type="email" class="cust-prof-input cust-prof-input-readonly"
                                       value="<?= htmlspecialchars($customer['email']) ?>" readonly>
                                <span class="cust-prof-hint">Contact support to change your email</span>
                            </div>
                        </div>
                        <div class="cust-prof-form-footer">
                            <button type="submit" class="cust-prof-btn-save" id="btn-save-info">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="cust-prof-card">
                <div class="cust-prof-card-header">
                    <i class="fas fa-lock"></i> Change Password
                </div>
                <div class="cust-prof-card-body">
                    <form id="form-password">
                        <div class="cust-prof-field-grid">
                            <div class="cust-prof-field cust-prof-field-full">
                                <label class="cust-prof-label">Current Password</label>
                                <div class="cust-prof-input-wrap">
                                    <input type="password" class="cust-prof-input" name="current_password"
                                           id="inp-current-pw" placeholder="Enter current password" required>
                                    <button type="button" class="cust-prof-pw-toggle" onclick="togglePw('inp-current-pw', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="cust-prof-field">
                                <label class="cust-prof-label">New Password</label>
                                <div class="cust-prof-input-wrap">
                                    <input type="password" class="cust-prof-input" name="new_password"
                                           id="inp-new-pw" placeholder="Min. 8 characters" required>
                                    <button type="button" class="cust-prof-pw-toggle" onclick="togglePw('inp-new-pw', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="cust-prof-field">
                                <label class="cust-prof-label">Confirm New Password</label>
                                <div class="cust-prof-input-wrap">
                                    <input type="password" class="cust-prof-input" name="confirm_password"
                                           id="inp-confirm-pw" placeholder="Repeat new password" required>
                                    <button type="button" class="cust-prof-pw-toggle" onclick="togglePw('inp-confirm-pw', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="cust-prof-form-footer">
                            <button type="submit" class="cust-prof-btn-save" id="btn-save-pw">
                                <i class="fas fa-key"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div><!-- /.cust-prof-main -->
    </div><!-- /.cust-prof-layout -->
</div><!-- /.cust-prof-page -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Toggle password visibility ───────────────────────────
function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.querySelector('i').className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
}

// ── Save personal info ───────────────────────────────────
document.getElementById('form-info').addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('btn-save-info');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    const fd = new FormData(e.target);
    fd.append('action', 'update_info');

    try {
        const res  = await fetch('../process/profile_process.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Updated!', text: data.message,
                confirmButtonColor: '#0f3d33', timer: 1800, showConfirmButton: false })
                .then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
    }
});

// ── Change password ──────────────────────────────────────
document.getElementById('form-password').addEventListener('submit', async e => {
    e.preventDefault();
    const newPw     = document.getElementById('inp-new-pw').value;
    const confirmPw = document.getElementById('inp-confirm-pw').value;

    if (newPw.length < 8) {
        Swal.fire('Too Short', 'Password must be at least 8 characters.', 'warning'); return;
    }
    if (newPw !== confirmPw) {
        Swal.fire('Mismatch', 'New password and confirmation do not match.', 'warning'); return;
    }

    const btn = document.getElementById('btn-save-pw');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

    const fd = new FormData(e.target);
    fd.append('action', 'change_password');

    try {
        const res  = await fetch('../process/profile_process.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Password Updated!', text: data.message,
                confirmButtonColor: '#0f3d33', timer: 1800, showConfirmButton: false })
                .then(() => { e.target.reset(); });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-key"></i> Update Password';
    }
});
</script>
</body>
</html>