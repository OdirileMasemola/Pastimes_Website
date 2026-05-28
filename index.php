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
                    <img src="images/dolce.jpg" alt="Dolce & Gabbana" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/dsqaured.png" alt="Dsquared2" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/kenzo.jpg" alt="Kenzo" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/lacostejacket.png" alt="Lacoste" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/louboutin.jpg" alt="Louboutin" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/LVSneaker.png" alt="Louis Vuitton" class="brand-image">
                </div>

                <!-- Duplicated set for seamless loop -->
                <div class="brand-card">
                    <img src="images/dolce.jpg" alt="Dolce & Gabbana" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/dsqaured.png" alt="Dsquared2" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/kenzo.jpg" alt="Kenzo" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/lacostejacket.png" alt="Lacoste" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/louboutin.jpg" alt="Louboutin" class="brand-image">
                </div>
                <div class="brand-card">
                    <img src="images/LVSneaker.png" alt="Louis Vuitton" class="brand-image">
                </div>
            </div>
        </div>
    </section>

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
    </footer>
</body>
</html>
<?php
/*
This code is the original work of:
ST10441421 	- Odirile Masemola
ST10450294 	- Ripfumelo Mabasa
*/
?>
