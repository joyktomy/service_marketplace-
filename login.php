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

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = strtolower($_POST['role']); 
    $input = trim($_POST['email_or_username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM accounts WHERE (username = ? OR email = ?) AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $input, $input, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

    
        if ($password === $user['password']) {
            $_SESSION['id'] = $user['id'];          
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($role === 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($role === 'provider') {
                header("Location: provider_home.php");
            } else {
                header("Location: user_home.php");
            }
            exit();
        } else {
            $error = "❌ Incorrect password";
        }
    } else {
        $error = "❌ User not found with role $role";
    }
}
?>

<html>
<head>
  <title>Service Marketplace - Login</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: "Segoe UI", sans-serif; }

    body {
      background: linear-gradient(135deg, #00c6ff, #0072ff);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      flex-direction: column;
    }

    .navbar {
      position: fixed;
      top: 0; left: 0;
      width: 100%;
      background: white;
      padding: 12px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .navbar h1 {
      color: #0072ff;
      font-size: 20px;
      font-weight: 600;
      display: flex; align-items: center; gap: 8px;
    }
    .navbar a {
      margin-left: 20px;
      text-decoration: none;
      font-weight: 500;
      color: #333;
    }
    .navbar a:hover { color: #0072ff; }

    .login-container {
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      width: 450px;
      margin-top: 80px;
    }
    .login-container h2 { text-align: center; margin-bottom: 20px; }

    .error { color: red; text-align: center; margin-bottom: 10px; font-weight: bold; }

    .form-group {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
    }
    .form-group label {
      width: 140px;
      font-size: 14px;
      color: #444;
    }
    .form-group input {
      flex: 1;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    .form-group input:focus { border-color: #0072ff; }

    .role-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-bottom: 20px;
    }
    .role-buttons input[type="radio"] {
      display: none;
    }
    .role-buttons label {
      padding: 10px 20px;
      border: 2px solid #0072ff;
      border-radius: 6px;
      background: white;
      color: #0072ff;
      cursor: pointer;
      font-weight: bold;
      transition: all 0.3s ease;
    }
    .role-buttons input[type="radio"]:checked + label {
      background: #0072ff;
      color: white;
    }

    .btn-login { 
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background: #0072ff;
      color: white;
      font-size: 16px;
      cursor: pointer;
      margin-top: 10px;
    }
    .btn-login:hover { background: #005fcc; }

    .extra-links { text-align: center; margin-top: 15px; font-size: 14px; }
    .extra-links a { color: #0072ff; text-decoration: none; }
    .extra-links a:hover { text-decoration: underline; }
  </style>
</head>
<body>

  <div class="navbar">
    <h1>🧑‍🔧 SERVICE MARKETPLACE</h1>
    <div>
      <a href="user_home.php">Home</a>
    </div>
  </div>


  <div class="login-container">
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
      <div class="role-buttons">
        <input type="radio" id="user" name="role" value="user" required>
        <label for="user">User</label>

        <input type="radio" id="provider" name="role" value="provider">
        <label for="provider">Provider</label>

        <input type="radio" id="admin" name="role" value="admin">
        <label for="admin">Admin</label>
      </div>

      <div class="form-group">
        <label>Email/Username</label>
        <input type="text" name="email_or_username" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>

      <button type="submit" class="btn-login">Login</button>
      <div class="extra-links">
        Don’t have an account? <a href="register.php">Sign Up</a>
      </div>
    </form>
  </div>
</body>
</html>
