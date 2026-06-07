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
    <link rel="stylesheet" href="../assets/style.css?v=4">
</head>
<body class="admin-page">
    <header>
        <?php include '../includes/navbar.php'; ?>
    </header>

    <main>
        <div class="admin-container">
            <nav class="admin-breadcrumb">Dashboard / <span>Users</span></nav>

            <div class="admin-page-head">
                <div>
                    <h1 class="admin-title">Manage Users</h1>
                    <p class="admin-subtitle">Verify registrations, edit details, and manage user accounts.</p>
                </div>
                <a href="add-user.php" class="admin-action-btn primary">Add New User</a>
            </div>

            <div class="admin-card">
                <?php if (count($users) > 0): ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($user['userID']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['fullName']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if ($user['isVerified']): ?>
                                                <span class="admin-badge is-green">Verified</span>
                                            <?php else: ?>
                                                <span class="admin-badge is-amber">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="admin-row-actions">
                                                <a href="edit-user.php?id=<?php echo $user['userID']; ?>" class="admin-action-btn ghost">Edit</a>
                                                <a href="delete-user.php?id=<?php echo $user['userID']; ?>" class="admin-action-btn danger" onclick="return confirm('Are you sure?');">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="admin-empty">No users found.</p>
                <?php endif; ?>
            </div>

            <a href="dashboard.php" class="admin-action-btn ghost admin-back">Back to Dashboard</a>
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
ST10441421 	- Odirile Masemola
ST10450294 	- Ripfumelo Mabasa
*/
?>