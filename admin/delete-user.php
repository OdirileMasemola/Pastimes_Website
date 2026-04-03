<?php
/**
 * Delete User Page
 * 
 * Admin can delete users from the system
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$userID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userID > 0) {
    $deleteSql = "DELETE FROM tblUser WHERE userID = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("i", $userID);
    
    if ($deleteStmt->execute()) {
        header("Location: manage-users.php?success=1");
        exit();
    } else {
        header("Location: manage-users.php?error=1");
        exit();
    }
    
    $deleteStmt->close();
}

$conn->close();
header("Location: manage-users.php");
exit();
?>
