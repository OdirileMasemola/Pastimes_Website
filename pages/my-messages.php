<?php
/**
 * My Messages Page
 * 
 * Displays messages for logged-in users
 * Users can read messages from admin and reply
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$message = '';
$messageType = '';

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reply') {
        $originalMessageID = intval($_POST['messageID']);
        $replyText = trim($_POST['replyText']);
        
        if (!empty($replyText)) {
            $senderType = 'user';
            $receiverID = 1;
            $subject = 'Re: ' . (isset($_POST['originalSubject']) ? trim($_POST['originalSubject']) : 'Message');
            
            $stmt = $conn->prepare("INSERT INTO tblMessage (senderType, senderID, receiverID, subject, messageText) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                $message = "Error preparing statement: " . $conn->error;
                $messageType = "error";
            } else {
                $stmt->bind_param("siiss", $senderType, $userID, $receiverID, $subject, $replyText);
                if ($stmt->execute()) {
                    $message = "Reply sent successfully.";
                    $messageType = "success";
                } else {
                    $message = "Error sending reply: " . $stmt->error;
                    $messageType = "error";
                }
                $stmt->close();
            }
        } else {
            $message = "Please enter a message to send.";
            $messageType = "error";
        }
    } elseif ($_POST['action'] === 'mark_read' && isset($_POST['messageID'])) {
        $messageID = intval($_POST['messageID']);
        $stmt = $conn->prepare("UPDATE tblMessage SET isRead = 1 WHERE messageID = ? AND receiverID = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $messageID, $userID);
            $stmt->execute();
            $stmt->close();
        } else {
            // Error preparing statement, but we don't need to show error to user
            error_log("Error preparing mark_read statement: " . $conn->error);
        }
    }
}

// Fetch messages for this user
$sql = "SELECT m.messageID, m.senderType, m.senderID, m.subject, m.messageText, m.sentDate, m.isRead,
               u.username, u.fullName
        FROM tblMessage m
        LEFT JOIN tblUser u ON m.senderID = u.userID
        WHERE m.receiverID = ?
        ORDER BY m.sentDate DESC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$messages = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Messages - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .messages-container {
            max-width: 800px;
            margin: 20px auto;
        }
        
        .message-item {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .message-item:hover {
            background-color: #f0f0f0;
        }
        
        .message-item.unread {
            background-color: #e8f4f8;
            border-left: 4px solid #333;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }
        
        .message-from {
            font-weight: bold;
            color: #333;
        }
        
        .message-date {
            font-size: 0.9rem;
            color: #666;
        }
        
        .message-subject {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin: 5px 0;
        }
        
        .message-preview {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.4;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .message-detail {
            display: none;
            background-color: white;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .message-detail.show {
            display: block;
        }
        
        .message-detail-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .message-detail-text {
            line-height: 1.6;
            color: #333;
            margin-bottom: 15px;
            white-space: pre-wrap;
        }
        
        .reply-form {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        
        .reply-form textarea {
            width: 100%;
            min-height: 100px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            margin-bottom: 10px;
        }
        
        .close-detail {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #666;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .close-detail:hover {
            background-color: #333;
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
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Shopping Cart Icon -->
    <a href="cart.php" class="cart-icon-link" title="Shopping Cart">
        <i class="fa-solid fa-bag-shopping"></i>
        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <span class="cart-badge"><?php echo count($_SESSION['cart']); ?></span>
        <?php endif; ?>
    </a>

    <main>
        <div class="container">
            <div class="messages-container">
                <h2>My Messages</h2>
                
                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (count($messages) > 0): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-item <?php echo $msg['isRead'] ? '' : 'unread'; ?>" onclick="toggleMessage(<?php echo $msg['messageID']; ?>)">
                            <div class="message-header">
                                <div>
                                    <div class="message-from">
                                        <?php echo htmlspecialchars($msg['senderType'] === 'admin' ? 'Admin' : ($msg['fullName'] ?? 'Unknown')); ?>
                                    </div>
                                    <div class="message-subject">
                                        <?php echo htmlspecialchars($msg['subject']); ?>
                                    </div>
                                    <div class="message-preview">
                                        <?php echo htmlspecialchars(substr($msg['messageText'], 0, 80)) . (strlen($msg['messageText']) > 80 ? '...' : ''); ?>
                                    </div>
                                </div>
                                <div class="message-date">
                                    <?php echo date('M d, Y', strtotime($msg['sentDate'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div id="detail-<?php echo $msg['messageID']; ?>" class="message-detail">
                            <div class="message-detail-header">
                                <div class="message-from">
                                    From: <?php echo htmlspecialchars($msg['senderType'] === 'admin' ? 'Admin' : ($msg['fullName'] ?? 'Unknown')); ?>
                                </div>
                                <div class="message-date">
                                    <?php echo date('M d, Y \a\t H:i', strtotime($msg['sentDate'])); ?>
                                </div>
                                <div class="message-subject">
                                    <?php echo htmlspecialchars($msg['subject']); ?>
                                </div>
                            </div>
                            
                            <div class="message-detail-text">
                                <?php echo htmlspecialchars($msg['messageText']); ?>
                            </div>
                            
                            <form class="reply-form" method="POST" action="my-messages.php">
                                <label for="reply-<?php echo $msg['messageID']; ?>">Reply:</label>
                                <textarea id="reply-<?php echo $msg['messageID']; ?>" name="replyText" placeholder="Type your reply here..."></textarea>
                                <input type="hidden" name="action" value="reply">
                                <input type="hidden" name="messageID" value="<?php echo $msg['messageID']; ?>">
                                <input type="hidden" name="originalSubject" value="<?php echo htmlspecialchars($msg['subject']); ?>">
                                <button type="submit" class="btn btn-primary">Send Reply</button>
                                <button type="button" class="close-detail" onclick="toggleMessage(<?php echo $msg['messageID']; ?>)">Close</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>You have no messages.</p>
                <?php endif; ?>
                
                <a href="account.php" class="btn btn-secondary">Back to Account</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
    
    <script>
        function toggleMessage(messageID) {
            const detail = document.getElementById('detail-' + messageID);
            if (detail) {
                detail.classList.toggle('show');
                if (detail.classList.contains('show')) {
                    const form = new FormData();
                    form.append('action', 'mark_read');
                    form.append('messageID', messageID);
                    fetch('my-messages.php', { method: 'POST', body: form });
                }
            }
        }
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navbarToggle = document.getElementById('navbarToggle');
            const navbarLinks = document.getElementById('navbarLinks');
            
            if (navbarToggle && navbarLinks) {
                navbarToggle.addEventListener('click', function() {
                    navbarToggle.classList.toggle('active');
                    navbarLinks.classList.toggle('active');
                });
                
                const links = navbarLinks.querySelectorAll('a');
                links.forEach(link => {
                    link.addEventListener('click', function() {
                        navbarToggle.classList.remove('active');
                        navbarLinks.classList.remove('active');
                    });
                });
            }
        });
    </script>
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
