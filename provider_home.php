<?php
session_start();

$providerName = isset($_SESSION['provider_name']) ? $_SESSION['provider_name'] : "Provider";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Provider Home - Service Marketplace</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: "Segoe UI", sans-serif; }

  body {
    background: linear-gradient(135deg, #00c6ff, #0072ff);
    min-height: 100vh;
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
    z-index: 1000;
  }
  .navbar h1 { color: #0072ff; font-size: 20px; font-weight: 600; }
  .menu-btn { font-size: 22px; background: none; border: none; cursor: pointer; color: #0072ff; }

  .side-menu {
    position: fixed;
    top: 0;
    right: -260px;
    width: 260px;
    height: 100%;
    background: #fff;
    box-shadow: -2px 0 6px rgba(0,0,0,0.2);
    padding: 60px 20px;
    transition: right 0.3s ease;
    z-index: 1200;
  }
  .side-menu.active { right: 0; }
  .side-menu a { display: block; margin: 15px 0; color: #0072ff; font-weight: 500; text-decoration: none; }
  .side-menu a:hover { text-decoration: underline; }

  .content {
    margin-top: 100px;
    padding: 40px;
    transition: margin-right 0.3s ease;
    color: white;
    text-align: center;
  }
  .side-menu.active ~ .content { margin-right: 260px; }

  .dashboard {
    margin-top: 40px; 
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    justify-content: center;
  }

  .card {
    flex: 1 1 250px;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    text-decoration: none;
    color: #333;
    transition: transform 0.2s, box-shadow 0.2s;
    max-width: 300px;
  }

  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
  }
</style>
</head>
<body>

  <div class="navbar">
    <h1>🧑‍🔧 SERVICE MARKETPLACE</h1>
    <button class="menu-btn" onclick="toggleMenu()">☰</button>
  </div>

 <div class="side-menu" id="sideMenu">
    <?php if (isset($_SESSION['id'])): ?>
        <p style="font-weight:bold; margin-bottom:15px;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <a href="provider_home.php">🏠 Home</a>
        <a href="provider_account.php">👤 My Account</a>
        <a href="contact.php">📨 Contact Us</a>
        <a href="login.php">🚪 Logout</a>
    <?php else: ?>
        <a href="login.php">🔑 Login</a>
        <a href="register.php">📝 Sign Up</a>
        <a href="contact.php">📨 Contact Us</a>
    <?php endif; ?>
    <a href="aboutus.php">ℹ️ About Us</a>
    <a href="privacy.php">📜 Privacy Policy</a>
    <a href="terms.php">⚖️ Terms & Conditions</a>
</div>

  <div class="content">
    <h2>Welcome, <?php echo htmlspecialchars($providerName); ?>!</h2>
    <p>Manage your services and respond to client requests.</p>

    <div class="dashboard">
      <a href="service.php" class="card">
        <h3>📋 My Services</h3>
        <p>Add, update, or remove the services you provide.</p>
      </a>

      <a href="chat.php" class="card">
        <h3>📨 Requests</h3>
        <p>View and respond to client service requests.</p>
      </a>
    </div>
  </div>

  <script>
    function toggleMenu() {
      document.getElementById("sideMenu").classList.toggle("active");
    }
  </script>

</body>
</html>
