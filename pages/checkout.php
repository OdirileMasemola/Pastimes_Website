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
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$error = '';
$success = '';
$previewTotal = 0;
$previewItems = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// Work out the current cart total for display.
if ($previewItems > 0) {
    $previewStmt = $conn->prepare("SELECT price FROM tblClothes WHERE clothingID = ?");

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
                $previewTotal += floatval($previewProduct['price']) * $quantity;
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

        $priceStmt = $conn->prepare("SELECT price FROM tblClothes WHERE clothingID = ?");

        if (!$priceStmt) {
            $error = "Database error: " . $conn->error;
        } else {
            foreach ($_SESSION['cart'] as $clothingID => $quantity) {
                $clothingID = intval($clothingID);
                $quantity = intval($quantity);

                if ($clothingID <= 0 || $quantity <= 0) {
                    continue;
                }

                $priceStmt->bind_param("i", $clothingID);
                $priceStmt->execute();
                $result = $priceStmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    $price = floatval($product['price']);
                    $totalAmount += $price * $quantity;

                    $cartItems[] = array(
                        'clothingID' => $clothingID,
                        'quantity' => $quantity,
                        'price' => $price
                    );
                }
            }

            if (count($cartItems) === 0) {
                $error = "No valid items were found in your cart.";
            } else {
                $conn->begin_transaction();

                try {
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
                    }

                    $itemStmt->close();
                    $conn->commit();

                    $success = "Order placed successfully!";
                    $_SESSION['cart'] = array();
                    $previewTotal = 0;
                    $previewItems = 0;
                } catch (Exception $exception) {
                    $conn->rollback();
                    $error = $exception->getMessage();
                }
            }

            $priceStmt->close();
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
                    <p>Total Items: <?php echo htmlspecialchars($previewItems); ?></p>
                    <p>Total Amount: R <?php echo number_format($previewTotal, 2); ?></p>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary" <?php echo $previewItems === 0 ? 'disabled' : ''; ?>>Place Order</button>
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
<?php
/*
This code is the original work of:
ST10441421 - Odirile Masemola
ST10450294 - Ripfumelo Mabasa
All rights reserved.
*/
?>