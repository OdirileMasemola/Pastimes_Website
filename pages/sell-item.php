<?php
/**
 * Sell an Item Page
 *
 * Logged-in users can submit clothing items for sale
 * Items are marked as pending and must be approved by admin
 */

session_start();
include '../includes/DBConn.php';

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$message = '';
$messageType = '';

// Initialize form variables
$clothingName = '';
$brand = '';
$category = '';
$description = '';
$size = '';
$clothingCondition = '';
$price = 0;
$imageURL = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $clothingName = isset($_POST['clothingName']) ? trim($_POST['clothingName']) : '';
    $brand = isset($_POST['brand']) ? trim($_POST['brand']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $size = isset($_POST['size']) ? trim($_POST['size']) : '';
    $clothingCondition = isset($_POST['clothingCondition']) ? trim($_POST['clothingCondition']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
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
            $message = "Only JPEG, PNG, GIF, and WebP image formats are allowed.";
            $messageType = "error";
        } elseif ($fileSize > 5242880) { // 5MB max
            $message = "File size must be less than 5MB.";
            $messageType = "error";
        } else {
            // Create upload directory if it doesn't exist
            $uploadDir = '../images/uploads/sellers/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $uploadedFileName = 'item_' . $userID . '_' . time() . '.' . $fileExt;
            $uploadPath = $uploadDir . $uploadedFileName;
            
            // Move uploaded file
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                $uploadedFile = $uploadPath;
                $imageURL = $uploadedFile; // Use uploaded file as image URL
            } else {
                $message = "Error uploading file. Please try again.";
                $messageType = "error";
            }
        }
    }
    
    // Validate required fields
    if (empty($clothingName) || empty($category) || empty($size) || empty($clothingCondition) || $price <= 0) {
        $message = "Please fill in all required fields correctly.";
        $messageType = "error";
    } elseif ($messageType !== "error") {
        // Insert into database
        $sql = "INSERT INTO tblClothes (clothingName, brand, category, description, size, clothingCondition, price, imageURL, sellerID, approvalStatus) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            $message = "Database error: " . $conn->error;
            $messageType = "error";
        } else {
            $stmt->bind_param("ssssssdsss", $clothingName, $brand, $category, $description, $size, $clothingCondition, $price, $imageURL, $userID);
            
            if ($stmt->execute()) {
                $message = "Item submitted successfully! Admin will review your request.";
                $messageType = "success";
                // Clear form
                $clothingName = $brand = $category = $description = $size = $clothingCondition = $imageURL = '';
                $price = 0;
            } else {
                $message = "Error submitting item: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell an Item - Pastimes</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .sell-form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        
        input[type="file"] {
            padding: 5px;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background-color: #0056b3;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <main>
        <div class="container">
            <div class="sell-form-container">
                <h2>Sell an Item</h2>
                <p>Submit your clothing item for sale. Our admin team will review your request and approve it within 24 hours.</p>
                
                <?php if (!empty($message)): ?>
                    <div class="message <?php echo htmlspecialchars($messageType); ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="clothingName">Clothing Name *</label>
                        <input type="text" id="clothingName" name="clothingName" value="<?php echo htmlspecialchars($clothingName); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="brand">Brand</label>
                        <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($brand); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">-- Select a category --</option>
                            <option value="Men" <?php if ($category === 'Men') echo 'selected'; ?>>Men</option>
                            <option value="Women" <?php if ($category === 'Women') echo 'selected'; ?>>Women</option>
                            <option value="Unisex" <?php if ($category === 'Unisex') echo 'selected'; ?>>Unisex</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="size">Size *</label>
                        <input type="text" id="size" name="size" value="<?php echo htmlspecialchars($size); ?>" placeholder="e.g., S, M, L, XL" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="clothingCondition">Condition *</label>
                        <select id="clothingCondition" name="clothingCondition" required>
                            <option value="">-- Select condition --</option>
                            <option value="New" <?php if ($clothingCondition === 'New') echo 'selected'; ?>>New</option>
                            <option value="Like New" <?php if ($clothingCondition === 'Like New') echo 'selected'; ?>>Like New</option>
                            <option value="Good" <?php if ($clothingCondition === 'Good') echo 'selected'; ?>>Good</option>
                            <option value="Fair" <?php if ($clothingCondition === 'Fair') echo 'selected'; ?>>Fair</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (USD) *</label>
                        <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($price > 0 ? $price : ''); ?>" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="imageFile">Upload Image from Device</label>
                        <input type="file" id="imageFile" name="imageFile" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small style="color: #666; display: block; margin-top: 5px;">Supported formats: JPEG, PNG, GIF, WebP. Max size: 5MB</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="imageURL">Or Paste Image URL</label>
                        <input type="text" id="imageURL" name="imageURL" value="<?php echo htmlspecialchars($imageURL); ?>" placeholder="e.g., https://example.com/shirt.jpg">
                        <small style="color: #666; display: block; margin-top: 5px;">Use this if you have an image link instead of uploading</small>
                    </div>
                    
                    <button type="submit">Submit Item for Approval</button>
                    <a href="account.php" class="btn btn-secondary" style="display: inline-block; margin-left: 10px; background-color: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Back to Account</a>
                </form>
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
ST10441421 - Odirile Masemola
ST10450294 - Ripfumelo Mabasa
All rights reserved.
*/
?>
