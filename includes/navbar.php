<?php
/**
 * Navbar Include File
 * Shared navbar component for all pages
 */

// Determine the correct paths based on current file location
$isAdminPage = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$isPagePage = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;

if ($isAdminPage) {
    // Admin pages: admin/xxx.php (admin/ is one level below the project root)
    $indexPath = '../index.php';
    $shopPath = '../pages/shop.php';
    $sellPath = '../pages/sell-item.php';
    $loginPath = '../pages/login.php';
    $registerPath = '../pages/register.php';
    $accountPath = '../pages/account.php';
    $logoutPath = '../pages/logout.php';
    $adminDashboardPath = 'dashboard.php';
    $adminLoginPath = 'admin-login.php';
    $cartPath = '../pages/cart.php';
    $myMessagesPath = '../pages/my-messages.php';
} elseif ($isPagePage) {
    // Pages folder: pages/xxx.php
    $indexPath = '../index.php';
    $shopPath = 'shop.php';
    $sellPath = 'sell-item.php';
    $loginPath = 'login.php';
    $registerPath = 'register.php';
    $accountPath = 'account.php';
    $logoutPath = 'logout.php';
    $adminDashboardPath = '../admin/dashboard.php';
    $adminLoginPath = '../admin/admin-login.php';
    $cartPath = 'cart.php';
    $myMessagesPath = 'my-messages.php';
} else {
    // Root pages: xxx.php
    $indexPath = 'index.php';
    $shopPath = 'pages/shop.php';
    $sellPath = 'pages/sell-item.php';
    $loginPath = 'pages/login.php';
    $registerPath = 'pages/register.php';
    $accountPath = 'pages/account.php';
    $logoutPath = 'pages/logout.php';
    $adminDashboardPath = 'admin/dashboard.php';
    $adminLoginPath = 'admin/admin-login.php';
    $cartPath = 'pages/cart.php';
    $myMessagesPath = 'pages/my-messages.php';
}

// Count unread messages for logged-in users.
// Use a dedicated short-lived connection so the navbar never depends on the
// including page's $conn, which may already have been closed (e.g. shop.php).
$unreadMessageCount = 0;
if (isset($_SESSION['userID'])) {
    $navConn = @new mysqli("localhost", "root", "", "ClothingStore");
    if (!$navConn->connect_error) {
        require_once __DIR__ . '/messageSchema.php';
        pastimesEnsureMessageSchema($navConn);

        $msgStmt = $navConn->prepare("SELECT COUNT(*) as unreadCount
                                      FROM tblMessage
                                      WHERE receiverType = 'user'
                                        AND receiverID = ?
                                        AND isRead = 0");
        if ($msgStmt) {
            $msgStmt->bind_param("i", $_SESSION['userID']);
            $msgStmt->execute();
            $msgResult = $msgStmt->get_result();
            if ($msgResult && $msgResult->num_rows > 0) {
                $msgRow = $msgResult->fetch_assoc();
                $unreadMessageCount = (int)$msgRow['unreadCount'];
            }
            $msgStmt->close();
        }
        $navConn->close();
    }
}
?>
<nav class="navbar-wrapper">
    <div class="navbar">
        <div class="navbar-brand">
            <a href="<?php echo $indexPath; ?>">
                <span class="brand-text">Pastimes</span>
            </a>
        </div>
        
        <button class="navbar-toggle" id="navbarToggle" aria-label="Toggle navigation menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <div class="navbar-links" id="navbarLinks">
            <a href="<?php echo $indexPath; ?>" class="nav-link">
                <div class="nav-link-inner">
                    <span>Home</span>
                    <span>Home</span>
                </div>
            </a>
            <a href="<?php echo $shopPath; ?>" class="nav-link">
                <div class="nav-link-inner">
                    <span>Shop</span>
                    <span>Shop</span>
                </div>
            </a>
            <a href="<?php echo $sellPath; ?>" class="nav-link">
                <div class="nav-link-inner">
                    <span>Sell</span>
                    <span>Sell</span>
                </div>
            </a>
        </div>
        
        <div class="navbar-actions">
            <?php if (isset($_SESSION['userID'])): ?>
                <a href="<?php echo $accountPath; ?>" class="btn btn-secondary">Account</a>
                <a href="<?php echo $logoutPath; ?>" class="btn btn-danger">Logout</a>
            <?php else: ?>
                <a href="<?php echo $loginPath; ?>" class="btn btn-secondary">Login</a>
                <a href="<?php echo $registerPath; ?>" class="btn btn-primary">Register</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['adminID'])): ?>
                <a href="<?php echo $adminDashboardPath; ?>" class="btn btn-secondary">Admin</a>
            <?php else: ?>
                <a href="<?php echo $adminLoginPath; ?>" class="btn btn-secondary">Admin</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navbarToggle = document.getElementById('navbarToggle');
    const navbarLinks = document.getElementById('navbarLinks');
    const navbarWrapper = document.querySelector('.navbar-wrapper');
    
    if (navbarToggle && navbarLinks) {
        navbarToggle.addEventListener('click', function() {
            navbarToggle.classList.toggle('active');
            navbarLinks.classList.toggle('active');
            navbarWrapper.classList.toggle('mobile-open');
        });
        
        // Close menu when a link is clicked
        const links = navbarLinks.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', function() {
                navbarToggle.classList.remove('active');
                navbarLinks.classList.remove('active');
                navbarWrapper.classList.remove('mobile-open');
            });
        });
    }
});
</script>
