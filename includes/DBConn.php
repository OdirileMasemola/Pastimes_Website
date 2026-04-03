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

// Create connection using improved MySQLi
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

?>
