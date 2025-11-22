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
  <title>Privacy Policy | Service Marketplace</title>
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
    <h1>Privacy Policy</h1>
    <p>At <strong>Service Marketplace</strong>, we respect your privacy and are committed to protecting your personal data. This Privacy Policy explains how we collect, use, and safeguard your information when you use our website and services.</p>

    <h2>1. Information We Collect</h2>
    <p>We may collect the following types of information:</p>
    <ul>
      <li>Personal details (name, email, phone number, address, etc.) during registration.</li>
      <li>Login details and account credentials.</li>
      <li>Service usage information, such as bookings, searches, and preferences.</li>
      <li>Technical data such as IP address, browser type, and device information.</li>
    </ul>

    <h2>2. How We Use Your Information</h2>
    <p>Your data may be used for:</p>
    <ul>
      <li>Providing and improving our services.</li>
      <li>Processing bookings, payments, and transactions.</li>
      <li>Sending important updates, notifications, and offers.</li>
      <li>Ensuring account security and preventing fraud.</li>
    </ul>

    <h2>3. Data Sharing</h2>
    <p>We do not sell your personal information. However, we may share data with:</p>
    <ul>
      <li>Trusted service providers to fulfill your requests.</li>
      <li>Payment gateways for transaction processing.</li>
      <li>Legal authorities, if required by law.</li>
    </ul>

    <h2>4. Cookies</h2>
    <p>We use cookies to enhance your browsing experience, analyze site traffic, and improve our platform.</p>

    <h2>5. Data Security</h2>
    <p>We take reasonable measures to protect your data from unauthorized access, alteration, or disclosure.</p>

    <h2>6. Your Rights</h2>
    <p>You have the right to access, update, or delete your personal data. Please contact us if you wish to exercise these rights.</p>

    <h2>7. Updates to This Policy</h2>
    <p>We may update this Privacy Policy from time to time. Changes will be posted on this page with a revised "Last Updated" date.</p>

    <h2>8. Contact Us</h2>
    <p>If you have any questions about this Privacy Policy, please contact us at:</p>
    <p>Email: support@servicemarketplace.com</p>
    <p>Phone: +91 9633241348</p>

    <footer>
      <p>© 2025 Service Marketplace. All Rights Reserved. | <a href="<?php echo $homeLink; ?>">Home</a></p>
    </footer>
  </div>
</body>
</html>
