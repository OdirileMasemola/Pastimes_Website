<?php
/**
 * Product Details Page
 * 
 * Displays detailed information about a specific clothing item
 * Users can add item to cart
 */

session_start();
include '../includes/DBConn.php';

$clothingID = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;

if ($clothingID > 0) {
    $sql = "SELECT * FROM tblClothes WHERE clothingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $clothingID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Pastimes</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1>Pastimes</h1>
                </div>
                <ul class="nav-menu">
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <?php if (isset($_SESSION['userID'])): ?>
                        <li><a href="account.php">My Account</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <?php if ($product): ?>
                <div class="product-details">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($product['imageURL']); ?>" alt="<?php echo htmlspecialchars($product['clothingName']); ?>">
                    </div>
                    <div class="product-info">
                        <h2><?php echo htmlspecialchars($product['clothingName']); ?></h2>
                        <p class="category">Category: <?php echo htmlspecialchars($product['category']); ?></p>
                        <p class="price">Price: R <?php echo number_format($product['price'], 2); ?></p>
                        <p class="quantity">Stock Available: <?php echo $product['quantity']; ?></p>
                        <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>
                        
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="clothingID" value="<?php echo $product['clothingID']; ?>">
                            <div class="form-group">
                                <label for="quantity">Quantity:</label>
                                <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $product['quantity']; ?>" value="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary" <?php echo $product['quantity'] <= 0 ? 'disabled' : ''; ?>>Add to Cart</button>
                        </form>
                        
                        <a href="shop.php" class="btn btn-secondary">Back to Shop</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="error-message">
                    <p>Product not found.</p>
                    <a href="shop.php" class="btn btn-secondary">Back to Shop</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
