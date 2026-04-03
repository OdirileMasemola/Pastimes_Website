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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Clothing - Admin Panel</title>
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
            <h2>Edit Clothing Item</h2>
            
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
                <form method="POST" action="edit-clothing.php?id=<?php echo $clothingID; ?>" novalidate>
                    <div class="form-group">
                        <label for="clothingName">Clothing Name:</label>
                        <input 
                            type="text" 
                            id="clothingName" 
                            name="clothingName" 
                            value="<?php echo htmlspecialchars($clothing['clothingName']); ?>" 
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <input 
                            type="text" 
                            id="category" 
                            name="category" 
                            value="<?php echo htmlspecialchars($clothing['category']); ?>" 
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea 
                            id="description" 
                            name="description"
                        ><?php echo htmlspecialchars($clothing['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (R):</label>
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
                        <label for="quantity">Quantity:</label>
                        <input 
                            type="number" 
                            id="quantity" 
                            name="quantity" 
                            value="<?php echo htmlspecialchars($clothing['quantity']); ?>" 
                            min="0"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Clothing Item</button>
                        <a href="manage-clothes.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
