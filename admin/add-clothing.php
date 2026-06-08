<?php
/**
 * Add Clothing Page
 * 
 * Admin can add new clothing items to the inventory
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$clothingName = '';
$category = '';
$description = '';
$price = '';
$quantity = '';
$imageURL = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $clothingName = isset($_POST['clothingName']) ? trim($_POST['clothingName']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $imageURL = isset($_POST['imageURL']) ? trim($_POST['imageURL']) : '';
    
    // Handle file upload
    $uploadedFile = '';
    if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imageFile']['tmp_name'];
        $fileName = $_FILES['imageFile']['name'];
        $fileSize = $_FILES['imageFile']['size'];
        $fileType = $_FILES['imageFile']['type'];
        
        // Validate file type (only images)
        $allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        if (!in_array($fileType, $allowedTypes)) {
            $error = "Only JPEG, PNG, GIF, and WebP image formats are allowed.";
        } elseif ($fileSize > 5242880) { // 5MB max
            $error = "File size must be less than 5MB.";
        } else {
            // Create upload directory if it doesn't exist
            $uploadDir = '../images/uploads/admin/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $uploadedFileName = 'admin_' . $_SESSION['adminID'] . '_' . time() . '.' . $fileExt;
            $uploadPath = $uploadDir . $uploadedFileName;
            
            // Move uploaded file
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                $uploadedFile = $uploadPath;
                $imageURL = $uploadedFile; // Use uploaded file as image URL
            } else {
                $error = "Error uploading file. Please try again.";
            }
        }
    }
    
    if (empty($clothingName) || empty($category) || $price <= 0 || $quantity < 0) {
        $error = "Please fill in all required fields correctly.";
    } elseif (empty($error)) {
        $sql = "INSERT INTO tblClothes (clothingName, category, description, price, quantity, imageURL, approvalStatus) VALUES (?, ?, ?, ?, ?, ?, 'approved')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdis", $clothingName, $category, $description, $price, $quantity, $imageURL);
        
        if ($stmt->execute()) {
            $success = "Clothing item added successfully!";
            $clothingName = '';
            $category = '';
            $description = '';
            $price = '';
            $quantity = '';
            $imageURL = '';
        } else {
            $error = "Error adding clothing item: " . $conn->error;
        }
        
        $stmt->close();
    }
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
    <title>Add Clothing - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css?v=5">
</head>
<body class="admin-page">
    <header>
        <?php include '../includes/navbar.php'; ?>
    </header>

    <main>
        <div class="admin-container">
            <nav class="admin-breadcrumb">Dashboard / Clothing / <span>Add Item</span></nav>

            <div class="admin-page-head">
                <div>
                    <h1 class="admin-title">Add New Clothing Item</h1>
                    <p class="admin-subtitle">Add a new product to the store inventory.</p>
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
            
            <form method="POST" action="add-clothing.php" enctype="multipart/form-data" class="admin-form-card" novalidate>
                <div class="admin-form-grid">
                    <div class="form-group">
                        <label for="clothingName">Clothing Name</label>
                        <input 
                            type="text" 
                            id="clothingName" 
                            name="clothingName" 
                            value="<?php echo htmlspecialchars($clothingName); ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <input 
                            type="text" 
                            id="category" 
                            name="category" 
                            value="<?php echo htmlspecialchars($category); ?>" 
                            required
                        >
                    </div>

                    <div class="form-group admin-full">
                        <label for="description">Description</label>
                        <textarea 
                            id="description" 
                            name="description"
                        ><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (R)</label>
                        <input 
                            type="number" 
                            id="price" 
                            name="price" 
                            value="<?php echo htmlspecialchars($price); ?>" 
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
                            value="<?php echo htmlspecialchars($quantity); ?>" 
                            min="0"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="imageFile">Upload Image from Device</label>
                        <input 
                            type="file" 
                            id="imageFile" 
                            name="imageFile"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                        >
                        <small style="display: block; margin-top: 5px;">Supported formats: JPEG, PNG, GIF, WebP. Max size: 5MB</small>
                    </div>

                    <div class="form-group">
                        <label for="imageURL">Or Paste Image URL</label>
                        <input 
                            type="text" 
                            id="imageURL" 
                            name="imageURL"
                            value="<?php echo htmlspecialchars($imageURL); ?>"
                            placeholder="e.g., https://example.com/shirt.jpg"
                        >
                        <small style="display: block; margin-top: 5px;">Use this if you have an image link instead of uploading</small>
                    </div>
                </div>

                <div class="admin-row-actions">
                    <button type="submit" class="admin-action-btn primary">Add Clothing Item</button>
                    <a href="manage-clothes.php" class="admin-action-btn ghost">Cancel</a>
                </div>
            </form>
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
