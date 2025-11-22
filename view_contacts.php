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
    $conn->query("DELETE FROM contact_messages WHERE id=$id");
    header("Location: view_contacts.php");
    exit();
}

if (isset($_POST['reply'])) {
    $contact_id = intval($_POST['contact_id']);
    $reply_message = $conn->real_escape_string($_POST['reply_message']);

    $contact = $conn->query("SELECT email FROM contact_messages WHERE id=$contact_id")->fetch_assoc();
    if ($contact) {
        $email = $contact['email'];
        $user = $conn->query("SELECT id FROM accounts WHERE email='$email'")->fetch_assoc();

        if ($user) {
            $receiver_id = $user['id'];
            $sender_id = $_SESSION['user_id']; 

            $conn->query("INSERT INTO chats (sender_id, receiver_id, message) VALUES ($sender_id, $receiver_id, '$reply_message')");
            $msg = "✅ Reply sent successfully to chat.";
        } else {
            $msg = "⚠️ No matching user found with this email.";
        }
    }
}

$contacts = $conn->query("SELECT * FROM contact_messages ORDER BY id DESC");
?>
<html>
<head>
    <title>View Contacts - Admin Panel</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background: #f4f6fc; }

         .sidebar {
        width: 200px; background: #1e1f26; color: white; padding: 20px;
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
        .topbar a { color: #0072ff; text-decoration: none; font-weight: bold; }
        table {
            width: 100%; border-collapse: collapse; margin-top: 30px; background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        table th { background: #0072ff; color: white; }
        table tr:hover { background: #f9f9f9; }

        .message-box {
            background: rgba(0,0,0,0.6); position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; display: none; justify-content: center; align-items: center;
        }
        .message-content {
            background: white; padding: 30px; border-radius: 10px; width: 400px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .message-content textarea {
            width: 100%; height: 100px; margin-top: 10px; border: 1px solid #ccc; border-radius: 5px; padding: 10px;
        }
        .btn {
            display: inline-block; padding: 8px 14px; border-radius: 5px;
            text-decoration: none; color: white; font-size: 14px;
        }
        .btn-view { background: #00bcd4; }
        .btn-reply { background: #4caf50; }
        .btn-delete { background: #f44336; }
        .close-btn { float: right; background: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
        .msg-info { margin-top: 15px; color: green; }
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
    <a href="admin_view_chats.php">💬 View Chats</a>
    <a href="view_contacts.php" class="active">📨 Contact Messages</a>
    <a href="admin_dashboard.php?logout=1">🚪 Logout</a>
</div>

<div class="main-content">
    <div class="topbar">
        <h1>📨 Contact Messages</h1>
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>

    <?php if (!empty($msg)) echo "<p class='msg-info'>$msg</p>"; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Message</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $contacts->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['message']) ?></td>
            <td>
                <a href="#" class="btn btn-view" onclick="showMessage('<?= htmlspecialchars(addslashes($row['message'])) ?>')">View</a>
                <a href="#" class="btn btn-reply" onclick="openReplyForm(<?= $row['id'] ?>)">Reply</a>
                <a href="?delete=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this message?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>


<div id="messageBox" class="message-box">
    <div class="message-content">
        <button class="close-btn" onclick="closeMessage()">X</button>
        <h3>Message</h3>
        <p id="messageText"></p>
    </div>
</div>


<div id="replyBox" class="message-box">
    <div class="message-content">
        <button class="close-btn" onclick="closeReply()">X</button>
        <h3>Reply to Message</h3>
        <form method="POST">
            <input type="hidden" name="contact_id" id="contactId">
            <textarea name="reply_message" required placeholder="Type your reply here..."></textarea>
            <br><br>
            <button type="submit" name="reply" class="btn btn-reply">Send Reply</button>
        </form>
    </div>
</div>

<script>
function showMessage(message) {
    document.getElementById('messageText').innerText = message;
    document.getElementById('messageBox').style.display = 'flex';
}
function closeMessage() {
    document.getElementById('messageBox').style.display = 'none';
}
function openReplyForm(id) {
    document.getElementById('contactId').value = id;
    document.getElementById('replyBox').style.display = 'flex';
}
function closeReply() {
    document.getElementById('replyBox').style.display = 'none';
}
</script>
</body>
</html>
