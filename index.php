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

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
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
