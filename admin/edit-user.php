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
    $fullName = isset($_POST['fullName']) ? $_POST['fullName'] : '';
    $isVerified = isset($_POST['isVerified']) ? 1 : 0;
    
    $updateSql = "UPDATE tblUser SET fullName = ?, isVerified = ? WHERE userID = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sii", $fullName, $isVerified, $userID);
    
    if ($updateStmt->execute()) {
        $success = "User updated successfully!";
        $user['fullName'] = $fullName;
        $user['isVerified'] = $isVerified;
    } else {
        $error = "Error updating user: " . $conn->error;
    }
    
    $updateStmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1>Pastimes - Admin Panel</h1>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h2>Edit User</h2>
            
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
                <form method="POST" action="edit-user.php?id=<?php echo $userID; ?>" novalidate>
                    <div class="form-group">
                        <label for="fullName">Full Name:</label>
                        <input 
                            type="text" 
                            id="fullName" 
                            name="fullName" 
                            value="<?php echo htmlspecialchars($user['fullName']); ?>" 
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($user['email']); ?>" 
                            readonly
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="isVerified">
                            <input 
                                type="checkbox" 
                                id="isVerified" 
                                name="isVerified" 
                                <?php echo $user['isVerified'] ? 'checked' : ''; ?>
                            >
                            Verified
                        </label>
                    </div>
                    
                    <div class="form-group">
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
