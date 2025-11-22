<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "servicemarketplace");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM reports WHERE id = $id");
    echo "<script>alert('Report deleted successfully!'); window.location='manage_reports.php';</script>";
    exit();
}

if (isset($_GET['review'])) {
    $id = intval($_GET['review']);
    $conn->query("UPDATE reports SET status='Reviewed' WHERE id = $id");
    echo "<script>alert('Report marked as reviewed.'); window.location='manage_reports.php';</script>";
    exit();
}

if (isset($_GET['suspend'])) {
    $provider_id = intval($_GET['suspend']);
    $conn->query("UPDATE accounts SET status='blacklisted' WHERE id = $provider_id");
    echo "<script>alert('Provider suspended successfully!'); window.location='manage_reports.php';</script>";
    exit();
}

if (isset($_GET['unsuspend'])) {
    $provider_id = intval($_GET['unsuspend']);
    $conn->query("UPDATE accounts SET status='active' WHERE id = $provider_id");
    echo "<script>alert('Provider unsuspended successfully!'); window.location='manage_reports.php';</script>";
    exit();
}


if (isset($_POST['reply_to'])) {
    $reporter_id = intval($_POST['reply_to']);
    $admin_id = $_SESSION['id'];
    $message = "Hello! We’ve reviewed your report and taken the necessary action. Thank you for helping keep our platform safe.";

    $stmt = $conn->prepare("INSERT INTO chats (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $admin_id, $reporter_id, $message);
    $stmt->execute();

    echo "<script>alert('Automated reply sent successfully to reporter!'); window.location='manage_reports.php';</script>";
    exit();
}


$sql = "SELECT r.*, 
        s.name AS service_name, 
        a.username AS reporter_name, 
        a.id AS reporter_id,
        p.username AS provider_name,
        p.id AS provider_id,
        p.status AS provider_status
        FROM reports r
        JOIN services s ON r.service_id = s.id
        JOIN accounts a ON r.reported_by = a.id
        JOIN accounts p ON s.provider_id = p.id
        ORDER BY r.created_at DESC";

$reports = $conn->query($sql);
?>

<html>
<head>
<meta charset="UTF-8">
<title>Manage Reports</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
    body { background: #f4f6fc; display: flex; }

    .sidebar {
        width: 250px;
        background: #1e1f26;
        color: white;
        height: 100vh;
        padding: 20px;
        position: fixed;
    }
    .sidebar h2 { text-align: center; color: #00c6ff; margin-bottom: 25px; }
    .sidebar a {
        display: block; color: white; text-decoration: none;
        padding: 12px 15px; border-radius: 6px; margin-bottom: 8px;
        transition: 0.3s;
    }
    .sidebar a:hover, .sidebar a.active { background: #00c6ff; color: black; }

    .main-content {
        margin-left: 250px;
        width: calc(100% - 250px);
        padding: 30px;
    }
    .topbar {
        background: white;
        padding: 15px 30px;
        display: flex; justify-content: space-between;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        border-radius: 8px;
    }
    .topbar h1 { font-size: 20px; color: #0072ff; }
    .topbar a { color: #0072ff; font-weight: bold; text-decoration: none; }

    h2 { color: #0072ff; margin-top: 25px; margin-bottom: 15px; }

    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    table th, table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    table th { background: #0072ff; color: white; }
    table tr:hover { background: #f9f9f9; }

    .btn {
        padding: 6px 10px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
        color: white;
        margin: 2px;
        display: inline-block;
    }
    .btn.view { background: #00c6ff; }
    .btn.details { background: #6f42c1; }
    .btn.review { background: #28a745; }
    .btn.delete { background: #dc3545; }
    .btn.suspend { background: #ff9800; }
    .btn.unsuspend { background: #4caf50; }
    .btn.reply { background: #0072ff; }
    .btn:hover { opacity: 0.85; }

    .status {
        font-weight: bold;
        padding: 4px 10px;
        border-radius: 6px;
    }
    .status.Pending { background: #ffe08a; color: #b8860b; }
    .status.Reviewed { background: #d4edda; color: #155724; }


    .modal {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.6);
        justify-content: center;
        align-items: center;
    }
    .modal-content {
        background: white;
        padding: 25px;
        width: 460px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn { from {opacity: 0;} to {opacity: 1;} }
    .modal h3 { color: #0072ff; margin-bottom: 15px; }
    .modal p { margin: 8px 0; }
    .close {
        float: right;
        font-weight: bold;
        color: red;
        cursor: pointer;
    }
    .close:hover { color: darkred; }
</style>
</head>
<body>
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">🏠 Dashboard</a>
    <a href="manage_users.php">👥 Manage Users</a>
    <a href="manage_providers.php">🔧 Manage Providers</a>
    <a href="manage_services.php">📦 Manage Services</a>
    <a href="view_chats.php">💬 View Chats</a>
    <a href="view_contacts.php">📨 Contact Messages</a>
    <a href="manage_reports.php" class="active">🚫 Manage Reports</a>
    <a href="admin_dashboard.php?logout=1">🚪 Logout</a>
</div>


<div class="main-content">
    <div class="topbar">
        <h1>Manage Reported Services</h1>
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>

    <h2>Reported Services</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Service</th>
            <th>Provider</th>
            <th>Reported By</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>

        <?php if ($reports->num_rows > 0): ?>
            <?php while($r = $reports->fetch_assoc()): ?>
                <tr>
                    <td><?= $r['id']; ?></td>
                    <td><?= htmlspecialchars($r['service_name']); ?></td>
                    <td><?= htmlspecialchars($r['provider_name']); ?></td>
                    <td><?= htmlspecialchars($r['reporter_name']); ?></td>
                    <td><span class="status <?= $r['status']; ?>"><?= $r['status']; ?></span></td>
                    <td><?= date('Y-m-d H:i', strtotime($r['created_at'])); ?></td>
                    <td>
                        <button class="btn details" onclick='viewDetails(<?= json_encode($r); ?>)'>Details</button>
                        <a href="view_service.php?id=<?= $r['service_id']; ?>" class="btn view" target="_blank">View</a>
                        <?php if ($r['status'] == 'Pending'): ?>
                            <a href="?review=<?= $r['id']; ?>" class="btn review">Mark Reviewed</a>
                        <?php endif; ?>
                        <a href="?delete=<?= $r['id']; ?>" class="btn delete" onclick="return confirm('Delete this report?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" style="text-align:center;">No reports found</td></tr>
        <?php endif; ?>
    </table>
</div>


<div class="modal" id="reportModal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('reportModal').style.display='none'">&times;</span>
        <h3>Report Details</h3>
        <p><strong>ID:</strong> <span id="rep_id"></span></p>
        <p><strong>Service:</strong> <span id="rep_service"></span></p>
        <p><strong>Provider:</strong> <span id="rep_provider"></span></p>
        <p><strong>Reported By:</strong> <span id="rep_reporter"></span></p>
        <p><strong>Reason:</strong> <span id="rep_reason"></span></p>
        <p><strong>Status:</strong> <span id="rep_status"></span></p>
        <p><strong>Date:</strong> <span id="rep_date"></span></p>

        <form method="POST" style="margin-top:15px;">
            <input type="hidden" name="reply_to" id="reply_to">
            <button type="submit" class="btn reply">💬 Send Automated Reply</button>
        </form>
    </div>
</div>

<script>
function viewDetails(data) {
    document.getElementById("reportModal").style.display = "flex";
    document.getElementById("rep_id").innerText = data.id;
    document.getElementById("rep_service").innerText = data.service_name;
    document.getElementById("rep_provider").innerText = data.provider_name;
    document.getElementById("rep_reporter").innerText = data.reporter_name;
    document.getElementById("rep_reason").innerText = data.reason;
    document.getElementById("rep_status").innerText = data.status;
    document.getElementById("rep_date").innerText = data.created_at;
    document.getElementById("reply_to").value = data.reporter_id;
}

window.onclick = function(event) {
    let modal = document.getElementById("reportModal");
    if (event.target == modal) modal.style.display = "none";
};
</script>

</body>
</html>
