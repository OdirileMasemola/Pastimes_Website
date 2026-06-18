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
$next = isset($_GET['next']) ? strtolower(trim($_GET['next'])) : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['next'])) {
    $next = strtolower(trim($_POST['next']));
}
$allowedNext = array('checkout', 'cart');
if (!in_array($next, $allowedNext, true)) {
    $next = '';
}
$querySuffix = $next !== '' ? '?next=' . urlencode($next) : '';
$loginLink = 'login.php' . $querySuffix;
$formAction = 'register.php' . $querySuffix;

/**
 * Ensures tblUser has the minimum columns required by this page.
 * This guards against legacy schemas missing username/isVerified.
 */
function ensureUserTableSchema(mysqli $conn, &$errorMessage)
{
    $requiredColumns = array(
        'username' => "ALTER TABLE tblUser ADD COLUMN username VARCHAR(50) NOT NULL UNIQUE AFTER userID",
        'fullName' => "ALTER TABLE tblUser ADD COLUMN fullName VARCHAR(100) NOT NULL AFTER username",
        'email' => "ALTER TABLE tblUser ADD COLUMN email VARCHAR(100) NOT NULL UNIQUE AFTER fullName",
        'passwordHash' => "ALTER TABLE tblUser ADD COLUMN passwordHash VARCHAR(255) NOT NULL AFTER email",
        'isVerified' => "ALTER TABLE tblUser ADD COLUMN isVerified TINYINT(1) NOT NULL DEFAULT 0 AFTER phone"
    );

    foreach ($requiredColumns as $columnName => $alterSql) {
        $columnCheck = $conn->query("SHOW COLUMNS FROM tblUser LIKE '" . $conn->real_escape_string($columnName) . "'");

        if ($columnCheck === false) {
            $errorMessage = "Error checking user table structure: " . $conn->error;
            return false;
        }

        if ($columnCheck->num_rows === 0) {
            if (!$conn->query($alterSql)) {
                $errorMessage = "User table schema is outdated. Please run /includes/loadClothingStore.php. Technical details: " . $conn->error;
                return false;
            }
        }

        $columnCheck->free();
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = isset($_POST['fullName']) ? trim($_POST['fullName']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
    
    // Validation
    if (empty($fullName) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!ensureUserTableSchema($conn, $error)) {
        // $error has already been set by ensureUserTableSchema().
    } else {
        // Check if username or email already exists
        $checkUser = "SELECT userID FROM tblUser WHERE email = ? OR username = ? LIMIT 1";
        $stmt = $conn->prepare($checkUser);

        if (!$stmt) {
            $error = "Unable to validate your details right now. Please try again. Technical details: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $email, $username);
            $stmt->execute();
            $stmt->store_result();
            $existingUsers = $stmt->num_rows;
        
            if ($existingUsers > 0) {
                $error = "Username or email already registered. Please login or use different details.";
            } else {
                // Hash password and insert new user
                $hashedPassword = md5($password);
                $insertUser = "INSERT INTO tblUser (username, fullName, email, passwordHash, isVerified) VALUES (?, ?, ?, ?, 0)";
                $insertStmt = $conn->prepare($insertUser);

                if (!$insertStmt) {
                    $error = "Unable to create account right now. Please try again. Technical details: " . $conn->error;
                } else {
                    $insertStmt->bind_param("ssss", $username, $fullName, $email, $hashedPassword);

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
    <title>Register - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth-page">
    <main class="auth-layout auth-layout-register">
        <section class="auth-visual auth-visual-register" aria-label="Fashion showcase">
            <div class="auth-visual-overlay">
                <p class="auth-visual-kicker">Join Pastimes</p>
                <h1>Create Account</h1>
            </div>
        </section>

        <section class="auth-panel" aria-label="Sign-up section">
            <a href="javascript:history.back()" class="back-arrow" aria-label="Go back" title="Go back">&larr;</a>
            <div class="auth-card">
                <a href="../index.php" class="auth-brand" aria-label="Pastimes Home">PASTIMES</a>

                <h2 class="auth-heading">Create account</h2>
                <p class="auth-subtitle">Join Pastimes and start buying or selling pre-loved fashion</p>

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

                <form method="POST" action="<?php echo htmlspecialchars($formAction); ?>" class="auth-form">
                    <?php if ($next !== ''): ?>
                        <input type="hidden" name="next" value="<?php echo htmlspecialchars($next); ?>">
                    <?php endif; ?>
                    <div class="auth-field">
                        <label for="fullName">Full Name</label>
                        <input
                            type="text"
                            id="fullName"
                            name="fullName"
                            value="<?php echo htmlspecialchars($fullName); ?>"
                            placeholder="Your Full Name"
                            required
                        >
                    </div>

                    <div class="auth-field">
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

                    <div class="auth-field">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($email); ?>"
                            placeholder="youremail@domain.com"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Create a password"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="confirmPassword">Confirm Password</label>
                        <input
                            type="password"
                            id="confirmPassword"
                            name="confirmPassword"
                            placeholder="Re-enter password"
                            required
                        >
                    </div>

                    <button type="submit" class="auth-btn auth-btn-primary">Sign up</button>

                    <p class="auth-switch-text">Already have an account? <a href="<?php echo htmlspecialchars($loginLink); ?>" class="auth-link">Login</a></p>
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
