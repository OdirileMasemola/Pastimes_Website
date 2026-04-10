<?php
/**
 * Database Connection File
 * 
 * This file handles the connection to the ClothingStore database
 * using MySQLi
 */

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "ClothingStore";

// Connect without selecting a database first so we can create it if needed.
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!$conn->query("CREATE DATABASE IF NOT EXISTS `" . $database . "`") ) {
    die("Failed to create database '" . $database . "': " . $conn->error);
}

if (!$conn->select_db($database)) {
    die("Failed to select database '" . $database . "': " . $conn->error);
}

// Set charset to utf8
$conn->set_charset("utf8");

?>
