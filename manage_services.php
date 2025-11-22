<?php

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "servicemarketplace";
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    die("Connection failed: " . $conn->connect_error);
}


function json_out($arr) {
    header('Content-Type: application/json');
    echo json_encode($arr);
    exit();
}


$action = $_REQUEST['action'] ?? null;
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

if ($action) {
   
    if ($action === 'approve' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $sid = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("UPDATE services SET status='approved' WHERE id = ?");
        $stmt->bind_param("i", $sid);
        $ok = $stmt->execute();
        $stmt->close();
        json_out(['ok' => (bool)$ok, 'status' => 'approved']);
    }

    if ($action === 'deactivate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $sid = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("UPDATE services SET status='inactive' WHERE id = ?");
        $stmt->bind_param("i", $sid);
        $ok = $stmt->execute();
        $stmt->close();
        json_out(['ok' => (bool)$ok, 'status' => 'inactive']);
    }

    if ($action === 'blacklist' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $sid = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("UPDATE services SET status='blacklisted' WHERE id = ?");
        $stmt->bind_param("i", $sid);
        $ok = $stmt->execute();
        $stmt->close();
        json_out(['ok' => (bool)$ok, 'status' => 'blacklisted']);
    }

    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $sid = intval($_POST['id'] ?? 0);
        $conn->begin_transaction();
        try {
            
            $stmt = $conn->prepare("DELETE FROM reports WHERE service_id = ?");
            $stmt->bind_param("i", $sid); $stmt->execute(); $stmt->close();

            $stmt = $conn->prepare("DELETE FROM chats WHERE service_id = ?");
            $stmt->bind_param("i", $sid); $stmt->execute(); $stmt->close();

            $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
            $stmt->bind_param("i", $sid); $stmt->execute(); $affected = $stmt->affected_rows; $stmt->close();

            $conn->commit();
            json_out(['ok' => true, 'deleted' => (bool)$affected]);
        } catch (Exception $e) {
            $conn->rollback();
            json_out(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

   
    if ($action === 'get' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $sid = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT s.*, a.username AS provider_name FROM services s LEFT JOIN accounts a ON s.provider_id = a.id WHERE s.id = ? LIMIT 1");
        $stmt->bind_param("i", $sid);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($res) json_out(['ok' => true, 'service' => $res]);
        else json_out(['ok' => false, 'error' => 'Not found']);
    }

    
    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $sid = intval($_POST['id'] ?? 0);
       
        $name = $_POST['name'] ?? '';
        $service_type = $_POST['service_type'] ?? '';
        $rate = floatval($_POST['rate'] ?? 0);
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $pincode = $_POST['pincode'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $description = $_POST['description'] ?? '';

        
        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $photoPath = $targetDir . time() . "_" . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath);
        }

        
        if ($photoPath) {
            $stmt = $conn->prepare("UPDATE services SET name=?, service_type=?, rate=?, phone=?, address=?, pincode=?, gender=?, description=?, photo=? WHERE id = ?");
            $stmt->bind_param("ssdsissssi", $name, $service_type, $rate, $phone, $address, $pincode, $gender, $description, $photoPath, $sid);
        } else {
            $stmt = $conn->prepare("UPDATE services SET name=?, service_type=?, rate=?, phone=?, address=?, pincode=?, gender=?, description=? WHERE id = ?");
            $stmt->bind_param("ssdsisssi", $name, $service_type, $rate, $phone, $address, $pincode, $gender, $description, $sid);
        }

        $ok = $stmt->execute();
        $stmt->close();
        json_out(['ok' => (bool)$ok]);
    }
}


$search = $_GET['search'] ?? '';
$search_sql = "";
$params = [];
if ($search !== '') {
    $search_sql = " WHERE s.name LIKE ? OR a.username LIKE ? ";
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like;
}

$sql = "SELECT s.*, a.username AS provider_name FROM services s LEFT JOIN accounts a ON s.provider_id = a.id {$search_sql} ORDER BY s.id DESC";
if ($search_sql) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $params[0], $params[1]);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

?>
<html>
<head>
<title>Manage Services - Admin</title>
<style>

* { box-sizing: border-box; font-family: "Segoe UI",sans-serif; }
body { margin:0; background:#f4f6fc; color:#222; }
.sidebar { width:220px; background:#1e1f26; color:#fff; position:fixed; top:0; bottom:0; padding:20px; }
.sidebar h2{color:#00c6ff;margin:0 0 18px 0}
.sidebar a{display:block;color:#fff;padding:10px;border-radius:6px;text-decoration:none;margin-bottom:6px}
.sidebar a.active, .sidebar a:hover{background:#00c6ff;color:#111}

.main{margin-left:240px;padding:28px}
.topbar{background:#fff;padding:18px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.04);display:flex;justify-content:space-between;align-items:center}
.search-box{margin-top:16px;margin-bottom:18px}
.search-box input{padding:10px;width:360px;border:1px solid #ddd;border-radius:6px}
.search-box button{padding:10px 14px;background:#2575fc;border:none;color:#fff;border-radius:6px;cursor:pointer}

.table-wrap{background:#fff;border-radius:12px;padding:0;margin-top:18px;box-shadow:0 6px 20px rgba(0,0,0,0.04)}
table{width:100%;border-collapse:collapse}
thead th{background:#2575fc;color:#fff;padding:14px;text-align:left;border-radius:8px 8px 0 0}
td, th{padding:12px;border-bottom:1px solid #f0f0f0}
.action-buttons {
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn-edit {
    background: #4bb3a7;
    padding: 6px 12px;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
}

.btn-approve {
    background: #28a745;
    padding: 6px 12px;
    color: white;
    border-radius: 6px;
    text-decoration: none;
}

.btn-reject {
    background: #ff6b3d;
    padding: 6px 12px;
    color: white;
    border-radius: 6px;
    text-decoration: none;
}

.btn-delete {
    background: #d63031;
    padding: 6px 12px;
    color: white;
    border-radius: 6px;
    text-decoration: none;
}

.btn-black {
    background: #4c1d95;
    padding: 6px 12px;
    color: white;
    border-radius: 6px;
    text-decoration: none;
}

.modal-backdrop{position:fixed;left:0;top:0;right:0;bottom:0;background:rgba(0,0,0,0.4);display:none;align-items:center;justify-content:center;z-index:999}
.modal{width:700px;background:#fff;border-radius:10px;padding:18px;box-shadow:0 10px 40px rgba(0,0,0,0.25);transform:translateY(-30px);opacity:0;transition:all .28s ease}
.modal.show{transform:translateY(0);opacity:1}
.modal h3{margin:0 0 12px 0;color:#2575fc}
.form-row{display:flex;gap:10px;margin-bottom:10px}
.form-row .col{flex:1}
.form-row input,.form-row select, textarea{width:100%;padding:8px;border:1px solid #ddd;border-radius:6px}
.modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:12px}

@media (max-width:800px){
  .modal{width:90%}
  .search-box input{width:60%}
}
</style>
</head>
<body>

<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="admin_dashboard.php">🏠 Dashboard</a>
  <a href="manage_users.php">👥 Manage Users</a>
  <a href="manage_providers.php">🔧 Manage Providers</a>
  <a href="manage_services.php" class="active">📦 Manage Services</a>
  <a href="manage_reports.php">🚫 Manage Reports</a>
  <a href="admin_view_chats.php">💬 View Chats</a>
  <a href="view_contacts.php">📨 Contacts</a>
  <a href="admin_dashboard.php?logout=1">🚪 Logout</a>
</div>

<div class="main">
  <div class="topbar">
    <h1 style="color:#2575fc;margin:0">Manage Services</h1>
    <a href="admin_dashboard.php" style="color:#2575fc;text-decoration:none">Back</a>
  </div>

  <div class="search-box">
    <form method="GET" id="searchForm" onsubmit="return true;">
      <input type="text" name="search" placeholder="Search service or provider..." value="<?php echo htmlspecialchars($search); ?>">
      <button type="submit">Search</button>
    </form>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:60px">ID</th>
          <th>Service Name</th>
          <th>Provider</th>
          <th style="width:110px">Price</th>
          <th style="width:140px">Status</th>
          <th style="width:360px">Actions</th>
        </tr>
      </thead>
      <tbody id="servicesBody">
<?php while ($row = $result->fetch_assoc()): 
    $sid = (int)$row['id'];
    $status = $row['status'] ?? 'pending';
?>
        <tr id="row-<?php echo $sid; ?>">
          <td><?php echo $sid; ?></td>
          <td><?php echo htmlspecialchars($row['name']); ?></td>
          <td><?php echo htmlspecialchars($row['provider_name'] ?? ''); ?></td>
          <td>₹<?php echo htmlspecialchars($row['rate']); ?></td>
          <td class="status-cell" data-status="<?php echo htmlspecialchars($status); ?>">
            <?php
              if ($status === 'pending') echo "<span class='status-pending'>Pending</span>";
              elseif ($status === 'approved') echo "<span class='status-approved'>Approved</span>";
              elseif ($status === 'inactive') echo "<span class='status-inactive'>Inactive</span>";
              elseif ($status === 'blacklisted') echo "<span class='status-blacklisted'>Blacklisted</span>";
              else echo htmlspecialchars($status);
            ?>
          </td>
          <td class="actions">
    <div class="action-buttons">

        <!-- EDIT BUTTON -->
        <a href="#" class="btn-edit" 
           onclick="openEdit(event, <?php echo $sid; ?>)">Edit</a>

        <!-- APPROVE BUTTON (only if NOT approved or blacklisted) -->
        <?php if ($status !== 'approved' && $status !== 'blacklisted'): ?>
            <a href="#" class="btn-approve"
               onclick="approveService(event, <?php echo $sid; ?>)">Approve</a>
        <?php endif; ?>

        <!-- REJECT BUTTON (only if approved) -->
        <?php if ($status === 'approved'): ?>
            <a href="#" class="btn-reject"
               onclick="rejectService(event, <?php echo $sid; ?>)">Reject</a>
        <?php endif; ?>

        <!-- DELETE BUTTON (always visible) -->
        <a href="#" class="btn-delete"
           onclick="deleteService(event, <?php echo $sid; ?>)">Delete</a>

        <!-- BLACKLIST BUTTON (always visible) -->
        <a href="#" class="btn-black"
           onclick="blacklistService(event, <?php echo $sid; ?>)">Blacklist</a>
    </div>
</td>
        </tr>
<?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>


<div id="modalBackdrop" class="modal-backdrop" onclick="closeModal(event)">
  <div id="modal" class="modal" onclick="event.stopPropagation()">
    <h3>Edit Service</h3>
    <form id="editForm" enctype="multipart/form-data">
      <input type="hidden" name="id" id="svc_id">
      <div class="form-row">
        <div class="col"><input type="text" name="name" id="svc_name" placeholder="Name" required></div>
        <div class="col">
          <select name="service_type" id="svc_type" required>
            <option>Plumbing</option><option>Electrician</option><option>Cleaning</option>
            <option>Carpentry</option><option>Painting</option><option>Appliance Repair</option>
            <option>Pest Control</option><option>Gardening</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="col"><input type="number" name="rate" id="svc_rate" step="0.01" placeholder="Rate" required></div>
        <div class="col"><input type="text" name="phone" id="svc_phone" placeholder="Phone"></div>
      </div>

      <div class="form-row">
        <div class="col"><input type="text" name="address" id="svc_address" placeholder="Address"></div>
        <div class="col"><input type="text" name="pincode" id="svc_pincode" placeholder="Pincode"></div>
      </div>

      <div class="form-row">
        <div class="col">
          <select name="gender" id="svc_gender">
            <option>Male</option><option>Female</option><option>Other</option>
          </select>
        </div>
        <div class="col"><input type="file" name="photo" id="svc_photo"></div>
      </div>

      <div style="margin-top:8px">
        <textarea name="description" id="svc_description" rows="4" placeholder="Description" style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd"></textarea>
      </div>

      <div class="modal-actions">
        <button type="button" onclick="closeModal()" style="padding:8px 12px;border-radius:6px;border:1px solid #aaa;background:#fff;cursor:pointer">Cancel</button>
        <button type="submit" style="padding:8px 12px;border-radius:6px;border:none;background:#2575fc;color:#fff;cursor:pointer">Update</button>
      </div>
    </form>
  </div>
</div>

<script>

async function postJSON(url, data) {
  const resp = await fetch(url, {
    method: 'POST',
    body: data
  });
  return resp.json();
}


async function approveService(e, id) {
  e.preventDefault();
  if (!confirm('Approve this service?')) return;
  const form = new FormData();
  form.append('id', id);
  form.append('action', 'approve'); 
  try {
    const res = await fetch('manage_services.php?action=approve', {method:'POST', body: form});
    const j = await res.json();
    if (j.ok) {
      
      const row = document.getElementById('row-' + id);
      if (!row) return;
      const statusCell = row.querySelector('.status-cell');
      statusCell.innerHTML = "<span class='status-approved'>Approved</span>";
   
      const btn = row.querySelector('.btn-approve');
      if (btn) btn.remove();
      
      if (!row.querySelector('.btn-reject')) {
        const a = document.createElement('a');
        a.href='#'; a.textContent='Reject'; a.className='btn-reject';
        a.onclick = function(ev){ deactivateService(ev, id); };
        row.querySelector('.actions .action-buttons').insertBefore(a, row.querySelector('.btn-delete'));
      }
      alert('Service approved');
    } else {
      alert('Error approving');
    }
  } catch (err) {
    console.error(err); alert('Network error');
  }
}


async function deactivateService(e, id) {
  e.preventDefault();
  if (!confirm('Reject / deactivate this service?')) return;
  const form = new FormData();
  form.append('id', id);
  try {
    const res = await fetch('manage_services.php?action=deactivate', {method:'POST', body: form});
    const j = await res.json();
    if (j.ok) {
      const row = document.getElementById('row-' + id);
      if (!row) return;
      const statusCell = row.querySelector('.status-cell');
      statusCell.innerHTML = "<span class='status-inactive'>Inactive</span>";
    
      const btnReject = row.querySelector('.btn-reject'); if (btnReject) btnReject.remove();
      if (!row.querySelector('.btn-approve')) {
        const a = document.createElement('a'); a.href='#'; a.textContent='Approve'; a.className='btn-approve';
        a.onclick = function(ev){ approveService(ev, id); };
        row.querySelector('.actions .action-buttons').insertBefore(a, row.querySelector('.btn-delete'));
      }
      alert('Service deactivated');
    } else {
      alert('Error deactivating');
    }
  } catch (err) {
    console.error(err); alert('Network error');
  }
}


async function blacklistService(e, id) {
  e.preventDefault();
  if (!confirm('Blacklist this service? This cannot be undone easily.')) return;
  const form = new FormData();
  form.append('id', id);
  try {
    const res = await fetch('manage_services.php?action=blacklist', {method:'POST', body: form});
    const j = await res.json();
    if (j.ok) {
      const row = document.getElementById('row-' + id);
      row.querySelector('.status-cell').innerHTML = "<span class='status-blacklisted'>Blacklisted</span>";
      alert('Service blacklisted');
    } else alert('Error blacklisting');
  } catch (err) { console.error(err); alert('Network error'); }
}


async function deleteService(e, id) {
  e.preventDefault();
  if (!confirm('Permanently delete this service (and related reports/chats)?')) return;
  const form = new FormData();
  form.append('id', id);
  try {
    const res = await fetch('manage_services.php?action=delete', {method:'POST', body: form});
    const j = await res.json();
    if (j.ok) {
  
      const row = document.getElementById('row-' + id);
      if (row) row.remove();
      alert('Service deleted');
    } else {
      alert('Error deleting');
    }
  } catch (err) {
    console.error(err); alert('Network error');
  }
}


function openEdit(e, id) {
  e.preventDefault();
  fetch('manage_services.php?action=get&id=' + id)
    .then(r => r.json())
    .then(data => {
      if (!data.ok) { alert('Service not found'); return; }
      const s = data.service;
      document.getElementById('svc_id').value = s.id;
      document.getElementById('svc_name').value = s.name || '';
      document.getElementById('svc_type').value = s.service_type || 'Plumbing';
      document.getElementById('svc_rate').value = s.rate || '';
      document.getElementById('svc_phone').value = s.phone || '';
      document.getElementById('svc_address').value = s.address || '';
      document.getElementById('svc_pincode').value = s.pincode || '';
      document.getElementById('svc_gender').value = s.gender || 'Male';
      document.getElementById('svc_description').value = s.description || '';
      showModal();
    }).catch(err => { console.error(err); alert('Network error'); });
}

function showModal() {
  const backdrop = document.getElementById('modalBackdrop');
  const modal = document.getElementById('modal');
  backdrop.style.display = 'flex';
  setTimeout(()=> modal.classList.add('show'), 20);
}
function closeModal(e) {
  if (e) e.preventDefault && e.preventDefault();
  const backdrop = document.getElementById('modalBackdrop');
  const modal = document.getElementById('modal');
  modal.classList.remove('show');
  setTimeout(()=> backdrop.style.display = 'none', 240);
}


document.getElementById('editForm').addEventListener('submit', async function(ev){
  ev.preventDefault();
  const id = document.getElementById('svc_id').value;
  const form = new FormData(this);
  form.append('action','update');
  try {
    const res = await fetch('manage_services.php?action=update', { method:'POST', body: form });
    const j = await res.json();
    if (j.ok) {
     
      const row = document.getElementById('row-' + id);
      if (row) {
        row.children[1].textContent = document.getElementById('svc_name').value;
        row.children[3].textContent = '₹' + (parseFloat(document.getElementById('svc_rate').value) || 0).toFixed(2);
      }
      closeModal();
      alert('Service updated');
    } else {
      alert('Update failed');
    }
  } catch (err) {
    console.error(err); alert('Network error');
  }
});


document.addEventListener('keydown', function(e){
  if (e.key === 'Escape') {
    const backdrop = document.getElementById('modalBackdrop');
    if (backdrop.style.display === 'flex') closeModal();
  }
});
</script>
</body>
</html>
