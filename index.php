<?php
/**
 * Home Page - Index
 * 
 * Main landing page for Pastimes Website
 */

session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastimes - Home</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="home-page">
    <?php include 'includes/navbar.php'; ?>

    <main>
        <section class="hero" style="background-image: url('images/homepagewallpaper.jpg');">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1 class="hero-title">Buy and Sell Pre-Loved Fashion</h1>
                <p class="hero-subtitle">Discover quality second-hand clothing, list your own items, and shop affordable fashion in one simple marketplace.</p>
                <div class="hero-buttons">
                    <a href="pages/shop.php" class="hero-btn hero-btn-primary">Shop Now</a>
                    <a href="pages/sell-item.php" class="hero-btn hero-btn-secondary">Sell an Item</a>
                </div>
            </div>
        </section>
    </main>

    <!-- Trusted Brands Section -->
    <section class="brands-section">
        <div class="brands-header">
            <h2 class="brands-title">Trusted Brands</h2>
            <p class="brands-subtitle">Discover premium fashion brands available through the Pastimes marketplace.</p>
        </div>

        <div class="brands-slider">
            <div class="brands-track">
                <!-- First set of brand images -->
                <div class="brand-card">
                    <img src="images/D&G.webp" alt="Dolce & Gabbana" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/dsquared2logo.png" alt="Dsquared2" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/kenzoLogo.png" alt="Kenzo" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/lacostelogo.jpg" alt="Lacoste" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/christianlogo.png" alt="Louboutin" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/lvlogo.png" alt="Louis Vuitton" class="brand-image">
                </div>

                <!-- Duplicated set for seamless loop -->
                <div class="brand-card">
                    <img src="images/D&G.webp" alt="Dolce & Gabbana" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/dsquared2logo.png" alt="Dsquared2" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/kenzoLogo.png" alt="Kenzo" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/lacostelogo.jpg" alt="Lacoste" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/christianlogo.png" alt="Louboutin" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/lvlogo.png" alt="Louis Vuitton" class="brand-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products-section">
        <div class="featured-products-header">
            <h2 class="featured-products-title">Featured Products</h2>
            <p class="featured-products-subtitle">Explore selected pre-loved fashion pieces available on Pastimes.</p>
        </div>

        <div class="featured-products-grid">
            <!-- Product 1: Dolce -->
            <div class="product-card" data-product-id="1" data-product-name="Dolce Statement Piece" data-product-brand="Dolce & Gabbana" data-product-price="R18,500" data-product-image="images/dolce.jpg" data-product-desc="Premium Dolce & Gabbana statement piece. Authentic designer fashion for the discerning shopper.">
                <div class="product-image-wrap">
                    <img src="images/dolce.jpg" alt="Dolce Statement Piece" class="product-image">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Portofino sneakers in calfskin and patent leather</h3>
                    <p class="product-brand">Dolce & Gabbana</p>
                    <p class="product-price">R18,500</p>
                </div>
                <button class="product-view-btn" title="View Product"></button>
            </div>

            <!-- Product 2: Dsquared -->
            <div class="product-card" data-product-id="2" data-product-name="Dsquared2 Denim Jean" data-product-brand="Dsquared2" data-product-price="R16,000" data-product-image="images/dsquared.png" data-product-desc="Iconic Dsquared2 denim jean. Contemporary designer style with authentic craftsmanship.">
                <div class="product-image-wrap">
                    <img src="images/dsquared.png" alt="Dsquared2 Denim Jean" class="product-image">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Dsquared2 Denim Jean</h3>
                    <p class="product-brand">Dsquared2</p>
                    <p class="product-price">R16,000</p>
                </div>
                <button class="product-view-btn" title="View Product"></button>
            </div>

            <!-- Product 3: Kenzo -->
            <div class="product-card" data-product-id="3" data-product-name="Kenzo Graphic Tee" data-product-brand="Kenzo" data-product-price="R3,500" data-product-image="images/kenzo.jpg" data-product-desc="Signature Kenzo graphic tee. Bold design meets comfort in this premium pre-loved piece.">
                <div class="product-image-wrap">
                    <img src="images/kenzo.jpg" alt="Kenzo Graphic Tee" class="product-image">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Kenzo Graphic Tee</h3>
                    <p class="product-brand">Kenzo</p>
                    <p class="product-price">R3,500</p>
                </div>
                <button class="product-view-btn" title="View Product"></button>
            </div>

            <!-- Product 4: Lacoste -->
            <div class="product-card" data-product-id="4" data-product-name="Lacoste Jacket" data-product-brand="Lacoste" data-product-price="R3,000" data-product-image="images/lacostejacket.png" data-product-desc="Classic Lacoste jacket. Timeless elegance and quality construction in this iconic piece.">
                <div class="product-image-wrap">
                    <img src="images/lacostejacket.png" alt="Lacoste Jacket" class="product-image">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Lacoste Monogram Jacket</h3>
                    <p class="product-brand">Lacoste</p>
                    <p class="product-price">R3,000</p>
                </div>
                <button class="product-view-btn" title="View Product"></button>
            </div>

            <!-- Product 5: Louboutin -->
            <div class="product-card" data-product-id="5" data-product-name="Louboutin Sneaker" data-product-brand="Christian Louboutin" data-product-price="R23,000" data-product-image="images/louboutin.jpg" data-product-desc="Luxury Christian Louboutin sneaker. Premium footwear with iconic design and superior craftsmanship.">
                <div class="product-image-wrap">
                    <img src="images/louboutin.jpg" alt="Louboutin Sneaker" class="product-image">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Louboutin Sneaker</h3>
                    <p class="product-brand">Christian Louboutin</p>
                    <p class="product-price">R23,000</p>
                </div>
                <button class="product-view-btn" title="View Product"></button>
            </div>

            <!-- Product 6: Louis Vuitton -->
            <div class="product-card" data-product-id="6" data-product-name="Louis Vuitton Sneaker" data-product-brand="Louis Vuitton" data-product-price="R30,000" data-product-image="images/LVSneaker.png" data-product-desc="Prestigious Louis Vuitton sneaker. Premium luxury footwear with exceptional quality and design.">
                <div class="product-image-wrap">
                    <img src="images/LVSneaker.png" alt="Louis Vuitton Sneaker" class="product-image">
                </div>
                <div class="product-info">
                    <h3 class="product-name">Louis Vuitton Trainers</h3>
                    <p class="product-brand">Louis Vuitton</p>
                    <p class="product-price">R30,000</p>
                </div>
                <button class="product-view-btn" title="View Product"></button>
            </div>
        </div>
    </section>

    <!-- Product Modal -->
    <div class="product-modal-overlay" id="productModalOverlay"></div>
    <div class="product-modal" id="productModal">
        <button class="product-modal-close" id="productModalClose">&times;</button>
        <div class="product-modal-content">
            <div class="product-modal-image-wrap">
                <img src="" alt="Product" class="product-modal-image" id="productModalImage">
            </div>
            <div class="product-modal-info">
                <h2 id="productModalName"></h2>
                <p class="product-modal-brand" id="productModalBrand"></p>
                <p class="product-modal-price" id="productModalPrice"></p>
                <p class="product-modal-description" id="productModalDesc"></p>
                <a href="pages/shop.php" class="product-modal-action">View in Shop</a>
            </div>
        </div>
    </div>

    <!-- How Pastimes Works Section -->
    <section class="how-section">
        <div class="how-header">
            <h2 class="how-title">How Pastimes Works</h2>
            <p class="how-subtitle">A simple way to shop, sell, and manage pre-loved fashion online.</p>
        </div>

        <div class="how-cards">
            <!-- Card 1: Browse Quality Fashion -->
            <div class="how-card">
                <div class="how-gradient" style="background: linear-gradient(135deg, #FF3366, #FF6B00);"></div>
                <div class="how-gradient-blur"></div>
                <div class="how-glass">
                    <div class="how-step">01</div>
                    <h3 class="how-card-title">Browse Quality Fashion</h3>
                    <p class="how-card-text">Explore selected second-hand clothing from trusted brands and find pieces that match your style.</p>
                </div>
            </div>

            <!-- Card 2: Buy With Ease -->
            <div class="how-card">
                <div class="how-gradient" style="background: linear-gradient(135deg, #2EC4B6, #20A4F3);"></div>
                <div class="how-gradient-blur"></div>
                <div class="how-glass">
                    <div class="how-step">02</div>
                    <h3 class="how-card-title">Buy With Ease</h3>
                    <p class="how-card-text">Add products to your cart, checkout easily, and track your orders from your account.</p>
                </div>
            </div>

            <!-- Card 3: Sell Your Items -->
            <div class="how-card">
                <div class="how-gradient" style="background: linear-gradient(135deg, #2EC4B6, #39FF14);"></div>
                <div class="how-gradient-blur"></div>
                <div class="how-glass">
                    <div class="how-step">03</div>
                    <h3 class="how-card-title">Sell Your Items</h3>
                    <p class="how-card-text">List your own branded clothing, manage your listings, and let admins review your submissions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Become a Seller Section -->
    <section class="seller-section">
        <div class="seller-header">
            <span class="seller-badge">Seller Program</span>
            <h2 class="seller-title">Become a Seller</h2>
            <p class="seller-subtitle">Turn your wardrobe into cash by listing quality pre-loved fashion on Pastimes.</p>
        </div>

        <div class="seller-grid-bg">
            <div class="seller-card">
                <div class="border-trail"></div>
                
                <div class="seller-decorative seller-decorative-tl">+</div>
                <div class="seller-decorative seller-decorative-tr">+</div>
                <div class="seller-decorative seller-decorative-bl">+</div>
                <div class="seller-decorative seller-decorative-br">+</div>

                <div class="seller-card-content">
                    <!-- Left Side -->
                    <div class="seller-card-left">
                        <h3 class="seller-card-title">Sell Your Pre-Loved Fashion</h3>
                        <p class="seller-card-description">Upload your branded clothing, add product details, and submit your item for admin review.</p>
                        
                        <ul class="seller-benefits">
                            <li class="seller-benefit">List items in minutes</li>
                            <li class="seller-benefit">Reach fashion shoppers</li>
                            <li class="seller-benefit">Manage listings from your account</li>
                        </ul>
                    </div>

                    <!-- Right Side -->
                    <div class="seller-card-right">
                        <h3 class="seller-card-title-right">Start Selling</h3>
                        <p class="seller-card-highlight">No monthly fees</p>
                        <p class="seller-card-info">Submit your items and wait for approval before they appear in the shop.</p>
                        
                        <a href="pages/sell-item.php" class="seller-button">Start Selling</a>
                    </div>
                </div>
            </div>
        </div>

        <p class="seller-trust-note">Secure seller submissions reviewed by Pastimes admins.</p>
    </section>

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
</body>
</html>

<script>
// Featured Products Modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('productModal');
    const overlay = document.getElementById('productModalOverlay');
    const closeBtn = document.getElementById('productModalClose');
    const productCards = document.querySelectorAll('.product-card');
    const viewBtns = document.querySelectorAll('.product-view-btn');

    // Function to open modal
    function openModal(productData) {
        document.getElementById('productModalImage').src = productData.image;
        document.getElementById('productModalName').textContent = productData.name;
        document.getElementById('productModalBrand').textContent = productData.brand;
        document.getElementById('productModalPrice').textContent = productData.price;
        document.getElementById('productModalDesc').textContent = productData.desc;
        
        modal.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Function to close modal
    function closeModal() {
        modal.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Open modal on card click
    productCards.forEach(card => {
        card.addEventListener('click', function() {
            const productData = {
                name: this.dataset.productName,
                brand: this.dataset.productBrand,
                price: this.dataset.productPrice,
                image: this.dataset.productImage,
                desc: this.dataset.productDesc
            };
            openModal(productData);
        });
    });

    // Open modal on view button click
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const card = this.closest('.product-card');
            const productData = {
                name: card.dataset.productName,
                brand: card.dataset.productBrand,
                price: card.dataset.productPrice,
                image: card.dataset.productImage,
                desc: card.dataset.productDesc
            };
            openModal(productData);
        });
    });

    // Close modal on close button click
    closeBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        closeModal();
    });

    // Close modal on overlay click
    overlay.addEventListener('click', closeModal);

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });
});
</script>
<?php
/*
This code is the original work of:
ST10441421 	- Odirile Masemola
ST10450294 	- Ripfumelo Mabasa
*/
?>
