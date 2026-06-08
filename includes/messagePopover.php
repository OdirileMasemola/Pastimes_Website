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
                    $sentDisplay = date('M d, Y \a\t g:i A', strtotime($popMsg['sentDate']));
                    ?>
                    <div class="message-pop-item <?php echo $popMsg['isRead'] ? '' : 'unread'; ?>"
                         role="button"
                         tabindex="0"
                         data-message-id="<?php echo intval($popMsg['messageID']); ?>"
                         data-from="<?php echo htmlspecialchars($fromName, ENT_QUOTES, 'UTF-8'); ?>"
                         data-subject="<?php echo htmlspecialchars($popMsg['subject'], ENT_QUOTES, 'UTF-8'); ?>"
                         data-body="<?php echo htmlspecialchars($popMsg['messageText'], ENT_QUOTES, 'UTF-8'); ?>"
                         data-sent="<?php echo htmlspecialchars($sentDisplay, ENT_QUOTES, 'UTF-8'); ?>"
                         data-unread="<?php echo $popMsg['isRead'] ? '0' : '1'; ?>">
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

<div class="message-modal-overlay" id="messageModalOverlay" style="display:none;" aria-hidden="true"></div>
<div class="message-modal-wrap" id="messageModalWrap" style="display:none;">
    <button type="button" class="message-modal-close" id="messageModalClose" aria-label="Close message">&times;</button>
    <div class="message-modal" id="messageModal" role="dialog" aria-modal="true" aria-labelledby="messageModalSubject">
        <div class="message-modal-content">
            <div class="message-modal-meta">
                <span class="message-modal-from" id="messageModalFrom"></span>
                <span class="message-modal-sent" id="messageModalSent"></span>
            </div>
            <h3 class="message-modal-subject" id="messageModalSubject"></h3>
            <div class="message-modal-body" id="messageModalBody"></div>
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
    var modalOverlay = document.getElementById('messageModalOverlay');
    var modalWrap    = document.getElementById('messageModalWrap');
    var modal      = document.getElementById('messageModal');
    var modalClose = document.getElementById('messageModalClose');
    var modalFrom  = document.getElementById('messageModalFrom');
    var modalSubject = document.getElementById('messageModalSubject');
    var modalBody  = document.getElementById('messageModalBody');
    var modalSent  = document.getElementById('messageModalSent');
    var activeMessageItem = null;

    if (!btn || !popover) {
        return;
    }

    function updateUnreadUI(count) {
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }
        }
        if (unreadTxt) {
            if (count > 0) {
                unreadTxt.textContent = count + ' unread';
                unreadTxt.style.display = '';
            } else {
                unreadTxt.style.display = 'none';
            }
        }
        if (markBtn) {
            markBtn.style.display = count > 0 ? '' : 'none';
        }
    }

    function markMessageRead(messageID, itemEl) {
        if (!messageID) {
            return;
        }

        fetch('<?php echo $popEndpoint; ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'messageID=' + encodeURIComponent(messageID)
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (itemEl) {
                    itemEl.classList.remove('unread');
                    itemEl.setAttribute('data-unread', '0');
                    var dot = itemEl.querySelector('.message-dot');
                    if (dot) { dot.parentNode.removeChild(dot); }
                }
                if (data && typeof data.unreadCount !== 'undefined') {
                    updateUnreadUI(data.unreadCount);
                }
            })
            .catch(function () { /* stay silent on error */ });
    }

    function openMessageModal(itemEl) {
        if (!modalWrap || !modalOverlay || !itemEl) {
            return;
        }

        activeMessageItem = itemEl;
        var fromName = itemEl.getAttribute('data-from') || 'Admin User';
        var subject = itemEl.getAttribute('data-subject') || 'Message';
        var bodyText = itemEl.getAttribute('data-body') || '';
        var sentDate = itemEl.getAttribute('data-sent') || '';
        var messageID = itemEl.getAttribute('data-message-id');
        var isUnread = itemEl.getAttribute('data-unread') === '1';

        if (modalFrom) { modalFrom.textContent = 'From: ' + fromName; }
        if (modalSubject) { modalSubject.textContent = subject; }
        if (modalBody) { modalBody.textContent = bodyText; }
        if (modalSent) { modalSent.textContent = sentDate; }

        modalOverlay.style.display = 'block';
        modalOverlay.classList.add('active');
        modalWrap.style.display = 'flex';
        modalWrap.classList.add('active');
        modalOverlay.setAttribute('aria-hidden', 'false');
        closePopover();

        if (isUnread) {
            markMessageRead(messageID, itemEl);
        }
    }

    function closeMessageModal() {
        if (!modalWrap || !modalOverlay) {
            return;
        }

        modalOverlay.style.display = 'none';
        modalOverlay.classList.remove('active');
        modalWrap.style.display = 'none';
        modalWrap.classList.remove('active');
        modalOverlay.setAttribute('aria-hidden', 'true');
        activeMessageItem = null;
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

    // Escape key closes popover or modal
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (modalWrap && modalWrap.classList.contains('active')) {
                closeMessageModal();
            } else {
                closePopover();
            }
        }
    });

    // Open full message modal from popover items
    var popItems = popover.querySelectorAll('.message-pop-item');
    popItems.forEach(function (item) {
        item.addEventListener('click', function () {
            openMessageModal(item);
        });
        item.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openMessageModal(item);
            }
        });
    });

    if (modalClose) {
        modalClose.addEventListener('click', function () {
            closeMessageModal();
        });
    }

    if (modalOverlay) {
        modalOverlay.addEventListener('click', function () {
            closeMessageModal();
        });
    }

    if (modalWrap) {
        modalWrap.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    // Mark all as read
    if (markBtn) {
        markBtn.addEventListener('click', function () {
            fetch('<?php echo $popEndpoint; ?>', { method: 'POST' })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data && typeof data.unreadCount !== 'undefined') {
                        updateUnreadUI(data.unreadCount);
                    } else {
                        updateUnreadUI(0);
                    }
                    var items = popover.querySelectorAll('.message-pop-item.unread');
                    items.forEach(function (item) {
                        item.classList.remove('unread');
                        item.setAttribute('data-unread', '0');
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
