<?php
/**
 * User Account/Dashboard Page
 * 
 * Displays user profile information and links to account features
 * Only accessible to logged-in users
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$userID = $_SESSION['userID'];
$sql = "SELECT * FROM tblUser WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Pastimes</title>
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
                    <li><a href="account.php">My Account</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h2>User Logged In: <?php echo htmlspecialchars($user['fullName']); ?></h2>
            
            <div class="dashboard">
                <h3>Account Dashboard</h3>
                
                <div class="user-info">
                    <h4>Profile Information</h4>
                    <table class="info-table">
                        <tr>
                            <th>Full Name:</th>
                            <td><?php echo htmlspecialchars($user['fullName']); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td><?php echo htmlspecialchars($user['address'] ?? 'Not provided'); ?></td>
                        </tr>
                        <tr>
                            <th>City:</th>
                            <td><?php echo htmlspecialchars($user['city'] ?? 'Not provided'); ?></td>
                        </tr>
                        <tr>
                            <th>Account Status:</th>
                            <td><?php echo $user['isVerified'] ? 'Verified' : 'Pending Verification'; ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="dashboard-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="my-orders.php" class="btn btn-secondary">My Orders</a></li>
                        <li><a href="my-messages.php" class="btn btn-secondary">My Messages</a></li>
                        <li><a href="sell-item.php" class="btn btn-secondary">Sell an Item</a></li>
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
