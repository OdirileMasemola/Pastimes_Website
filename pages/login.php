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
    $sessionUserID = (int) $_SESSION['userID'];
    $sessionStmt = $conn->prepare('SELECT * FROM tblUser WHERE userID = ? LIMIT 1');

    if ($sessionStmt) {
        $sessionStmt->bind_param('i', $sessionUserID);
        $sessionStmt->execute();
        $sessionResult = $sessionStmt->get_result();

        if ($sessionResult && $sessionResult->num_rows > 0) {
            $userRecord = $sessionResult->fetch_assoc();
            $loginSuccess = true;
        }

        $sessionStmt->close();
    }
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
                        $userRecord = $user;
                        $loginSuccess = true;
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

if ($loginSuccess && $userRecord === null && isset($_SESSION['userID'])) {
    $userID = (int) $_SESSION['userID'];
    $userStmt = $conn->prepare('SELECT * FROM tblUser WHERE userID = ? LIMIT 1');

    if ($userStmt) {
        $userStmt->bind_param('i', $userID);
        $userStmt->execute();
        $userResult = $userStmt->get_result();

        if ($userResult && $userResult->num_rows > 0) {
            $userRecord = $userResult->fetch_assoc();
        }

        $userStmt->close();
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-visual auth-visual-login" aria-label="Welcome panel">
            <div class="auth-visual-overlay">
                <p class="auth-visual-kicker">Pastimes Streetwear</p>
                <h1>WELCOME BACK</h1>
                <p>Discover curated pieces that define your everyday style.</p>
            </div>
        </section>

        <section class="auth-panel" aria-label="Login section">
            <div class="auth-section-label">Login</div>
            <div class="auth-card">
                <a href="../index.php" class="auth-brand" aria-label="Pastimes Home">PASTIMES</a>
                <a href="javascript:history.back()" class="back-arrow" aria-label="Go back" title="Go back">&larr;</a>

                <?php if ($loginSuccess && $userRecord): ?>
                    <div class="success-message auth-message">
                        <p>User <strong><?php echo htmlspecialchars($userRecord['fullName']); ?></strong> is logged in.</p>
                    </div>
                <?php endif; ?>

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

                <?php if (!$loginSuccess): ?>
                    <form method="POST" action="login.php" class="auth-form">
                        <div class="form-group auth-field">
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
                                placeholder="Enter your password"
                                required
                            >
                        </div>

                        <div class="auth-links-row">
                            <a href="#" class="auth-link">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn auth-btn auth-btn-primary">Login</button>

                        <p class="auth-switch-text">Don't have an account? <a href="register.php" class="auth-link">Sign Up</a></p>
                    </form>
                <?php else: ?>
                    <div class="auth-result-table-wrap">
                        <table class="admin-table auth-result-table">
                            <thead>
                                <tr>
                                    <th>Column</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userRecord as $columnName => $value): ?>
                                    <?php if ($columnName === 'passwordHash'): ?>
                                        <?php continue; ?>
                                    <?php endif; ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($columnName); ?></td>
                                        <td>
                                            <?php
                                            if ($columnName === 'isVerified') {
                                                echo ((int) $value === 1) ? 'Verified' : 'Pending Verification';
                                            } else {
                                                echo htmlspecialchars((string) $value);
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="auth-switch-text"><a href="logout.php" class="auth-link">Logout</a></p>
                <?php endif; ?>
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
