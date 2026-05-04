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
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="edit-user-page">
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1>Pastimes - Admin Panel</h1>
                </div>
                <ul class="nav-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage-users.php">Manage Users</a></li>
                    <li><a href="manage-clothes.php">Manage Clothes</a></li>
                    <li><a href="manage-orders.php">Manage Orders</a></li>
                    <li><a href="admin-logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <section class="edit-user-header">
                <div>
                    <h2>Edit User</h2>
                    <p>Update customer details and verification status.</p>
                </div>
                <a href="manage-users.php" class="btn btn-secondary">Back to Users</a>
            </section>
            
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
                <form method="POST" action="edit-user.php?id=<?php echo $userID; ?>" class="edit-user-form" novalidate>
                    <div class="edit-user-grid">
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
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($user['email']); ?>" 
                            readonly
                        >
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

                    <div class="form-group edit-user-actions">
                        <button type="submit" class="btn btn-primary">Update User</button>
                        <a href="manage-users.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
