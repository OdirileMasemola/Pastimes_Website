<?php
/**
 * Load ClothingStore Database Script
 * 
 * This script will:
 * - Create all tables in the ClothingStore database
 * - Drop existing tables if they exist
 * - Create tables only if they don't exist
 * - Load initial data
 */

// Include database connection
include 'DBConn.php';

// Array of tables to create
$tables = array();

// tblUser table
$tables['tblUser'] = "CREATE TABLE IF NOT EXISTS tblUser (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    fullName VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    city VARCHAR(50),
    zipCode VARCHAR(10),
    phone VARCHAR(20),
    isVerified BOOLEAN DEFAULT FALSE,
    createdDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// tblAdmin table
$tables['tblAdmin'] = "CREATE TABLE IF NOT EXISTS tblAdmin (
    adminID INT AUTO_INCREMENT PRIMARY KEY,
    adminName VARCHAR(100) NOT NULL,
    adminEmail VARCHAR(100) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    createdDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// tblClothes table
$tables['tblClothes'] = "CREATE TABLE IF NOT EXISTS tblClothes (
    clothingID INT AUTO_INCREMENT PRIMARY KEY,
    clothingName VARCHAR(150) NOT NULL,
    category VARCHAR(50),
    description TEXT,
    price DECIMAL(8, 2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    imageURL VARCHAR(255),
    createdDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// tblOrder table
$tables['tblOrder'] = "CREATE TABLE IF NOT EXISTS tblOrder (
    orderID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    orderDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    totalAmount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    FOREIGN KEY (userID) REFERENCES tblUser(userID) ON DELETE CASCADE
)";

// Create all tables
foreach ($tables as $tableName => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table '$tableName' created or already exists.<br>";
    } else {
        echo "Error creating table '$tableName': " . $conn->error . "<br>";
    }
}

echo "<br>All tables have been processed successfully!<br>";

$conn->close();
?>
