<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'provider') {
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

$providerId = $_SESSION['id'];
$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if (!empty($password)) {
        if ($password !== $confirmPassword) {
            $message = "❌ Passwords do not match!";
        } else {
           
            $sql = "UPDATE accounts SET username=?, email=?, phone=?, password=? WHERE id=? AND role='provider'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $username, $email, $phone, $password, $providerId);
            if ($stmt->execute()) {
                $message = "✅ Account updated successfully!";
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
            } else {
                $message = "❌ Error updating account: " . $conn->error;
            }
        }
    } else {
     
        $sql = "UPDATE accounts SET username=?, email=?, phone=? WHERE id=? AND role='provider'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $email, $phone, $providerId);
        if ($stmt->execute()) {
            $message = "✅ Account updated successfully!";
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
        } else {
            $message = "❌ Error updating account: " . $conn->error;
        }
    }
}


$sql = "SELECT * FROM accounts WHERE id=? AND role='provider'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $providerId);
$stmt->execute();
$result = $stmt->get_result();
$provider = $result->fetch_assoc();
?>
<html>
<head>
  <title>Provider Account</title>
  <style>
    body { font-family: "Segoe UI", sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
    .navbar {
      background: #fff;
      padding: 12px 40px;
      display: flex; justify-content: space-between; align-items: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .navbar h1 { color: #0072ff; margin: 0; font-size: 20px; }
    .navbar a { text-decoration: none; color: #0072ff; font-weight: bold; }

    .account-container {
      max-width: 500px;
      margin: 40px auto;
      background: #fff;
      padding: 25px 30px;
      border-radius: 12px;
      box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    }

    .account-container h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #0072ff;
      font-size: 22px;
      font-weight: 600;
    }

    .message { margin-bottom: 15px; text-align: center; font-weight: bold; }
    .message.success { color: green; }
    .message.error { color: red; }

    form label {
      display: block;
      margin-top: 12px;
      margin-bottom: 5px;
      font-size: 14px;
      color: #333;
      font-weight: 500;
    }

    form input {
      width: 98%;
      padding: 12px 14px;
      border: 1px solid #ccc;
      border-radius: 8px;
      margin-bottom: 12px;
      font-size: 14px;
      transition: 0.2s;
    }

    form input:focus {
      border-color: #0072ff;
      box-shadow: 0 0 5px rgba(0,114,255,0.2);
      outline: none;
    }

    form button {
      width: 100%;
      padding: 14px;
      border: none;
      background: #0072ff;
      color: #fff;
      border-radius: 8px;
      font-size: 15px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.2s;
    }

    form button:hover {
      background: #005fcc;
    }
  </style>
</head>
<body>
  <div class="navbar">
    <h1>🧑‍🔧 SERVICE MARKETPLACE</h1>
    <a href="provider_home.php">Home</a>
  </div>

  <div class="account-container">
    <h2>My Account</h2>
    <?php if (!empty($message)) : ?>
      <p class="message <?php echo (strpos($message, '✅') !== false) ? 'success' : 'error'; ?>">
        <?php echo $message; ?>
      </p>
    <?php endif; ?>
    <form method="POST">
      <label>Username</label>
      <input type="text" name="username" value="<?php echo htmlspecialchars($provider['username']); ?>" required>

      <label>Email</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($provider['email']); ?>" required>

      <label>Phone</label>
      <input type="text" name="phone" value="<?php echo htmlspecialchars($provider['phone']); ?>" required>

      <label>New Password</label>
      <input type="password" name="password" placeholder="Leave blank to keep current">

      <label>Confirm Password</label>
      <input type="password" name="confirm_password" placeholder="Re-enter new password">

      <button type="submit">Update Account</button>
    </form>
  </div>
</body>
</html>
