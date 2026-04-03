<?php
/**
 * Admin Login Page
 * 
 * Authenticates admin users
 * Administrators can manage users, clothes, and orders
 */

session_start();
include '../includes/DBConn.php';

$email = '';
$password = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Query admin from database
        $sql = "SELECT adminID, adminName, passwordHash FROM tblAdmin WHERE adminEmail = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            
            // Verify password hash
            $hashedPassword = md5($password);
            if ($hashedPassword === $admin['passwordHash']) {
                // Password matches, create session
                $_SESSION['adminID'] = $admin['adminID'];
                $_SESSION['adminName'] = $admin['adminName'];
                $_SESSION['adminEmail'] = $email;
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Admin account not found.";
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Pastimes</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1>Pastimes - Admin</h1>
                </div>
                <ul class="nav-menu">
                    <li><a href="../index.php">Home</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <div class="admin-login-form">
                <h2>Admin Login</h2>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="admin-login.php" novalidate>
                    <div class="form-group">
                        <label for="email">Admin Email:</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($email); ?>" 
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Admin Login</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
