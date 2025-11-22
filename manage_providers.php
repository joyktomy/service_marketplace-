<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "servicemarketplace";
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM accounts WHERE id = $id");
    header("Location: manage_providers.php?msg=Provider+Deleted");
    exit();
}


if (isset($_GET['blacklist'])) {
    $id = intval($_GET['blacklist']);
    $conn->query("UPDATE accounts SET status = 'blacklisted' WHERE id = $id");
    header("Location: manage_providers.php?msg=Provider+Blacklisted");
    exit();
}
if (isset($_GET['unblacklist'])) {
    $id = intval($_GET['unblacklist']);
    $conn->query("UPDATE accounts SET status = 'active' WHERE id = $id");
    header("Location: manage_providers.php?msg=Provider+Restored");
    exit();
}

$result = $conn->query("SELECT id, username, email, phone, role, status FROM accounts WHERE role='provider' ORDER BY id DESC");
?>
<html>
<head>
<title>Manage Providers</title>
<style>
    body { font-family: 'Segoe UI', sans-serif; background: #f4f6fc; margin: 0; }
    .container { margin-left: 250px; padding: 30px; }
    h1 { color: #0072ff; }
    table {
        width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 10px;
        box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    }
    th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
    th { background: #0072ff; color: white; }
    tr:hover { background: #f9f9f9; }
    a.btn {
        padding: 6px 10px; text-decoration: none; border-radius: 6px; font-size: 14px; margin-right: 5px;
    }
    .delete { background: #e74c3c; color: white; }
    .blacklist { background: #f39c12; color: white; }
    .unblacklist { background: #2ecc71; color: white; }
    .sidebar {
        width: 20
        
        0px; background: #1e1f26; color: white; position: fixed; height: 100%;
        padding: 20px;
    }
    .sidebar h2 { text-align: center; margin-bottom: 30px; color: #00c6ff; }
    .sidebar a {
        display: block; color: white; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px;
        text-decoration: none; transition: 0.3s;
    }
    .sidebar a:hover, .sidebar a.active { background: #00c6ff; color: black; }
</style>
<script>
function confirmDelete(id) {
    if (confirm("Are you sure you want to permanently delete this provider?")) {
        window.location.href = "manage_providers.php?delete=" + id;
    }
}
</script>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">🏠 Dashboard</a>
    <a href="manage_users.php">👥 Manage Users</a>
    <a href="manage_providers.php" class="active">🔧 Manage Providers</a>
     <a href="manage_services.php">📦 Manage Services</a>
    <a href="manage_reports.php">🚫 Manage Report</a>
    <a href="admin_view_chats.php">💬 View Chats</a>
    <a href="view_contacts.php">📨 Contact Messages</a>
    <a href="admin_dashboard.php?logout=1">🚪 Logout</a>
</div>

<div class="container">
    <h1>Manage Providers</h1>

    <?php if (isset($_GET['msg'])): ?>
        <p style="color:green;"><strong><?= htmlspecialchars($_GET['msg']); ?></strong></p>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['username']); ?></td>
            <td><?= htmlspecialchars($row['email']); ?></td>
            <td><?= htmlspecialchars($row['phone']); ?></td>
            <td><?= htmlspecialchars($row['role']); ?></td>
            <td><?= htmlspecialchars($row['status']); ?></td>
            <td>
                <a href="javascript:void(0)" class="btn delete" onclick="confirmDelete(<?= $row['id']; ?>)">Delete</a>
                <?php if ($row['status'] === 'blacklisted'): ?>
                    <a href="?unblacklist=<?= $row['id']; ?>" class="btn unblacklist">Unblacklist</a>
                <?php else: ?>
                    <a href="?blacklist=<?= $row['id']; ?>" class="btn blacklist">Blacklist</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
