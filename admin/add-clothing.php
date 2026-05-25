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
ST10441421 - Odirile Masemola
ST10450294 - Ripfumelo Mabasa
All rights reserved.
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Clothing - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        input[type="file"] {
            padding: 5px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1>Pastimes - Admin Panel</h1>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h2>Add New Clothing Item</h2>
            
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
            
            <form method="POST" action="add-clothing.php" enctype="multipart/form-data" novalidate>
                <div class="form-group">
                    <label for="clothingName">Clothing Name:</label>
                    <input 
                        type="text" 
                        id="clothingName" 
                        name="clothingName" 
                        value="<?php echo htmlspecialchars($clothingName); ?>" 
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="category">Category:</label>
                    <input 
                        type="text" 
                        id="category" 
                        name="category" 
                        value="<?php echo htmlspecialchars($category); ?>" 
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea 
                        id="description" 
                        name="description"
                    ><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Price (R):</label>
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
                    <label for="quantity">Quantity:</label>
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
                    <label for="imageFile">Upload Image from Device:</label>
                    <input 
                        type="file" 
                        id="imageFile" 
                        name="imageFile"
                        accept="image/jpeg,image/png,image/gif,image/webp"
                    >
                    <small style="color: #666; display: block; margin-top: 5px;">Supported formats: JPEG, PNG, GIF, WebP. Max size: 5MB</small>
                </div>
                
                <div class="form-group">
                    <label for="imageURL">Or Paste Image URL:</label>
                    <input 
                        type="text" 
                        id="imageURL" 
                        name="imageURL"
                        value="<?php echo htmlspecialchars($imageURL); ?>"
                        placeholder="e.g., https://example.com/shirt.jpg"
                    >
                    <small style="color: #666; display: block; margin-top: 5px;">Use this if you have an image link instead of uploading</small>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Add Clothing Item</button>
                    <a href="manage-clothes.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
