<?php
/**
 * Admin Logout Script
 * 
 * Destroys admin session and redirects to home
 */

session_start();
session_destroy();
header("Location: ../index.php");
exit();
?>
