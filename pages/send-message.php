<?php
/**
 * User messaging endpoint (AJAX).
 *
 * Supports:
 * - New messages to seller/admin (with optional product context)
 * - Replies to inbox messages
 */

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('ok' => false, 'message' => 'Invalid request method.'));
    exit();
}

if (!isset($_SESSION['userID'])) {
    echo json_encode(array(
        'ok' => false,
        'loginRequired' => true,
        'message' => 'Please log in to send a message.'
    ));
    exit();
}

include '../includes/DBConn.php';
require_once '../includes/messageSchema.php';
pastimesEnsureMessageSchema($conn);

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : 'send';
$senderType = 'user';
$senderID = (int) $_SESSION['userID'];

if ($action === 'reply') {
    $replyMessageID = isset($_POST['replyMessageID']) ? intval($_POST['replyMessageID']) : 0;
    $replyText = isset($_POST['messageText']) ? trim($_POST['messageText']) : '';

    if ($replyMessageID <= 0 || $replyText === '') {
        echo json_encode(array('ok' => false, 'message' => 'Reply message cannot be empty.'));
        $conn->close();
        exit();
    }

    $originalSql = "SELECT messageID, senderType, senderID, subject, productID
                    FROM tblMessage
                    WHERE messageID = ?
                      AND receiverType = 'user'
                      AND receiverID = ?
                    LIMIT 1";
    $originalStmt = $conn->prepare($originalSql);
    if (!$originalStmt) {
        echo json_encode(array('ok' => false, 'message' => 'Failed to prepare reply lookup.'));
        $conn->close();
        exit();
    }

    $originalStmt->bind_param("ii", $replyMessageID, $senderID);
    $originalStmt->execute();
    $originalResult = $originalStmt->get_result();
    $original = $originalResult ? $originalResult->fetch_assoc() : null;
    $originalStmt->close();

    if (!$original) {
        echo json_encode(array('ok' => false, 'message' => 'Original message was not found.'));
        $conn->close();
        exit();
    }

    $receiverType = ($original['senderType'] === 'admin') ? 'admin' : 'user';
    $receiverID = (int) $original['senderID'];
    $productID = isset($original['productID']) && intval($original['productID']) > 0 ? intval($original['productID']) : null;
    $subject = 'RE: ' . preg_replace('/^\s*RE:\s*/i', '', (string) $original['subject']);

    if ($receiverType === 'user' && $receiverID === $senderID) {
        echo json_encode(array('ok' => false, 'message' => 'You cannot message yourself.'));
        $conn->close();
        exit();
    }

    if ($productID !== null) {
        $insertStmt = $conn->prepare("INSERT INTO tblMessage
            (senderType, senderID, receiverType, receiverID, productID, subject, messageText, isRead, sentDate)
            VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())");
        if ($insertStmt) {
            $insertStmt->bind_param("sisisss", $senderType, $senderID, $receiverType, $receiverID, $productID, $subject, $replyText);
        }
    } else {
        $insertStmt = $conn->prepare("INSERT INTO tblMessage
            (senderType, senderID, receiverType, receiverID, subject, messageText, isRead, sentDate)
            VALUES (?, ?, ?, ?, ?, ?, 0, NOW())");
        if ($insertStmt) {
            $insertStmt->bind_param("sisiss", $senderType, $senderID, $receiverType, $receiverID, $subject, $replyText);
        }
    }

    $ok = $insertStmt ? $insertStmt->execute() : false;
    if ($insertStmt) {
        $insertStmt->close();
    }
    $conn->close();

    if ($ok) {
        echo json_encode(array('ok' => true, 'message' => 'Reply sent successfully.'));
    } else {
        echo json_encode(array('ok' => false, 'message' => 'Failed to send reply. Please try again.'));
    }
    exit();
}

$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$messageText = isset($_POST['messageText']) ? trim($_POST['messageText']) : '';
$productID = isset($_POST['productID']) && $_POST['productID'] !== '' ? intval($_POST['productID']) : 0;

if ($subject === '' || $messageText === '') {
    echo json_encode(array('ok' => false, 'message' => 'Subject and message are required.'));
    $conn->close();
    exit();
}

$receiverType = 'admin';
$receiverID = 0;
$successMessage = 'Message sent successfully.';
$cleanProductID = null;

if ($productID > 0) {
    $product = null;
    $productStmt = $conn->prepare("SELECT c.clothingID, c.clothingName, c.sellerID, u.fullName AS sellerName
                                   FROM tblClothes c
                                   LEFT JOIN tblUser u ON c.sellerID = u.userID
                                   WHERE c.clothingID = ?
                                   LIMIT 1");
    if ($productStmt) {
        $productStmt->bind_param("i", $productID);
        $productStmt->execute();
        $productResult = $productStmt->get_result();
        $product = $productResult ? $productResult->fetch_assoc() : null;
        $productStmt->close();
    } else {
        // Backward compatibility for databases where tblClothes has no sellerID.
        $fallbackStmt = $conn->prepare("SELECT clothingID, clothingName FROM tblClothes WHERE clothingID = ? LIMIT 1");
        if ($fallbackStmt) {
            $fallbackStmt->bind_param("i", $productID);
            $fallbackStmt->execute();
            $fallbackResult = $fallbackStmt->get_result();
            $product = $fallbackResult ? $fallbackResult->fetch_assoc() : null;
            if ($product) {
                $product['sellerID'] = 0;
            }
            $fallbackStmt->close();
        }
    }

    if (!$product) {
        echo json_encode(array('ok' => false, 'message' => 'The selected product could not be found.'));
        $conn->close();
        exit();
    }

    $cleanProductID = (int) $product['clothingID'];
    $sellerID = isset($product['sellerID']) ? intval($product['sellerID']) : 0;

    if ($sellerID > 0) {
        if ($sellerID === $senderID) {
            echo json_encode(array('ok' => false, 'message' => 'You cannot message yourself about your own listing.'));
            $conn->close();
            exit();
        }
        $receiverType = 'user';
        $receiverID = $sellerID;
        $successMessage = 'Message sent to seller successfully.';
    }
}

if ($receiverType === 'admin') {
    $adminStmt = $conn->prepare("SELECT adminID FROM tblAdmin ORDER BY adminID ASC LIMIT 1");
    if (!$adminStmt) {
        echo json_encode(array('ok' => false, 'message' => 'No admin account is available to receive messages.'));
        $conn->close();
        exit();
    }

    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    if ($adminResult && ($adminRow = $adminResult->fetch_assoc())) {
        $receiverID = (int) $adminRow['adminID'];
    }
    $adminStmt->close();

    if ($receiverID <= 0) {
        echo json_encode(array('ok' => false, 'message' => 'No admin account is available to receive messages.'));
        $conn->close();
        exit();
    }
}

if ($cleanProductID !== null) {
    $insertStmt = $conn->prepare("INSERT INTO tblMessage
        (senderType, senderID, receiverType, receiverID, productID, subject, messageText, isRead, sentDate)
        VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())");
    if ($insertStmt) {
        $insertStmt->bind_param("sisisss", $senderType, $senderID, $receiverType, $receiverID, $cleanProductID, $subject, $messageText);
    }
} else {
    $insertStmt = $conn->prepare("INSERT INTO tblMessage
        (senderType, senderID, receiverType, receiverID, subject, messageText, isRead, sentDate)
        VALUES (?, ?, ?, ?, ?, ?, 0, NOW())");
    if ($insertStmt) {
        $insertStmt->bind_param("sisiss", $senderType, $senderID, $receiverType, $receiverID, $subject, $messageText);
    }
}

$ok = $insertStmt ? $insertStmt->execute() : false;
if ($insertStmt) {
    $insertStmt->close();
}

$conn->close();

if ($ok) {
    echo json_encode(array('ok' => true, 'message' => $successMessage));
} else {
    echo json_encode(array('ok' => false, 'message' => 'Failed to send message. Please try again.'));
}
?>
