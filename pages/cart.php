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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Shopping Cart Icon -->
    <a href="cart.php" class="cart-icon-link" title="Shopping Cart">
        <i class="fa-solid fa-bag-shopping"></i>
        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <span class="cart-badge"><?php echo count($_SESSION['cart']); ?></span>
        <?php endif; ?>
    </a>

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
