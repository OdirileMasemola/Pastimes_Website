<?php
/**
 * Shopping Cart Page
 * 
 * Displays items in the shopping cart
 * Allows user to modify quantities and proceed to checkout
 */

session_start();

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clothingID'])) {
    $clothingID = intval($_POST['clothingID']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if ($clothingID > 0 && $quantity > 0) {
        if (isset($_SESSION['cart'][$clothingID])) {
            $_SESSION['cart'][$clothingID] += $quantity;
        } else {
            $_SESSION['cart'][$clothingID] = $quantity;
        }
    }
}

// Remove from cart
if (isset($_GET['remove'])) {
    $clothingID = intval($_GET['remove']);
    unset($_SESSION['cart'][$clothingID]);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Pastimes</title>
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
                    <li><a href="cart.php">Cart (<?php echo count($_SESSION['cart']); ?>)</a></li>
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
            <h2>Shopping Cart</h2>
            
            <?php if (count($_SESSION['cart']) > 0): ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $clothingID => $quantity): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($clothingID); ?></td>
                                <td><?php echo htmlspecialchars($quantity); ?></td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $clothingID; ?>" class="btn btn-danger">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-actions">
                    <a href="shop.php" class="btn btn-secondary">Continue Shopping</a>
                    <?php if (isset($_SESSION['userID'])): ?>
                        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">Login to Checkout</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>Your cart is empty.</p>
                <div class="cart-actions">
                    <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
