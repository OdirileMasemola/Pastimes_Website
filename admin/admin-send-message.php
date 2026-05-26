<?php
/**
 * Admin Send Message Page
 * 
 * Admin can send messages to users
 */

session_start();
include '../includes/DBConn.php';

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
        
        $stmt = $conn->prepare("INSERT INTO tblMessage (senderType, senderID, receiverID, subject, messageText) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("siiss", $senderType, $senderID, $receiverID, $subject, $messageText);
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
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .message-form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
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
                    <li><a href="admin-logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <div class="message-form-container">
                <h2>Send Message to User</h2>
                
                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="admin-send-message.php">
                    <div class="form-group">
                        <label for="userID">Select User:</label>
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
                        <label for="subject">Subject:</label>
                        <input type="text" id="subject" name="subject" required placeholder="Message subject">
                    </div>
                    
                    <div class="form-group">
                        <label for="messageText">Message:</label>
                        <textarea id="messageText" name="messageText" required placeholder="Type your message here..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Send Message</button>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
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
