<?php
/**
 * Message Popover Include
 *
 * Renders the header message icon plus a notification-style popover that lists
 * the logged-in user's most recent messages. Self-contained: it opens its own
 * short-lived database connection so it never depends on a page's $conn
 * (which may already be closed by the time this is included).
 *
 * Only shown to logged-in users.
 */

if (isset($_SESSION['userID'])):

    // Small helper for "x ago" style timestamps (guarded against double include)
    if (!function_exists('pastimesTimeAgo')) {
        function pastimesTimeAgo($dateString)
        {
            $timestamp = strtotime($dateString);
            if ($timestamp === false) {
                return '';
            }

            $diff = time() - $timestamp;
            if ($diff < 60) {
                return 'Just now';
            }
            if ($diff < 3600) {
                $mins = (int) floor($diff / 60);
                return $mins . ' minute' . ($mins === 1 ? '' : 's') . ' ago';
            }
            if ($diff < 86400) {
                $hours = (int) floor($diff / 3600);
                return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';
            }
            if ($diff < 604800) {
                $days = (int) floor($diff / 86400);
                return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
            }
            return date('M d, Y', $timestamp);
        }
    }

    $popUserID = $_SESSION['userID'];
    $popMessages = array();
    $popUnread = 0;

    $popConn = @new mysqli("localhost", "root", "", "ClothingStore");
    if (!$popConn->connect_error) {
        // Latest messages, joined with tblAdmin so we can show the admin name
        $popSql = "SELECT m.messageID, m.senderType, m.senderID, m.subject, m.messageText,
                          m.sentDate, m.isRead, a.adminName
                   FROM tblMessage m
                   LEFT JOIN tblAdmin a ON (m.senderType = 'admin' AND m.senderID = a.adminID)
                   WHERE m.receiverID = ?
                   ORDER BY m.sentDate DESC
                   LIMIT 8";
        if ($popStmt = $popConn->prepare($popSql)) {
            $popStmt->bind_param("i", $popUserID);
            $popStmt->execute();
            $popResult = $popStmt->get_result();
            if ($popResult) {
                while ($popRow = $popResult->fetch_assoc()) {
                    $popMessages[] = $popRow;
                }
            }
            $popStmt->close();
        }

        // Unread count for the badge
        if ($popCountStmt = $popConn->prepare("SELECT COUNT(*) AS unreadCount FROM tblMessage WHERE receiverID = ? AND isRead = 0")) {
            $popCountStmt->bind_param("i", $popUserID);
            $popCountStmt->execute();
            $popCountResult = $popCountStmt->get_result();
            if ($popCountResult && ($popCountRow = $popCountResult->fetch_assoc())) {
                $popUnread = (int) $popCountRow['unreadCount'];
            }
            $popCountStmt->close();
        }

        $popConn->close();
    }

    // Work out a relative URL to the mark-as-read endpoint for the current page
    $popSelf = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
    $popPrefix = (strpos($popSelf, '/pages/') !== false || strpos($popSelf, '/admin/') !== false) ? '../' : '';
    $popEndpoint = $popPrefix . 'includes/markMessagesRead.php';
?>
<div class="message-icon-wrap">
    <button type="button" class="message-icon-link" id="messageIconBtn" title="Messages" aria-label="Messages">
        <i class="fas fa-envelope"></i>
        <span class="message-badge" id="messageBadge" style="<?php echo $popUnread > 0 ? '' : 'display:none;'; ?>"><?php echo $popUnread; ?></span>
    </button>

    <div class="message-popover" id="messagePopover" role="dialog" aria-label="Messages" style="display:none;">
        <div class="message-popover-header">
            <div class="message-popover-heading">
                <span class="message-popover-title">Messages</span>
                <span class="message-popover-unread" id="messagePopoverUnread" style="<?php echo $popUnread > 0 ? '' : 'display:none;'; ?>"><?php echo $popUnread; ?> unread</span>
            </div>
            <button type="button" class="message-mark-read" id="markAllReadBtn" style="<?php echo $popUnread > 0 ? '' : 'display:none;'; ?>">Mark all as read</button>
        </div>

        <div class="message-popover-list">
            <?php if (count($popMessages) > 0): ?>
                <?php foreach ($popMessages as $popMsg): ?>
                    <?php
                    if ($popMsg['senderType'] === 'admin') {
                        $fromName = !empty($popMsg['adminName']) ? $popMsg['adminName'] : 'Admin User';
                    } else {
                        $fromName = 'You';
                    }
                    $previewText = substr($popMsg['messageText'], 0, 70);
                    if (strlen($popMsg['messageText']) > 70) {
                        $previewText .= '...';
                    }
                    ?>
                    <div class="message-pop-item <?php echo $popMsg['isRead'] ? '' : 'unread'; ?>">
                        <?php if (!$popMsg['isRead']): ?>
                            <span class="message-dot" title="Unread"></span>
                        <?php endif; ?>
                        <div class="message-pop-from">From: <?php echo htmlspecialchars($fromName); ?></div>
                        <div class="message-pop-subject"><?php echo htmlspecialchars($popMsg['subject']); ?></div>
                        <div class="message-pop-preview"><?php echo htmlspecialchars($previewText); ?></div>
                        <div class="message-pop-time"><?php echo htmlspecialchars(pastimesTimeAgo($popMsg['sentDate'])); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="message-pop-empty">No messages yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function () {
    var btn      = document.getElementById('messageIconBtn');
    var popover  = document.getElementById('messagePopover');
    var markBtn  = document.getElementById('markAllReadBtn');
    var badge    = document.getElementById('messageBadge');
    var unreadTxt = document.getElementById('messagePopoverUnread');

    if (!btn || !popover) {
        return;
    }

    // Show/hide using an inline style so the popover is always hidden by
    // default, even if the (cached) stylesheet hasn't loaded the rules yet.
    function openPopover() {
        popover.style.display = 'flex';
        popover.classList.add('open');
    }
    function closePopover() {
        popover.style.display = 'none';
        popover.classList.remove('open');
    }

    // Toggle the popover when clicking the icon
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (popover.style.display === 'flex') {
            closePopover();
        } else {
            openPopover();
        }
    });

    // Keep clicks inside the popover from closing it
    popover.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    // Click anywhere else closes the popover
    document.addEventListener('click', function () {
        closePopover();
    });

    // Escape key closes it too
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closePopover();
        }
    });

    // Mark all as read
    if (markBtn) {
        markBtn.addEventListener('click', function () {
            fetch('<?php echo $popEndpoint; ?>', { method: 'POST' })
                .then(function () {
                    if (badge) { badge.style.display = 'none'; }
                    if (unreadTxt) { unreadTxt.style.display = 'none'; }
                    markBtn.style.display = 'none';
                    var items = popover.querySelectorAll('.message-pop-item.unread');
                    items.forEach(function (item) {
                        item.classList.remove('unread');
                        var dot = item.querySelector('.message-dot');
                        if (dot) { dot.parentNode.removeChild(dot); }
                    });
                })
                .catch(function () { /* stay silent on error */ });
        });
    }
})();
</script>
<?php endif; ?>
