<?php
/**
 * Manage Seller Requests Page
 *
 * Admin can review and approve/reject clothing items submitted by sellers
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$message = '';
$messageType = '';

// Handle approve/reject/delete actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $clothingID = intval($_GET['id']);
    
    if ($clothingID > 0) {
        if ($action === 'approve' || $action === 'reject') {
            $newStatus = ($action === 'approve') ? 'approved' : 'rejected';
            
            $updateSql = "UPDATE tblClothes SET approvalStatus = ? WHERE clothingID = ?";
            $updateStmt = $conn->prepare($updateSql);
            
            if (!$updateStmt) {
                $message = "Database error: " . $conn->error;
                $messageType = "error";
            } else {
                $updateStmt->bind_param("si", $newStatus, $clothingID);
                
                if ($updateStmt->execute()) {
                    $message = "Item " . htmlspecialchars($action) . "ed successfully.";
                    $messageType = "success";
                } else {
                    $message = "Error updating item: " . $updateStmt->error;
                    $messageType = "error";
                }
                $updateStmt->close();
            }
        } elseif ($action === 'delete') {
            $deleteSql = "DELETE FROM tblClothes WHERE clothingID = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            
            if (!$deleteStmt) {
                $message = "Database error: " . $conn->error;
                $messageType = "error";
            } else {
                $deleteStmt->bind_param("i", $clothingID);
                
                if ($deleteStmt->execute()) {
                    $message = "Item deleted successfully.";
                    $messageType = "success";
                } else {
                    $message = "Error deleting item: " . $deleteStmt->error;
                    $messageType = "error";
                }
                $deleteStmt->close();
            }
        }
    }
}

// Fetch pending seller requests
$sql = "SELECT c.clothingID, c.clothingName, c.brand, c.category, c.size, c.clothingCondition, 
               c.price, c.description, c.imageURL, c.createdDate, c.sellerID, u.username, u.fullName, u.email
        FROM tblClothes c
        LEFT JOIN tblUser u ON c.sellerID = u.userID
        WHERE c.approvalStatus = 'pending'
        ORDER BY c.createdDate DESC";
$result = $conn->query($sql);
$pendingItems = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pendingItems[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Seller Requests - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css?v=5">
</head>
<body class="admin-page">
    <header>
        <?php include '../includes/navbar.php'; ?>
    </header>

    <main>
        <div class="admin-container">
            <nav class="admin-breadcrumb">Dashboard / <span>Seller Requests</span></nav>

            <div class="admin-page-head">
                <div>
                    <h1 class="admin-title">Manage Seller Requests</h1>
                    <p class="admin-subtitle">Review items submitted by sellers and approve, reject, or remove them.</p>
                </div>
                <a href="dashboard.php" class="admin-action-btn ghost">Back to Dashboard</a>
            </div>

            <?php if (!empty($message)): ?>
                <div class="<?php echo $messageType === 'success' ? 'success-message' : 'error-message'; ?>">
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (count($pendingItems) > 0): ?>
                <p class="admin-subtitle" style="margin-bottom:1.25rem;">
                    <?php echo count($pendingItems); ?> pending request<?php echo count($pendingItems) !== 1 ? 's' : ''; ?>
                </p>

                <?php foreach ($pendingItems as $item): ?>
                    <div class="admin-card admin-request">
                        <div class="admin-request-top">
                            <?php if (!empty($item['imageURL'])): ?>
                                <img class="admin-request-img" src="<?php echo htmlspecialchars($item['imageURL']); ?>" alt="<?php echo htmlspecialchars($item['clothingName']); ?>" onerror="this.style.display='none';">
                            <?php endif; ?>
                            <div style="flex:1; min-width:0;">
                                <div class="admin-request-head">
                                    <h3><?php echo htmlspecialchars($item['clothingName']); ?></h3>
                                    <span class="admin-badge is-amber">Pending</span>
                                </div>
                                <div class="admin-meta-grid">
                                    <div><span class="admin-meta-label">Brand</span><span class="admin-meta-value"><?php echo htmlspecialchars($item['brand'] ?? 'N/A'); ?></span></div>
                                    <div><span class="admin-meta-label">Category</span><span class="admin-meta-value"><?php echo htmlspecialchars($item['category']); ?></span></div>
                                    <div><span class="admin-meta-label">Size</span><span class="admin-meta-value"><?php echo htmlspecialchars($item['size']); ?></span></div>
                                    <div><span class="admin-meta-label">Condition</span><span class="admin-meta-value"><?php echo htmlspecialchars($item['clothingCondition']); ?></span></div>
                                    <div><span class="admin-meta-label">Price</span><span class="admin-meta-value">R <?php echo number_format($item['price'], 2); ?></span></div>
                                    <div><span class="admin-meta-label">Submitted</span><span class="admin-meta-value"><?php echo htmlspecialchars($item['createdDate']); ?></span></div>
                                </div>
                            </div>
                        </div>

                        <div class="admin-seller-block">
                            <span class="admin-meta-label">Submitted by</span>
                            <p>Name: <?php echo htmlspecialchars($item['fullName'] ?? 'Unknown'); ?></p>
                            <p>Username: <?php echo htmlspecialchars($item['username'] ?? 'Unknown'); ?></p>
                            <p>Email: <?php echo htmlspecialchars($item['email'] ?? 'Unknown'); ?></p>
                        </div>

                        <?php if (!empty($item['description'])): ?>
                            <p class="admin-request-desc"><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php endif; ?>

                        <div class="admin-row-actions">
                            <a href="?action=approve&id=<?php echo $item['clothingID']; ?>" class="admin-action-btn success" onclick="return confirm('Approve this item?');">Approve</a>
                            <a href="?action=reject&id=<?php echo $item['clothingID']; ?>" class="admin-action-btn danger" onclick="return confirm('Reject this item?');">Reject</a>
                            <a href="?action=delete&id=<?php echo $item['clothingID']; ?>" class="admin-action-btn danger" onclick="return confirm('Delete this item permanently?');">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="admin-card">
                    <p class="admin-empty">No pending seller requests at this time.</p>
                </div>
            <?php endif; ?>

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
                        <li><a href="pages/my-orders.php" class="footer-link">My Orders</a></li>
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
