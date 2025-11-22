<?php
session_start();
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
  <title>About Us - Service Marketplace</title>
  <style>
    body { 
      background: linear-gradient(135deg, #00c6ff, #0072ff); 
      min-height: 100vh; 
      margin:0; 
      font-family: "Segoe UI", sans-serif; 
      color: #333;
    }

    .navbar {
      background: #fff;
      padding: 12px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .navbar h1 { color: #0072ff; font-size: 20px; font-weight: 600; }
    .navbar a {
      color: #0072ff;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
    }
    .navbar a:hover { color: #005fcc; }

    .container {
      max-width: 900px;
      margin: 60px auto;
      background: #fff;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .container h2 { color: #0072ff; margin-bottom: 20px; }
    .container p { margin-bottom: 15px; line-height: 1.8; font-size: 16px; }

    .team-section {
      margin-top: 30px;
    }
    .team-members {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }
    .member {
      flex: 1 1 calc(33.333% - 20px);
      background: #f9f9f9;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .member img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      margin-bottom: 10px;
    }
    .member h4 { color: #0072ff; margin-bottom: 5px; }
    .member p { font-size: 14px; color: #666; }
        footer {
      text-align: center;
      margin-top: 40px;
      font-size: 14px;
      color: #777;
    }
  </style>
</head>
<body>

  <div class="navbar">
    <h1>🧑‍🔧 SERVICE MARKETPLACE</h1>
    <a href="<?php echo $homeLink; ?>">Home</a>
  </div>


  <div class="container">
    <h2>About Us</h2>
    <p>Welcome to <strong>Service Marketplace</strong> — your trusted platform to connect with local service providers. Our mission is to make everyday services like plumbing, electrical work, cleaning, carpentry, and more accessible at your fingertips.</p>

    <p>We believe in <strong>quality, trust, and convenience</strong>. Whether you’re a customer looking for reliable services or a professional wanting to showcase your skills, our platform bridges the gap with ease and transparency.</p>

    <p>With verified providers, secure bookings, and transparent pricing, we aim to bring professionalism into local services, ensuring peace of mind for both customers and providers.</p>

    <div class="team-section">
      <h2>Our Team</h2>
      <div class="team-members">
        <div class="member">
          <img src="https://via.placeholder.com/80" alt="Team Member">
          <h4>Rahul Sharma</h4>
          <p>Founder & CEO</p>
        </div>
        <div class="member">
          <img src="https://via.placeholder.com/80" alt="Team Member">
          <h4>Priya Verma</h4>
          <p>Operations Head</p>
        </div>
        <div class="member">
          <img src="https://via.placeholder.com/80" alt="Team Member">
          <h4>Arjun Mehta</h4>
          <p>Tech Lead</p>
        </div>
      </div>
    </div>
    <footer>
      <p>© 2025 Service Marketplace. All Rights Reserved. | <a href="<?php echo $homeLink; ?>">Home</a></p>
    </footer>
  </div>

</body>
</html>
