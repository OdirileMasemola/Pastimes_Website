<?php
/**
 * Admin Send Message Page
 * 
 * Admin can send messages to users
 */

session_start();
include '../includes/DBConn.php';
require_once '../includes/messageSchema.php';
pastimesEnsureMessageSchema($conn);

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$message = '';
$messageType = '';
$users = array();

// Fetch all users
$userSql = "SELECT userID, username, fullName, email FROM tblUser ORDER BY fullName ASC";
$userResult = $conn->query($userSql);
if ($userResult && $userResult->num_rows > 0) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['userID'])) {
    $receiverID = intval($_POST['userID']);
    $subject = trim($_POST['subject']);
    $messageText = trim($_POST['messageText']);
    
    if ($receiverID > 0 && !empty($subject) && !empty($messageText)) {
        $senderType = 'admin';
        $senderID = $_SESSION['adminID'];
        $receiverType = 'user';
        
        $stmt = $conn->prepare("INSERT INTO tblMessage (senderType, senderID, receiverType, receiverID, subject, messageText, isRead, sentDate) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())");
        if ($stmt) {
            $stmt->bind_param("sisiss", $senderType, $senderID, $receiverType, $receiverID, $subject, $messageText);
            if ($stmt->execute()) {
                $message = "Message sent successfully.";
                $messageType = "success";
            } else {
                $message = "Error sending message: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        }
    } else {
        $message = "Please fill in all fields.";
        $messageType = "error";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css?v=5">
</head>
<body class="admin-page">
    <header>
        <?php include '../includes/navbar.php'; ?>
    </header>

    <main>
        <div class="admin-container">
            <nav class="admin-breadcrumb">Dashboard / <span>Send Message</span></nav>

            <div class="admin-page-head">
                <div>
                    <h1 class="admin-title">Send Message to User</h1>
                    <p class="admin-subtitle">Send a direct message to a customer. They will see it in their messages popover.</p>
                </div>
                <a href="dashboard.php" class="admin-action-btn ghost">Back to Dashboard</a>
            </div>

            <?php if (!empty($message)): ?>
                <div class="<?php echo $messageType === 'success' ? 'success-message' : 'error-message'; ?>">
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="admin-send-message.php" class="admin-form-card">
                <div class="form-group">
                    <label for="userID">Select User</label>
                    <select id="userID" name="userID" required>
                        <option value="">-- Choose a user --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['userID']; ?>">
                                <?php echo htmlspecialchars($user['fullName'] . ' (' . $user['email'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required placeholder="Message subject">
                </div>

                <div class="form-group">
                    <label for="messageText">Message</label>
                    <textarea id="messageText" name="messageText" required placeholder="Type your message here..."></textarea>
                </div>

                <div class="admin-row-actions">
                    <button type="submit" class="admin-action-btn primary">Send Message</button>
                    <a href="dashboard.php" class="admin-action-btn ghost">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer Section -->
    <footer class="footer">
        <!-- Top Card Area -->
        <div class="footer-top-card"></div>

        <!-- Footer Content -->
        <div class="footer-content">
            <!-- Brand Section -->
            <div class="footer-brand">
                <h2 class="footer-brand-title">Pastimes</h2>
                <p class="footer-copyright">&copy; 2026 Pastimes. All rights reserved.</p>
            </div>

            <!-- Footer Grid (4 Columns) -->
            <div class="footer-grid">
                <!-- Product Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">Product</h4>
                    <ul class="footer-links">
                        <li><a href="pages/shop.php" class="footer-link">Shop</a></li>
                        <li><a href="pages/sell-item.php" class="footer-link">Sell Item</a></li>
                        <li><a href="pages/cart.php" class="footer-link">Cart</a></li>
                    </ul>
                </div>

                <!-- Company Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">Company</h4>
                    <ul class="footer-links">
                        <li><a href="index.php" class="footer-link">About</a></li>
                        <li><a href="pages/account.php" class="footer-link">Account</a></li>
                        <li><a href="pages/login.php" class="footer-link">Login</a></li>
                        <li><a href="pages/register.php" class="footer-link">Register</a></li>
                    </ul>
                </div>

                <!-- Resources Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">Resources</h4>
                    <ul class="footer-links">
                        <li><a href="index.php" class="footer-link">Help</a></li>
                        <li><a href="pages/my-messages.php" class="footer-link">Messages</a></li>
                        <li><a href="pages/my-listings.php" class="footer-link">Seller Listings</a></li>
                        <li><a href="admin/admin-login.php" class="footer-link">Admin</a></li>
                    </ul>
                </div>

                <!-- Social Links Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">Social</h4>
                    <ul class="footer-links">
                        <li><a href="#" class="footer-link">Facebook</a></li>
                        <li><a href="#" class="footer-link">Instagram</a></li>
                        <li><a href="#" class="footer-link">YouTube</a></li>
                        <li><a href="#" class="footer-link">LinkedIn</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
<?php
/*
This code is the original work of:
ST10441421 	- Odirile Masemola
ST10450294 	- Ripfumelo Mabasa
*/
?>
