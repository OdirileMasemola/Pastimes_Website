<?php
/**
 * Purchase History Report
 *
 * Shows only the logged-in user's order history with grouped order lines
 * and a grand total of all purchases
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = intval($_SESSION['userID']);
$orders = array();
$grandTotal = 0.0;

$historySql = "SELECT o.orderID, o.orderDate, o.status,
                      oi.orderItemID, oi.clothingID, oi.quantity, oi.priceAtPurchase,
                      c.clothingName
               FROM tblOrder o
               INNER JOIN tblOrderItem oi ON o.orderID = oi.orderID
               LEFT JOIN tblClothes c ON oi.clothingID = c.clothingID
               WHERE o.userID = ?
               ORDER BY o.orderDate DESC, o.orderID DESC, oi.orderItemID ASC";
$historyStmt = $conn->prepare($historySql);
if ($historyStmt) {
    $historyStmt->bind_param("i", $userID);
    $historyStmt->execute();
    $historyResult = $historyStmt->get_result();

    if ($historyResult) {
        while ($row = $historyResult->fetch_assoc()) {
            $orderID = intval($row['orderID']);
            $lineQty = intval($row['quantity']);
            $linePrice = floatval($row['priceAtPurchase']);
            $lineTotal = $lineQty * $linePrice;

            if (!isset($orders[$orderID])) {
                $orders[$orderID] = array(
                    'orderID' => $orderID,
                    'orderDate' => $row['orderDate'],
                    'status' => $row['status'],
                    'orderTotal' => 0.0,
                    'items' => array()
                );
            }

            $orders[$orderID]['items'][] = array(
                'name' => !empty($row['clothingName']) ? $row['clothingName'] : ('Item #' . intval($row['clothingID'])),
                'quantity' => $lineQty,
                'priceAtPurchase' => $linePrice,
                'lineTotal' => $lineTotal
            );
            $orders[$orderID]['orderTotal'] += $lineTotal;
        }
    }
    $historyStmt->close();
}

$totalSql = "SELECT COALESCE(SUM(oi.quantity * oi.priceAtPurchase), 0) AS grandTotal
             FROM tblOrder o
             INNER JOIN tblOrderItem oi ON o.orderID = oi.orderID
             WHERE o.userID = ?";
$totalStmt = $conn->prepare($totalSql);
if ($totalStmt) {
    $totalStmt->bind_param("i", $userID);
    $totalStmt->execute();
    $totalResult = $totalStmt->get_result();
    if ($totalResult && ($totalRow = $totalResult->fetch_assoc())) {
        $grandTotal = floatval($totalRow['grandTotal']);
    }
    $totalStmt->close();
}

if (!function_exists('pastimesOrderBadgeClass')) {
    function pastimesOrderBadgeClass($status)
    {
        $s = strtolower(trim($status));
        if (in_array($s, array('delivered'), true)) return 'is-green';
        if (in_array($s, array('pending', 'processing', 'shipped'), true)) return 'is-amber';
        if (in_array($s, array('cancelled', 'canceled'), true)) return 'is-red';
        return 'is-neutral';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css?v=6">
</head>
<body class="cart-page-body">
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/messagePopover.php'; ?>

    <a href="cart.php" class="cart-icon-link" title="Shopping Cart">
        <i class="fa-solid fa-bag-shopping"></i>
        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <span class="cart-badge"><?php echo count($_SESSION['cart']); ?></span>
        <?php endif; ?>
    </a>

    <main>
        <div class="cart-shell">
            <div class="cart-card purchase-history-card">
                <div class="cart-card-header">
                    <div class="cart-card-title">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Purchase History</span>
                    </div>
                </div>

                <?php if (count($orders) === 0): ?>
                    <div class="cart-empty">
                        <i class="fa-solid fa-receipt"></i>
                        <p>You have not made any purchases yet.</p>
                        <a href="shop.php" class="sell-btn sell-btn-primary">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="purchase-history-list">
                        <?php foreach ($orders as $order): ?>
                            <section class="purchase-order-card">
                                <div class="purchase-order-head">
                                    <div>
                                        <div class="purchase-order-id">Order #<?php echo htmlspecialchars($order['orderID']); ?></div>
                                        <div class="purchase-order-date"><?php echo htmlspecialchars(date('M d, Y \a\t H:i', strtotime($order['orderDate']))); ?></div>
                                    </div>
                                    <div class="purchase-order-meta">
                                        <span class="admin-badge <?php echo pastimesOrderBadgeClass($order['status']); ?>">
                                            <?php echo htmlspecialchars(ucfirst(strtolower($order['status']))); ?>
                                        </span>
                                        <span class="purchase-order-total">R <?php echo number_format($order['orderTotal'], 2); ?></span>
                                    </div>
                                </div>

                                <div class="purchase-table-wrap">
                                    <table class="purchase-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Price At Purchase</th>
                                                <th>Line Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($order['items'] as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                    <td><?php echo intval($item['quantity']); ?></td>
                                                    <td>R <?php echo number_format($item['priceAtPurchase'], 2); ?></td>
                                                    <td>R <?php echo number_format($item['lineTotal'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    </div>

                    <div class="purchase-grand-total">
                        <span>Total of All Purchases</span>
                        <span>R <?php echo number_format($grandTotal, 2); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
