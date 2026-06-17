<?php
/**
 * User Account/Dashboard Page
 * 
 * Displays user profile information and links to account features
 * Only accessible to logged-in users
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$userID = $_SESSION['userID'];
$sql = "SELECT * FROM tblUser WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();

$fullName = isset($user['fullName']) ? trim((string) $user['fullName']) : '';
$nameParts = preg_split('/\s+/', $fullName);
$initials = '';
if (!empty($nameParts[0])) {
    $initials .= strtoupper(substr($nameParts[0], 0, 1));
}
if (count($nameParts) > 1 && !empty($nameParts[count($nameParts) - 1])) {
    $initials .= strtoupper(substr($nameParts[count($nameParts) - 1], 0, 1));
}
if ($initials === '') {
    $initials = 'U';
}

$memberSince = 'Not available';
if (!empty($user['createdDate'])) {
    $memberSince = date('M d, Y', strtotime($user['createdDate']));
} elseif (!empty($user['createdAt'])) {
    $memberSince = date('M d, Y', strtotime($user['createdAt']));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css?v=3">
</head>
<body class="account-page-body">
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Message Icon + Notification Popover (only for logged-in users) -->
    <?php include '../includes/messagePopover.php'; ?>
    
    <!-- Shopping Cart Icon -->
    <a href="cart.php" class="cart-icon-link" title="Shopping Cart">
        <i class="fa-solid fa-bag-shopping"></i>
        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <span class="cart-badge"><?php echo count($_SESSION['cart']); ?></span>
        <?php endif; ?>
    </a>

    <main>
        <div class="account-shell">
            <div class="account-page-head">
                <nav class="account-breadcrumb">Account / <span>Overview</span></nav>
                <h1 class="account-title">Account Dashboard</h1>
                <p class="account-subtitle">Manage your profile, orders, listings, and messages.</p>
            </div>

            <div class="account-grid account-grid-top">
                <section class="account-card account-summary-card" aria-label="Profile summary">
                    <div class="account-avatar"><?php echo htmlspecialchars($initials); ?></div>
                    <div class="account-summary-content">
                        <h2><?php echo htmlspecialchars($user['fullName']); ?></h2>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                        <div class="account-summary-meta">
                            <span class="account-status-badge <?php echo !empty($user['isVerified']) ? 'is-verified' : 'is-pending'; ?>">
                                <?php echo !empty($user['isVerified']) ? 'Verified' : 'Pending'; ?>
                            </span>
                            <span class="account-member-since">Member since: <?php echo htmlspecialchars($memberSince); ?></span>
                        </div>
                    </div>
                </section>

                <section class="account-card account-actions-card" aria-label="Quick actions">
                    <h3 class="account-card-title">Quick Actions</h3>
                    <div class="account-actions-grid">
                        <a href="cart.php" class="account-action-btn account-action-primary">View Orders</a>
                        <a href="sell-item.php" class="account-action-btn account-action-secondary">Sell an Item</a>
                        <a href="my-listings.php" class="account-action-btn account-action-secondary">My Listings</a>
                        <button type="button" class="account-action-btn account-action-secondary" id="accountMessageAdminBtn">Message Admin</button>
                        <a href="shop.php" class="account-action-btn account-action-secondary">Continue Shopping</a>
                    </div>
                </section>
            </div>

            <section class="account-card" aria-label="Profile information">
                <h3 class="account-card-title">Profile Information</h3>
                <div class="account-info-grid">
                    <div class="account-info-item">
                        <span class="account-info-label">Username</span>
                        <span class="account-info-value"><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div class="account-info-item">
                        <span class="account-info-label">Full Name</span>
                        <span class="account-info-value"><?php echo htmlspecialchars($user['fullName']); ?></span>
                    </div>
                    <div class="account-info-item">
                        <span class="account-info-label">Email</span>
                        <span class="account-info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="account-info-item">
                        <span class="account-info-label">Phone</span>
                        <span class="account-info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></span>
                    </div>
                    <div class="account-info-item">
                        <span class="account-info-label">Address</span>
                        <span class="account-info-value"><?php echo htmlspecialchars($user['address'] ?? 'Not provided'); ?></span>
                    </div>
                    <div class="account-info-item">
                        <span class="account-info-label">City</span>
                        <span class="account-info-value"><?php echo htmlspecialchars($user['city'] ?? 'Not provided'); ?></span>
                    </div>
                    <div class="account-info-item">
                        <span class="account-info-label">Account Status</span>
                        <span class="account-info-value"><?php echo !empty($user['isVerified']) ? 'Verified' : 'Pending Verification'; ?></span>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer Section -->
    <footer class="footer">
        <!-- Top Card Area -->
        <div class="footer-top-card"></div>

        <!-- Footer Content -->
        <div class="footer-content">
            <!-- Brand Section -->
            <div class="footer-brand">
                <h2 class="footer-brand-title">Pastimes</h2>
                <p class="footer-copyright">&copy; 2026 Pastimes. All rights reserved.</p>
            </div>

            <!-- Footer Grid (4 Columns) -->
            <div class="footer-grid">
                <!-- Product Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">Product</h4>
                    <ul class="footer-links">
                        <li><a href="pages/shop.php" class="footer-link">Shop</a></li>
                        <li><a href="pages/sell-item.php" class="footer-link">Sell Item</a></li>
                        <li><a href="pages/cart.php" class="footer-link">Cart</a></li>
                    </ul>
                </div>

                <!-- Company Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">Company</h4>
                    <ul class="footer-links">
                        <li><a href="index.php" class="footer-link">About</a></li>
                        <li><a href="pages/account.php" class="footer-link">Account</a></li>
                        <li><a href="pages/login.php" class="footer-link">Login</a></li>
                        <li><a href="pages/register.php" class="footer-link">Register</a></li>
                    </ul>
                </div>

                <!-- Resources Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">Resources</h4>
                    <ul class="footer-links">
                        <li><a href="index.php" class="footer-link">Help</a></li>
                        <li><a href="pages/my-messages.php" class="footer-link">Messages</a></li>
                        <li><a href="pages/my-listings.php" class="footer-link">Seller Listings</a></li>
                        <li><a href="admin/admin-login.php" class="footer-link">Admin</a></li>
                    </ul>
                </div>

                <!-- Social Links Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">Social</h4>
                    <ul class="footer-links">
                        <li><a href="#" class="footer-link">Facebook</a></li>
                        <li><a href="#" class="footer-link">Instagram</a></li>
                        <li><a href="#" class="footer-link">YouTube</a></li>
                        <li><a href="#" class="footer-link">LinkedIn</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navbarToggle = document.getElementById('navbarToggle');
            const navbarLinks = document.getElementById('navbarLinks');
            const messageAdminBtn = document.getElementById('accountMessageAdminBtn');
            
            if (navbarToggle && navbarLinks) {
                navbarToggle.addEventListener('click', function() {
                    navbarToggle.classList.toggle('active');
                    navbarLinks.classList.toggle('active');
                });
                
                const links = navbarLinks.querySelectorAll('a');
                links.forEach(link => {
                    link.addEventListener('click', function() {
                        navbarToggle.classList.remove('active');
                        navbarLinks.classList.remove('active');
                    });
                });
            }

            if (messageAdminBtn) {
                messageAdminBtn.addEventListener('click', function () {
                    if (typeof window.openMessageCompose === 'function') {
                        window.openMessageCompose({ subject: 'Message for admin' });
                    } else {
                        window.location.href = 'my-messages.php';
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php
/*
This code is the original work of:
ST10441421 - Odirile Masemola
ST10450294 - Ripfumelo Mabasa
All rights reserved.
*/
?>
