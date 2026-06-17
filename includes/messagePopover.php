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
    require_once __DIR__ . '/messageSchema.php';

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
        pastimesEnsureMessageSchema($popConn);

        // Seller/buyer/admin inbox query:
        // receiverType='user' and receiverID=current user includes:
        // - admin -> user
        // - buyer -> seller
        // - seller -> buyer
        $popSql = "SELECT m.messageID, m.senderType, m.senderID, m.subject, m.messageText,
                          m.sentDate, m.isRead, m.productID,
                          a.adminName, u.fullName AS userFullName, u.username AS userUsername,
                          c.clothingName AS productName
                   FROM tblMessage m
                   LEFT JOIN tblAdmin a ON (m.senderType = 'admin' AND m.senderID = a.adminID)
                   LEFT JOIN tblUser u ON (m.senderType = 'user' AND m.senderID = u.userID)
                   LEFT JOIN tblClothes c ON m.productID = c.clothingID
                   WHERE m.receiverType = 'user'
                     AND m.receiverID = ?
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
        if ($popCountStmt = $popConn->prepare("SELECT COUNT(*) AS unreadCount
                                               FROM tblMessage
                                               WHERE receiverType = 'user'
                                                 AND receiverID = ?
                                                 AND isRead = 0")) {
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
    $popSendEndpoint = $popPrefix . 'pages/send-message.php';
    $popLoginPath = $popPrefix . 'pages/login.php';
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
            <div class="message-popover-actions">
                <button type="button" class="message-compose-trigger" id="messageComposeBtn">Message Admin</button>
                <button type="button" class="message-mark-read" id="markAllReadBtn" style="<?php echo $popUnread > 0 ? '' : 'display:none;'; ?>">Mark all as read</button>
            </div>
        </div>

        <div class="message-popover-list">
            <?php if (count($popMessages) > 0): ?>
                <?php foreach ($popMessages as $popMsg): ?>
                    <?php
                    if ($popMsg['senderType'] === 'admin') {
                        $fromName = 'Admin User';
                    } else {
                        if (!empty($popMsg['userFullName'])) {
                            $fromName = $popMsg['userFullName'];
                        } elseif (!empty($popMsg['userUsername'])) {
                            $fromName = $popMsg['userUsername'];
                        } else {
                            $fromName = 'User #' . intval($popMsg['senderID']);
                        }
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
                         data-sender-type="<?php echo htmlspecialchars($popMsg['senderType'], ENT_QUOTES, 'UTF-8'); ?>"
                         data-sender-id="<?php echo intval($popMsg['senderID']); ?>"
                         data-from="<?php echo htmlspecialchars($fromName, ENT_QUOTES, 'UTF-8'); ?>"
                         data-subject="<?php echo htmlspecialchars($popMsg['subject'], ENT_QUOTES, 'UTF-8'); ?>"
                         data-body="<?php echo htmlspecialchars($popMsg['messageText'], ENT_QUOTES, 'UTF-8'); ?>"
                         data-product-id="<?php echo intval(isset($popMsg['productID']) ? $popMsg['productID'] : 0); ?>"
                         data-product="<?php echo htmlspecialchars((string) ($popMsg['productName'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                         data-sent="<?php echo htmlspecialchars($sentDisplay, ENT_QUOTES, 'UTF-8'); ?>"
                         data-unread="<?php echo $popMsg['isRead'] ? '0' : '1'; ?>">
                        <?php if (!$popMsg['isRead']): ?>
                            <span class="message-dot" title="Unread"></span>
                        <?php endif; ?>
                        <div class="message-pop-from">From: <?php echo htmlspecialchars($fromName); ?></div>
                        <div class="message-pop-subject"><?php echo htmlspecialchars($popMsg['subject']); ?></div>
                        <?php if (!empty($popMsg['productName'])): ?>
                            <div class="message-pop-product">Product: <?php echo htmlspecialchars($popMsg['productName']); ?></div>
                        <?php endif; ?>
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
            <div class="message-modal-product" id="messageModalProduct" style="display:none;"></div>
            <div class="message-modal-body" id="messageModalBody"></div>
            <form class="message-modal-reply" id="messageModalReplyForm">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="replyMessageID" id="messageModalReplyID" value="">
                <div class="message-compose-field">
                    <label for="messageModalReplyText">Reply</label>
                    <textarea id="messageModalReplyText" name="messageText" rows="4" placeholder="Type your reply..." required></textarea>
                </div>
                <div class="message-compose-actions">
                    <button type="submit" class="message-compose-send">Send Reply</button>
                </div>
                <p class="message-compose-status" id="messageModalReplyStatus" aria-live="polite"></p>
            </form>
        </div>
    </div>
</div>

<div class="message-modal-overlay" id="messageComposeOverlay" style="display:none;" aria-hidden="true"></div>
<div class="message-modal-wrap" id="messageComposeWrap" style="display:none;">
    <button type="button" class="message-modal-close" id="messageComposeClose" aria-label="Close message form">&times;</button>
    <div class="message-modal message-compose-modal" role="dialog" aria-modal="true" aria-labelledby="messageComposeTitle">
        <div class="message-modal-content">
            <h3 class="message-modal-subject" id="messageComposeTitle">Message Admin</h3>
            <div class="message-compose-meta" id="messageComposeMeta" style="display:none;"></div>
            <form id="messageComposeForm">
                <input type="hidden" id="messageComposeProductId" name="productID" value="">
                <div class="message-compose-field">
                    <label for="messageComposeSubject">Subject</label>
                    <input type="text" id="messageComposeSubject" name="subject" maxlength="200" required>
                </div>
                <div class="message-compose-field">
                    <label for="messageComposeText">Message</label>
                    <textarea id="messageComposeText" name="messageText" rows="5" required placeholder="Type your message to the admin..."></textarea>
                </div>
                <div class="message-compose-actions">
                    <button type="submit" class="message-compose-send">Send Message</button>
                </div>
                <p class="message-compose-status" id="messageComposeStatus" aria-live="polite"></p>
            </form>
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
    var modalProduct = document.getElementById('messageModalProduct');
    var modalReplyForm = document.getElementById('messageModalReplyForm');
    var modalReplyID = document.getElementById('messageModalReplyID');
    var modalReplyText = document.getElementById('messageModalReplyText');
    var modalReplyStatus = document.getElementById('messageModalReplyStatus');
    var activeMessageItem = null;
    var composeBtn = document.getElementById('messageComposeBtn');
    var composeOverlay = document.getElementById('messageComposeOverlay');
    var composeWrap = document.getElementById('messageComposeWrap');
    var composeClose = document.getElementById('messageComposeClose');
    var composeForm = document.getElementById('messageComposeForm');
    var composeSubject = document.getElementById('messageComposeSubject');
    var composeText = document.getElementById('messageComposeText');
    var composeProductId = document.getElementById('messageComposeProductId');
    var composeStatus = document.getElementById('messageComposeStatus');
    var composeMeta = document.getElementById('messageComposeMeta');

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
        var fromName = itemEl.getAttribute('data-from') || 'Sender';
        var subject = itemEl.getAttribute('data-subject') || 'Message';
        var bodyText = itemEl.getAttribute('data-body') || '';
        var productName = itemEl.getAttribute('data-product') || '';
        var sentDate = itemEl.getAttribute('data-sent') || '';
        var messageID = itemEl.getAttribute('data-message-id');
        var isUnread = itemEl.getAttribute('data-unread') === '1';

        if (modalFrom) { modalFrom.textContent = 'From: ' + fromName; }
        if (modalSubject) { modalSubject.textContent = subject; }
        if (modalProduct) {
            if (productName) {
                modalProduct.textContent = 'Product: ' + productName;
                modalProduct.style.display = 'block';
            } else {
                modalProduct.style.display = 'none';
            }
        }
        if (modalBody) { modalBody.textContent = bodyText; }
        if (modalSent) { modalSent.textContent = sentDate; }
        if (modalReplyID) { modalReplyID.value = messageID || ''; }
        if (modalReplyText) { modalReplyText.value = ''; }
        if (modalReplyStatus) {
            modalReplyStatus.textContent = '';
            modalReplyStatus.classList.remove('success', 'error');
        }

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

    function openComposeModal(options) {
        if (!composeWrap || !composeOverlay) {
            return;
        }

        var opts = options || {};
        var productName = opts.productName || '';
        var productPrice = opts.productPrice || '';
        var productID = opts.productID || '';
        var defaultSubject = opts.subject || 'Message for admin';

        if (composeSubject) {
            composeSubject.value = defaultSubject;
        }
        if (composeText) {
            composeText.value = '';
        }
        if (composeProductId) {
            composeProductId.value = productID ? String(productID) : '';
        }
        if (composeMeta) {
            if (productName) {
                composeMeta.style.display = 'block';
                composeMeta.textContent = productPrice
                    ? ('Product: ' + productName + ' (' + productPrice + ')')
                    : ('Product: ' + productName);
            } else {
                composeMeta.style.display = 'none';
                composeMeta.textContent = '';
            }
        }
        if (composeStatus) {
            composeStatus.textContent = '';
            composeStatus.classList.remove('success', 'error');
        }

        composeOverlay.style.display = 'block';
        composeOverlay.classList.add('active');
        composeWrap.style.display = 'flex';
        composeWrap.classList.add('active');
        composeOverlay.setAttribute('aria-hidden', 'false');
        closePopover();
        if (composeText) {
            composeText.focus();
        }
    }

    function closeComposeModal() {
        if (!composeWrap || !composeOverlay) {
            return;
        }
        composeOverlay.style.display = 'none';
        composeOverlay.classList.remove('active');
        composeWrap.style.display = 'none';
        composeWrap.classList.remove('active');
        composeOverlay.setAttribute('aria-hidden', 'true');
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
            } else if (composeWrap && composeWrap.classList.contains('active')) {
                closeComposeModal();
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

    if (composeBtn) {
        composeBtn.addEventListener('click', function () {
            openComposeModal({ subject: 'Message for admin' });
        });
    }

    if (composeClose) {
        composeClose.addEventListener('click', closeComposeModal);
    }
    if (composeOverlay) {
        composeOverlay.addEventListener('click', closeComposeModal);
    }

    if (composeForm) {
        composeForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var formData = new FormData(composeForm);
            fetch('<?php echo $popSendEndpoint; ?>', {
                method: 'POST',
                body: formData
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (!composeStatus) {
                        return;
                    }

                    if (data && data.ok) {
                        composeStatus.textContent = data.message || 'Message sent successfully.';
                        composeStatus.classList.remove('error');
                        composeStatus.classList.add('success');
                        composeForm.reset();
                        if (composeProductId) {
                            composeProductId.value = '';
                        }
                        setTimeout(function () {
                            closeComposeModal();
                        }, 700);
                    } else if (data && data.loginRequired) {
                        composeStatus.innerHTML = 'Please log in to send a message. <a href="<?php echo $popLoginPath; ?>">Log in</a>';
                        composeStatus.classList.remove('success');
                        composeStatus.classList.add('error');
                    } else {
                        composeStatus.textContent = (data && data.message) ? data.message : 'Could not send message.';
                        composeStatus.classList.remove('success');
                        composeStatus.classList.add('error');
                    }
                })
                .catch(function () {
                    if (!composeStatus) {
                        return;
                    }
                    composeStatus.textContent = 'Could not send message right now. Please try again.';
                    composeStatus.classList.remove('success');
                    composeStatus.classList.add('error');
                });
        });
    }

    if (modalReplyForm) {
        modalReplyForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var replyIdVal = modalReplyID ? modalReplyID.value : '';
            if (!replyIdVal) {
                return;
            }

            var formData = new FormData(modalReplyForm);
            fetch('<?php echo $popSendEndpoint; ?>', {
                method: 'POST',
                body: formData
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (!modalReplyStatus) {
                        return;
                    }
                    if (data && data.ok) {
                        modalReplyStatus.textContent = data.message || 'Reply sent successfully.';
                        modalReplyStatus.classList.remove('error');
                        modalReplyStatus.classList.add('success');
                        if (modalReplyText) {
                            modalReplyText.value = '';
                        }
                    } else {
                        modalReplyStatus.textContent = (data && data.message) ? data.message : 'Could not send reply.';
                        modalReplyStatus.classList.remove('success');
                        modalReplyStatus.classList.add('error');
                    }
                })
                .catch(function () {
                    if (!modalReplyStatus) {
                        return;
                    }
                    modalReplyStatus.textContent = 'Could not send reply right now. Please try again.';
                    modalReplyStatus.classList.remove('success');
                    modalReplyStatus.classList.add('error');
                });
        });
    }

    // Allows other pages (e.g. shop quick-view) to open the same compose modal.
    window.openMessageCompose = function (options) {
        openComposeModal(options || {});
    };
})();
</script>
<?php endif; ?>
