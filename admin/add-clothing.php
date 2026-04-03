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
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $clothingName = isset($_POST['clothingName']) ? $_POST['clothingName'] : '';
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    
    if (empty($clothingName) || empty($category) || $price <= 0 || $quantity < 0) {
        $error = "Please fill in all required fields correctly.";
    } else {
        $sql = "INSERT INTO tblClothes (clothingName, category, description, price, quantity) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdi", $clothingName, $category, $description, $price, $quantity);
        
        if ($stmt->execute()) {
            $success = "Clothing item added successfully!";
            $clothingName = '';
            $category = '';
            $description = '';
            $price = '';
            $quantity = '';
        } else {
            $error = "Error adding clothing item: " . $conn->error;
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Clothing - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css">
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
            
            <form method="POST" action="add-clothing.php" novalidate>
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
