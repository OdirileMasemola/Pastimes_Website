<?php
/**
 * Mark Messages Read Endpoint
 *
 * Marks all of the logged-in user's messages as read (isRead = 1).
 * Called by the header message popover via fetch(). Returns simple JSON.
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(array('ok' => false));
    exit();
}

$conn = @new mysqli("localhost", "root", "", "ClothingStore");
if ($conn->connect_error) {
    echo json_encode(array('ok' => false));
    exit();
}
require_once __DIR__ . '/messageSchema.php';
pastimesEnsureMessageSchema($conn);

$userID = $_SESSION['userID'];
$ok = false;
$messageID = 0;

if (isset($_POST['messageID'])) {
    $messageID = intval($_POST['messageID']);
} else {
    $rawInput = file_get_contents('php://input');
    if ($rawInput) {
        parse_str($rawInput, $parsedInput);
        if (isset($parsedInput['messageID'])) {
            $messageID = intval($parsedInput['messageID']);
        }
    }
}

if ($messageID > 0) {
    $stmt = $conn->prepare("UPDATE tblMessage
                            SET isRead = 1
                            WHERE messageID = ?
                              AND receiverType = 'user'
                              AND receiverID = ?
                              AND isRead = 0");
    if ($stmt) {
        $stmt->bind_param("ii", $messageID, $userID);
        $ok = $stmt->execute();
        $stmt->close();
    }
} else {
    $stmt = $conn->prepare("UPDATE tblMessage
                            SET isRead = 1
                            WHERE receiverType = 'user'
                              AND receiverID = ?
                              AND isRead = 0");
    if ($stmt) {
        $stmt->bind_param("i", $userID);
        $ok = $stmt->execute();
        $stmt->close();
    }
}

$unreadCount = 0;
$countStmt = $conn->prepare("SELECT COUNT(*) AS unreadCount
                             FROM tblMessage
                             WHERE receiverType = 'user'
                               AND receiverID = ?
                               AND isRead = 0");
if ($countStmt) {
    $countStmt->bind_param("i", $userID);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    if ($countResult && ($countRow = $countResult->fetch_assoc())) {
        $unreadCount = (int) $countRow['unreadCount'];
    }
    $countStmt->close();
}

$conn->close();

echo json_encode(array('ok' => $ok, 'unreadCount' => $unreadCount));
?>
