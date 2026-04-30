<?php
/**
 * Logout Script
 * 
 * Destroys session and redirects to home
 */

session_start();
session_destroy();
header("Location: ../index.php");
exit();
?>
<?php
/*
This code is the original work of:
ST10441421 - Odirile Masemola
ST10450294 - Ripfumelo Mabasa
All rights reserved.
*/
?>
