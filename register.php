<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "servicemarketplace";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); 
    $phone = trim($_POST['phone']);

    
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $error = "Phone number must be exactly 10 digits.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{6,}$/", $password)) {
        $error = "Password must include 1 lowercase, 1 uppercase, 1 special symbol, and be 6+ characters.";
    } else {
       
        $check = $conn->prepare("SELECT * FROM accounts WHERE username=? OR email=?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Username or Email already exists!";
        } else {
           
            $sql = "INSERT INTO accounts (username, email, password, phone, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $email, $password, $phone, $role);

            if ($stmt->execute()) {
                $success = true;
            } else {
                $error = "Database error: " . $conn->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Service Market - Register</title>
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
    .navbar h1 { color: #0072ff; font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .navbar a { margin-left: 20px; text-decoration: none; font-weight: 500; color: #333; }
    .navbar a:hover { color: #0072ff; }

    .register-container {
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      width: 450px;
      margin-top: 80px;
    }
    .register-container h2 { text-align: center; margin-bottom: 20px; }
    .form-group { display: flex; align-items: center; margin-bottom: 15px; }
    .form-group label { width: 140px; font-size: 14px; color: #444; }
    .form-group input { flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 6px; }
    .form-group input:focus { border-color: #0072ff; }

    .role-buttons { display: flex; justify-content: center; gap: 15px; margin-bottom: 20px; }
    .role-buttons button {
      flex: 1; padding: 10px; border: 2px solid #0072ff; border-radius: 6px;
      background: white; color: #0072ff; font-weight: bold; cursor: pointer; transition: all 0.3s;
    }
    .role-buttons button.active, .role-buttons button:hover { background: #0072ff; color: white; }

    .btn-register {
      width: 100%; padding: 12px; border: none; border-radius: 8px;
      background: #0072ff; color: white; font-size: 16px; cursor: pointer; margin-top: 10px;
    }
    .btn-register:hover { background: #005fcc; }
    .extra-links { text-align: center; margin-top: 15px; font-size: 14px; }
    .extra-links a { color: #0072ff; text-decoration: none; }
    .extra-links a:hover { text-decoration: underline; }
  </style>
  <script>
    function selectRole(role) {
      document.getElementById("role").value = role;
      document.querySelectorAll(".role-buttons button").forEach(btn => btn.classList.remove("active"));
      document.getElementById(role+"Btn").classList.add("active");
    }
  </script>
</head>
<body>
  <div class="navbar">
    <h1>🧑‍🔧 SERVICE MARKETPLACE</h1>
    <div>
      <a href="home.php">Home</a>
    </div>
  </div>

  <div class="register-container">
    <h2>Create Account</h2>
    <form method="POST">
      <input type="hidden" name="role" id="role" value="user">

      <div class="role-buttons">
        <button type="button" id="userBtn" onclick="selectRole('user')" class="active">User</button>
        <button type="button" id="providerBtn" onclick="selectRole('provider')">Provider</button>
      </div>

      <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
      <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
      <div class="form-group"><label>Password</label>
        <input type="password" name="password"
          pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{6,}$"
          title="Must have 1 uppercase, 1 lowercase, 1 special symbol, and 6+ characters"
          required>
      </div>
      <div class="form-group"><label>Phone Number</label>
        <input type="text" name="phone"
          pattern="^[0-9]{10}$"
          title="Phone number must be exactly 10 digits"
          required>
      </div>

      <button type="submit" class="btn-register">Register</button>
      <div class="extra-links">Already have an account? <a href="login.php">Login</a></div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <?php if (!empty($success)): ?>
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Registration Successful!',
      text: 'Redirecting to login page in 4 seconds...',
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true
    }).then(() => {
      window.location.href = "login.php";
    });
  </script>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
  <script>
    Swal.fire({
      icon: 'error',
      title: 'Registration Failed!',
      text: "<?php echo addslashes($error); ?>",
      confirmButtonText: 'Try Again'
    });
  </script>
  <?php endif; ?>
</body>
</html>
