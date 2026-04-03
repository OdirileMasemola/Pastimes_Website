<?php
/**
 * User Registration Page
 * 
 * Allows new users to create an account
 * Password is hashed using MD5
 * Account is pending verification by admin
 */

session_start();
include '../includes/DBConn.php';

$fullName = '';
$email = '';
$password = '';
$confirmPassword = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = isset($_POST['fullName']) ? $_POST['fullName'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
    
    // Validation
    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $checkEmail = "SELECT userID FROM tblUser WHERE email = ?";
        $stmt = $conn->prepare($checkEmail);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already registered. Please login or use a different email.";
        } else {
            // Hash password and insert new user
            $hashedPassword = md5($password);
            $insertUser = "INSERT INTO tblUser (fullName, email, passwordHash, isVerified) VALUES (?, ?, ?, 0)";
            $insertStmt = $conn->prepare($insertUser);
            $insertStmt->bind_param("sss", $fullName, $email, $hashedPassword);
            
            if ($insertStmt->execute()) {
                $success = "Registration successful! Your account is pending admin verification. You will be able to login once verified.";
                $fullName = '';
                $email = '';
            } else {
                $error = "Error registering user: " . $conn->error;
            }
            
            $insertStmt->close();
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
    <title>Register - Pastimes</title>
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
                    <li><a href="login.php">Login</a></li>
                    <li><a href="../admin/admin-login.php">Admin</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <div class="register-form">
                <h2>Create Account</h2>
                
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
                
                <form method="POST" action="register.php" novalidate>
                    <div class="form-group">
                        <label for="fullName">Full Name:</label>
                        <input 
                            type="text" 
                            id="fullName" 
                            name="fullName" 
                            value="<?php echo htmlspecialchars($fullName); ?>" 
                            required
                        >
                    </div>
                    
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
                        <label for="confirmPassword">Confirm Password:</label>
                        <input 
                            type="password" 
                            id="confirmPassword" 
                            name="confirmPassword" 
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Register</button>
                        <a href="login.php" class="btn btn-secondary">Back to Login</a>
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
