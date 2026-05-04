<?php
/**
 * Load ClothingStore Database Script
 *
 * Drops all tables, recreates them, and loads sample data from the text files
 * in the data folder.
 */

require_once __DIR__ . '/DBConn.php';

$dataDirectory = __DIR__ . '/../data';

function loadDelimitedFile(mysqli $conn, string $filePath, callable $rowHandler): int
{
    if (!file_exists($filePath)) {
        return 0;
    }

    $handle = fopen($filePath, 'r');
    if ($handle === false) {
        return 0;
    }

    $loaded = 0;

    while (($line = fgets($handle)) !== false) {
        $line = trim($line);

        if ($line === '') {
            continue;
        }

        $parts = array_map('trim', explode('|', $line));

        if ($rowHandler($parts, $conn)) {
            $loaded++;
        }
    }

    fclose($handle);

    return $loaded;
}

$conn->query('SET FOREIGN_KEY_CHECKS=0');

foreach (array('tblOrderItem', 'tblOrder', 'tblClothes', 'tblUser', 'tblAdmin') as $tableName) {
    if (!$conn->query("DROP TABLE IF EXISTS {$tableName}")) {
        die('Error dropping ' . $tableName . ': ' . $conn->error);
    }
}

$conn->query('SET FOREIGN_KEY_CHECKS=1');

$tableSql = array(
    'tblAdmin' => "CREATE TABLE IF NOT EXISTS tblAdmin (
        adminID INT AUTO_INCREMENT PRIMARY KEY,
        adminName VARCHAR(100) NOT NULL,
        adminEmail VARCHAR(100) NOT NULL UNIQUE,
        passwordHash VARCHAR(255) NOT NULL,
        createdDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'tblUser' => "CREATE TABLE IF NOT EXISTS tblUser (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'tblClothes' => "CREATE TABLE IF NOT EXISTS tblClothes (
        clothingID INT AUTO_INCREMENT PRIMARY KEY,
        clothingName VARCHAR(150) NOT NULL,
        category VARCHAR(50) NOT NULL,
        description TEXT,
        price DECIMAL(8,2) NOT NULL,
        quantity INT NOT NULL DEFAULT 0,
        imageURL VARCHAR(255) DEFAULT NULL,
        createdDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'tblOrder' => "CREATE TABLE IF NOT EXISTS tblOrder (
        orderID INT AUTO_INCREMENT PRIMARY KEY,
        userID INT NOT NULL,
        orderDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        totalAmount DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Pending',
        CONSTRAINT fk_order_user FOREIGN KEY (userID) REFERENCES tblUser(userID) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'tblOrderItem' => "CREATE TABLE IF NOT EXISTS tblOrderItem (
        orderItemID INT AUTO_INCREMENT PRIMARY KEY,
        orderID INT NOT NULL,
        clothingID INT NOT NULL,
        quantity INT NOT NULL,
        priceAtPurchase DECIMAL(10,2) NOT NULL,
        CONSTRAINT fk_orderitem_order FOREIGN KEY (orderID) REFERENCES tblOrder(orderID) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_orderitem_clothing FOREIGN KEY (clothingID) REFERENCES tblClothes(clothingID) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

foreach ($tableSql as $tableName => $sql) {
    if (!$conn->query($sql)) {
        die('Error creating ' . $tableName . ': ' . $conn->error);
    }
}

$adminLoaded = loadDelimitedFile($conn, $dataDirectory . '/adminData.txt', function (array $parts, mysqli $conn): bool {
    if (count($parts) < 3) {
        return false;
    }

    $stmt = $conn->prepare('INSERT INTO tblAdmin (adminName, adminEmail, passwordHash) VALUES (?, ?, ?)');
    if (!$stmt) {
        return false;
    }

    $adminName = $parts[0];
    $adminEmail = $parts[1];
    $passwordHash = $parts[2];
    $stmt->bind_param('sss', $adminName, $adminEmail, $passwordHash);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
});

$userLoaded = loadDelimitedFile($conn, $dataDirectory . '/userData.txt', function (array $parts, mysqli $conn): bool {
    if (count($parts) < 4) {
        return false;
    }

    $stmt = $conn->prepare('INSERT INTO tblUser (username, fullName, email, passwordHash, isVerified) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) {
        return false;
    }

    $username = $parts[0];
    $fullName = $parts[1];
    $email = $parts[2];
    $passwordHash = $parts[3];
    $isVerified = isset($parts[4]) ? (int) $parts[4] : 1;

    $stmt->bind_param('ssssi', $username, $fullName, $email, $passwordHash, $isVerified);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
});

$clothesLoaded = loadDelimitedFile($conn, $dataDirectory . '/clothesData.txt', function (array $parts, mysqli $conn): bool {
    if (count($parts) < 5) {
        return false;
    }

    $stmt = $conn->prepare('INSERT INTO tblClothes (clothingName, category, description, price, quantity) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) {
        return false;
    }

    $clothingName = $parts[0];
    $category = $parts[1];
    $description = $parts[2];
    $price = (float) $parts[3];
    $quantity = (int) $parts[4];

    $stmt->bind_param('sssdi', $clothingName, $category, $description, $price, $quantity);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
});

$orderLoaded = loadDelimitedFile($conn, $dataDirectory . '/orderData.txt', function (array $parts, mysqli $conn): bool {
    if (count($parts) < 4) {
        return false;
    }

    $stmt = $conn->prepare('INSERT INTO tblOrder (userID, orderDate, totalAmount, status) VALUES (?, ?, ?, ?)');
    if (!$stmt) {
        return false;
    }

    $userID = (int) $parts[0];
    $orderDate = $parts[1];
    $totalAmount = (float) $parts[2];
    $status = $parts[3];

    $stmt->bind_param('isds', $userID, $orderDate, $totalAmount, $status);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
});

echo 'ClothingStore tables created successfully.<br>';
echo 'Loaded ' . $adminLoaded . ' admin records, ' . $userLoaded . ' user records, ' . $clothesLoaded . ' clothing records, and ' . $orderLoaded . ' order records.<br>';

$conn->close();
?>
