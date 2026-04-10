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
$username = '';
$email = '';
$password = '';
$confirmPassword = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = isset($_POST['fullName']) ? $_POST['fullName'] : '';
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
    
    // Validation
    if (empty($fullName) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
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
                $username = '';
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth-page">
    <main class="auth-layout auth-layout-register">
        <section class="auth-visual auth-visual-register" aria-label="Welcome panel">
            <div class="auth-visual-overlay">
                <p class="auth-visual-kicker">Pastimes New Arrivals</p>
                <h1>WELCOME TO PASTIMES</h1>
                <p>Build your profile and shop timeless fashion essentials.</p>
            </div>
        </section>

        <section class="auth-panel" aria-label="Sign-Up section">
            <div class="auth-section-label">Sign-Up</div>
            <div class="auth-card">
                <a href="../index.php" class="auth-brand" aria-label="Pastimes Home">PASTIMES</a>

                <?php if ($error): ?>
                    <div class="error-message auth-message">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success-message auth-message">
                        <p><?php echo htmlspecialchars($success); ?></p>
                    </div>
                <?php endif; ?>

                <div class="auth-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" role="img" focusable="false">
                        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"></path>
                    </svg>
                </div>

                <form method="POST" action="register.php" class="auth-form" novalidate>
                    <div class="form-group auth-field">
                        <label for="fullName">Full Name</label>
                        <input
                            type="text"
                            id="fullName"
                            name="fullName"
                            value="<?php echo htmlspecialchars($fullName); ?>"
                            placeholder="Enter your full name"
                            required
                        >
                    </div>

                    <div class="form-group auth-field">
                        <label for="username">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?php echo htmlspecialchars($username); ?>"
                            placeholder="Choose a username"
                            required
                        >
                    </div>

                    <div class="form-group auth-field">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($email); ?>"
                            placeholder="name@example.com"
                            required
                        >
                    </div>

                    <div class="form-group auth-field">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Create password"
                            required
                        >
                    </div>

                    <div class="form-group auth-field">
                        <label for="confirmPassword">Confirm Password</label>
                        <input
                            type="password"
                            id="confirmPassword"
                            name="confirmPassword"
                            placeholder="Re-enter password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn auth-btn auth-btn-primary">Sign-Up</button>

                    <p class="auth-switch-text">Already have an account? <a href="login.php" class="auth-link">Login</a></p>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
