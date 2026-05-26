<?php
/**
 * Manage Seller Requests Page
 *
 * Admin can review and approve/reject clothing items submitted by sellers
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$message = '';
$messageType = '';

// Handle approve/reject/delete actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $clothingID = intval($_GET['id']);
    
    if ($clothingID > 0) {
        if ($action === 'approve' || $action === 'reject') {
            $newStatus = ($action === 'approve') ? 'approved' : 'rejected';
            
            $updateSql = "UPDATE tblClothes SET approvalStatus = ? WHERE clothingID = ?";
            $updateStmt = $conn->prepare($updateSql);
            
            if (!$updateStmt) {
                $message = "Database error: " . $conn->error;
                $messageType = "error";
            } else {
                $updateStmt->bind_param("si", $newStatus, $clothingID);
                
                if ($updateStmt->execute()) {
                    $message = "Item " . htmlspecialchars($action) . "ed successfully.";
                    $messageType = "success";
                } else {
                    $message = "Error updating item: " . $updateStmt->error;
                    $messageType = "error";
                }
                $updateStmt->close();
            }
        } elseif ($action === 'delete') {
            $deleteSql = "DELETE FROM tblClothes WHERE clothingID = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            
            if (!$deleteStmt) {
                $message = "Database error: " . $conn->error;
                $messageType = "error";
            } else {
                $deleteStmt->bind_param("i", $clothingID);
                
                if ($deleteStmt->execute()) {
                    $message = "Item deleted successfully.";
                    $messageType = "success";
                } else {
                    $message = "Error deleting item: " . $deleteStmt->error;
                    $messageType = "error";
                }
                $deleteStmt->close();
            }
        }
    }
}

// Fetch pending seller requests
$sql = "SELECT c.clothingID, c.clothingName, c.brand, c.category, c.size, c.clothingCondition, 
               c.price, c.description, c.imageURL, c.createdDate, c.sellerID, u.username, u.fullName, u.email
        FROM tblClothes c
        LEFT JOIN tblUser u ON c.sellerID = u.userID
        WHERE c.approvalStatus = 'pending'
        ORDER BY c.createdDate DESC";
$result = $conn->query($sql);
$pendingItems = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pendingItems[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Seller Requests - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .seller-request-card {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .seller-request-card h3 {
            margin-top: 0;
        }
        
        .seller-info {
            background-color: #e7f3ff;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .seller-info p {
            margin: 5px 0;
        }
        
        .item-details {
            margin: 10px 0;
        }
        
        .item-details p {
            margin: 5px 0;
        }
        
        .action-buttons {
            margin-top: 15px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 15px;
            margin-right: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .btn-approve {
            background-color: #28a745;
            color: white;
        }
        
        .btn-approve:hover {
            background-color: #218838;
        }
        
        .btn-reject {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-reject:hover {
            background-color: #c82333;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1>Pastimes - Admin Panel</h1>
                </div>
                <ul class="nav-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage-clothes.php">Manage Clothes</a></li>
                    <li><a href="manage-seller-requests.php">Seller Requests</a></li>
                    <li><a href="admin-logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h2>Manage Seller Requests</h2>
            
            <?php if (!empty($message)): ?>
                <div class="message <?php echo htmlspecialchars($messageType); ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (count($pendingItems) > 0): ?>
                <p><?php echo count($pendingItems); ?> pending request<?php echo count($pendingItems) !== 1 ? 's' : ''; ?></p>
                
                <?php foreach ($pendingItems as $item): ?>
                    <div class="seller-request-card">
                        <h3><?php echo htmlspecialchars($item['clothingName']); ?></h3>
                        
                        <div class="seller-info">
                            <strong>Submitted by:</strong>
                            <p>
                                Name: <?php echo htmlspecialchars($item['fullName'] ?? 'Unknown'); ?> <br>
                                Username: <?php echo htmlspecialchars($item['username'] ?? 'Unknown'); ?> <br>
                                Email: <?php echo htmlspecialchars($item['email'] ?? 'Unknown'); ?>
                            </p>
                        </div>
                        
                        <div class="item-details">
                            <p><strong>Brand:</strong> <?php echo htmlspecialchars($item['brand'] ?? 'N/A'); ?></p>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
                            <p><strong>Size:</strong> <?php echo htmlspecialchars($item['size']); ?></p>
                            <p><strong>Condition:</strong> <?php echo htmlspecialchars($item['clothingCondition']); ?></p>
                            <p><strong>Price:</strong> R <?php echo number_format($item['price'], 2); ?></p>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($item['description'] ?? 'N/A'); ?></p>
                            <p><strong>Image URL:</strong> <?php echo htmlspecialchars($item['imageURL'] ?? 'None'); ?></p>
                            <p><strong>Submitted:</strong> <?php echo htmlspecialchars($item['createdDate']); ?></p>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="?action=approve&id=<?php echo $item['clothingID']; ?>" class="btn btn-approve" onclick="return confirm('Approve this item?');">Approve</a>
                            <a href="?action=reject&id=<?php echo $item['clothingID']; ?>" class="btn btn-reject" onclick="return confirm('Reject this item?');">Reject</a>
                            <a href="?action=delete&id=<?php echo $item['clothingID']; ?>" class="btn btn-reject" onclick="return confirm('Delete this item permanently?');">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No pending seller requests at this time.</p>
            <?php endif; ?>
            
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
<?php
/*
This code is the original work of:
ST10441421 	- Odirile Masemola
ST10450294 	- Ripfumelo Mabasa
*/
?>
