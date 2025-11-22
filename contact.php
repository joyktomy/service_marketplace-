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

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $msg = $conn->real_escape_string($_POST['message']);

    $sql = "INSERT INTO contact_messages (name, email, message) VALUES ('$name', '$email', '$msg')";
    
    if ($conn->query($sql) === TRUE) {
        $message = "✅ Message sent successfully!";
    } else {
        $message = "❌ Error: " . $conn->error;
    }
}

$homeLink = "#"; 
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'user') {
        $homeLink = "user_home.php";
    } elseif ($_SESSION['role'] === 'provider') {
        $homeLink = "provider_home.php";
    } elseif ($_SESSION['role'] === 'admin') {
        $homeLink = "admin_home.php";
    }
}
?>
<html>
<head>
  <title>Contact Us - Service Marketplace</title>
  <style>
    body { background: linear-gradient(135deg, #00c6ff, #0072ff); min-height: 100vh; margin:0; font-family: "Segoe UI", sans-serif; }
    .navbar { background: #fff; padding: 12px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .navbar h1 { color: #0072ff; font-size: 20px; font-weight: 600; }
    .navbar a { color: #0072ff; text-decoration: none; font-weight: 500; transition: color 0.3s; }
    .navbar a:hover { color: #005fcc; }
    .container { max-width: 700px; margin: 80px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
    .container h2 { margin-bottom: 20px; color: #0072ff; }
    .contact-info strong { color: #0072ff; }
    form { margin-top: 30px; display: flex; flex-direction: column; gap: 15px; }
    input, textarea { padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; width: 100%; }
    textarea { resize: none; height: 120px; }
    button { background: #0072ff; color: #fff; padding: 12px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: 600; }
    button:hover { background: #005fcc; }
    .msg { margin-top: 15px; font-weight: bold; color: green; }
  </style>
</head>
<body>

  <!-- Navbar -->
  <div class="navbar">
    <h1>🧑‍🔧 SERVICE MARKETPLACE</h1>
    <a href="<?php echo $homeLink; ?>">Home</a>
  </div>

  <!-- Contact Section -->
  <div class="container">
    <h2>📞 Contact Us</h2>
    <p>If you have any queries or need support, feel free to reach us:</p>
    <div class="contact-info">
      <p><strong>Email:</strong> support@servicemarketplace.com</p>
      <p><strong>Phone:</strong> +91 98765 43210</p>
      <p><strong>Address:</strong> 123 Service Lane, Tech City, India</p>
    </div>

    <!-- Contact Form -->
    <form method="POST">
      <input type="text" name="name" placeholder="Your Name" required>
      <input type="email" name="email" placeholder="Your Email" required>
      <textarea name="message" placeholder="Your Message" required></textarea>
      <button type="submit">Send Message</button>
    </form>

    <?php if ($message) { echo "<p class='msg'>$message</p>"; } ?>
  </div>

</body>
</html>
