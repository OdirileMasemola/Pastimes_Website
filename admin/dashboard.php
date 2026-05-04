<?php
/**
 * Admin Dashboard
 * 
 * Main page for admin users
 * Provides links to manage users, clothes, and orders
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$userCount = 0;
$pendingUserCount = 0;
$clothingCount = 0;
$orderCount = 0;

$userResult = $conn->query("SELECT COUNT(*) AS total FROM tblUser");
if ($userResult && $userRow = $userResult->fetch_assoc()) {
    $userCount = (int) $userRow['total'];
}

$pendingResult = $conn->query("SELECT COUNT(*) AS total FROM tblUser WHERE isVerified = 0");
if ($pendingResult && $pendingRow = $pendingResult->fetch_assoc()) {
    $pendingUserCount = (int) $pendingRow['total'];
}

$clothingResult = $conn->query("SELECT COUNT(*) AS total FROM tblClothes");
if ($clothingResult && $clothingRow = $clothingResult->fetch_assoc()) {
    $clothingCount = (int) $clothingRow['total'];
}

$orderResult = $conn->query("SELECT COUNT(*) AS total FROM tblOrder");
if ($orderResult && $orderRow = $orderResult->fetch_assoc()) {
    $orderCount = (int) $orderRow['total'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pastimes</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="admin-dashboard-page">
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1>Pastimes - Admin Dashboard</h1>
                </div>
                <ul class="nav-menu">
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="admin-logout.php">Logout (<?php echo htmlspecialchars($_SESSION['adminName']); ?>)</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="admin-main">
        <div class="container">
            <section class="admin-dash-hero">
                <div>
                    <p class="admin-dash-kicker">Control Center</p>
                    <h2>Admin Dashboard</h2>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['adminName']); ?>. Manage users, products, and orders from one place.</p>
                </div>
                <a href="../index.php" class="btn btn-secondary">View Storefront</a>
            </section>

            <section class="admin-stats-grid" aria-label="Dashboard metrics">
                <article class="admin-stat-card">
                    <h3>Total Users</h3>
                    <p class="admin-stat-value"><?php echo $userCount; ?></p>
                </article>
                <article class="admin-stat-card">
                    <h3>Pending Verification</h3>
                    <p class="admin-stat-value"><?php echo $pendingUserCount; ?></p>
                </article>
                <article class="admin-stat-card">
                    <h3>Clothing Items</h3>
                    <p class="admin-stat-value"><?php echo $clothingCount; ?></p>
                </article>
                <article class="admin-stat-card">
                    <h3>Total Orders</h3>
                    <p class="admin-stat-value"><?php echo $orderCount; ?></p>
                </article>
            </section>

            <div class="admin-dashboard">
                <div class="dashboard-section">
                    <h3>Manage Users</h3>
                    <p>Verify new customer registrations and manage user accounts.</p>
                    <div class="dashboard-actions">
                        <a href="manage-users.php" class="btn btn-primary">View All Users</a>
                        <a href="add-user.php" class="btn btn-secondary">Add New User</a>
                    </div>
                </div>
                
                <div class="dashboard-section">
                    <h3>Manage Clothing</h3>
                    <p>Add, update, and delete clothing items from inventory.</p>
                    <div class="dashboard-actions">
                        <a href="manage-clothes.php" class="btn btn-primary">View All Clothes</a>
                        <a href="add-clothing.php" class="btn btn-secondary">Add New Clothing</a>
                    </div>
                </div>
                
                <div class="dashboard-section">
                    <h3>Manage Orders</h3>
                    <p>View and manage customer orders.</p>
                    <div class="dashboard-actions">
                        <a href="manage-orders.php" class="btn btn-primary">View All Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
