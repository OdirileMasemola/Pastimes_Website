<?php
/**
 * Admin inbox for user messages and replies.
 */

session_start();
include '../includes/DBConn.php';
require_once '../includes/messageSchema.php';
pastimesEnsureMessageSchema($conn);

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$adminID = (int) $_SESSION['adminID'];
$flashMessage = '';
$flashType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_read') {
    header('Content-Type: application/json');
    $messageID = isset($_POST['messageID']) ? (int) $_POST['messageID'] : 0;
    $ok = false;
    if ($messageID > 0) {
        $markStmt = $conn->prepare("UPDATE tblMessage
                                    SET isRead = 1
                                    WHERE messageID = ?
                                      AND senderType = 'user'
                                      AND receiverType = 'admin'
                                      AND receiverID = ?");
        if ($markStmt) {
            $markStmt->bind_param("ii", $messageID, $adminID);
            $ok = $markStmt->execute();
            $markStmt->close();
        }
    }
    echo json_encode(array('ok' => $ok));
    $conn->close();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    $receiverID = isset($_POST['receiverUserID']) ? (int) $_POST['receiverUserID'] : 0;
    $originalSubject = isset($_POST['originalSubject']) ? trim($_POST['originalSubject']) : 'Message';
    $replyText = isset($_POST['replyText']) ? trim($_POST['replyText']) : '';
    $productID = isset($_POST['productID']) && (int) $_POST['productID'] > 0 ? (int) $_POST['productID'] : null;

    if ($receiverID > 0 && $replyText !== '') {
        $senderType = 'admin';
        $receiverType = 'user';
        $subject = 'RE: ' . preg_replace('/^\s*RE:\s*/i', '', $originalSubject);
        $replyStmt = null;

        if ($productID !== null) {
            $replyStmt = $conn->prepare("INSERT INTO tblMessage
                (senderType, senderID, receiverType, receiverID, productID, subject, messageText, isRead, sentDate)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())");
            if ($replyStmt) {
                $replyStmt->bind_param("sisisss", $senderType, $adminID, $receiverType, $receiverID, $productID, $subject, $replyText);
            }
        } else {
            $replyStmt = $conn->prepare("INSERT INTO tblMessage
                (senderType, senderID, receiverType, receiverID, subject, messageText, isRead, sentDate)
                VALUES (?, ?, ?, ?, ?, ?, 0, NOW())");
            if ($replyStmt) {
                $replyStmt->bind_param("sisiss", $senderType, $adminID, $receiverType, $receiverID, $subject, $replyText);
            }
        }

        if ($replyStmt && $replyStmt->execute()) {
            $flashMessage = 'Message sent successfully.';
            $flashType = 'success';
        } else {
            $flashMessage = 'Error sending reply. Please try again.';
            $flashType = 'error';
        }
        if ($replyStmt) {
            $replyStmt->close();
        }
    } else {
        $flashMessage = 'Please enter a reply message.';
        $flashType = 'error';
    }
}

$inboxMessages = array();
$inboxSql = "SELECT m.messageID, m.senderID, m.subject, m.messageText, m.sentDate, m.isRead, m.productID,
                    u.fullName, u.email, c.clothingName
             FROM tblMessage m
             LEFT JOIN tblUser u ON m.senderID = u.userID
             LEFT JOIN tblClothes c ON m.productID = c.clothingID
             WHERE m.senderType = 'user'
               AND m.receiverType = 'admin'
               AND m.receiverID = ?
             ORDER BY m.sentDate DESC";
$inboxStmt = $conn->prepare($inboxSql);
if ($inboxStmt) {
    $inboxStmt->bind_param("i", $adminID);
    $inboxStmt->execute();
    $inboxResult = $inboxStmt->get_result();
    if ($inboxResult) {
        while ($row = $inboxResult->fetch_assoc()) {
            $inboxMessages[] = $row;
        }
    }
    $inboxStmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Messages - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css?v=6">
</head>
<body class="admin-page">
    <?php include '../includes/navbar.php'; ?>

    <main>
        <div class="admin-container">
            <nav class="admin-breadcrumb">Dashboard / <span>Messages</span></nav>

            <div class="admin-page-head">
                <div>
                    <h1 class="admin-title">User Messages</h1>
                    <p class="admin-subtitle">Review customer messages and send replies directly from the inbox.</p>
                </div>
                <a href="dashboard.php" class="admin-action-btn ghost">Back to Dashboard</a>
            </div>

            <?php if ($flashMessage !== ''): ?>
                <div class="<?php echo $flashType === 'success' ? 'success-message' : 'error-message'; ?>">
                    <p><?php echo htmlspecialchars($flashMessage); ?></p>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <h3 class="admin-card-title">Inbox</h3>
                <?php if (count($inboxMessages) > 0): ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>From</th>
                                    <th>Subject</th>
                                    <th>Product</th>
                                    <th>Preview</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inboxMessages as $msg): ?>
                                    <?php
                                    $fromLabel = ($msg['fullName'] ?: 'Unknown User') . ' (' . ($msg['email'] ?: 'No email') . ')';
                                    $preview = substr($msg['messageText'], 0, 70) . (strlen($msg['messageText']) > 70 ? '...' : '');
                                    ?>
                                    <tr class="<?php echo (int) $msg['isRead'] === 0 ? 'admin-message-row-unread' : ''; ?>">
                                        <td><?php echo htmlspecialchars($fromLabel); ?></td>
                                        <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['clothingName'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($preview); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($msg['sentDate'])); ?></td>
                                        <td>
                                            <span class="admin-badge <?php echo (int) $msg['isRead'] === 0 ? 'is-amber' : 'is-neutral'; ?>">
                                                <?php echo (int) $msg['isRead'] === 0 ? 'Unread' : 'Read'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="admin-action-btn ghost admin-message-open"
                                                data-message-id="<?php echo (int) $msg['messageID']; ?>"
                                                data-receiver-id="<?php echo (int) $msg['senderID']; ?>"
                                                data-from="<?php echo htmlspecialchars($fromLabel, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-subject="<?php echo htmlspecialchars($msg['subject'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-body="<?php echo htmlspecialchars($msg['messageText'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-product-id="<?php echo (int) $msg['productID']; ?>"
                                                data-product="<?php echo htmlspecialchars((string) ($msg['clothingName'] ?: ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                data-sent="<?php echo htmlspecialchars(date('M d, Y \a\t g:i A', strtotime($msg['sentDate'])), ENT_QUOTES, 'UTF-8'); ?>"
                                                data-unread="<?php echo (int) $msg['isRead'] === 0 ? '1' : '0'; ?>">
                                                Open
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="admin-empty">No user messages yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div class="message-modal-overlay" id="adminMessageOverlay" style="display:none;" aria-hidden="true"></div>
    <div class="message-modal-wrap" id="adminMessageWrap" style="display:none;">
        <button type="button" class="message-modal-close" id="adminMessageClose" aria-label="Close message">&times;</button>
        <div class="message-modal" role="dialog" aria-modal="true" aria-labelledby="adminMessageSubject">
            <div class="message-modal-content">
                <div class="message-modal-meta">
                    <span class="message-modal-from" id="adminMessageFrom"></span>
                    <span class="message-modal-sent" id="adminMessageSent"></span>
                </div>
                <h3 class="message-modal-subject" id="adminMessageSubject"></h3>
                <div class="message-modal-product" id="adminMessageProduct" style="display:none;"></div>
                <div class="message-modal-body" id="adminMessageBody"></div>

                <form method="POST" action="messages.php" class="message-compose-form" style="margin-top:1rem;">
                    <input type="hidden" name="action" value="reply">
                    <input type="hidden" name="receiverUserID" id="adminReplyReceiverID" value="">
                    <input type="hidden" name="originalSubject" id="adminReplyOriginalSubject" value="">
                    <input type="hidden" name="productID" id="adminReplyProductID" value="">

                    <div class="message-compose-field">
                        <label for="adminReplyText">Reply</label>
                        <textarea id="adminReplyText" name="replyText" rows="4" required placeholder="Type your reply..."></textarea>
                    </div>
                    <div class="message-compose-actions">
                        <button type="submit" class="message-compose-send">Send Reply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var overlay = document.getElementById('adminMessageOverlay');
        var wrap = document.getElementById('adminMessageWrap');
        var closeBtn = document.getElementById('adminMessageClose');
        var fromEl = document.getElementById('adminMessageFrom');
        var sentEl = document.getElementById('adminMessageSent');
        var subjectEl = document.getElementById('adminMessageSubject');
        var productEl = document.getElementById('adminMessageProduct');
        var bodyEl = document.getElementById('adminMessageBody');
        var replyReceiver = document.getElementById('adminReplyReceiverID');
        var replySubject = document.getElementById('adminReplyOriginalSubject');
        var replyProduct = document.getElementById('adminReplyProductID');
        var buttons = document.querySelectorAll('.admin-message-open');

        function openModal() {
            overlay.style.display = 'block';
            overlay.classList.add('active');
            wrap.style.display = 'flex';
            wrap.classList.add('active');
            overlay.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            overlay.style.display = 'none';
            overlay.classList.remove('active');
            wrap.style.display = 'none';
            wrap.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
        }

        function markRead(messageID) {
            var body = new URLSearchParams();
            body.set('action', 'mark_read');
            body.set('messageID', messageID);
            fetch('messages.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).catch(function () {});
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var messageID = btn.getAttribute('data-message-id');
                var unread = btn.getAttribute('data-unread') === '1';
                var productName = btn.getAttribute('data-product') || '';
                var productID = btn.getAttribute('data-product-id') || '';

                fromEl.textContent = 'From: ' + (btn.getAttribute('data-from') || 'Unknown User');
                sentEl.textContent = btn.getAttribute('data-sent') || '';
                subjectEl.textContent = btn.getAttribute('data-subject') || 'Message';
                bodyEl.textContent = btn.getAttribute('data-body') || '';

                if (productName) {
                    productEl.textContent = 'Product: ' + productName;
                    productEl.style.display = 'block';
                } else {
                    productEl.style.display = 'none';
                }

                replyReceiver.value = btn.getAttribute('data-receiver-id') || '';
                replySubject.value = btn.getAttribute('data-subject') || 'Message';
                replyProduct.value = productID !== '0' ? productID : '';

                openModal();

                if (unread && messageID) {
                    markRead(messageID);
                    btn.setAttribute('data-unread', '0');
                }
            });
        });

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (overlay) overlay.addEventListener('click', closeModal);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeModal();
        });
    })();
    </script>
</body>
</html>
