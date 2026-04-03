<?php
/**
 * Create Table Script
 * 
 * This script will:
 * - Check if tblUser exists
 * - Delete tblUser if it exists
 * - Recreate tblUser
 * - Load data from userData.txt file
 */

// Include database connection
include 'DBConn.php';

// Check if tblUser exists and drop it if it does
$checkTable = "SHOW TABLES LIKE 'tblUser'";
$result = $conn->query($checkTable);

if ($result && $result->num_rows > 0) {
    // Table exists, so drop it
    $dropTable = "DROP TABLE tblUser";
    if ($conn->query($dropTable) === TRUE) {
        echo "Existing tblUser table dropped.<br>";
    } else {
        echo "Error dropping table: " . $conn->error . "<br>";
    }
}

// Create tblUser table
$createTable = "CREATE TABLE tblUser (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    fullName VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    isVerified BOOLEAN DEFAULT FALSE,
    createdDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($createTable) === TRUE) {
    echo "tblUser table created successfully.<br>";
    
    // Load data from userData.txt file
    $dataFile = '../data/userData.txt';
    if (file_exists($dataFile)) {
        $file = fopen($dataFile, 'r');
        $count = 0;
        
        while (!feof($file)) {
            $line = trim(fgets($file));
            if (!empty($line)) {
                // Parse the line: name | email | passwordHash
                $parts = explode('|', $line);
                if (count($parts) >= 3) {
                    $name = $conn->real_escape_string(trim($parts[0]));
                    $email = $conn->real_escape_string(trim($parts[1]));
                    $hash = $conn->real_escape_string(trim($parts[2]));
                    
                    $insertQuery = "INSERT INTO tblUser (fullName, email, passwordHash, isVerified) 
                                   VALUES ('$name', '$email', '$hash', 1)";
                    
                    if ($conn->query($insertQuery) === TRUE) {
                        $count++;
                    } else {
                        echo "Error inserting record: " . $conn->error . "<br>";
                    }
                }
            }
        }
        
        fclose($file);
        echo "$count records inserted successfully.<br>";
    } else {
        echo "userData.txt file not found.<br>";
    }
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

$conn->close();
?>
