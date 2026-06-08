<?php
/**
 * Shopping Cart Page
 *
 * Displays items in the shopping cart
 * Allows user to modify quantities and proceed to checkout
 */

session_start();
include '../includes/DBConn.php';

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

// Update quantity using the +/- controls (additive: does not change add/remove logic)
if (isset($_GET['update']) && isset($_GET['action'])) {
    $updateID = intval($_GET['update']);
    if ($updateID > 0 && isset($_SESSION['cart'][$updateID])) {
        if ($_GET['action'] === 'inc') {
            $_SESSION['cart'][$updateID] += 1;
        } elseif ($_GET['action'] === 'dec') {
            $_SESSION['cart'][$updateID] -= 1;
            if ($_SESSION['cart'][$updateID] <= 0) {
                unset($_SESSION['cart'][$updateID]);
            }
        }
    }
}

// Fashion image fallback (same logic used on the shop / product pages)
$maleFashionImages = array(
    '../images/charles-etoroma-PpLrGyWo7-Q-unsplash.jpg',
    '../images/daniel-adesina-sIARkv6B7fI-unsplash.jpg',
    '../images/mikhail-pasynkov-_GrR2bX183s-unsplash.jpg'
);
$femaleFashionImages = array(
    '../images/anhelina-osaulenko-ypL-2HbvwNU-unsplash.jpg',
    '../images/parsa-foroughi-Nz93TtvjM5o-unsplash.jpg',
    '../images/stan-diordiev-U_HRcBSGYB0-unsplash.jpg'
);
$unisexFashionImages = array(
    '../images/the-ian-PLU3VxyEzxM-unsplash.jpg'
);

function pickFashionImage($category, $clothingName, $clothingID, $maleFashionImages, $femaleFashionImages, $unisexFashionImages) {
    $text = strtolower(trim($category . ' ' . $clothingName));
    $femaleHints = array('dress', 'skirt', 'women', 'woman', 'ladies', 'blouse');
    $maleHints = array('men', 'man', 'mens', 'hoodie', 'cargo', 'jacket', 'coat', 'sweater', 'jeans', 'boots');
    $unisexHints = array('unisex', 't-shirt', 'tee', 'shirt', 'shorts', 'classic');

    foreach ($femaleHints as $hint) {
        if (strpos($text, $hint) !== false) {
            return $femaleFashionImages[$clothingID % count($femaleFashionImages)];
        }
    }
    foreach ($maleHints as $hint) {
        if (strpos($text, $hint) !== false) {
            return $maleFashionImages[$clothingID % count($maleFashionImages)];
        }
    }
    foreach ($unisexHints as $hint) {
        if (strpos($text, $hint) !== false) {
            return $unisexFashionImages[$clothingID % count($unisexFashionImages)];
        }
    }
    if ($clothingID % 2 === 0) {
        return $maleFashionImages[$clothingID % count($maleFashionImages)];
    }
    return $femaleFashionImages[$clothingID % count($femaleFashionImages)];
}

// Build the list of cart products from the database
$cartProducts = array();
$cartSubtotal = 0;

if (count($_SESSION['cart']) > 0) {
    $cartStmt = $conn->prepare("SELECT clothingID, clothingName, price, category, imageURL FROM tblClothes WHERE clothingID = ?");
    if ($cartStmt) {
        foreach ($_SESSION['cart'] as $cid => $qty) {
            $cid = intval($cid);
            $qty = intval($qty);
            if ($cid <= 0 || $qty <= 0) {
                continue;
            }

            $cartStmt->bind_param("i", $cid);
            $cartStmt->execute();
            $cartResult = $cartStmt->get_result();

            if ($cartResult && $cartResult->num_rows > 0) {
                $product = $cartResult->fetch_assoc();

                $image = pickFashionImage($product['category'], $product['clothingName'], intval($product['clothingID']), $maleFashionImages, $femaleFashionImages, $unisexFashionImages);
                if (!empty($product['imageURL']) && file_exists($product['imageURL'])) {
                    $image = $product['imageURL'];
                }

                $lineTotal = floatval($product['price']) * $qty;
                $cartSubtotal += $lineTotal;

                $cartProducts[] = array(
                    'id'        => intval($product['clothingID']),
                    'name'      => $product['clothingName'],
                    'price'     => floatval($product['price']),
                    'image'     => $image,
                    'quantity'  => $qty,
                    'lineTotal' => $lineTotal
                );
            }
        }
        $cartStmt->close();
    }
}

$cartCount = count($cartProducts);

$userOrders = array();
if (isset($_SESSION['userID'])) {
    $orderUserID = intval($_SESSION['userID']);
    $orderStmt = $conn->prepare("SELECT orderID, orderDate, totalAmount, status FROM tblOrder WHERE userID = ? ORDER BY orderDate DESC LIMIT 10");
    if ($orderStmt) {
        $orderStmt->bind_param("i", $orderUserID);
        $orderStmt->execute();
        $orderResult = $orderStmt->get_result();
        if ($orderResult) {
            while ($orderRow = $orderResult->fetch_assoc()) {
                $userOrders[] = $orderRow;
            }
        }
        $orderStmt->close();
    }
}

if (!function_exists('pastimesOrderBadgeClass')) {
    function pastimesOrderBadgeClass($status) {
        $s = strtolower(trim($status));
        if (in_array($s, array('delivered'), true)) {
            return 'is-green';
        }
        if (in_array($s, array('pending', 'processing', 'shipped'), true)) {
            return 'is-amber';
        }
        if (in_array($s, array('cancelled', 'canceled'), true)) {
            return 'is-red';
        }
        return 'is-neutral';
    }
}

if (!function_exists('pastimesOrderProgressStep')) {
    function pastimesOrderProgressStep($status) {
        $s = strtolower(trim($status));
        $steps = array(
            'pending'    => 1,
            'processing' => 2,
            'shipped'    => 3,
            'delivered'  => 4
        );
        return isset($steps[$s]) ? $steps[$s] : 0;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Pastimes</title>
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
            <div class="cart-card">

                <div class="cart-card-header">
                    <div class="cart-card-title">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>Your Shopping Cart</span>
                    </div>
                    <span class="cart-card-count"><?php echo $cartCount; ?> item<?php echo $cartCount !== 1 ? 's' : ''; ?></span>
                </div>

                <?php if ($cartCount > 0): ?>
                    <div class="cart-items">
                        <?php foreach ($cartProducts as $item): ?>
                            <div class="cart-item">
                                <img class="cart-item-img" src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">

                                <div class="cart-item-info">
                                    <p class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></p>
                                    <p class="cart-item-unit">R <?php echo number_format($item['price'], 2); ?> per item</p>

                                    <div class="cart-qty">
                                        <a class="cart-qty-btn" href="cart.php?update=<?php echo $item['id']; ?>&action=dec" title="Decrease quantity" aria-label="Decrease quantity">&minus;</a>
                                        <span class="cart-qty-value"><?php echo $item['quantity']; ?></span>
                                        <a class="cart-qty-btn" href="cart.php?update=<?php echo $item['id']; ?>&action=inc" title="Increase quantity" aria-label="Increase quantity">&plus;</a>
                                    </div>
                                </div>

                                <div class="cart-item-right">
                                    <span class="cart-item-total">R <?php echo number_format($item['lineTotal'], 2); ?></span>
                                    <a class="cart-item-remove" href="cart.php?remove=<?php echo $item['id']; ?>" title="Remove item" aria-label="Remove item">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-divider"></div>

                    <div class="cart-totals">
                        <div class="cart-total-row">
                            <span>Subtotal</span>
                            <span>R <?php echo number_format($cartSubtotal, 2); ?></span>
                        </div>
                        <div class="cart-total-row cart-total-grand">
                            <span>Total</span>
                            <span>R <?php echo number_format($cartSubtotal, 2); ?></span>
                        </div>
                    </div>

                    <div class="cart-card-actions">
                        <a href="shop.php" class="sell-btn sell-btn-ghost">Continue Shopping</a>
                        <?php if (isset($_SESSION['userID'])): ?>
                            <a href="checkout.php" class="sell-btn sell-btn-primary">Proceed to Checkout</a>
                        <?php else: ?>
                            <a href="login.php" class="sell-btn sell-btn-primary">Login to Checkout</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="cart-empty">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <p>Your cart is empty.</p>
                        <a href="shop.php" class="sell-btn sell-btn-primary">Continue Shopping</a>
                    </div>
                <?php endif; ?>

            </div>

            <?php if (isset($_SESSION['userID'])): ?>
                <div class="order-track-card">
                    <div class="cart-card-header">
                        <div class="cart-card-title">
                            <i class="fa-solid fa-truck-fast"></i>
                            <span>Track Your Orders</span>
                        </div>
                    </div>

                    <?php if (count($userOrders) > 0): ?>
                        <div class="order-track-list">
                            <?php foreach ($userOrders as $order): ?>
                                <?php
                                $orderStatus = strtolower(trim($order['status']));
                                $progressStep = pastimesOrderProgressStep($order['status']);
                                $statusLabel = ucfirst($orderStatus);
                                ?>
                                <div class="order-track-item">
                                    <div class="order-track-top">
                                        <div class="order-track-info">
                                            <span class="order-track-id">Order #<?php echo htmlspecialchars($order['orderID']); ?></span>
                                            <span class="order-track-date"><?php echo htmlspecialchars(date('M d, Y', strtotime($order['orderDate']))); ?></span>
                                        </div>
                                        <div class="order-track-amount">R <?php echo number_format($order['totalAmount'], 2); ?></div>
                                    </div>

                                    <div class="order-track-status-row">
                                        <span class="admin-badge <?php echo pastimesOrderBadgeClass($order['status']); ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                                    </div>

                                    <?php if ($progressStep > 0): ?>
                                        <div class="order-track-progress" aria-label="Order progress">
                                            <div class="order-track-step <?php echo $progressStep >= 1 ? 'is-done' : ''; ?> <?php echo $progressStep === 1 ? 'is-current' : ''; ?>">
                                                <span class="order-track-dot"></span>
                                                <span class="order-track-label">Pending</span>
                                            </div>
                                            <div class="order-track-line <?php echo $progressStep >= 2 ? 'is-done' : ''; ?>"></div>
                                            <div class="order-track-step <?php echo $progressStep >= 2 ? 'is-done' : ''; ?> <?php echo $progressStep === 2 ? 'is-current' : ''; ?>">
                                                <span class="order-track-dot"></span>
                                                <span class="order-track-label">Processing</span>
                                            </div>
                                            <div class="order-track-line <?php echo $progressStep >= 3 ? 'is-done' : ''; ?>"></div>
                                            <div class="order-track-step <?php echo $progressStep >= 3 ? 'is-done' : ''; ?> <?php echo $progressStep === 3 ? 'is-current' : ''; ?>">
                                                <span class="order-track-dot"></span>
                                                <span class="order-track-label">Shipped</span>
                                            </div>
                                            <div class="order-track-line <?php echo $progressStep >= 4 ? 'is-done' : ''; ?>"></div>
                                            <div class="order-track-step <?php echo $progressStep >= 4 ? 'is-done' : ''; ?> <?php echo $progressStep === 4 ? 'is-current' : ''; ?>">
                                                <span class="order-track-dot"></span>
                                                <span class="order-track-label">Delivered</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="order-track-empty">You have not placed any orders yet.</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer Section -->
    <footer class="footer">
        <!-- Top Card Area -->
        <div class="footer-top-card"></div>

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
