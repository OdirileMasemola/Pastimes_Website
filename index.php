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
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1>Pastimes</h1>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="pages/shop.php">Shop</a></li>
                    <?php if (isset($_SESSION['userID'])): ?>
                        <li><a href="pages/account.php">My Account</a></li>
                        <li><a href="pages/logout.php">Logout (<?php echo $_SESSION['userName']; ?>)</a></li>
                    <?php else: ?>
                        <li><a href="pages/login.php">Login</a></li>
                        <li><a href="pages/register.php">Register</a></li>
                    <?php endif; ?>
                    <li><a href="admin/admin-login.php">Admin</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h2>Welcome to Pastimes</h2>
            <p>Your One-Stop Shop for Quality Clothing</p>
            <a href="pages/shop.php" class="btn btn-primary">Shop Now</a>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
