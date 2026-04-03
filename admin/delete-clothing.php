<?php
/**
 * Delete Clothing Page
 * 
 * Admin can delete clothing items from the inventory
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$clothingID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($clothingID > 0) {
    $deleteSql = "DELETE FROM tblClothes WHERE clothingID = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("i", $clothingID);
    
    if ($deleteStmt->execute()) {
        header("Location: manage-clothes.php?success=1");
        exit();
    } else {
        header("Location: manage-clothes.php?error=1");
        exit();
    }
    
    $deleteStmt->close();
}

$conn->close();
header("Location: manage-clothes.php");
exit();
?>
