<?php
/**
 * My Listings Page
 * 
 * Sellers can view their submitted clothing items and approval status
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$message = '';
$messageType = '';

// Fetch seller's clothing items
$sql = "SELECT clothingID, clothingName, brand, category, size, clothingCondition, 
               price, quantity, imageURL, approvalStatus, createdDate
        FROM tblClothes 
        WHERE sellerID = ?
        ORDER BY createdDate DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$listings = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Listings - Pastimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .listings-container {
            max-width: 900px;
            margin: 20px auto;
        }
        
        .listing-card {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            gap: 15px;
        }
        
        .listing-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            background-color: #e0e0e0;
            flex-shrink: 0;
        }
        
        .listing-info {
            flex-grow: 1;
        }
        
        .listing-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin: 0 0 5px 0;
        }
        
        .listing-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            font-size: 0.9rem;
            color: #666;
            margin: 5px 0;
        }
        
        .listing-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-block;
            margin-top: 8px;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .listing-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin: 8px 0;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Message Icon (only for logged-in users) -->
    <?php if (isset($_SESSION['userID'])): ?>
        <a href="<?php echo $myMessagesPath; ?>" class="message-icon-link" title="Messages">
            <i class="fas fa-envelope"></i>
            <?php if ($unreadMessageCount > 0): ?>
                <span class="message-badge"><?php echo $unreadMessageCount; ?></span>
            <?php endif; ?>
        </a>
    <?php endif; ?>
    
    <!-- Shopping Cart Icon -->
    <a href="cart.php" class="cart-icon-link" title="Shopping Cart">
        <i class="fa-solid fa-bag-shopping"></i>
        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <span class="cart-badge"><?php echo count($_SESSION['cart']); ?></span>
        <?php endif; ?>
    </a>

    <main>
        <div class="container">
            <div class="listings-container">
                <h2>My Listings</h2>
                
                <?php if (!empty($message)): ?>
                    <div class="alert">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (count($listings) > 0): ?>
                    <?php foreach ($listings as $listing): ?>
                        <div class="listing-card">
                            <?php
                                $imageURL = $listing['imageURL'];
                                if (empty($imageURL) || !file_exists($imageURL)) {
                                    $imageURL = '../images/placeholder.jpg';
                                }
                                if (!file_exists($imageURL)) {
                                    $imageURL = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100"%3E%3Crect fill="%23ccc" width="100" height="100"/%3E%3Ctext fill="white" text-anchor="middle" x="50" y="50" font-size="12"%3ENo Image%3C/text%3E%3C/svg%3E';
                                }
                            ?>
                            <img src="<?php echo htmlspecialchars($imageURL); ?>" alt="<?php echo htmlspecialchars($listing['clothingName']); ?>" class="listing-image">
                            
                            <div class="listing-info">
                                <h3 class="listing-title"><?php echo htmlspecialchars($listing['clothingName']); ?></h3>
                                
                                <div class="listing-meta">
                                    <div><strong>Brand:</strong> <?php echo htmlspecialchars($listing['brand'] ?? 'N/A'); ?></div>
                                    <div><strong>Category:</strong> <?php echo htmlspecialchars($listing['category']); ?></div>
                                    <div><strong>Size:</strong> <?php echo htmlspecialchars($listing['size']); ?></div>
                                    <div><strong>Condition:</strong> <?php echo htmlspecialchars($listing['clothingCondition']); ?></div>
                                </div>
                                
                                <div class="listing-price">R <?php echo number_format($listing['price'], 2); ?></div>
                                
                                <div>
                                    <span class="listing-status status-<?php echo strtolower($listing['approvalStatus']); ?>">
                                        <?php echo ucfirst($listing['approvalStatus']); ?>
                                    </span>
                                </div>
                                
                                <div style="font-size: 0.85rem; color: #999; margin-top: 8px;">
                                    Posted: <?php echo date('M d, Y', strtotime($listing['createdDate'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>You haven't listed any items yet.</p>
                        <a href="sell-item.php" class="btn btn-primary">List an Item</a>
                    </div>
                <?php endif; ?>
                
                <div style="margin-top: 20px;">
                    <a href="sell-item.php" class="btn btn-primary">List New Item</a>
                    <a href="account.php" class="btn btn-secondary">Back to Account</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navbarToggle = document.getElementById('navbarToggle');
            const navbarLinks = document.getElementById('navbarLinks');
            
            if (navbarToggle && navbarLinks) {
                navbarToggle.addEventListener('click', function() {
                    navbarToggle.classList.toggle('active');
                    navbarLinks.classList.toggle('active');
                });
                
                const links = navbarLinks.querySelectorAll('a');
                links.forEach(link => {
                    link.addEventListener('click', function() {
                        navbarToggle.classList.remove('active');
                        navbarLinks.classList.remove('active');
                    });
                });
            }
        });
    </script>
</body>
</html>
<?php
/*
This code is the original work of:
ST10441421 - Odirile Masemola
ST10450294 - Ripfumelo Mabasa
All rights reserved.
*/
?>
