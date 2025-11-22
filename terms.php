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
  <title>Terms & Conditions | Service Marketplace</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: "Segoe UI", sans-serif; }
    body {
      background: #f9f9f9;
      color: #333;
      line-height: 1.6;
      padding: 40px;
    }
    h1, h2 {
      color: #0072ff;
      margin-bottom: 15px;
    }
    p {
      margin-bottom: 15px;
    }
    .container {
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    ul {
      margin: 10px 0 20px 20px;
    }
    footer {
      text-align: center;
      margin-top: 40px;
      font-size: 14px;
      color: #777;
    }
    a {
      color: #0072ff;
      text-decoration: none;
    }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Terms & Conditions</h1>
    <p>Welcome to <strong>Service Marketplace</strong>. By accessing or using our website and services, you agree to comply with the following Terms & Conditions. Please read them carefully.</p>

    <h2>1. Acceptance of Terms</h2>
    <p>By registering, logging in, or using our platform, you agree to be bound by these Terms & Conditions. If you do not agree, please stop using our services immediately.</p>

    <h2>2. User Accounts</h2>
    <ul>
      <li>You must provide accurate and complete information during registration.</li>
      <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
      <li>You are solely responsible for any activity that occurs under your account.</li>
    </ul>

    <h2>3. Service Providers</h2>
    <ul>
      <li>Providers must ensure that the services listed are legal, safe, and comply with local regulations.</li>
      <li>Service Marketplace is not responsible for the quality, safety, or legality of services provided.</li>
    </ul>
    
    <h2>4. Prohibited Activities</h2>
    <p>Users are prohibited from:</p>
    <ul>
      <li>Posting false or misleading information.</li>
      <li>Engaging in fraud, spam, or illegal activities.</li>
      <li>Violating intellectual property rights of others.</li>
    </ul>

    <h2>5. Limitation of Liability</h2>
    <p>We act only as a marketplace platform. We are not responsible for any disputes, damages, or losses that may occur between users and providers.</p>

    <h2>6. Termination</h2>
    <p>We reserve the right to suspend or terminate your account if you violate these Terms & Conditions.</p>

    <h2>7. Modifications</h2>
    <p>We may update these Terms & Conditions at any time. Continued use of our services after updates indicates your acceptance of the revised terms.</p>

    <h2>8. Governing Law</h2>
    <p>These Terms & Conditions shall be governed by and interpreted in accordance with the laws of India (or update with your country’s law).</p>

    <h2>9. Contact Us</h2>
    <p>If you have any questions about these Terms & Conditions, contact us at:</p>
    <p>Email: support@servicemarketplace.com</p>
    <p>Phone: +91 9633241348</p>

    <footer>
      <p>© 2025 Service Marketplace. All Rights Reserved. | <a href="<?php echo $homeLink; ?>">Home</a></p>
    </footer>
  </div>
</body>
</html>















