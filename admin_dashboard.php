<?php
session_start();

// ✅ Only allow admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "servicemarketplace";
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Handle user deletion
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM accounts WHERE id = $id");
    header("Location: admin_dashboard.php");
    exit();
}

// ✅ Get summary counts
$total_users = $conn->query("SELECT COUNT(*) AS total FROM accounts WHERE role='user'")->fetch_assoc()['total'];
$total_providers = $conn->query("SELECT COUNT(*) AS total FROM accounts WHERE role='provider'")->fetch_assoc()['total'];
$total_services = $conn->query("SELECT COUNT(*) AS total FROM services")->fetch_assoc()['total'];
$total_contacts = $conn->query("SELECT COUNT(*) AS total FROM contact_messages")->fetch_assoc()['total'];

// ✅ Logout logic
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<html>
<head>
<title>Admin Dashboard</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
    body { display: flex; min-height: 100vh; background: #f4f6fc; }

    .sidebar {
        width: 250px; background: #1e1f26; color: white; padding: 20px;
        position: fixed; height: 100%; left: 0; top: 0;
    }
    .sidebar h2 { text-align: center; margin-bottom: 30px; color: #00c6ff; }
    .sidebar a {
        display: block; color: white; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px;
        text-decoration: none; transition: 0.3s;
    }
    .sidebar a:hover, .sidebar a.active { background: #00c6ff; color: black; }

    .main-content { margin-left: 250px; padding: 30px; width: 100%; }
    .topbar {
        background: white; padding: 15px 30px; display: flex;
        justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .topbar h1 { color: #0072ff; font-size: 22px; }
    .topbar a {
        color: #0072ff; text-decoration: none; font-weight: bold;
    }

    .cards {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px; margin-top: 40px;
    }
    .card {
        background: white; border-radius: 10px; padding: 25px; text-align: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .card h3 { font-size: 18px; color: #555; margin-bottom: 10px; }
    .card p { font-size: 22px; font-weight: bold; color: #0072ff; }

    table {
        width: 100%; border-collapse: collapse; margin-top: 40px; background: white; border-radius: 10px;
        overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    table th { background: #0072ff; color: white; }
    table tr:hover { background: #f9f9f9; }
    .action-btns a {
        padding: 6px 10px; text-decoration: none; border-radius: 4px;
        margin-right: 5px; font-size: 14px;
    }
    .edit-btn { background: #ffc107; color: black; }
    .delete-btn { background: #dc3545; color: white; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php" class="active">🏠 Dashboard</a>
    <a href="manage_users.php">👥 Manage Users</a>
    <a href="manage_providers.php">🔧 Manage Providers</a>
    <a href="manage_services.php">📦 Manage Services</a>
     <a href="manage_reports.php">🚫 Manage Report</a>
    <a href="admin_view_chats.php">💬 View Chats</a>
    <a href="view_contacts.php">📨 Contact Messages</a>
    <a href="admin_dashboard.php?logout=1">🚪 Logout</a>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <a href="admin_dashboard.php?logout=1">Logout</a>
    </div>

    <div class="cards">
        <div class="card">
            <h3>Total Users</h3>
            <p><?php echo $total_users; ?></p>
        </div>
        <div class="card">
            <h3>Total Providers</h3>
            <p><?php echo $total_providers; ?></p>
        </div>
        <div class="card">
            <h3>Total Services</h3>
            <p><?php echo $total_services; ?></p>
        </div>
        <div class="card">
            <h3>Contact Messages</h3>
            <p><?php echo $total_contacts; ?></p>
        </div>
    </div>

    <h2 style="margin-top:40px; color:#0072ff;">Recent Users</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php
        $recent_users = $conn->query("SELECT id, username, email, role FROM accounts ORDER BY id DESC LIMIT 5");
        while ($row = $recent_users->fetch_assoc()) {
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['username']}</td>
                <td>{$row['email']}</td>
                <td>{$row['role']}</td>
                <td class='action-btns'>
                    <a href='edit_user.php?id={$row['id']}' class='edit-btn'>Edit</a>
                    <a href='admin_dashboard.php?delete_id={$row['id']}' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>
                </td>
            </tr>";
        }
        ?>
    </table>
</div>

</body>
</html>
