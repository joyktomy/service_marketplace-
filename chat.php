<?php
session_start();
$conn = new mysqli("localhost", "root", "", "servicemarketplace");

if (!isset($_SESSION['id'])) {
    die("You must login first!");
}

$currentUser = $_SESSION['id'];
$currentRole = $_SESSION['role'];


$chatableRole = ($currentRole === 'user') ? 'provider' : 'user';


$users = $conn->query("SELECT id, username, role FROM accounts WHERE role='$chatableRole'");


$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;


$homeLink = "#"; 
if ($currentRole === 'user') $homeLink = "user_home.php";
elseif ($currentRole === 'provider') $homeLink = "provider_home.php";
elseif ($currentRole === 'admin') $homeLink = "admin_dashboard.php";
?>
<html>
<head>
    <title>Internal Chat</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; height: 100vh; display: flex; flex-direction: column; }

   
        .navbar {
            background: #0072ff;
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 { font-size: 18px; margin: 0; }
        .navbar a { color: white; text-decoration: none; font-weight: bold; }
        .navbar a:hover { text-decoration: underline; }

  
        .main { flex: 1; display: flex; height: calc(100vh - 50px); }
        .sidebar { width: 250px; background: #f4f4f4; padding: 20px; border-right: 1px solid #ddd; overflow-y: auto; }
        .chat-box { flex: 1; display: flex; flex-direction: column; }


        .messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #e5ddd5;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.message {
    max-width: 70%;
    padding: 4px 8px;
    display: flex;
    flex-direction: column;
}


.message.mine {
    align-self: flex-end;
    text-align: right;
}


.message.theirs {
    align-self: flex-start;
    text-align: left;
}


.bubble {
    padding: 12px 16px;
    border-radius: 18px;
    font-size: 14px;
    word-break: break-word;
    display: inline-block;
}


.mine .bubble {
    background: #0084ff;
    color: #fff;
    border-bottom-right-radius: 5px;
}


.theirs .bubble {
    background: #ffffff;
    border: 1px solid #cfcfcf;
    color: #000;
    border-bottom-left-radius: 5px;
}


.time {
    font-size: 10px;
    color: #666;
    margin-top: 3px;
}

        form { display: flex; border-top: 1px solid #ddd; background: #fff; }
        form input { flex: 1; padding: 10px; border: none; font-size: 14px; }
        form button { padding: 10px 20px; border: none; background: #0072ff; color: white; cursor: pointer; }
        form button:hover { background: #005fcc; }

        .sidebar h3 { margin-top: 0; }
        .sidebar p a { display: block; padding: 8px 12px; text-decoration: none; color: #0072ff; border-radius: 6px; margin-bottom: 5px; transition: 0.2s; }
        .sidebar p a:hover { background: #0072ff; color: white; }
    </style>
</head>
<body>

<div class="navbar">
    <h1>🧑‍🔧 Service Marketplace</h1>
    <a href="<?php echo $homeLink; ?>">Home</a>
</div>

<div class="main">

    <div class="sidebar">
        <h3>Contacts</h3>
        <?php while ($u = $users->fetch_assoc()): ?>
            <p><a href="chat.php?receiver_id=<?php echo $u['id']; ?>">
                <?php echo htmlspecialchars($u['username']); ?> (<?php echo $u['role']; ?>)
            </a></p>
        <?php endwhile; ?>
    </div>

    <div class="chat-box">
        <div class="messages" id="chat-messages">
            <?php if (!$receiver_id): ?>
                <p>Select a contact to start chatting.</p>
            <?php endif; ?>
        </div>

        <?php if ($receiver_id): ?>
            <form id="chatForm">
                <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
                <input type="text" name="message" placeholder="Type your message..." required>
                <button type="submit">Send</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function loadMessages() {
    let receiver_id = "<?php echo $receiver_id; ?>";
    if (receiver_id != "0") {
        fetch("fetch_messages.php?receiver_id=" + receiver_id)
            .then(response => response.text())
            .then(data => {
                const chatDiv = document.getElementById("chat-messages");
                chatDiv.innerHTML = data;
                chatDiv.scrollTop = chatDiv.scrollHeight;
            });
    }
}
setInterval(loadMessages, 2000);
loadMessages();

document.getElementById("chatForm")?.addEventListener("submit", function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    fetch("send_message.php", { method: "POST", body: formData })
        .then(() => { this.message.value = ""; loadMessages(); });
});
</script>

</body>
</html>
