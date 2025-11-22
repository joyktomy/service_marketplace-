<?php
session_start();
$host = "localhost";
$user = "root";
$password = "";
$database = "servicemarketplace";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("No service selected.");
}

$service_id = intval($_GET['id']);

// ✅ Fixed join using provider_id
$sql = "SELECT s.*, a.username AS provider_name, a.email AS provider_email 
        FROM services s
        JOIN accounts a ON s.provider_id = a.id
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Service not found.");
}

$service = $result->fetch_assoc();
?>


<html>
<head>
    <title>View Service</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            color: #333;
            margin: 0; padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .profile-header img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 12px;
            border: 3px solid #0072ff;
        }
        .profile-header h2 {
            margin: 0;
            color: #0072ff;
        }
        .details { margin-top: 20px; }
        .details p { margin: 8px 0; font-size: 15px; }
        .details strong { color: #0072ff; }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #0072ff;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .btn:hover { background: #005fcc; }
        .btn.whatsapp { background: #25D366; }
        .btn.whatsapp:hover { background: #1ebe5d; }
        .btn.login { background: #ff9800; }
        .btn.login:hover { background: #e68900; }
        .btn.report { background: #dc3545; }
        .btn.report:hover { background: #b02a37; }

        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #fff;
            padding: 20px;
            width: 350px;
            border-radius: 10px;
        }
        textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            resize: none;
        }
        .close {
            float: right;
            cursor: pointer;
            font-weight: bold;
            color: red;
        }
        .close:hover { color: darkred; }
    </style>
</head>
<body>

<div class="container">
    <div class="profile-header">
        <?php if (!empty($service['photo'])): ?>
            <img src="<?= htmlspecialchars($service['photo']); ?>" alt="Provider Photo">
        <?php else: ?>
            <img src="https://via.placeholder.com/150" alt="No Photo">
        <?php endif; ?>
        <div>
            <h2><?= htmlspecialchars($service['name']); ?></h2>
            <p><strong>Service:</strong> <?= htmlspecialchars($service['service_type']); ?></p>
            <p><strong>Rate:</strong> ₹<?= htmlspecialchars($service['rate']); ?> / hr</p>
            <p><strong>Provider:</strong> <?= htmlspecialchars($service['provider_name']); ?> (<?= htmlspecialchars($service['provider_email']); ?>)</p>
        </div>
    </div>

    <div class="details">
        <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($service['description'])); ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($service['phone']); ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($service['address']); ?></p>
        <p><strong>Pincode:</strong> <?= htmlspecialchars($service['pincode']); ?></p>
        <p><strong>Gender:</strong> <?= htmlspecialchars($service['gender']); ?></p>
    </div>

    <a href="user_home.php" class="btn">⬅ Back to Search</a>

    <?php if (isset($_SESSION['id'])): ?>
        <?php
            $fullPhone = preg_replace('/[^0-9]/', '', $service['country_code'] . $service['phone']);
            $whatsappLink = "https://wa.me/" . $fullPhone . "?text=" . urlencode("Hi, I'm interested in your service.");
        ?>
        <a href="<?= $whatsappLink; ?>" target="_blank" class="btn whatsapp">💬 Chat on WhatsApp</a>
        <button class="btn report" onclick="document.getElementById('reportModal').style.display='flex'">🚫 Report Service</button>
    <?php else: ?>
        <a href="login.php" class="btn login">🔑 Login to Chat or Report</a>
    <?php endif; ?>
</div>


<div class="modal" id="reportModal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('reportModal').style.display='none'">&times;</span>
        <h3>Report This Service</h3>
        <form action="report_service.php" method="POST">
            <input type="hidden" name="service_id" value="<?= $service_id; ?>">
            <textarea name="reason" required placeholder="Describe the issue..."></textarea>
            <br><br>
            <button type="submit" style="padding:10px 18px; background:red; color:white; border:none; border-radius:6px;">Submit Report</button>
        </form>
    </div>
</div>

<script>
window.onclick = function(e) {
    let modal = document.getElementById('reportModal');
    if (e.target === modal) modal.style.display = 'none';
}
</script>

</body>
</html>
