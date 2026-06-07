<?php
/**
 * Edit User Page
 * 
 * Admin can edit user information and verify accounts
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$userID = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = null;
$error = '';
$success = '';

if ($userID > 0) {
    $sql = "SELECT * FROM tblUser WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $error = "User not found.";
    }
    
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user) {
    $fullName = isset($_POST['fullName']) ? trim($_POST['fullName']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $isVerified = isset($_POST['isVerified']) ? 1 : 0;

    if ($fullName === '' || $username === '') {
        $error = "Full name and username are required.";
    }

    if (!$error) {
        $duplicateSql = "SELECT userID FROM tblUser WHERE (username = ? OR email = ?) AND userID <> ? LIMIT 1";
        $duplicateStmt = $conn->prepare($duplicateSql);

        if (!$duplicateStmt) {
            $error = "Database error: " . $conn->error;
        } else {
            $duplicateStmt->bind_param("ssi", $username, $user['email'], $userID);
            $duplicateStmt->execute();
            $duplicateResult = $duplicateStmt->get_result();

            if ($duplicateResult->num_rows > 0) {
                $error = "Username already exists for another customer.";
            } else {
                $updateSql = "UPDATE tblUser SET fullName = ?, username = ?, isVerified = ? WHERE userID = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("ssii", $fullName, $username, $isVerified, $userID);

                if ($updateStmt->execute()) {
                    $success = "User updated successfully!";
                    $user['fullName'] = $fullName;
                    $user['username'] = $username;
                    $user['isVerified'] = $isVerified;
                } else {
                    $error = "Error updating user: " . $conn->error;
                }

                $updateStmt->close();
            }

            $duplicateStmt->close();
        }
    }
}

$conn->close();
?>
<?php
/*
This code is the original work of:
ST10441421 - Odirile Masemola
ST10450294 - Ripfumelo Mabasa
All rights reserved.
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css?v=5">
</head>
<body class="admin-page">
    <header>
        <?php include '../includes/navbar.php'; ?>
    </header>

    <main>
        <div class="admin-container">
            <nav class="admin-breadcrumb">Dashboard / Users / <span>Edit User</span></nav>

            <div class="admin-page-head">
                <div>
                    <h1 class="admin-title">Edit User</h1>
                    <p class="admin-subtitle">Update customer details and verification status.</p>
                </div>
                <a href="manage-users.php" class="admin-action-btn ghost">Back to Users</a>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($user): ?>
                <form method="POST" action="edit-user.php?id=<?php echo $userID; ?>" class="admin-form-card" novalidate>
                    <div class="admin-form-grid">
                        <div class="form-group">
                            <label for="fullName">Full Name</label>
                            <input 
                                type="text" 
                                id="fullName" 
                                name="fullName" 
                                value="<?php echo htmlspecialchars($user['fullName']); ?>" 
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                value="<?php echo htmlspecialchars($user['username']); ?>" 
                                required
                            >
                        </div>

                        <div class="form-group admin-full">
                            <label for="email">Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?php echo htmlspecialchars($user['email']); ?>" 
                                readonly
                            >
                        </div>
                    </div>

                    <label for="isVerified" class="verify-toggle">
                        <input 
                            type="checkbox" 
                            id="isVerified" 
                            name="isVerified" 
                            <?php echo $user['isVerified'] ? 'checked' : ''; ?>
                        >
                        <span>
                            <strong>Account Verified</strong>
                            <small>Allow this user to log in when checked.</small>
                        </span>
                    </label>

                    <div class="admin-row-actions">
                        <button type="submit" class="admin-action-btn primary">Update User</button>
                        <a href="manage-users.php" class="admin-action-btn ghost">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

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
