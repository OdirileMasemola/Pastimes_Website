<?php
/**
 * Manage Clothing Page
 * 
 * Admin can view all clothing items, add, update, or delete items
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$sql = "SELECT * FROM tblClothes ORDER BY createdDate DESC";
$result = $conn->query($sql);
$clothes = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clothes[] = $row;
    }
}

$conn->close();

if (!function_exists('adminBadgeClass')) {
    function adminBadgeClass($status) {
        $s = strtolower(trim($status));
        if (in_array($s, array('verified', 'approved', 'delivered', 'active', 'featured'), true)) return 'is-green';
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
    <title>Manage Clothes - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css?v=5">
</head>
<body class="admin-page">
    <header>
        <?php include '../includes/navbar.php'; ?>
    </header>

    <main>
        <div class="admin-container">
            <nav class="admin-breadcrumb">Dashboard / <span>Clothing</span></nav>

            <div class="admin-page-head">
                <div>
                    <h1 class="admin-title">Manage Clothing</h1>
                    <p class="admin-subtitle">Add, update, and remove clothing items from the inventory.</p>
                </div>
                <a href="add-clothing.php" class="admin-action-btn primary">Add New Item</a>
            </div>

            <div class="admin-card">
                <?php if (count($clothes) > 0): ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Brand</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clothes as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($item['imageURL'])): ?>
                                                <img class="admin-thumb" src="<?php echo htmlspecialchars($item['imageURL']); ?>" alt="<?php echo htmlspecialchars($item['clothingName']); ?>" onerror="this.style.display='none';">
                                            <?php else: ?>
                                                <span class="admin-thumb"></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>#<?php echo htmlspecialchars($item['clothingID']); ?></td>
                                        <td><?php echo htmlspecialchars($item['clothingName']); ?></td>
                                        <td><?php echo htmlspecialchars($item['brand'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                                        <td>R <?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                        <td><span class="admin-badge <?php echo adminBadgeClass($item['approvalStatus']); ?>"><?php echo htmlspecialchars(ucfirst($item['approvalStatus'])); ?></span></td>
                                        <td>
                                            <div class="admin-row-actions">
                                                <a href="edit-clothing.php?id=<?php echo $item['clothingID']; ?>" class="admin-action-btn ghost">Edit</a>
                                                <a href="delete-clothing.php?id=<?php echo $item['clothingID']; ?>" class="admin-action-btn danger" onclick="return confirm('Are you sure?');">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="admin-empty">No clothing items found.</p>
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