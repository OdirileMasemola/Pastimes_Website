<?php
/**
 * Manage Orders Page
 * 
 * Admin can view and manage all customer orders
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$message = '';
$allowedStatuses = array('pending', 'processing', 'shipped', 'delivered', 'cancelled');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['orderID'], $_POST['status'])) {
    $orderID = intval($_POST['orderID']);
    $status = strtolower(trim($_POST['status']));

    if ($orderID > 0 && in_array($status, $allowedStatuses, true)) {
        $updateSql = "UPDATE tblOrder SET status = ? WHERE orderID = ?";
        $updateStmt = $conn->prepare($updateSql);

        if ($updateStmt) {
            $updateStmt->bind_param("si", $status, $orderID);

            if ($updateStmt->execute()) {
                $message = "Order status updated successfully.";
            } else {
                $message = "Error updating order status: " . $conn->error;
            }

            $updateStmt->close();
        } else {
            $message = "Database error: " . $conn->error;
        }
    } else {
        $message = "Please select a valid order status.";
    }
}

$sql = "SELECT 
    o.orderID, 
    o.orderDate, 
    o.totalAmount, 
    o.status, 
    u.fullName, 
    u.email 
FROM tblOrder o 
INNER JOIN tblUser u ON o.userID = u.userID 
ORDER BY o.orderDate DESC";

$result = $conn->query($sql);
$orders = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

$conn->close();

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
    <title>Manage Orders - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css?v=5">
</head>
<body class="admin-page">
    <header>
        <?php include '../includes/navbar.php'; ?>
    </header>

    <main>
        <div class="admin-container">
            <nav class="admin-breadcrumb">Dashboard / <span>Orders</span></nav>

            <div class="admin-page-head">
                <div>
                    <h1 class="admin-title">Manage Orders</h1>
                    <p class="admin-subtitle">View customer orders and update their fulfilment status.</p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="success-message">
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <?php if (count($orders) > 0): ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer Name</th>
                                    <th>Email</th>
                                    <th>Order Date</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Update Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($order['orderID']); ?></td>
                                        <td><?php echo htmlspecialchars($order['fullName']); ?></td>
                                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                                        <td><?php echo htmlspecialchars($order['orderDate']); ?></td>
                                        <td>R <?php echo number_format($order['totalAmount'], 2); ?></td>
                                        <td><span class="admin-badge <?php echo adminBadgeClass($order['status']); ?>"><?php echo htmlspecialchars(ucfirst(strtolower($order['status']))); ?></span></td>
                                        <td>
                                            <form method="POST" action="manage-orders.php" class="admin-inline-form">
                                                <input type="hidden" name="orderID" value="<?php echo $order['orderID']; ?>">
                                                <select name="status">
                                                    <option value="pending" <?php echo strtolower($order['status']) === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="processing" <?php echo strtolower($order['status']) === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                    <option value="shipped" <?php echo strtolower($order['status']) === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                    <option value="delivered" <?php echo strtolower($order['status']) === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?php echo strtolower($order['status']) === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                                <button type="submit" class="admin-action-btn primary">Update</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="admin-empty">No orders found.</p>
                <?php endif; ?>
            </div>

            <a href="dashboard.php" class="admin-action-btn ghost admin-back">Back to Dashboard</a>
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
</body>
</html>
<?php
/*
This code is the original work of:
ST10441421 	- Odirile Masemola
ST10450294 	- Ripfumelo Mabasa
*/
?>