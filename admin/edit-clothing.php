<?php
/**
 * Edit Clothing Page
 * 
 * Admin can edit clothing item information
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$clothingID = isset($_GET['id']) ? intval($_GET['id']) : 0;
$clothing = null;
$error = '';
$success = '';

if ($clothingID > 0) {
    $sql = "SELECT * FROM tblClothes WHERE clothingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $clothingID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $clothing = $result->fetch_assoc();
    } else {
        $error = "Clothing item not found.";
    }
    
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $clothing) {
    $clothingName = isset($_POST['clothingName']) ? $_POST['clothingName'] : '';
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    
    $updateSql = "UPDATE tblClothes SET clothingName = ?, category = ?, description = ?, price = ?, quantity = ? WHERE clothingID = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sssdii", $clothingName, $category, $description, $price, $quantity, $clothingID);
    
    if ($updateStmt->execute()) {
        $success = "Clothing item updated successfully!";
        $clothing['clothingName'] = $clothingName;
        $clothing['category'] = $category;
        $clothing['description'] = $description;
        $clothing['price'] = $price;
        $clothing['quantity'] = $quantity;
    } else {
        $error = "Error updating clothing item: " . $conn->error;
    }
    
    $updateStmt->close();
}

$conn->close();
?>
<?php
/*
This code is the original work of:
ST10441421 	- Odirile Masemola
ST10450294 	- Ripfumelo Mabasa
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Clothing - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css?v=5">
</head>
<body class="admin-page">
    <header>
        <?php include '../includes/navbar.php'; ?>
    </header>

    <main>
        <div class="admin-container">
            <nav class="admin-breadcrumb">Dashboard / Clothing / <span>Edit Item</span></nav>

            <div class="admin-page-head">
                <div>
                    <h1 class="admin-title">Edit Clothing Item</h1>
                    <p class="admin-subtitle">Update the details for this product.</p>
                </div>
                <a href="manage-clothes.php" class="admin-action-btn ghost">Back to Clothing</a>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($clothing): ?>
                <form method="POST" action="edit-clothing.php?id=<?php echo $clothingID; ?>" class="admin-form-card" novalidate>
                    <div class="admin-form-grid">
                        <div class="form-group">
                            <label for="clothingName">Clothing Name</label>
                            <input 
                                type="text" 
                                id="clothingName" 
                                name="clothingName" 
                                value="<?php echo htmlspecialchars($clothing['clothingName']); ?>" 
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="category">Category</label>
                            <input 
                                type="text" 
                                id="category" 
                                name="category" 
                                value="<?php echo htmlspecialchars($clothing['category']); ?>" 
                                required
                            >
                        </div>

                        <div class="form-group admin-full">
                            <label for="description">Description</label>
                            <textarea 
                                id="description" 
                                name="description"
                            ><?php echo htmlspecialchars($clothing['description']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="price">Price (R)</label>
                            <input 
                                type="number" 
                                id="price" 
                                name="price" 
                                value="<?php echo htmlspecialchars($clothing['price']); ?>" 
                                step="0.01"
                                min="0"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input 
                                type="number" 
                                id="quantity" 
                                name="quantity" 
                                value="<?php echo htmlspecialchars($clothing['quantity']); ?>" 
                                min="0"
                                required
                            >
                        </div>
                    </div>

                    <div class="admin-row-actions">
                        <button type="submit" class="admin-action-btn primary">Update Clothing Item</button>
                        <a href="manage-clothes.php" class="admin-action-btn ghost">Cancel</a>
                    </div>
                </form>
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
