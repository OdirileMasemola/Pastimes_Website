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
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-visual auth-visual-login" aria-label="Admin welcome panel">
            <div class="auth-visual-overlay">
                <p class="auth-visual-kicker">Pastimes Back Office</p>
                <h1>ADMIN ACCESS</h1>
                <p>Manage users, inventory, and orders with a streamlined control panel.</p>
            </div>
        </section>

        <section class="auth-panel" aria-label="Admin login section">
            <div class="auth-section-label">Admin Login</div>
            <div class="auth-card admin-login-form">
                <a href="../index.php" class="auth-brand" aria-label="Pastimes Home">PASTIMES</a>
                <a href="javascript:history.back()" class="back-arrow" aria-label="Go back" title="Go back">&larr;</a>

                <?php if ($error): ?>
                    <div class="error-message auth-message">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <div class="auth-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" role="img" focusable="false">
                        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"></path>
                    </svg>
                </div>

                <form method="POST" action="admin-login.php" class="auth-form" novalidate>
                    <div class="form-group auth-field">
                        <label for="email">Admin Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($email); ?>"
                            placeholder="admin@example.com"
                            required
                        >
                    </div>

                    <div class="form-group auth-field">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn auth-btn auth-btn-primary">Admin Login</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
