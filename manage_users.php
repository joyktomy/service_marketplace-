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
    header("Location: manage_users.php?msg=" . urlencode("User Deleted"));
    exit();
}

if (isset($_GET['blacklist'])) {
    $id = intval($_GET['blacklist']);
    $conn->query("UPDATE accounts SET status = 'blacklisted' WHERE id = $id");
    header("Location: manage_users.php?msg=" . urlencode("User Blacklisted"));
    exit();
}

if (isset($_GET['unblacklist'])) {
    $id = intval($_GET['unblacklist']);
    $conn->query("UPDATE accounts SET status = 'active' WHERE id = $id");
    header("Location: manage_users.php?msg=" . urlencode("User Restored"));
    exit();
}


$result = $conn->query("SELECT id, username, email, phone, role, status FROM accounts WHERE role='user' ORDER BY id DESC");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Manage Users</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f4f6fc; margin: 0; }
    .sidebar { width: 200px; background: #1e1f26; color: #fff; position: fixed; height: 100vh; padding: 20px; }
    .sidebar h2 { color:#00c6ff; text-align:center; margin-bottom:20px; }
    .sidebar a { display:block; color:#fff; padding:10px; text-decoration:none; margin-bottom:6px; border-radius:6px; }
    .sidebar a.active, .sidebar a:hover { background:#00c6ff; color:#000; }
    .container { margin-left:250px; padding:30px; }
    h1 { color:#0072ff; }
    table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 3px 8px rgba(0,0,0,0.08); border-radius:8px; overflow:hidden; }
    th, td { padding:12px; border-bottom:1px solid #eee; text-align:left; }
    th { background:#0072ff; color:#fff; }
    tr:hover { background:#f9f9f9; }
    .btn { padding:6px 10px; border-radius:6px; color:#fff; text-decoration:none; font-weight:600; margin-right:6px; }
    .delete { background:#e74c3c; }
    .blacklist { background:#f39c12; color:#000; }
    .unblacklist { background:#2ecc71; }
    .msg { padding:10px 14px; background:#e6ffe6; color:#006600; border-radius:6px; display:inline-block; margin-bottom:12px; }
  </style>
  <script>
    function confirmDelete(id) {
      if (confirm('Are you sure you want to permanently delete this user? This action cannot be undone.')) {
        window.location.href = 'manage_users.php?delete=' + id;
      }
    }
  </script>
</head>
<body>
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">🏠 Dashboard</a>
    <a href="manage_users.php" class="active">👥 Manage Users</a>
    <a href="manage_providers.php">🔧 Manage Providers</a>
    <a href="manage_services.php">📦 Manage Services</a>
    <a href="manage_reports.php">🚫 Manage Report</a>
    <a href="admin_view_chats.php">💬 View Chats</a>
    <a href="view_contacts.php">📨 Contact Messages</a>
    <a href="admin_dashboard.php?logout=1">🚪 Logout</a>
  </div>

  <div class="container">
    <h1>Manage Users</h1>

    <?php if (isset($_GET['msg'])): ?>
      <div class="msg"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Role</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['phone']); ?></td>
            <td><?php echo htmlspecialchars($row['role']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td>
              <a href="javascript:void(0)" class="btn delete" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</a>
              <?php if ($row['status'] === 'blacklisted'): ?>
                <a href="?unblacklist=<?php echo $row['id']; ?>" class="btn unblacklist">Unblacklist</a>
              <?php else: ?>
                <a href="?blacklist=<?php echo $row['id']; ?>" class="btn blacklist">Blacklist</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="7">No users found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
