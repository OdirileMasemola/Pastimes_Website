<?php
/**
 * Manage Orders Page
 * 
 * Admin can view and manage all customer orders
 */

session_start();
include("includes/DBConn.php");

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$message = '';
$allowedStatuses = array('pending', 'processing', 'delivered');

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1>Pastimes - Admin Panel</h1>
                </div>
                <ul class="nav-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage-orders.php">Manage Orders</a></li>
                    <li><a href="admin-logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h2>Manage Orders</h2>

            <?php if ($message): ?>
                <div class="success-message">
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (count($orders) > 0): ?>
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
                                <td><?php echo htmlspecialchars($order['orderID']); ?></td>
                                <td><?php echo htmlspecialchars($order['fullName']); ?></td>
                                <td><?php echo htmlspecialchars($order['email']); ?></td>
                                <td><?php echo htmlspecialchars($order['orderDate']); ?></td>
                                <td>R <?php echo number_format($order['totalAmount'], 2); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst(strtolower($order['status']))); ?></td>
                                <td>
                                    <form method="POST" action="manage-orders.php" style="display: flex; gap: 8px; align-items: center;">
                                        <input type="hidden" name="orderID" value="<?php echo $order['orderID']; ?>">
                                        <select name="status" class="form-group" style="margin: 0;">
                                            <option value="pending" <?php echo strtolower($order['status']) === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo strtolower($order['status']) === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="delivered" <?php echo strtolower($order['status']) === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No orders found.</p>
            <?php endif; ?>
            
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
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
