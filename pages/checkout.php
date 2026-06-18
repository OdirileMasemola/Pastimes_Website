<?php
/**
 * Checkout Page
 *
 * Final step before placing an order.
 * Calculates the cart total from tblClothes, saves the order,
 * and stores each cart item in tblOrderItem.
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php?message=checkout_required&next=checkout");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$error = '';
$success = '';
$previewTotal = 0;
$previewItems = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$previewProducts = array();

// Work out the current cart total and line items for display.
if ($previewItems > 0) {
    $previewStmt = $conn->prepare("SELECT clothingName, price FROM tblClothes WHERE clothingID = ?");

    if ($previewStmt) {
        foreach ($_SESSION['cart'] as $clothingID => $quantity) {
            $clothingID = intval($clothingID);
            $quantity = intval($quantity);

            if ($clothingID <= 0 || $quantity <= 0) {
                continue;
            }

            $previewStmt->bind_param("i", $clothingID);
            $previewStmt->execute();
            $previewResult = $previewStmt->get_result();

            if ($previewResult && $previewResult->num_rows > 0) {
                $previewProduct = $previewResult->fetch_assoc();
                $unitPrice = floatval($previewProduct['price']);
                $lineTotal = $unitPrice * $quantity;
                $previewTotal += $lineTotal;

                $previewProducts[] = array(
                    'name'      => $previewProduct['clothingName'],
                    'quantity'  => $quantity,
                    'unitPrice' => $unitPrice,
                    'lineTotal' => $lineTotal
                );
            }
        }

        $previewStmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (count($_SESSION['cart']) === 0) {
        $error = "Your cart is empty.";
    } else {
        $userID = $_SESSION['userID'];
        $totalAmount = 0;
        $cartItems = array();

        $stockStmt = $conn->prepare("SELECT clothingName, price, quantity FROM tblClothes WHERE clothingID = ? FOR UPDATE");
        $stockUpdateStmt = $conn->prepare("UPDATE tblClothes SET quantity = quantity - ? WHERE clothingID = ? AND quantity >= ?");

        if (!$stockStmt || !$stockUpdateStmt) {
            $error = "Database error: " . $conn->error;
        } else {
            $conn->begin_transaction();

            try {
                foreach ($_SESSION['cart'] as $clothingID => $quantity) {
                    $clothingID = intval($clothingID);
                    $quantity = intval($quantity);

                    if ($clothingID <= 0 || $quantity <= 0) {
                        continue;
                    }

                    $stockStmt->bind_param("i", $clothingID);
                    $stockStmt->execute();
                    $stockResult = $stockStmt->get_result();

                    if (!$stockResult || $stockResult->num_rows === 0) {
                        throw new Exception("A product in your cart could not be found.");
                    }

                    $product = $stockResult->fetch_assoc();
                    $availableQty = intval($product['quantity']);
                    $productName = $product['clothingName'];
                    $price = floatval($product['price']);

                    if ($availableQty <= 0) {
                        throw new Exception("`" . $productName . "` is out of stock.");
                    }
                    if ($quantity > $availableQty) {
                        throw new Exception("Requested quantity for `" . $productName . "` exceeds available stock (" . $availableQty . ").");
                    }

                    $totalAmount += $price * $quantity;
                    $cartItems[] = array(
                        'clothingID' => $clothingID,
                        'quantity' => $quantity,
                        'price' => $price
                    );
                }

                if (count($cartItems) === 0) {
                    throw new Exception("No valid items were found in your cart.");
                }

                {
                    $orderSql = "INSERT INTO tblOrder (userID, totalAmount, status) VALUES (?, ?, 'pending')";
                    $orderStmt = $conn->prepare($orderSql);

                    if (!$orderStmt) {
                        throw new Exception("Database error: " . $conn->error);
                    }

                    $orderStmt->bind_param("id", $userID, $totalAmount);

                    if (!$orderStmt->execute()) {
                        throw new Exception("Error placing order: " . $conn->error);
                    }

                    $orderID = $conn->insert_id;
                    $orderStmt->close();

                    $itemSql = "INSERT INTO tblOrderItem (orderID, clothingID, quantity, priceAtPurchase) VALUES (?, ?, ?, ?)";
                    $itemStmt = $conn->prepare($itemSql);

                    if (!$itemStmt) {
                        throw new Exception("Database error: " . $conn->error);
                    }

                    foreach ($cartItems as $item) {
                        $itemStmt->bind_param(
                            "iiid",
                            $orderID,
                            $item['clothingID'],
                            $item['quantity'],
                            $item['price']
                        );

                        if (!$itemStmt->execute()) {
                            throw new Exception("Error saving order items: " . $conn->error);
                        }

                        $orderedQty = intval($item['quantity']);
                        $orderedCID = intval($item['clothingID']);
                        $stockUpdateStmt->bind_param("iii", $orderedQty, $orderedCID, $orderedQty);
                        if (!$stockUpdateStmt->execute() || $stockUpdateStmt->affected_rows !== 1) {
                            throw new Exception("Stock update failed for item ID " . $orderedCID . ". Please try again.");
                        }
                    }

                    $itemStmt->close();
                    $conn->commit();

                    $success = "Order placed successfully!";
                    $_SESSION['cart'] = array();
                    $previewTotal = 0;
                    $previewItems = 0;
                    $previewProducts = array();
                }
            } catch (Exception $exception) {
                $conn->rollback();
                $error = $exception->getMessage();
            }

            $stockStmt->close();
            $stockUpdateStmt->close();
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
    <title>Checkout - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css?v=6">
</head>
<body class="cart-page-body">
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Message Icon + Notification Popover (only for logged-in users) -->
    <?php include '../includes/messagePopover.php'; ?>
    
    <!-- Shopping Cart Icon -->
    <a href="cart.php" class="cart-icon-link" title="Shopping Cart">
        <i class="fa-solid fa-bag-shopping"></i>
        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <span class="cart-badge"><?php echo count($_SESSION['cart']); ?></span>
        <?php endif; ?>
    </a>

    <main>
        <div class="cart-shell">
            <div class="cart-card checkout-card">

                <div class="cart-card-header">
                    <div class="cart-card-title">
                        <i class="fa-solid fa-credit-card"></i>
                        <span>Checkout</span>
                    </div>
                    <?php if ($previewItems > 0): ?>
                        <span class="cart-card-count"><?php echo $previewItems; ?> item<?php echo $previewItems !== 1 ? 's' : ''; ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($error): ?>
                    <div class="checkout-alert checkout-alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="checkout-alert checkout-alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <p><?php echo htmlspecialchars($success); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (count($previewProducts) > 0): ?>
                    <div class="checkout-summary">
                        <h3 class="checkout-summary-title">Order Summary</h3>

                        <div class="checkout-summary-items">
                            <?php foreach ($previewProducts as $item): ?>
                                <div class="checkout-summary-row">
                                    <div class="checkout-summary-info">
                                        <span class="checkout-summary-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <span class="checkout-summary-qty">Qty: <?php echo $item['quantity']; ?> &times; R <?php echo number_format($item['unitPrice'], 2); ?></span>
                                    </div>
                                    <span class="checkout-summary-line">R <?php echo number_format($item['lineTotal'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="cart-divider"></div>

                        <div class="cart-totals">
                            <div class="cart-total-row">
                                <span>Total Items</span>
                                <span><?php echo $previewItems; ?></span>
                            </div>
                            <div class="cart-total-row cart-total-grand">
                                <span>Total Amount</span>
                                <span>R <?php echo number_format($previewTotal, 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="checkout.php" class="checkout-form">
                        <div class="cart-card-actions">
                            <a href="cart.php" class="sell-btn sell-btn-ghost">Back to Cart</a>
                            <button type="submit" class="sell-btn sell-btn-primary">Place Order</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="cart-empty">
                        <?php if ($success): ?>
                            <i class="fa-solid fa-circle-check"></i>
                            <p>Your order has been placed. Track its status from your cart page.</p>
                            <div class="cart-card-actions">
                                <a href="cart.php" class="sell-btn sell-btn-ghost">View Order Tracking</a>
                                <a href="purchase-history.php" class="sell-btn sell-btn-primary">View Purchase History</a>
                            </div>
                        <?php else: ?>
                            <i class="fa-solid fa-bag-shopping"></i>
                            <p>Your cart is empty. Add items before checking out.</p>
                            <a href="shop.php" class="sell-btn sell-btn-primary">Continue Shopping</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

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
*/
?>