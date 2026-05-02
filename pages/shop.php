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

    <main class="shop-page">
        <div class="container">
            <h2>Shop Our Collection</h2>

            <?php
            // Collect dynamic filter values from the loaded products
            $categories = array();
            $brands = array();
            $genders = array();
            $hasSaleFlag = false;
            foreach ($clothes as $c) {
                if (isset($c['category']) && $c['category'] !== '') {
                    $categories[] = $c['category'];
                }
                if (isset($c['brand']) && $c['brand'] !== '') {
                    $brands[] = $c['brand'];
                }
                if (isset($c['gender']) && $c['gender'] !== '') {
                    $genders[] = $c['gender'];
                }
                if (isset($c['onSale']) || isset($c['sale'])) {
                    $hasSaleFlag = true;
                }
            }
            $categories = array_values(array_unique($categories));
            $brands = array_values(array_unique($brands));
            $genders = array_values(array_unique($genders));
            ?>

            <div class="shop-layout">
                <aside class="shop-sidebar" aria-label="Product filters">
                    <div class="filter-block">
                        <label class="filter-label">SEARCH PRODUCTS</label>
                        <div class="search-wrap">
                            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <input id="searchInput" class="search-input" type="search" placeholder="Search products…" aria-label="Search products">
                        </div>
                    </div>

                    <hr>

                    <div class="filter-block">
                        <label class="filter-label">CATEGORY</label>
                        <select id="categorySelect" class="filter-select">
                            <option value="">ALL CATEGORIES</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <hr>

                    <div class="filter-block">
                        <label class="filter-label">BRAND</label>
                        <select id="brandSelect" class="filter-select">
                            <option value="">ALL BRANDS</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!empty($genders)): ?>
                        <hr>
                        <div class="filter-block">
                            <label class="filter-label">GENDER</label>
                            <select id="genderSelect" class="filter-select">
                                <option value="">ALL GENDERS</option>
                                <?php foreach ($genders as $g): ?>
                                    <option value="<?php echo htmlspecialchars($g); ?>"><?php echo htmlspecialchars($g); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <?php if ($hasSaleFlag): ?>
                        <hr>
                        <div class="filter-block">
                            <label class="filter-label">ON SALE</label>
                            <select id="saleSelect" class="filter-select">
                                <option value="">ALL</option>
                                <option value="1">On Sale</option>
                                <option value="0">Not On Sale</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </aside>

                <section class="shop-main">
                    <div class="products-grid" id="productsGrid">
                        <?php if (count($clothes) > 0): ?>
                            <?php foreach ($clothes as $item): ?>
                                <?php $displayImage = pickFashionImage($item['category'], $item['clothingName'], intval($item['clothingID']), $maleFashionImages, $femaleFashionImages, $unisexFashionImages); ?>
                                <?php
                                    $dataBrand = isset($item['brand']) ? $item['brand'] : '';
                                    $dataGender = isset($item['gender']) ? $item['gender'] : '';
                                    $dataSale = isset($item['onSale']) ? ($item['onSale'] ? '1' : '0') : (isset($item['sale']) ? ($item['sale'] ? '1' : '0') : '');
                                ?>
                                <div class="product-card" data-name="<?php echo htmlspecialchars(strtolower($item['clothingName'])); ?>" data-category="<?php echo htmlspecialchars(strtolower($item['category'])); ?>" data-brand="<?php echo htmlspecialchars(strtolower($dataBrand)); ?>" data-gender="<?php echo htmlspecialchars(strtolower($dataGender)); ?>" data-sale="<?php echo $dataSale; ?>">
                                    <img src="<?php echo htmlspecialchars($displayImage); ?>" alt="<?php echo htmlspecialchars($item['clothingName']); ?>">
                                    <h3><?php echo htmlspecialchars($item['clothingName']); ?></h3>
                                    <p class="price">R <?php echo number_format($item['price'], 2); ?></p>
                                    <a href="product-details.php?id=<?php echo $item['clothingID']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No products available at this time.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

        </div>
    </main>

    <script>
    (function(){
        const searchInput = document.getElementById('searchInput');
        const categorySelect = document.getElementById('categorySelect');
        const brandSelect = document.getElementById('brandSelect');
        const genderSelect = document.getElementById('genderSelect');
        const saleSelect = document.getElementById('saleSelect');
        const productsGrid = document.getElementById('productsGrid');
        const cards = Array.from(productsGrid.querySelectorAll('.product-card'));

        function matchesFilter(card, key, value) {
            if (!value) return true;
            const attr = card.dataset[key] || '';
            return attr.toLowerCase() === value.toLowerCase();
        }

        function filterProducts(){
            const q = searchInput ? searchInput.value.trim().toLowerCase() : '';
            const cat = categorySelect ? categorySelect.value : '';
            const brand = brandSelect ? brandSelect.value : '';
            const gender = genderSelect ? genderSelect.value : '';
            const sale = saleSelect ? saleSelect.value : '';

            cards.forEach(card => {
                let visible = true;
                if (q && !(card.dataset.name && card.dataset.name.indexOf(q) !== -1)) visible = false;
                if (cat && (card.dataset.category !== cat.toLowerCase())) visible = false;
                if (brand && (card.dataset.brand !== brand.toLowerCase())) visible = false;
                if (gender && (card.dataset.gender !== gender.toLowerCase())) visible = false;
                if (sale !== undefined && sale !== null && sale !== '' ) {
                    // match exact '1' or '0'
                    if ((card.dataset.sale || '') !== sale) visible = false;
                }

                card.style.display = visible ? '' : 'none';
            });
        }

        [searchInput, categorySelect, brandSelect, genderSelect, saleSelect].forEach(el=>{
            if (!el) return;
            el.addEventListener('input', filterProducts);
            el.addEventListener('change', filterProducts);
        });
    })();
    </script>

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
