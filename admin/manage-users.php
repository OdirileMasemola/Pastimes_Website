<?php
/**
 * Manage Users Page
 * 
 * Admin can view all users, verify pending registrations
 * Displays options to edit or delete users
 */

session_start();
include '../includes/DBConn.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin-login.php");
    exit();
}

$sql = "SELECT * FROM tblUser ORDER BY createdDate DESC";
$result = $conn->query($sql);
$users = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
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
                    <li><a href="manage-users.php">Manage Users</a></li>
                    <li><a href="manage-clothes.php">Manage Clothes</a></li>
                    <li><a href="manage-orders.php">Manage Orders</a></li>
                    <li><a href="admin-logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h2>Manage Users</h2>
            
            <div class="admin-actions">
                <a href="add-user.php" class="btn btn-primary">Add New User</a>
            </div>
            
            <?php if (count($users) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['userID']); ?></td>
                                <td><?php echo htmlspecialchars($user['fullName']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['isVerified'] ? 'Verified' : 'Pending'; ?></td>
                                <td>
                                    <a href="edit-user.php?id=<?php echo $user['userID']; ?>" class="btn btn-secondary">Edit</a>
                                    <a href="delete-user.php?id=<?php echo $user['userID']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
            
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>
</body>
</html>
