<?php
/**
 * Create Table Script
 *
 * Drops tblUser when it exists, recreates it, and reloads seed data from
 * data/userData.txt.
 */

require_once __DIR__ . '/DBConn.php';

$dataFile = __DIR__ . '/../data/userData.txt';

if (!$conn->query('DROP TABLE IF EXISTS tblUser')) {
    die('Error dropping tblUser: ' . $conn->error);
}

$createTable = "CREATE TABLE IF NOT EXISTS tblUser (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    fullName VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    city VARCHAR(50) DEFAULT NULL,
    zipCode VARCHAR(10) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    isVerified TINYINT(1) NOT NULL DEFAULT 0,
    createdDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($createTable)) {
    die('Error creating tblUser: ' . $conn->error);
}

$insertStmt = $conn->prepare('INSERT INTO tblUser (username, fullName, email, passwordHash, isVerified) VALUES (?, ?, ?, ?, ?)');

if (!$insertStmt) {
    die('Error preparing insert statement: ' . $conn->error);
}

$loadedRows = 0;

if (file_exists($dataFile)) {
    $fileHandle = fopen($dataFile, 'r');

    if ($fileHandle) {
        while (($line = fgets($fileHandle)) !== false) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $line));

            if (count($parts) < 4) {
                continue;
            }

            $username = $parts[0];
            $fullName = $parts[1];
            $email = $parts[2];
            $passwordHash = $parts[3];
            $isVerified = isset($parts[4]) ? (int) $parts[4] : 1;

            $insertStmt->bind_param('ssssi', $username, $fullName, $email, $passwordHash, $isVerified);

            if ($insertStmt->execute()) {
                $loadedRows++;
            }
        }

        fclose($fileHandle);
    }
}

$insertStmt->close();

echo 'tblUser recreated successfully.<br>';
echo $loadedRows . ' user records loaded from userData.txt.<br>';

$conn->close();
?>
