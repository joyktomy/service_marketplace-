<?php
session_start();
$conn = new mysqli("localhost", "root", "", "servicemarketplace");


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$provider_id = isset($_GET['provider_id']) ? intval($_GET['provider_id']) : 0;


$users = $conn->query("SELECT id, username FROM accounts WHERE role='user'");
$providers = $conn->query("SELECT id, username FROM accounts WHERE role='provider'");
?>
<html>
<head>
    <title>Admin - View Chats</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin:0; display:flex; }
        .sidebar { width: 200px; background: #1e1f26; color:white; padding:20px; height:100vh; }
        .sidebar h2 { text-align:center; margin-bottom:30px; color:#00c6ff; }
        .sidebar a { display:block; color:white; padding:12px 15px; border-radius:6px; margin-bottom:8px; text-decoration:none; }
        .sidebar a:hover, .sidebar a.active { background:#00c6ff; color:black; }

        .main { flex:1; padding:20px; background:#f4f6fc; min-height:100vh; }
        h1 { color:#0072ff; }

        .form-select { margin-bottom:20px; padding:10px; border-radius:6px; width:200px; }

        .chat-box { background:white; padding:15px; border-radius:10px; max-height:500px; overflow-y:auto; box-shadow:0 4px 10px rgba(0,0,0,0.1); }
        .message { margin-bottom:10px; }
        .user-msg .bubble { background:#e0f7fa; padding:8px; border-radius:8px; display:inline-block; }
        .provider-msg .bubble { background:#ffe0b2; padding:8px; border-radius:8px; display:inline-block; }
        .time { font-size:0.75rem; color:#888; margin-top:2px; }

    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">🏠 Dashboard</a>
    <a href="manage_users.php">👥 Manage Users</a>
    <a href="manage_providers.php">🔧 Manage Providers</a>
    <a href="manage_services.php">📦 Manage Services</a>
    <a href="manage_reports.php">🚫 Manage Report</a>
    <a href="view_contacts.php">📨 Contact Messages</a>
    <a href="admin_view_chats.php" class="active">💬 View Chats</a>
    <a href="admin_dashboard.php?logout=1">🚪 Logout</a>
</div>

<div class="main">
    <h1>View User-Provider Chats</h1>

    <form method="GET" action="">
        <select name="user_id" class="form-select" required>
            <option value="">Select User</option>
            <?php while($row = $users->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>" <?php if($row['id']==$user_id) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($row['username']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <select name="provider_id" class="form-select" required>
            <option value="">Select Provider</option>
            <?php while($row = $providers->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>" <?php if($row['id']==$provider_id) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($row['username']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">View Chat</button>
    </form>

    <?php if($user_id && $provider_id): ?>
        <div class="chat-box" id="chatBox">
            <?php
      
            $sql = "SELECT c.*, a.username AS sender_name 
                    FROM chats c 
                    JOIN accounts a ON c.sender_id = a.id
                    WHERE (c.sender_id = ? AND c.receiver_id = ?) 
                       OR (c.sender_id = ? AND c.receiver_id = ?)
                    ORDER BY c.created_at ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiii", $user_id, $provider_id, $provider_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while($m = $result->fetch_assoc()):
                $isMine = ($m['sender_id'] == $user_id) ? "user-msg" : "provider-msg";
            ?>
                <div class="message <?php echo $isMine; ?>">
                    <div class="bubble"><strong><?php echo htmlspecialchars($m['sender_name']); ?>:</strong> <?php echo htmlspecialchars($m['message']); ?></div>
                    <div class="time"><?php echo date("H:i", strtotime($m['created_at'])); ?></div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
