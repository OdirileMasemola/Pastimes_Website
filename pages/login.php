<?php
/**
 * User Login Page
 * 
 * Accepts username/email and password
 * Validates against hashed password in database
 * Creates session if credentials are valid
 */

session_start();
include '../includes/DBConn.php';

$email = '';
$password = '';
$error = '';
$loggedInUser = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Query user from database
        $sql = "SELECT userID, fullName, passwordHash, isVerified FROM tblUser WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Check if user is verified
            if ($user['isVerified'] == 0) {
                $error = "Your account is pending verification by an administrator.";
            } else {
                // Verify password hash
                $hashedPassword = md5($password);
                if ($hashedPassword === $user['passwordHash']) {
                    // Password matches, create session
                    $_SESSION['userID'] = $user['userID'];
                    $_SESSION['userName'] = $user['fullName'];
                    $_SESSION['userEmail'] = $email;
                    $loggedInUser = $user['fullName'];
                    header("Location: account.php");
                    exit();
                } else {
                    $error = "Invalid password.";
                }
            }
        } else {
            $error = "User not found. Please register to create an account.";
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
    <title>Login - Pastimes</title>
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
                    <li><a href="register.php">Register</a></li>
                    <li><a href="../admin/admin-login.php">Admin</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <div class="login-form">
                <h2>User Login</h2>
                
                <?php if ($loggedInUser): ?>
                    <div class="success-message">
                        <p>User <strong><?php echo htmlspecialchars($loggedInUser); ?></strong> is logged in.</p>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="login.php" novalidate>
                    <div class="form-group">
                        <label for="email">Email Address:</label>
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
                        <button type="submit" class="btn btn-primary">Login</button>
                        <a href="register.php" class="btn btn-secondary">Register</a>
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
