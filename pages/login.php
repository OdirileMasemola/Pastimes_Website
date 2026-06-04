<?php
/**
 * User Login Page
 * 
 * Accepts username, email, and password.
 * Shows the logged-in user data on the same page when credentials are valid.
 */

session_start();
include '../includes/DBConn.php';

$username = '';
$email = '';
$password = '';
$error = '';
$loginSuccess = false;
$userRecord = null;

if (isset($_SESSION['userID']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please enter your username, email, and password.";
    } else {
        // Query user from database
        $sql = "SELECT * FROM tblUser WHERE username = ? AND email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Check if user is verified
                if ((int) $user['isVerified'] === 0) {
                    $error = "Your account is pending verification by an administrator.";
                } else {
                    // Verify password hash
                    $hashedPassword = md5($password);
                    if ($hashedPassword === $user['passwordHash']) {
                        $_SESSION['userID'] = (int) $user['userID'];
                        $_SESSION['userName'] = $user['fullName'];
                        $_SESSION['userUsername'] = $user['username'];
                        $_SESSION['userEmail'] = $user['email'];

                        $stmt->close();
                        $conn->close();
                        header('Location: ../index.php');
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
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-visual auth-visual-login" aria-label="Fashion showcase">
            <div class="auth-visual-overlay">
                <p class="auth-visual-kicker">Welcome Back</p>
                <h1>Sign In</h1>
            </div>
        </section>

        <section class="auth-panel" aria-label="Sign in section">
            <a href="javascript:history.back()" class="back-arrow" aria-label="Go back" title="Go back">&larr;</a>
            <div class="auth-card">
                <a href="../index.php" class="auth-brand" aria-label="Pastimes Home">PASTIMES</a>
                
                <h2 class="auth-heading">Sign in</h2>
                <p class="auth-subtitle">Welcome back! Please sign in to continue</p>

                <?php if ($error): ?>
                    <div class="error-message auth-message">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="auth-form">
                    <div class="auth-field">
                        <label for="username">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?php echo htmlspecialchars($username); ?>"
                            placeholder="Enter your username"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($email); ?>"
                            placeholder="your@email.com"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                    <button type="submit" class="auth-btn auth-btn-primary">Login</button>

                    <p class="auth-switch-text">Don't have an account? <a href="register.php" class="auth-link">Sign up</a></p>
                </form>
            </div>
        </section>
    </main>
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
