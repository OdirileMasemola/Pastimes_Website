<?php
/**
 * Admin Dashboard
 * 
 * Main page for admin users
 * Provides links to manage users, clothes, and orders
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$userCount = 0;
$pendingUserCount = 0;
$clothingCount = 0;
$orderCount = 0;
$pendingSellerCount = 0;

$userResult = $conn->query("SELECT COUNT(*) AS total FROM tblUser");
if ($userResult && $userRow = $userResult->fetch_assoc()) {
    $userCount = (int) $userRow['total'];
}

$pendingResult = $conn->query("SELECT COUNT(*) AS total FROM tblUser WHERE isVerified = 0");
if ($pendingResult && $pendingRow = $pendingResult->fetch_assoc()) {
    $pendingUserCount = (int) $pendingRow['total'];
}

$clothingResult = $conn->query("SELECT COUNT(*) AS total FROM tblClothes");
if ($clothingResult && $clothingRow = $clothingResult->fetch_assoc()) {
    $clothingCount = (int) $clothingRow['total'];
}

$orderResult = $conn->query("SELECT COUNT(*) AS total FROM tblOrder");
if ($orderResult && $orderRow = $orderResult->fetch_assoc()) {
    $orderCount = (int) $orderRow['total'];
}

$pendingSellerResult = $conn->query("SELECT COUNT(*) AS total FROM tblClothes WHERE approvalStatus = 'pending'");
if ($pendingSellerResult && $pendingSellerRow = $pendingSellerResult->fetch_assoc()) {
    $pendingSellerCount = (int) $pendingSellerRow['total'];
}

$recentOrders = array();
$orderDetailsResult = $conn->query("SELECT o.orderID, o.orderDate, o.totalAmount, o.status, u.fullName 
                                     FROM tblOrder o 
                                     INNER JOIN tblUser u ON o.userID = u.userID 
                                     ORDER BY o.orderDate DESC 
                                     LIMIT 5");
if ($orderDetailsResult && $orderDetailsResult->num_rows > 0) {
    while ($row = $orderDetailsResult->fetch_assoc()) {
        $recentOrders[] = $row;
    }
}

// Pending seller requests (read-only, for the dashboard table)
$pendingSellerItems = array();
$pendingSellerResult2 = $conn->query("SELECT c.clothingID, c.clothingName, c.price, c.createdDate, u.fullName AS sellerName
                                       FROM tblClothes c
                                       LEFT JOIN tblUser u ON c.sellerID = u.userID
                                       WHERE c.approvalStatus = 'pending'
                                       ORDER BY c.createdDate DESC
                                       LIMIT 5");
if ($pendingSellerResult2 && $pendingSellerResult2->num_rows > 0) {
    while ($row = $pendingSellerResult2->fetch_assoc()) {
        $pendingSellerItems[] = $row;
    }
}

$conn->close();

// Map a status string to a badge colour class
if (!function_exists('adminBadgeClass')) {
    function adminBadgeClass($status) {
        $s = strtolower(trim($status));
        if (in_array($s, array('verified', 'approved', 'delivered', 'active'), true)) return 'is-green';
        if (in_array($s, array('pending', 'processing', 'shipped'), true)) return 'is-amber';
        if (in_array($s, array('rejected', 'cancelled', 'canceled', 'unverified'), true)) return 'is-red';
        return 'is-neutral';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css?v=5">
</head>
<body class="admin-page">
    <?php include '../includes/navbar.php'; ?>

    <main>
        <div class="admin-container">
            <nav class="admin-breadcrumb">Dashboard / <span>Overview</span></nav>

            <div class="admin-page-head">
                <div>
                    <h1 class="admin-title">Admin Dashboard</h1>
                    <p class="admin-subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['adminName']); ?>. Manage users, products, and orders from one place.</p>
                </div>
                <a href="../index.php" class="admin-action-btn ghost">View Storefront</a>
            </div>

            <!-- Summary cards -->
            <section class="admin-grid" aria-label="Dashboard metrics">
                <article class="admin-stat">
                    <span class="admin-stat-icon"><i class="fa-solid fa-users"></i></span>
                    <span class="admin-stat-label">Total Users</span>
                    <span class="admin-stat-value"><?php echo $userCount; ?></span>
                </article>
                <article class="admin-stat">
                    <span class="admin-stat-icon"><i class="fa-solid fa-shirt"></i></span>
                    <span class="admin-stat-label">Total Clothing Items</span>
                    <span class="admin-stat-value"><?php echo $clothingCount; ?></span>
                </article>
                <article class="admin-stat">
                    <span class="admin-stat-icon"><i class="fa-solid fa-box"></i></span>
                    <span class="admin-stat-label">Total Orders</span>
                    <span class="admin-stat-value"><?php echo $orderCount; ?></span>
                </article>
                <article class="admin-stat">
                    <span class="admin-stat-icon"><i class="fa-solid fa-clock"></i></span>
                    <span class="admin-stat-label">Pending Seller Requests</span>
                    <span class="admin-stat-value"><?php echo $pendingSellerCount; ?></span>
                </article>
            </section>

            <!-- Quick actions -->
            <div class="admin-card">
                <h3 class="admin-card-title">Quick Actions</h3>
                <div class="admin-row-actions">
                    <a href="manage-users.php" class="admin-action-btn ghost">Manage Users</a>
                    <a href="add-user.php" class="admin-action-btn ghost">Add User</a>
                    <a href="manage-clothes.php" class="admin-action-btn ghost">Manage Clothing</a>
                    <a href="add-clothing.php" class="admin-action-btn ghost">Add Clothing</a>
                    <a href="manage-seller-requests.php" class="admin-action-btn ghost">Seller Requests (<?php echo $pendingSellerCount; ?>)</a>
                    <a href="manage-orders.php" class="admin-action-btn ghost">Manage Orders</a>
                    <a href="admin-send-message.php" class="admin-action-btn ghost">Send Message</a>
                </div>
            </div>

            <!-- Recent orders -->
            <div class="admin-card">
                <h3 class="admin-card-title">Recent Orders</h3>
                <?php if (count($recentOrders) > 0): ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($order['orderID']); ?></td>
                                        <td><?php echo htmlspecialchars($order['fullName']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['orderDate'])); ?></td>
                                        <td>R <?php echo number_format($order['totalAmount'], 2); ?></td>
                                        <td><span class="admin-badge <?php echo adminBadgeClass($order['status']); ?>"><?php echo htmlspecialchars(ucfirst(strtolower($order['status']))); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="admin-empty">No recent orders.</p>
                <?php endif; ?>
            </div>

            <!-- Pending seller requests -->
            <?php if (count($pendingSellerItems) > 0): ?>
            <div class="admin-card">
                <h3 class="admin-card-title">Pending Seller Requests</h3>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item</th>
                                <th>Seller</th>
                                <th>Price</th>
                                <th>Submitted</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingSellerItems as $req): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($req['clothingID']); ?></td>
                                    <td><?php echo htmlspecialchars($req['clothingName']); ?></td>
                                    <td><?php echo htmlspecialchars($req['sellerName'] !== null ? $req['sellerName'] : 'Unknown'); ?></td>
                                    <td>R <?php echo number_format($req['price'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($req['createdDate'])); ?></td>
                                    <td><span class="admin-badge is-amber">Pending</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="admin-row-actions" style="margin-top:1rem;">
                    <a href="manage-seller-requests.php" class="admin-action-btn primary">Review All Requests</a>
                </div>
            </div>
            <?php endif; ?>
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
        });
    </script>
</body>
</html>
<?php
/*
This code is the original work of:
ST10441421 	- Odirile Masemola
ST10450294 	- Ripfumelo Mabasa
*/
?>
