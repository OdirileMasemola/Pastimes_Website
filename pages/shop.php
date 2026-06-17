<?php
/**
 * Shop Page
 * Luxury gallery — sidebar filter left, cards right, no duplicate brand on hover.
 * Backend PHP logic is unchanged from original.
 */

session_start();
include '../includes/DBConn.php';

// Read price filter values from the URL (GET). Empty = no limit.
$minPrice = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (float) $_GET['min_price'] : null;
$maxPrice = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (float) $_GET['max_price'] : null;

// Build the query with optional price filtering using prepared statements.
$sql    = "SELECT * FROM tblClothes WHERE approvalStatus = 'approved'";
$types  = '';
$params = array();

if ($minPrice !== null) {
    $sql     .= " AND price >= ?";
    $types   .= 'd';
    $params[] = $minPrice;
}
if ($maxPrice !== null) {
    $sql     .= " AND price <= ?";
    $types   .= 'd';
    $params[] = $maxPrice;
}

$clothes = array();
$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $clothes[] = $row;
        }
    }
    $stmt->close();
}

$currentUserID = isset($_SESSION['userID']) ? (int) $_SESSION['userID'] : 0;
$sellerNameMap = array();
$sellerIDs = array();
foreach ($clothes as $row) {
    $sid = isset($row['sellerID']) ? (int) $row['sellerID'] : 0;
    if ($sid > 0) {
        $sellerIDs[$sid] = true;
    }
}
if (!empty($sellerIDs)) {
    $sellerIDList = array_keys($sellerIDs);
    $placeholders = implode(',', array_fill(0, count($sellerIDList), '?'));
    $sellerSql = "SELECT userID, fullName FROM tblUser WHERE userID IN ($placeholders)";
    $sellerStmt = $conn->prepare($sellerSql);
    if ($sellerStmt) {
        $sellerTypes = str_repeat('i', count($sellerIDList));
        $sellerStmt->bind_param($sellerTypes, ...$sellerIDList);
        $sellerStmt->execute();
        $sellerRes = $sellerStmt->get_result();
        if ($sellerRes) {
            while ($sellerRow = $sellerRes->fetch_assoc()) {
                $sellerNameMap[(int) $sellerRow['userID']] = $sellerRow['fullName'];
            }
        }
        $sellerStmt->close();
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
    $text        = strtolower(trim($category . ' ' . $clothingName));
    $femaleHints = array('dress', 'skirt', 'women', 'woman', 'ladies', 'blouse');
    $maleHints   = array('men', 'man', 'mens', 'hoodie', 'cargo', 'jacket', 'coat', 'sweater', 'jeans', 'boots');
    $unisexHints = array('unisex', 't-shirt', 'tee', 'shirt', 'shorts', 'classic');

    foreach ($femaleHints as $hint) {
        if (strpos($text, $hint) !== false)
            return $femaleFashionImages[$clothingID % count($femaleFashionImages)];
    }
    foreach ($maleHints as $hint) {
        if (strpos($text, $hint) !== false)
            return $maleFashionImages[$clothingID % count($maleFashionImages)];
    }
    foreach ($unisexHints as $hint) {
        if (strpos($text, $hint) !== false)
            return $unisexFashionImages[$clothingID % count($unisexFashionImages)];
    }

    if ($clothingID % 2 === 0)
        return $maleFashionImages[$clothingID % count($maleFashionImages)];

    return $femaleFashionImages[$clothingID % count($femaleFashionImages)];
}

/* Keep $conn open: navbar.php reuses it for the unread-message count.
   PHP closes the connection automatically when the script finishes. */

/* Featured curated items — names and prices match index.php */
$featuredItems = array(
    array(
        'image'       => '../images/dolce.jpg',
        'title'       => 'Portofino sneakers in calfskin and patent leather',
        'brand'       => 'Dolce & Gabbana',
        'filter'      => 'dolce',
        'price'       => 'R18,500',
        'description' => 'Premium Dolce & Gabbana statement piece. Authentic designer fashion for the discerning shopper.',
        'link'        => 'shop.php'
    ),
    array(
        'image'       => '../images/dsquared.png',
        'title'       => 'Dsquared2 Denim Jean',
        'brand'       => 'Dsquared2',
        'filter'      => 'dsquared2',
        'price'       => 'R16,000',
        'description' => 'Iconic Dsquared2 denim jean. Contemporary designer style with authentic craftsmanship.',
        'link'        => 'shop.php'
    ),
    array(
        'image'       => '../images/kenzo.jpg',
        'title'       => 'Kenzo Graphic Tee',
        'brand'       => 'Kenzo',
        'filter'      => 'kenzo',
        'price'       => 'R3,500',
        'description' => 'Signature Kenzo graphic tee. Bold design meets comfort in this premium pre-loved piece.',
        'link'        => 'shop.php'
    ),
    array(
        'image'       => '../images/lacostejacket.png',
        'title'       => 'Lacoste Monogram Jacket',
        'brand'       => 'Lacoste',
        'filter'      => 'lacoste',
        'price'       => 'R3,000',
        'description' => 'Classic Lacoste jacket. Timeless elegance and quality construction in this iconic piece.',
        'link'        => 'shop.php'
    ),
    array(
        'image'       => '../images/louboutin.jpg',
        'title'       => 'Louboutin Sneaker',
        'brand'       => 'Christian Louboutin',
        'filter'      => 'louboutin',
        'price'       => 'R23,000',
        'description' => 'Luxury Christian Louboutin sneaker. Premium footwear with iconic design and superior craftsmanship.',
        'link'        => 'shop.php'
    ),
    array(
        'image'       => '../images/LVSneaker.png',
        'title'       => 'Louis Vuitton Trainers',
        'brand'       => 'Louis Vuitton',
        'filter'      => 'lv',
        'price'       => 'R30,000',
        'description' => 'Prestigious Louis Vuitton sneaker. Premium luxury footwear with exceptional quality and design.',
        'link'        => 'shop.php'
    ),
);

/* Attach real database IDs + stock to the featured items so they can be added
   to the cart from the quick-view modal. Matched by product name. */
$featuredLookup = array();
if ($featuredResult = $conn->query("SELECT clothingID, clothingName, quantity FROM tblClothes WHERE approvalStatus = 'featured'")) {
    while ($fRow = $featuredResult->fetch_assoc()) {
        $featuredLookup[$fRow['clothingName']] = $fRow;
    }
}
foreach ($featuredItems as $fi => $fItem) {
    $featuredItems[$fi]['id'] = 0;
    $featuredItems[$fi]['stock'] = 0;
    if (isset($featuredLookup[$fItem['title']])) {
        $featuredItems[$fi]['id'] = intval($featuredLookup[$fItem['title']]['clothingID']);
        $featuredItems[$fi]['stock'] = intval($featuredLookup[$fItem['title']]['quantity']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop — Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css?v=3">
</head>
<body class="shop-page-body">
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
        <div class="shop-page-shell">

            <!-- Page hero -->
            <section class="shop-hero">
                <span class="shop-hero-badge">Shop</span>
                <h1 class="shop-hero-title">Luxury Fashion <span>Marketplace</span></h1>
                <p class="shop-hero-subtitle">Browse premium pre-loved fashion pieces available on Pastimes.</p>
            </section>

            <!-- Sidebar + content layout -->
            <div class="shop-body">

                <!-- Left sidebar: search, brand pills, price range -->
                <aside class="shop-sidebar">

                    <!-- Search -->
                    <div class="sidebar-section">
                        <span class="sidebar-label">Search</span>
                        <div class="sidebar-search-wrap">
                            <svg class="sidebar-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none">
                                <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"/>
                                <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <input id="searchInput" class="sidebar-search" type="search" placeholder="Search products…" aria-label="Search products">
                        </div>
                    </div>

                    <div class="sidebar-divider"></div>

                    <!-- Brand filter pills -->
                    <div class="sidebar-section">
                        <span class="sidebar-label">Brand</span>
                        <div class="sidebar-pills" id="galleryFilters">
                            <button class="sidebar-pill active" data-filter="all">
                                <span class="sidebar-pill-dot"></span>All Brands
                            </button>
                            <button class="sidebar-pill" data-filter="dolce">
                                <span class="sidebar-pill-dot"></span>Dolce &amp; Gabbana
                            </button>
                            <button class="sidebar-pill" data-filter="dsquared2">
                                <span class="sidebar-pill-dot"></span>Dsquared2
                            </button>
                            <button class="sidebar-pill" data-filter="kenzo">
                                <span class="sidebar-pill-dot"></span>Kenzo
                            </button>
                            <button class="sidebar-pill" data-filter="lacoste">
                                <span class="sidebar-pill-dot"></span>Lacoste
                            </button>
                            <button class="sidebar-pill" data-filter="louboutin">
                                <span class="sidebar-pill-dot"></span>Louboutin
                            </button>
                            <button class="sidebar-pill" data-filter="lv">
                                <span class="sidebar-pill-dot"></span>Louis Vuitton
                            </button>
                        </div>
                    </div>

                    <div class="sidebar-divider"></div>

                    <!-- Price range -->
                    <div class="sidebar-section">
                        <span class="sidebar-label">Price Range (R)</span>
                        <form method="get" action="shop.php" class="sidebar-price-form">
                            <div class="sidebar-price-row">
                                <input id="minPrice" name="min_price" type="number" min="0" step="0.01" class="sidebar-price-input" placeholder="Min" value="<?php echo htmlspecialchars(isset($_GET['min_price']) ? $_GET['min_price'] : ''); ?>">
                                <span class="sidebar-price-sep">to</span>
                                <input id="maxPrice" name="max_price" type="number" min="0" step="0.01" class="sidebar-price-input" placeholder="Max" value="<?php echo htmlspecialchars(isset($_GET['max_price']) ? $_GET['max_price'] : ''); ?>">
                            </div>
                            <button type="submit" class="sidebar-pill" style="width:100%;justify-content:center;margin-top:10px;">Apply Filter</button>
                        </form>
                    </div>

                    <?php
                    /* Dynamic category filter from DB if categories exist */
                    $dbCategories = array_values(array_unique(array_filter(array_column($clothes, 'category'))));
                    if (!empty($dbCategories)):
                    ?>
                    <div class="sidebar-divider"></div>
                    <div class="sidebar-section">
                        <span class="sidebar-label">Category</span>
                        <div class="sidebar-pills">
                            <button class="sidebar-pill active" id="catAll" data-cat="">
                                <span class="sidebar-pill-dot"></span>All Categories
                            </button>
                            <?php foreach ($dbCategories as $cat): ?>
                            <button class="sidebar-pill" data-cat="<?php echo htmlspecialchars(strtolower($cat)); ?>">
                                <span class="sidebar-pill-dot"></span><?php echo htmlspecialchars($cat); ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </aside>

                <!-- Right content: featured gallery + all listings -->
                <div class="shop-content">

                    <!-- Featured gallery -->
                    <span class="content-section-title">Featured Pieces</span>

                    <div class="gallery-grid" id="galleryGrid">
                        <?php foreach ($featuredItems as $i => $item): ?>
                        <div class="gallery-card"
                             data-filter="<?php echo htmlspecialchars($item['filter']); ?>"
                             data-index="<?php echo $i; ?>"
                             data-name="<?php echo htmlspecialchars(strtolower($item['title'])); ?>"
                             data-price="<?php echo (float) preg_replace('/[^0-9.]/', '', $item['price']); ?>"
                             style="animation-delay: <?php echo ($i * 0.08); ?>s;">

                            <img class="gallery-card-img"
                                 src="<?php echo htmlspecialchars($item['image']); ?>"
                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                 loading="lazy">

                            <div class="gallery-card-overlay"></div>

                            <!-- Static brand label — hidden on hover so it never duplicates -->
                            <div class="gallery-card-brand-tag">
                                <?php echo htmlspecialchars($item['brand']); ?>
                            </div>

                            <!-- Hover info: name + price + view button only (no brand) -->
                            <div class="gallery-card-info">
                                <p class="gallery-card-name"><?php echo htmlspecialchars($item['title']); ?></p>
                                <p class="gallery-card-price"><?php echo htmlspecialchars($item['price']); ?></p>
                                <button class="gallery-card-view" onclick="openLightbox(<?php echo $i; ?>)">
                                    View <span class="gallery-card-arrow">&#8594;</span>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- All DB listings -->
                    <?php if (count($clothes) > 0): ?>
                    <span class="all-listings-title">
                        All Listings &nbsp;(<?php echo count($clothes); ?> piece<?php echo count($clothes) !== 1 ? 's' : ''; ?>)
                    </span>

                    <div class="db-products-grid" id="productsGrid">
                        <?php foreach ($clothes as $item): ?>
                            <?php
                            $displayImage = pickFashionImage(
                                $item['category'],
                                $item['clothingName'],
                                intval($item['clothingID']),
                                $maleFashionImages,
                                $femaleFashionImages,
                                $unisexFashionImages
                            );

                            $imageToDisplay = $displayImage;
                            if (!empty($item['imageURL']) && file_exists($item['imageURL'])) {
                                $imageToDisplay = $item['imageURL'];
                            }

                            $dataBrand  = isset($item['brand'])  ? $item['brand']  : '';
                            $dataGender = isset($item['gender']) ? $item['gender'] : '';
                            $dataSellerID = isset($item['sellerID']) ? (int) $item['sellerID'] : 0;
                            $dataSellerName = ($dataSellerID > 0 && isset($sellerNameMap[$dataSellerID])) ? $sellerNameMap[$dataSellerID] : '';
                            $dataSale   = isset($item['onSale']) ? ($item['onSale'] ? '1' : '0')
                                        : (isset($item['sale']) ? ($item['sale']   ? '1' : '0') : '');
                            ?>
                            <div class="db-product-card"
                                 data-name="<?php echo htmlspecialchars(strtolower($item['clothingName'])); ?>"
                                 data-category="<?php echo htmlspecialchars(strtolower($item['category'])); ?>"
                                 data-brand="<?php echo htmlspecialchars(strtolower($dataBrand)); ?>"
                                 data-gender="<?php echo htmlspecialchars(strtolower($dataGender)); ?>"
                                 data-sale="<?php echo $dataSale; ?>"
                                 data-price="<?php echo $item['price']; ?>"
                                 data-id="<?php echo intval($item['clothingID']); ?>"
                                 data-title="<?php echo htmlspecialchars($item['clothingName']); ?>"
                                 data-image="<?php echo htmlspecialchars($imageToDisplay); ?>"
                                 data-pricetext="R <?php echo number_format($item['price'], 2); ?>"
                                 data-desc="<?php echo htmlspecialchars(isset($item['description']) ? $item['description'] : ''); ?>"
                                 data-brandlabel="<?php echo htmlspecialchars($dataBrand); ?>"
                                 data-stock="<?php echo intval(isset($item['quantity']) ? $item['quantity'] : 0); ?>"
                                 data-sellerid="<?php echo $dataSellerID; ?>"
                                 data-sellername="<?php echo htmlspecialchars($dataSellerName); ?>">

                                <div class="db-product-img-wrap">
                                    <img class="db-product-img"
                                         src="<?php echo htmlspecialchars($imageToDisplay); ?>"
                                         alt="<?php echo htmlspecialchars($item['clothingName']); ?>"
                                         loading="lazy">
                                </div>

                                <div class="db-product-body">
                                    <p class="db-product-name"><?php echo htmlspecialchars($item['clothingName']); ?></p>
                                    <p class="db-product-price">R <?php echo number_format($item['price'], 2); ?></p>
                                    <button type="button" class="db-product-btn">View Details</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </main>

    <!-- Lightbox -->
    <div class="lb-overlay" id="lbOverlay" role="dialog" aria-modal="true" aria-label="Product lightbox">
        <div class="lb-modal" id="lbModal">

            <button class="lb-close" id="lbClose" aria-label="Close">&times;</button>
            <button class="lb-nav lb-prev" id="lbPrev" aria-label="Previous">&#8592;</button>
            <button class="lb-nav lb-next" id="lbNext" aria-label="Next">&#8594;</button>

            <div class="lb-img-side">
                <img class="lb-img" id="lbImg" src="" alt="">
            </div>

            <div class="lb-info-side">
                <p class="lb-kicker">Featured Piece</p>
                <h2 class="lb-title" id="lbTitle"></h2>
                <p class="lb-brand" id="lbBrand"></p>
                <p class="lb-price" id="lbPrice"></p>
                <p class="lb-desc"  id="lbDesc"></p>

                <p class="lb-stock" id="lbStock"></p>

                <div class="lb-cart-row">
                    <div class="lb-qty">
                        <button type="button" class="lb-qty-btn" id="lbQtyMinus" aria-label="Decrease quantity">&minus;</button>
                        <input type="text" class="lb-qty-input" id="lbQtyInput" value="1" readonly>
                        <button type="button" class="lb-qty-btn" id="lbQtyPlus" aria-label="Increase quantity">&plus;</button>
                    </div>

                    <form method="POST" action="cart.php" id="lbCartForm">
                        <input type="hidden" name="clothingID" id="lbCartId" value="">
                        <input type="hidden" name="quantity" id="lbCartQty" value="1">
                        <button type="submit" class="lb-cta" id="lbAddBtn">Add to Cart</button>
                    </form>
                    <button type="button" class="lb-cta lb-cta-secondary" id="lbEnquireBtn">Message Admin</button>
                </div>
            </div>

            <div class="lb-counter" id="lbCounter"></div>
        </div>
    </div>

    <div class="shop-enquiry-overlay" id="shopEnquiryOverlay" style="display:none;" aria-hidden="true"></div>
    <div class="shop-enquiry-wrap" id="shopEnquiryWrap" style="display:none;">
        <button type="button" class="shop-enquiry-close" id="shopEnquiryClose" aria-label="Close enquiry">&times;</button>
        <div class="shop-enquiry-modal">
            <h3 id="shopEnquiryTitle">Enquire About Product</h3>
            <p class="shop-enquiry-product" id="shopEnquiryProduct"></p>
            <p class="shop-enquiry-price" id="shopEnquiryPrice"></p>
            <p class="shop-enquiry-seller" id="shopEnquirySeller" style="display:none;"></p>

            <?php if (isset($_SESSION['userID'])): ?>
                <form id="shopEnquiryForm" class="shop-enquiry-form">
                    <input type="hidden" name="productID" id="shopEnquiryProductID" value="">
                    <label for="shopEnquirySubject">Subject</label>
                    <input type="text" id="shopEnquirySubject" name="subject" required maxlength="200">
                    <label for="shopEnquiryText">Message</label>
                    <textarea id="shopEnquiryText" name="messageText" rows="5" required placeholder="Hi admin, I would like to enquire about this product..."></textarea>
                    <button type="submit" class="shop-enquiry-send">Send Message</button>
                    <p class="shop-enquiry-status" id="shopEnquiryStatus" aria-live="polite"></p>
                </form>
            <?php else: ?>
                <div class="shop-enquiry-login">
                    <p id="shopEnquiryLoginText">Please log in to enquire about this product.</p>
                    <a href="login.php" class="shop-enquiry-login-btn">Log In</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

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
    (function () {

        /* Gallery brand filter pills (sidebar) */
        var pills      = document.querySelectorAll('#galleryFilters .sidebar-pill');
        var galCards   = document.querySelectorAll('#galleryGrid .gallery-card');

        /* Price filter for hardcoded gallery cards.
           Reads min_price / max_price from the URL on page load.
           (DB products are already price-filtered by PHP/SQL.) */
        var urlParams = new URLSearchParams(window.location.search);
        var urlMin    = parseFloat(urlParams.get('min_price'));
        var urlMax    = parseFloat(urlParams.get('max_price'));
        if (isNaN(urlMin)) urlMin = 0;
        if (isNaN(urlMax)) urlMax = Infinity;

        galCards.forEach(function (card) {
            var price = parseFloat(card.dataset.price) || 0;
            if (price < urlMin || price > urlMax) {
                card.style.display = 'none';
            }
        });

        pills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                pills.forEach(function (p) { p.classList.remove('active'); });
                pill.classList.add('active');

                var filter = pill.dataset.filter;
                galCards.forEach(function (card) {
                    card.style.display = (filter === 'all' || card.dataset.filter === filter) ? '' : 'none';
                });
            });
        });

        /* Scroll-triggered card reveal */
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.08 });

            galCards.forEach(function (card) { io.observe(card); });
        } else {
            galCards.forEach(function (card) { card.classList.add('visible'); });
        }

        /* Open lightbox when clicking a card (but not the view button — it handles itself) */
        galCards.forEach(function (card) {
            card.addEventListener('click', function (e) {
                if (!e.target.closest('.gallery-card-view')) {
                    openLightbox(parseInt(card.dataset.index, 10));
                }
            });
        });


        /* Lightbox / product quick-view */
        var featuredItems = <?php echo json_encode(array_values($featuredItems)); ?>;
        var activeItems   = featuredItems;
        var current       = 0;
        var currentStock  = 1;

        var overlay    = document.getElementById('lbOverlay');
        var lbImg      = document.getElementById('lbImg');
        var lbTitle    = document.getElementById('lbTitle');
        var lbBrand    = document.getElementById('lbBrand');
        var lbPrice    = document.getElementById('lbPrice');
        var lbDesc     = document.getElementById('lbDesc');
        var lbCounter  = document.getElementById('lbCounter');
        var lbStock    = document.getElementById('lbStock');
        var lbQtyInput = document.getElementById('lbQtyInput');
        var lbQtyMinus = document.getElementById('lbQtyMinus');
        var lbQtyPlus  = document.getElementById('lbQtyPlus');
        var lbCartId   = document.getElementById('lbCartId');
        var lbCartQty  = document.getElementById('lbCartQty');
        var lbAddBtn   = document.getElementById('lbAddBtn');
        var lbEnquireBtn = document.getElementById('lbEnquireBtn');
        var enquiryOverlay = document.getElementById('shopEnquiryOverlay');
        var enquiryWrap = document.getElementById('shopEnquiryWrap');
        var enquiryClose = document.getElementById('shopEnquiryClose');
        var enquiryProduct = document.getElementById('shopEnquiryProduct');
        var enquiryPrice = document.getElementById('shopEnquiryPrice');
        var enquiryForm = document.getElementById('shopEnquiryForm');
        var enquiryProductID = document.getElementById('shopEnquiryProductID');
        var enquirySubject = document.getElementById('shopEnquirySubject');
        var enquiryStatus = document.getElementById('shopEnquiryStatus');
        var enquiryText = document.getElementById('shopEnquiryText');
        var enquiryTitle = document.getElementById('shopEnquiryTitle');
        var enquirySeller = document.getElementById('shopEnquirySeller');
        var enquiryLoginText = document.getElementById('shopEnquiryLoginText');
        var currentViewerId = <?php echo $currentUserID; ?>;
        var enquiryMode = 'admin';

        function setQty(v) {
            if (isNaN(v) || v < 1) v = 1;
            if (v > currentStock) v = currentStock;
            lbQtyInput.value = v;
            lbCartQty.value = v;
        }

        function populate(idx) {
            var item = activeItems[idx];
            if (!item) return;
            current = idx;

            lbImg.src            = item.image;
            lbImg.alt            = item.title || '';
            lbTitle.textContent  = item.title || '';
            lbBrand.textContent  = item.brand || '';
            lbPrice.textContent  = item.price || '';
            lbDesc.textContent   = item.description || '';
            lbCounter.textContent = (idx + 1) + ' / ' + activeItems.length;

            var stock = parseInt(item.stock, 10);
            if (isNaN(stock)) stock = 1;
            currentStock = stock;

            var pid = parseInt(item.id, 10) || 0;
            lbCartId.value = pid;

            // Only real DB products (have an id and stock) can be added to cart
            var purchasable = (pid > 0 && stock > 0);

            if (pid <= 0) {
                // Featured showcase item (not a real database product)
                lbStock.textContent = '';
                lbStock.classList.remove('lb-stock-out');
            } else if (stock > 0) {
                lbStock.textContent = 'Available: ' + stock + ' unit' + (stock === 1 ? '' : 's');
                lbStock.classList.remove('lb-stock-out');
            } else {
                lbStock.textContent = 'Out of stock';
                lbStock.classList.add('lb-stock-out');
            }

            setQty(1);

            lbAddBtn.disabled   = !purchasable;
            lbQtyMinus.disabled = !purchasable;
            lbQtyPlus.disabled  = !purchasable;

            if (enquiryProduct) {
                enquiryProduct.textContent = item.title || '';
            }
            if (enquiryPrice) {
                enquiryPrice.textContent = item.price || '';
            }
            if (enquiryProductID) {
                enquiryProductID.value = pid > 0 ? String(pid) : '';
            }
            if (enquirySubject) {
                enquirySubject.value = 'Product enquiry: ' + (item.title || 'Product');
            }

            var sellerID = parseInt(item.sellerID || 0, 10);
            var sellerName = item.sellerName || '';
            enquiryMode = 'admin';

            if (pid > 0 && sellerID > 0) {
                if (currentViewerId > 0 && currentViewerId === sellerID) {
                    lbEnquireBtn.textContent = 'This is your listing';
                    lbEnquireBtn.disabled = true;
                    enquiryMode = 'self';
                } else {
                    lbEnquireBtn.textContent = 'Message Seller';
                    lbEnquireBtn.disabled = false;
                    enquiryMode = 'seller';
                }
            } else {
                lbEnquireBtn.textContent = 'Message Admin';
                lbEnquireBtn.disabled = false;
            }

            if (enquiryTitle) {
                enquiryTitle.textContent = enquiryMode === 'seller' ? 'Message Seller' : 'Enquire About Product';
            }
            if (enquirySeller) {
                if (enquiryMode === 'seller' && sellerName) {
                    enquirySeller.style.display = 'block';
                    enquirySeller.textContent = 'Seller: ' + sellerName;
                } else {
                    enquirySeller.style.display = 'none';
                    enquirySeller.textContent = '';
                }
            }
            if (enquiryLoginText) {
                enquiryLoginText.textContent = enquiryMode === 'seller'
                    ? 'Please log in to message the seller.'
                    : 'Please log in to enquire about this product.';
            }
        }

        function openAt(idx) {
            populate(idx);
            overlay.style.display = 'flex';
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    overlay.classList.add('open', 'visible');
                });
            });
            document.body.style.overflow = 'hidden';
        }

        // Featured gallery cards call this (keeps existing onclick handlers working)
        window.openLightbox = function (idx) {
            activeItems = featuredItems;
            openAt(idx);
        };

        function closeLightbox() {
            overlay.classList.remove('visible');
            setTimeout(function () {
                overlay.classList.remove('open');
                overlay.style.display = '';
                document.body.style.overflow = '';
            }, 360);
            closeEnquiry();
        }

        function openEnquiry() {
            if (!enquiryWrap || !enquiryOverlay) return;
            enquiryOverlay.style.display = 'block';
            enquiryOverlay.classList.add('active');
            enquiryWrap.style.display = 'flex';
            enquiryWrap.classList.add('active');
            enquiryOverlay.setAttribute('aria-hidden', 'false');
            if (enquiryStatus) {
                enquiryStatus.textContent = '';
                enquiryStatus.classList.remove('success', 'error');
            }
        }

        function closeEnquiry() {
            if (!enquiryWrap || !enquiryOverlay) return;
            enquiryOverlay.style.display = 'none';
            enquiryOverlay.classList.remove('active');
            enquiryWrap.style.display = 'none';
            enquiryWrap.classList.remove('active');
            enquiryOverlay.setAttribute('aria-hidden', 'true');
        }

        document.getElementById('lbClose').addEventListener('click', closeLightbox);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) closeLightbox(); });
        if (lbEnquireBtn) {
            lbEnquireBtn.addEventListener('click', function () {
                if (lbEnquireBtn.disabled || enquiryMode === 'self') {
                    return;
                }
                openEnquiry();
            });
        }
        if (enquiryClose) {
            enquiryClose.addEventListener('click', closeEnquiry);
        }
        if (enquiryOverlay) {
            enquiryOverlay.addEventListener('click', closeEnquiry);
        }

        document.getElementById('lbPrev').addEventListener('click', function () {
            populate((current - 1 + activeItems.length) % activeItems.length);
        });
        document.getElementById('lbNext').addEventListener('click', function () {
            populate((current + 1) % activeItems.length);
        });

        lbQtyMinus.addEventListener('click', function () { setQty((parseInt(lbQtyInput.value, 10) || 1) - 1); });
        lbQtyPlus.addEventListener('click', function () { setQty((parseInt(lbQtyInput.value, 10) || 1) + 1); });

        document.addEventListener('keydown', function (e) {
            if (!overlay.classList.contains('visible')) return;
            if (e.key === 'Escape')      closeLightbox();
            if (e.key === 'ArrowLeft')   populate((current - 1 + activeItems.length) % activeItems.length);
            if (e.key === 'ArrowRight')  populate((current + 1) % activeItems.length);
        });

        if (enquiryForm) {
            enquiryForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var fd = new FormData(enquiryForm);

                fetch('send-message.php', {
                    method: 'POST',
                    body: fd
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (!enquiryStatus) return;
                        if (data && data.ok) {
                            enquiryStatus.textContent = data.message || 'Message sent successfully.';
                            enquiryStatus.classList.remove('error');
                            enquiryStatus.classList.add('success');
                            if (enquiryText) enquiryText.value = '';
                        } else {
                            enquiryStatus.textContent = (data && data.message) ? data.message : 'Could not send message.';
                            enquiryStatus.classList.remove('success');
                            enquiryStatus.classList.add('error');
                        }
                    })
                    .catch(function () {
                        if (!enquiryStatus) return;
                        enquiryStatus.textContent = 'Could not send message right now. Please try again.';
                        enquiryStatus.classList.remove('success');
                        enquiryStatus.classList.add('error');
                    });
            });
        }

        /* DB product quick-view: build modal items from the rendered cards */
        var dbCardEls = document.querySelectorAll('#productsGrid .db-product-card');
        var dbItems = [];
        dbCardEls.forEach(function (card, i) {
            dbItems.push({
                id:          card.dataset.id,
                title:       card.dataset.title,
                brand:       card.dataset.brandlabel || '',
                price:       card.dataset.pricetext || '',
                description: card.dataset.desc || '',
                image:       card.dataset.image,
                stock:       card.dataset.stock,
                sellerID:    card.dataset.sellerid || '0',
                sellerName:  card.dataset.sellername || ''
            });
            card.style.cursor = 'pointer';
            card.addEventListener('click', function () {
                activeItems = dbItems;
                openAt(i);
            });
        });


        /* Live filter for DB listings — search, category, price */
        var searchInput    = document.getElementById('searchInput');
        var minPrice       = document.getElementById('minPrice');
        var maxPrice       = document.getElementById('maxPrice');
        var dbCards        = Array.from(document.querySelectorAll('#productsGrid .db-product-card'));
        var activeCat      = '';

        /* Category pills (if they exist) */
        var catPills = document.querySelectorAll('[data-cat]');
        catPills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                catPills.forEach(function (p) { p.classList.remove('active'); });
                pill.classList.add('active');
                activeCat = pill.dataset.cat;
                filterDb();
            });
        });

        function filterDb() {
            var q      = searchInput ? searchInput.value.trim().toLowerCase() : '';
            var minVal = minPrice && minPrice.value.trim() !== '' ? parseFloat(minPrice.value) : 0;
            var maxVal = maxPrice && maxPrice.value.trim() !== '' ? parseFloat(maxPrice.value) : Infinity;

            dbCards.forEach(function (card) {
                var show = true;

                // Search filter
                if (q && card.dataset.name && card.dataset.name.indexOf(q) === -1)
                    show = false;

                // Category filter
                if (activeCat && card.dataset.category && card.dataset.category.indexOf(activeCat) === -1)
                    show = false;

                // Price filter - clean the price value first
                var priceStr = (card.dataset.price || '0').toString().replace(/[R$,\s]/g, '');
                var price = parseFloat(priceStr) || 0;
                
                if (minVal > 0 && price < minVal) show = false;
                if (maxVal < Infinity && price > maxVal) show = false;

                card.style.display = show ? '' : 'none';
            });
        }

        /* Search also filters gallery cards by name */
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                var q = searchInput.value.trim().toLowerCase();

                galCards.forEach(function (card) {
                    var name = card.dataset.name || '';
                    card.style.display = (!q || name.indexOf(q) !== -1) ? '' : 'none';
                });

                filterDb();
            });
        }

        if (minPrice) {
            minPrice.addEventListener('input', function () { filterDb(); });
            minPrice.addEventListener('change', function () { filterDb(); });
        }
        if (maxPrice) {
            maxPrice.addEventListener('input', function () { filterDb(); });
            maxPrice.addEventListener('change', function () { filterDb(); });
        }


        /* Mobile navbar toggle */
        var navbarToggle = document.getElementById('navbarToggle');
        var navbarLinks  = document.getElementById('navbarLinks');

        if (navbarToggle && navbarLinks) {
            navbarToggle.addEventListener('click', function () {
                navbarToggle.classList.toggle('active');
                navbarLinks.classList.toggle('active');
            });

            navbarLinks.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    navbarToggle.classList.remove('active');
                    navbarLinks.classList.remove('active');
                });
            });
        }

    })();
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