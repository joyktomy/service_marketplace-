<?php
session_start();


$logout_message = "";
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    $logout_message = "You have been logged out successfully!";
}


$host = "localhost";
$user = "root";
$password = "";
$database = "servicemarketplace";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$results = null;
$total_results = 0;

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['service_type']) && $_GET['service_type'] !== "") {
    $service = $_GET['service_type'];
    $pincode = $_GET['pincode'];
    $gender = $_GET['gender'];

    $sql = "SELECT id, name, service_type, rate, description, photo 
            FROM services 
            WHERE service_type LIKE ? AND pincode LIKE ? AND gender LIKE ?";

    $stmt = $conn->prepare($sql);
    $service_like = "%$service%";
    $pincode_like = "%$pincode%";
    $gender_like = "%$gender%";
    $stmt->bind_param("sss", $service_like, $pincode_like, $gender_like);
    $stmt->execute();
    $results = $stmt->get_result();
    $total_results = $results->num_rows;
}
?>
<html>
<head>
<title>User Home Page</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: "Segoe UI", sans-serif; }
  body { background: linear-gradient(135deg, #00c6ff, #0072ff); min-height: 100vh; }

  .navbar {
    position: fixed; top: 0; left: 0; width: 100%;
    background: white; padding: 12px 40px;
    display: flex; justify-content: space-between; align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 1000;
  }
  .navbar h1 { color: #0072ff; font-size: 20px; font-weight: 600; }
  .menu-btn { font-size: 22px; background: none; border: none; cursor: pointer; color: #0072ff; }

  .side-menu {
    position: fixed; top: 0; right: -260px; width: 260px; height: 100%;
    background: #fff; box-shadow: -2px 0 6px rgba(0,0,0,0.2);
    padding: 60px 20px; transition: right 0.3s ease; z-index: 1200;
  }
  .side-menu.active { right: 0; }
  .side-menu a { display: block; margin: 15px 0; color: #0072ff; font-weight: 500; text-decoration: none; }
  .side-menu a:hover { text-decoration: underline; }

  .content { margin-top: 120px; padding: 20px; display: flex; flex-direction: column; align-items: center; gap: 40px; }

  .logout-message {
    background: #ffffffcc; color: #0072ff; padding: 12px 25px; border-radius: 8px;
    font-weight: bold; text-align: center; box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    opacity: 1; transition: opacity 1s ease-out;
  }

  .fade-out { opacity: 0; }

 

  .search-boxes {
    display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;
    background: #fff; padding: 20px; border-radius: 12px; max-width: 900px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  }
  .search-boxes input, .search-boxes select {
    padding: 12px; border-radius: 6px; border: 1px solid #ccc; width: 220px; font-size: 14px;
  }
  .search-boxes input:focus, .search-boxes select:focus { border-color: #0072ff; outline: none; }
  .btn-search { padding: 12px 20px; background: #0072ff; border: none; border-radius: 6px; color: white; font-weight: bold; cursor: pointer; }
  .btn-search:hover { background: #005fcc; }

  .results-container { width: 100%; max-width: 900px; display: flex; flex-direction: column; gap: 15px; }
  .results-count { color: white; font-weight: bold; margin-bottom: 10px; }
  .ad-card { background: #fff; display: flex; align-items: center; padding: 15px; border-radius: 10px; box-shadow: 0 3px 8px rgba(0,0,0,0.1); justify-content: space-between; }
  .ad-left img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; }
  .ad-middle { flex: 1; margin-left: 20px; }
  .ad-middle h3 { margin: 0; color: #0072ff; }
  .ad-middle p { margin: 3px 0; color: #333; font-size: 14px; }
  .ad-right a { background: #0072ff; color: #fff; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 14px; }
  .ad-right a:hover { background: #005fcc; }
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
        <a href="user_home.php">🏠 Home</a>
        <a href="user_account.php">👤 My Account</a>
        <a href="chat.php">💬 Chats</a>
        <a href="contact.php">📨 Contact Us</a>
        <a href="privacy.php">📜 Privacy Policy</a>
        <a href="terms.php">⚖️ Terms & Conditions</a>
        <a href="user_home.php?logout=1">🚪 Logout</a>
    <?php else: ?>
        <a href="login.php">🔑 Login</a>
        <a href="register.php">📝 Sign Up</a>
        <a href="contact.php">📨 Contact Us</a>
        <a href="aboutus.php">ℹ️ About Us</a>
        <a href="privacy.php">📜 Privacy Policy</a>
        <a href="terms.php">⚖️ Terms & Conditions</a>
    <?php endif; ?>
  </div>

  <div class="content">
    <?php if ($logout_message): ?>
      <div class="logout-message" id="logoutMsg"><?php echo $logout_message; ?></div>
    <?php endif; ?>


 

    <form class="search-boxes" method="GET" action="">
      <select name="service_type" required>
        <option value="">Select Service</option>
        <option>Plumbing</option>
        <option>Electrician</option>
        <option>Cleaning</option>
        <option>Carpentry</option>
        <option>Painting</option>
        <option>Appliance Repair</option>
        <option>Pest Control</option>
        <option>Gardening</option>
      </select>
      <input type="text" name="pincode" placeholder="Pincode" required>
      <select name="gender">
        <option value="">Any Gender</option>
        <option>Male</option>
        <option>Female</option>
        <option>Other</option>
      </select>
      <button class="btn-search" type="submit">Search</button>
    </form>

    <?php if (!empty($results) && $total_results > 0): ?>
      <div class="results-container">
        <p class="results-count"><?php echo $total_results; ?> services found</p>
        <?php while($row = $results->fetch_assoc()): ?>
          <div class="ad-card">
            <div class="ad-left">
              <img src="<?php echo htmlspecialchars($row['photo'] ?? 'https://via.placeholder.com/100'); ?>" alt="Provider">
            </div>
            <div class="ad-middle">
              <h3><?php echo htmlspecialchars($row['name']); ?></h3>
              <p>Service: <?php echo htmlspecialchars($row['service_type']); ?></p>
              <p>Rate/hr: ₹<?php echo htmlspecialchars($row['rate']); ?></p>
            </div>
            <div class="ad-right">
              <a href="view_service.php?id=<?php echo $row['id']; ?>">View More</a>
              <?php if (isset($_SESSION['id'])): ?>
                <a href="chat.php?provider_id=<?php echo $row['id']; ?>">Chat</a>
              <?php else: ?>
                <a href="login.php">Login to Chat</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['service_type'])): ?>
      <p style="color:white; font-weight:bold;">No services found for your search.</p>
    <?php endif; ?>
  </div>

  <script>
    function toggleMenu() {
      document.getElementById("sideMenu").classList.toggle("active");
    }

    window.onload = function() {
      const msg = document.getElementById('logoutMsg');
      if (msg) {
        setTimeout(() => msg.classList.add('fade-out'), 3000);
        setTimeout(() => msg.remove(), 4000);
      }
    };
  </script>
</body>
</html>
