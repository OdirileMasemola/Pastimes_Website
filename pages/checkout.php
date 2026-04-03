<?php
/**
 * Checkout Page
 * 
 * Final step before placing an order
 * Displays order summary and creates order in database
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create order
    $userID = $_SESSION['userID'];
    $totalAmount = 0;  // Calculate based on cart items
    
    // TODO: Calculate total amount from cart items
    
    $sql = "INSERT INTO tblOrder (userID, totalAmount, status) VALUES (?, ?, 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("id", $userID, $totalAmount);
    
    if ($stmt->execute()) {
        $success = "Order placed successfully!";
        $_SESSION['cart'] = array();  // Clear cart
    } else {
        $error = "Error placing order: " . $conn->error;
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
    <title>Checkout - Pastimes</title>
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
                    <li><a href="account.php">My Account</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h2>Checkout</h2>
            
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
            
            <form method="POST" action="checkout.php">
                <div class="checkout-section">
                    <h3>Order Summary</h3>
                    <p>Total Items: <?php echo count($_SESSION['cart']); ?></p>
                    <p>Total Amount: R 0.00 (To be calculated)</p>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Place Order</button>
                    <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
