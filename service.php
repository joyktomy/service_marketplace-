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

if (!isset($_SESSION['role'])) {
    die("You must login first!");
}

$role = $_SESSION['role'];
$user_id = $_SESSION['id'] ?? 0;

if ($role === 'admin' && isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE services SET status='approved' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    elseif ($action === 'deactivate') {
        $stmt = $conn->prepare("UPDATE services SET status='inactive' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: service.php");
    exit();
}


if (isset($_POST['add']) && $role === 'provider') {

    $name = $_POST['name'] ?? '';
    $service_type = $_POST['service_type'] ?? '';
    $rate = floatval($_POST['rate'] ?? 0);
    $country_code = $_POST['country_code'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $pincode = $_POST['pincode'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $description = $_POST['description'] ?? '';

    if ($service_type === '') {
        die("ERROR: Service type missing — form input mismatch.");
    }

    $photo = "";
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $photo = $targetDir . time() . "_" . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }

    $status = "pending";

    $stmt = $conn->prepare("INSERT INTO services 
        (provider_id, name, service_type, rate, country_code, phone, address, pincode, gender, description, photo, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("issdssssssss",
        $user_id, $name, $service_type, $rate, $country_code, $phone,
        $address, $pincode, $gender, $description, $photo, $status
    );

    $stmt->execute();
    $stmt->close();

    header("Location: service.php");
    exit();
}


if (isset($_POST['update'])) {

    $id = intval($_POST['id']);

    
    $stmt = $conn->prepare("SELECT provider_id FROM services WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $check = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$check || ($role === 'provider' && $check['provider_id'] != $user_id)) {
        die("Unauthorized");
    }

    $name = $_POST['name'];
    $service_type = $_POST['service_type'];
    $rate = floatval($_POST['rate']);
    $country_code = $_POST['country_code'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $pincode = $_POST['pincode'];
    $gender = $_POST['gender'];
    $description = $_POST['description'];

    $photo = null;
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $photo = $targetDir . time() . "_" . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);

        $stmt = $conn->prepare("UPDATE services SET 
            name=?, service_type=?, rate=?, country_code=?, phone=?, address=?, pincode=?, gender=?, description=?, photo=? 
            WHERE id=?");
        $stmt->bind_param("ssdsisssssi",
            $name, $service_type, $rate, $country_code, $phone, $address,
            $pincode, $gender, $description, $photo, $id
        );
    } else {
        $stmt = $conn->prepare("UPDATE services SET 
            name=?, service_type=?, rate=?, country_code=?, phone=?, address=?, pincode=?, gender=?, description=? 
            WHERE id=?");
        $stmt->bind_param("ssdsissssi",
            $name, $service_type, $rate, $country_code, $phone, $address,
            $pincode, $gender, $description, $id
        );
    }

    $stmt->execute();
    $stmt->close();

    header("Location: service.php");
    exit();
}


if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    
    $stmt = $conn->prepare("SELECT provider_id FROM services WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $owner = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$owner || ($role === 'provider' && $owner['provider_id'] != $user_id)) {
        die("Unauthorized");
    }

    $stmt = $conn->prepare("DELETE FROM services WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: service.php");
    exit();
}


if ($role === 'admin') {
    $result = $conn->query("SELECT s.*, a.username AS provider_name
        FROM services s LEFT JOIN accounts a ON s.provider_id = a.id ORDER BY s.id DESC");
} elseif ($role === 'provider') {
    $stmt = $conn->prepare("SELECT s.*, a.username AS provider_name 
        FROM services s LEFT JOIN accounts a ON s.provider_id = a.id 
        WHERE provider_id=? ORDER BY s.id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Access denied.");
}
?>
<html>
<head>
<title>Manage Services</title>
<style>

body { font-family: "Segoe UI", sans-serif; background: #f9f9f9; margin: 0; }
h1, h2 { color: #0072ff; text-align: center; margin: 15px 0; }
.navbar {
  background: #fff; padding: 12px 40px;
  display: flex; justify-content: space-between; align-items: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  position: sticky; top: 0; z-index: 1000;
}
.navbar h1 { font-size: 18px; margin: 0; }
.navbar a { color: #0072ff; text-decoration: none; font-weight: 500; }
.container { max-width: 900px; margin: auto; padding: 20px; }

form {
  background: #fff; padding: 20px; border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-bottom: 20px;
}
form input, form select {
  width: 100%; padding: 8px; margin: 6px 0;
  border: 1px solid #ddd; border-radius: 6px; font-size: 14px;
}
form button {
  background: #0072ff; color: #fff; padding: 10px 15px;
  border: none; border-radius: 6px; cursor: pointer; font-size: 14px;
}
form button:hover { background: #005fcc; }

table {
  width: 100%; border-collapse: collapse; background: #fff;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
th, td { padding: 10px; border: 1px solid #eee; font-size: 14px; text-align: center; }
th { background: #0072ff; color: #fff; }
img { width: 50px; height: 50px; border-radius: 6px; object-fit: cover; }
.edit { background: #ffc107; color: #fff; padding: 5px 12px; border-radius: 4px; text-decoration: none; }
.delete { background: #ff4d4d; color: #fff; padding: 5px 10px; border-radius: 4px; text-decoration: none; }
.approve { background: #28a745; color: #fff; padding: 5px 10px; border-radius: 4px; text-decoration: none; }
.deactivate { background: #ffc107; color: #fff; padding: 5px 10px; border-radius: 4px; text-decoration: none; }
.status-pending { color: #ff8c00; font-weight: 700; }
.status-approved { color: #1e7e34; font-weight: 700; }
.status-inactive { color: #dc3545; font-weight: 700; }
</style>
</head>

<body>

<div class="navbar">
  <h1>🧑‍🔧 SERVICE MARKETPLACE</h1>
  <a href="provider_home.php">Home</a>
</div>

<div class="container">

<h1>Manage Services</h1>

<?php

if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);

    $stmt = $conn->prepare("SELECT * FROM services WHERE id=? AND provider_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($edit):
?>
<h2>Edit Service</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $edit['id'] ?>">

    <input type="text" name="name" value="<?= htmlspecialchars($edit['name']) ?>" required>

    <select name="service_type" required>
        <option <?= $edit['service_type']=="Plumbing"?"selected":"" ?>>Plumbing</option>
        <option <?= $edit['service_type']=="Electrician"?"selected":"" ?>>Electrician</option>
        <option <?= $edit['service_type']=="Cleaning"?"selected":"" ?>>Cleaning</option>
        <option <?= $edit['service_type']=="Carpentry"?"selected":"" ?>>Carpentry</option>
        <option <?= $edit['service_type']=="Painting"?"selected":"" ?>>Painting</option>
        <option <?= $edit['service_type']=="Appliance Repair"?"selected":"" ?>>Appliance Repair</option>
        <option <?= $edit['service_type']=="Pest Control"?"selected":"" ?>>Pest Control</option>
        <option <?= $edit['service_type']=="Gardening"?"selected":"" ?>>Gardening</option>
    </select>

    <input type="number" name="rate" value="<?= $edit['rate'] ?>" required>

    <label>Phone</label>
    <div style="display:flex; gap:10px;">
        <select name="country_code" required style="width:30%;">
            <option value="+91" <?= $edit['country_code']=="+91"?"selected":"" ?>>🇮🇳 +91</option>
            <option value="+1" <?= $edit['country_code']=="+1"?"selected":"" ?>>🇺🇸 +1</option>
            <option value="+44" <?= $edit['country_code']=="+44"?"selected":"" ?>>🇬🇧 +44</option>
            <option value="+61" <?= $edit['country_code']=="+61"?"selected":"" ?>>🇦🇺 +61</option>
            <option value="+971" <?= $edit['country_code']=="+971"?"selected":"" ?>>🇦🇪 +971</option>
        </select>
        <input type="text" name="phone" value="<?= $edit['phone'] ?>" required>
    </div>

    <input type="text" name="address" value="<?= $edit['address'] ?>" required>
    <input type="text" name="pincode" value="<?= $edit['pincode'] ?>" required>

    <select name="gender" required>
        <option value="Male" <?= $edit['gender']=="Male"?"selected":"" ?>>Male</option>
        <option value="Female" <?= $edit['gender']=="Female"?"selected":"" ?>>Female</option>
        <option value="Other" <?= $edit['gender']=="Other"?"selected":"" ?>>Other</option>
    </select>

    <input type="text" name="description" value="<?= $edit['description'] ?>" required>

    <input type="file" name="photo">

    <button type="submit" name="update">Update</button>
</form>

<?php 
    endif;
} 

else if ($role === 'provider'): 
?>
<h2>Add New Service</h2>
<form method="POST" enctype="multipart/form-data">

    <input type="text" name="name" placeholder="Full Name" required>

    <select name="service_type" required>
        <option selected disabled>Select Service</option>
        <option>Plumbing</option>
        <option>Electrician</option>
        <option>Cleaning</option>
        <option>Carpentry</option>
        <option>Painting</option>
        <option>Appliance Repair</option>
        <option>Pest Control</option>
        <option>Gardening</option>
    </select>

    <input type="number" name="rate" placeholder="Rate per Hour" required>

    <label>Phone</label>
    <div style="display:flex; gap:10px;">
        <select name="country_code" style="width:30%;">
            <option value="+91">🇮🇳 +91</option>
            <option value="+1">🇺🇸 +1</option>
            <option value="+44">🇬🇧 +44</option>
            <option value="+61">🇦🇺 +61</option>
            <option value="+971">🇦🇪 +971</option>
        </select>
        <input type="text" name="phone" placeholder="Phone Number" required>
    </div>

    <input type="text" name="address" placeholder="Address" required>
    <input type="text" name="pincode" placeholder="Pincode" required>

    <select name="gender" required>
        <option disabled selected>Select Gender</option>
        <option>Male</option>
        <option>Female</option>
        <option>Other</option>
    </select>

    <input type="text" name="description" placeholder="Description" required>

    <input type="file" name="photo">

    <button type="submit" name="add">Add Service</button>
</form>
<?php endif; ?>


<h2>All Services</h2>

<table>
    <tr>
        <th>Photo</th>
        <th>Name</th>
        <th>Service</th>
        <th>Rate</th>
        <th>Phone</th>
        <th>Address</th>
        <th>Gender</th>
        <th>Description</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?php if ($row['photo']) echo "<img src='{$row['photo']}'>"; ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['service_type']) ?></td>
        <td>₹<?= htmlspecialchars($row['rate']) ?></td>
        <td><?= htmlspecialchars($row['country_code'] . " " . $row['phone']) ?></td>
        <td><?= htmlspecialchars($row['address']) ?></td>
        <td><?= htmlspecialchars($row['gender']) ?></td>
        <td><?= htmlspecialchars($row['description']) ?></td>

        <td>
            <?php 
                if ($row['status']=="pending") echo "<span class='status-pending'>Pending</span>";
                elseif ($row['status']=="approved") echo "<span class='status-approved'>Approved</span>";
                else echo "<span class='status-inactive'>Inactive</span>";
            ?>
        </td>

        <td>
            <?php if ($role === 'provider' && $row['provider_id']==$user_id): ?>
                <a class="edit" href="?edit=<?= $row['id'] ?>">Edit</a>
                <a class="delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this?')">Delete</a>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <?php if ($row['status'] !== 'approved'): ?>
                    <a class="approve" href="?action=approve&id=<?= $row['id'] ?>">Approve</a>
                <?php else: ?>
                    <a class="deactivate" href="?action=deactivate&id=<?= $row['id'] ?>">Deactivate</a>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>

</table>

</div>
</body>
</html>
