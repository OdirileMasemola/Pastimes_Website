<?php
/**
 * Add User Page
 * 
 * Admin can add new users to the system
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$fullName = '';
$username = '';
$email = '';
$password = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = isset($_POST['fullName']) ? $_POST['fullName'] : '';
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $checkSql = "SELECT userID FROM tblUser WHERE email = ? OR username = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);

        if (!$checkStmt) {
            $error = "Database error: " . $conn->error;
        } else {
            $checkStmt->bind_param("ss", $email, $username);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $error = "Username or email already exists.";
            } else {
                $hashedPassword = md5($password);
                $sql = "INSERT INTO tblUser (username, fullName, email, passwordHash, isVerified) VALUES (?, ?, ?, ?, 1)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $username, $fullName, $email, $hashedPassword);
                
                if ($stmt->execute()) {
                    $success = "User added successfully!";
                    $fullName = '';
                    $username = '';
                    $email = '';
                } else {
                    $error = "Error adding user: " . $conn->error;
                }
                
                $stmt->close();
            }

            $checkStmt->close();
        }
    }
}

$conn->close();
?>
<?php
/*
This code is the original work of:
ST10441421 - Odirile Masemola
ST10450294 - Ripfumelo Mabasa
All rights reserved.
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1>Pastimes - Admin Panel</h1>
                </div>
                <ul class="nav-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage-users.php">Manage Users</a></li>
                    <li><a href="admin-logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h2>Add New User</h2>
            
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
            
                <form method="POST" action="add-user.php">
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
                        <label for="username">Username:</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="<?php echo htmlspecialchars($username); ?>" 
                            required
                        >
                    </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
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
                    <button type="submit" class="btn btn-primary">Add User</button>
                    <a href="manage-users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
