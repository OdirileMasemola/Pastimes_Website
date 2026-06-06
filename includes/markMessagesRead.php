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

$userID = $_SESSION['userID'];
$ok = false;

$stmt = $conn->prepare("UPDATE tblMessage SET isRead = 1 WHERE receiverID = ? AND isRead = 0");
if ($stmt) {
    $stmt->bind_param("i", $userID);
    $ok = $stmt->execute();
    $stmt->close();
}

$conn->close();

echo json_encode(array('ok' => $ok));
?>
