<?php
/**
 * Shop Page
 * 
 * Displays all available clothing items
 * Users can view products and add to cart
 */

session_start();
include '../includes/DBConn.php';

$sql = "SELECT * FROM tblClothes";
$result = $conn->query($sql);
$clothes = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clothes[] = $row;
    }
}

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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Pastimes</title>
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
            <h2>Shop Our Collection</h2>
            
            <div class="products-grid">
                <?php if (count($clothes) > 0): ?>
                    <?php foreach ($clothes as $item): ?>
                        <?php $displayImage = pickFashionImage($item['category'], $item['clothingName'], intval($item['clothingID']), $maleFashionImages, $femaleFashionImages, $unisexFashionImages); ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($displayImage); ?>" alt="<?php echo htmlspecialchars($item['clothingName']); ?>">
                            <h3><?php echo htmlspecialchars($item['clothingName']); ?></h3>
                            <p class="category"><?php echo htmlspecialchars($item['category']); ?></p>
                            <p class="price">R <?php echo number_format($item['price'], 2); ?></p>
                            <a href="product-details.php?id=<?php echo $item['clothingID']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No products available at this time.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
