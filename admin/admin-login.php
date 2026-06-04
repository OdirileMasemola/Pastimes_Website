<?php
/**
 * Admin Login Page
 * 
 * Authenticates admin users
 * Administrators can manage users, clothes, and orders
 */

session_start();
include '../includes/DBConn.php';

if (isset($_SESSION['adminID'])) {
    header("Location: dashboard.php");
    exit();
}

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
        
        // Check if prepare failed
        if (!$stmt) {
            $error = "Database error: " . $conn->error;
        } else {
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
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-visual auth-visual-login" aria-label="Admin panel">
            <div class="auth-visual-overlay">
                <p class="auth-visual-kicker">Administration</p>
                <h1>Admin Access</h1>
                <p>Manage the Pastimes platform: users, inventory, orders, and marketplace operations.</p>
            </div>
        </section>

        <section class="auth-panel" aria-label="Admin login section">
            <div class="auth-section-label">Administration</div>
            <div class="auth-card">
                <a href="../index.php" class="auth-brand" aria-label="Pastimes Home">PASTIMES</a>
                <a href="javascript:history.back()" class="back-arrow" aria-label="Go back" title="Go back">&larr;</a>

                <?php if ($error): ?>
                    <div class="error-message auth-message">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="admin-login.php" class="auth-form">
                    <div class="auth-field">
                        <label for="email">Admin Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($email); ?>"
                            placeholder="admin@pastimes.com"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter admin password"
                            required
                        >
                    </div>

                    <button type="submit" class="auth-btn auth-btn-primary">Sign In</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
<?php
/*
This code is the original work of:
ST10441421 	- Odirile Masemola
ST10450294 	- Ripfumelo Mabasa
*/
?>