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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pastimes</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="admin-dashboard-page">
    <?php include '../includes/navbar.php'; ?>

    <main class="admin-main">
        <div class="container">
            <section class="admin-dash-hero">
                <div>
                    <p class="admin-dash-kicker">Control Center</p>
                    <h2>Admin Dashboard</h2>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['adminName']); ?>. Manage users, products, and orders from one place.</p>
                </div>
                <a href="../index.php" class="btn btn-secondary">View Storefront</a>
            </section>

            <section class="admin-stats-grid" aria-label="Dashboard metrics">
                <article class="admin-stat-card">
                    <h3>Total Users</h3>
                    <p class="admin-stat-value"><?php echo $userCount; ?></p>
                </article>
                <article class="admin-stat-card">
                    <h3>Pending Verification</h3>
                    <p class="admin-stat-value"><?php echo $pendingUserCount; ?></p>
                </article>
                <article class="admin-stat-card">
                    <h3>Clothing Items</h3>
                    <p class="admin-stat-value"><?php echo $clothingCount; ?></p>
                </article>
                <article class="admin-stat-card">
                    <h3>Pending Seller Requests</h3>
                    <p class="admin-stat-value"><?php echo $pendingSellerCount; ?></p>
                </article>
                <article class="admin-stat-card">
                    <h3>Total Orders</h3>
                    <p class="admin-stat-value"><?php echo $orderCount; ?></p>
                </article>
            </section>

            <div class="admin-dashboard">
                <div class="dashboard-section">
                    <h3>Manage Users</h3>
                    <p>Verify new customer registrations and manage user accounts.</p>
                    <div class="dashboard-actions">
                        <a href="manage-users.php" class="btn btn-primary">View All Users</a>
                        <a href="add-user.php" class="btn btn-secondary">Add New User</a>
                    </div>
                </div>
                
                <div class="dashboard-section">
                    <h3>Manage Clothing</h3>
                    <p>Add, update, and delete clothing items from inventory.</p>
                    <div class="dashboard-actions">
                        <a href="manage-clothes.php" class="btn btn-primary">View All Clothes</a>
                        <a href="add-clothing.php" class="btn btn-secondary">Add New Clothing</a>
                    </div>
                </div>
                
                <div class="dashboard-section">
                    <h3>Manage Seller Requests</h3>
                    <p>Review and approve/reject clothing items submitted by sellers.</p>
                    <div class="dashboard-actions">
                        <a href="manage-seller-requests.php" class="btn btn-primary">View Seller Requests (<?php echo $pendingSellerCount; ?>)</a>
                    </div>
                </div>
                
                <div class="dashboard-section">
                    <h3>Manage Orders</h3>
                    <p>View and manage customer orders.</p>
                    <div class="dashboard-actions">
                        <a href="manage-orders.php" class="btn btn-primary">View All Orders</a>
                    </div>
                </div>

                <div class="dashboard-section">
                    <h3>Send Messages</h3>
                    <p>Send messages to customers.</p>
                    <div class="dashboard-actions">
                        <a href="admin-send-message.php" class="btn btn-primary">Send Message</a>
                    </div>
                </div>
            </div>

            <div class="admin-recent-orders" style="margin-top: 30px;">
                <h3>Recent Orders</h3>
                <?php if (count($recentOrders) > 0): ?>
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
                                    <td><?php echo htmlspecialchars($order['orderID']); ?></td>
                                    <td><?php echo htmlspecialchars($order['fullName']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['orderDate'])); ?></td>
                                    <td>R <?php echo number_format($order['totalAmount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst(strtolower($order['status']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No recent orders.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
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
