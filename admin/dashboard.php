<?php
/**
 * Admin Dashboard
 * 
 * Main page for admin users
 * Provides links to manage users, clothes, and orders
 */

session_start();

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pastimes</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
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

    <main>
        <div class="container">
            <h2>Admin Dashboard</h2>
            
            <div class="admin-dashboard">
                <div class="dashboard-section">
                    <h3>Manage Users</h3>
                    <p>Verify new customer registrations and manage user accounts.</p>
                    <ul>
                        <li><a href="manage-users.php" class="btn btn-primary">View All Users</a></li>
                        <li><a href="add-user.php" class="btn btn-secondary">Add New User</a></li>
                    </ul>
                </div>
                
                <div class="dashboard-section">
                    <h3>Manage Clothing</h3>
                    <p>Add, update, and delete clothing items from inventory.</p>
                    <ul>
                        <li><a href="manage-clothes.php" class="btn btn-primary">View All Clothes</a></li>
                        <li><a href="add-clothing.php" class="btn btn-secondary">Add New Clothing</a></li>
                    </ul>
                </div>
                
                <div class="dashboard-section">
                    <h3>Manage Orders</h3>
                    <p>View and manage customer orders.</p>
                    <ul>
                        <li><a href="manage-orders.php" class="btn btn-primary">View All Orders</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
