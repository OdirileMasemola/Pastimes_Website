<?php
/**
 * Shop Page
 * Luxury gallery — sidebar filter left, cards right, no duplicate brand on hover.
 * Backend PHP logic is unchanged from original.
 */

session_start();
include '../includes/DBConn.php';

$sql = "SELECT * FROM tblClothes WHERE approvalStatus = 'approved'";
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

$conn->close();

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop — Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="shop-page-body">
    <?php include '../includes/navbar.php'; ?>
    
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
                        <div class="sidebar-price-row">
                            <input id="minPrice" type="number" class="sidebar-price-input" placeholder="Min">
                            <span class="sidebar-price-sep">to</span>
                            <input id="maxPrice" type="number" class="sidebar-price-input" placeholder="Max">
                        </div>
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
                            $dataSale   = isset($item['onSale']) ? ($item['onSale'] ? '1' : '0')
                                        : (isset($item['sale']) ? ($item['sale']   ? '1' : '0') : '');
                            ?>
                            <div class="db-product-card"
                                 data-name="<?php echo htmlspecialchars(strtolower($item['clothingName'])); ?>"
                                 data-category="<?php echo htmlspecialchars(strtolower($item['category'])); ?>"
                                 data-brand="<?php echo htmlspecialchars(strtolower($dataBrand)); ?>"
                                 data-gender="<?php echo htmlspecialchars(strtolower($dataGender)); ?>"
                                 data-sale="<?php echo $dataSale; ?>"
                                 data-price="<?php echo $item['price']; ?>">

                                <div class="db-product-img-wrap">
                                    <img class="db-product-img"
                                         src="<?php echo htmlspecialchars($imageToDisplay); ?>"
                                         alt="<?php echo htmlspecialchars($item['clothingName']); ?>"
                                         loading="lazy">
                                </div>

                                <div class="db-product-body">
                                    <p class="db-product-name"><?php echo htmlspecialchars($item['clothingName']); ?></p>
                                    <p class="db-product-price">R <?php echo number_format($item['price'], 2); ?></p>
                                    <a href="product-details.php?id=<?php echo $item['clothingID']; ?>" class="db-product-btn">View Details</a>
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
                <a href="#" class="lb-cta" id="lbCta">Browse Collection</a>
            </div>

            <div class="lb-counter" id="lbCounter"></div>
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
                        <li><a href="pages/my-orders.php" class="footer-link">My Orders</a></li>
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


        /* Lightbox */
        var items     = <?php echo json_encode(array_values($featuredItems)); ?>;
        var current   = 0;
        var overlay   = document.getElementById('lbOverlay');
        var lbImg     = document.getElementById('lbImg');
        var lbTitle   = document.getElementById('lbTitle');
        var lbBrand   = document.getElementById('lbBrand');
        var lbPrice   = document.getElementById('lbPrice');
        var lbDesc    = document.getElementById('lbDesc');
        var lbCta     = document.getElementById('lbCta');
        var lbCounter = document.getElementById('lbCounter');

        function populate(idx) {
            var item = items[idx];
            if (!item) return;
            lbImg.src             = item.image;
            lbImg.alt             = item.title;
            lbTitle.textContent   = item.title;
            lbBrand.textContent   = item.brand;
            lbPrice.textContent   = item.price;
            lbDesc.textContent    = item.description;
            lbCta.href            = item.link;
            lbCounter.textContent = (idx + 1) + ' / ' + items.length;
            current = idx;
        }

        window.openLightbox = function (idx) {
            populate(idx);
            overlay.style.display = 'flex';
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    overlay.classList.add('open', 'visible');
                });
            });
            document.body.style.overflow = 'hidden';
        };

        function closeLightbox() {
            overlay.classList.remove('visible');
            setTimeout(function () {
                overlay.classList.remove('open');
                overlay.style.display = '';
                document.body.style.overflow = '';
            }, 360);
        }

        document.getElementById('lbClose').addEventListener('click', closeLightbox);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) closeLightbox(); });

        document.getElementById('lbPrev').addEventListener('click', function () {
            populate((current - 1 + items.length) % items.length);
        });
        document.getElementById('lbNext').addEventListener('click', function () {
            populate((current + 1) % items.length);
        });

        document.addEventListener('keydown', function (e) {
            if (!overlay.classList.contains('visible')) return;
            if (e.key === 'Escape')      closeLightbox();
            if (e.key === 'ArrowLeft')   populate((current - 1 + items.length) % items.length);
            if (e.key === 'ArrowRight')  populate((current + 1) % items.length);
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