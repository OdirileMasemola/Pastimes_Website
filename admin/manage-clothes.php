<?php
/**
 * Manage Clothing Page
 * 
 * Admin can view all clothing items, add, update, or delete items
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$sql = "SELECT * FROM tblClothes ORDER BY createdDate DESC";
$result = $conn->query($sql);
$clothes = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clothes[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clothes - Admin Panel</title>
    <link rel="stylesheet" href="../assets/style.css">
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
                    <li><a href="admin-logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h2>Manage Clothing</h2>
            
            <div class="admin-actions">
                <a href="add-clothing.php" class="btn btn-primary">Add New Clothing Item</a>
            </div>
            
            <?php if (count($clothes) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clothes as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['clothingID']); ?></td>
                                <td><?php echo htmlspecialchars($item['clothingName']); ?></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td>R <?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td>
                                    <a href="edit-clothing.php?id=<?php echo $item['clothingID']; ?>" class="btn btn-secondary">Edit</a>
                                    <a href="delete-clothing.php?id=<?php echo $item['clothingID']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No clothing items found.</p>
            <?php endif; ?>
            
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
