<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';


if (isset($_POST['add_collector'])) {
    $name    = $_POST['collector_name'] ?? '';
    $email   = $_POST['collector_email'] ?? '';
    $contact = $_POST['collector_contact'] ?? '';
    $age     = $_POST['collector_age'] ?? '';
    $status  = $_POST['collector_status'] ?? 'Active';

   
    $profile_photo = null;
    if (isset($_FILES['collector_photo']) && $_FILES['collector_photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['collector_photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('collector_', true) . '.' . $ext;
        $uploadPath = 'uploads/collectors/' . $filename;

        if (!is_dir('uploads/collectors')) {
            mkdir('uploads/collectors', 0777, true);
        }

        move_uploaded_file($_FILES['collector_photo']['tmp_name'], $uploadPath);
        $profile_photo = $filename;
    }

    $stmt = $pdo->prepare("INSERT INTO collectors (fullname, email, profile_photo, contact_number, age, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $profile_photo, $contact, $age, $status]);

    header("Location: collectors.php");
    exit();
}


if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM collectors WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: collectors.php");
    exit();
}

$collectors = $pdo->query("SELECT * FROM collectors")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Collectors | Aqua-Bill</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #f4f7fa;
      display: flex;
      min-height: 100vh;
    }
    .sidebar {
      width: 240px;
      background: #0077B6;
      color: white;
      display: flex;
      flex-direction: column;
      padding-top: 1rem;
    }
    .logo-container {
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding-bottom: 1rem;
      border-bottom: 1px solid #ffffff44;
      margin-bottom: 1rem;
    }
    .sidebar-logo {
      width: 60px;
      height: 60px;
      object-fit: cover;
      margin-bottom: 0.5rem;
      border-radius: 50%;
      border: 2px solid white;
    }
    .logo-text {
      color: white;
      font-size: 1.2rem;
      font-weight: bold;
      letter-spacing: 1px;
    }
    .sidebar a {
      color: white;
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      text-decoration: none;
      font-weight: 500;
    }
    .sidebar a i {
      margin-right: 1rem;
      width: 20px;
    }
    .sidebar a:hover,
    .sidebar a.active {
      background: #90e0ef;
      color: black;
    }
    .main-content {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .topbar {
      background: #0077B6;
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .topbar-left {
      display: flex;
      align-items: center;
    }
    .burger {
      font-size: 1.5rem;
      cursor: pointer;
      margin-right: 1rem;
    }
    .user-dropdown {
      position: relative;
    }
    .user-logo {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      cursor: pointer;
      object-fit: cover;
      border: 2px solid white;
    }
    .dropdown-menu {
      position: absolute;
      right: 0;
      top: 45px;
      background: white;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      display: none;
      flex-direction: column;
      min-width: 150px;
      z-index: 10;
    }
    .dropdown-menu a {
      padding: 0.75rem 1rem;
      display: block;
      text-decoration: none;
      color: #333;
      font-size: 0.95rem;
      border-bottom: 1px solid #eee;
    }
    .dropdown-menu a:hover {
      background-color: #90e0ef;
    }
    .container {
      padding: 2rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px 16px;
      border-bottom: 1px solid #eee;
    }
    th {
      background-color: #0077B6;
      color: white;
      text-align: left;
    }
    tr:hover {
      background-color: #f1f1f1;
    }
    .add-btn {
      padding: 10px 20px;
      background: #0077B6;
      color: white;
      border: none;
      border-radius: 5px;
      margin-bottom: 1rem;
      cursor: pointer;
    }
    .delete-btn {
      background: #e63946;
      color: white;
      border: none;
      padding: 6px 10px;
      border-radius: 4px;
      cursor: pointer;
    }
    #collectorModal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: #000000aa;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }
    #collectorModal .modal-content {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      width: 100%;
      max-width: 400px;
    }
  </style>
</head>
<body>


<div class="sidebar" id="sidebar">
  <div class="logo-container">
    <img src="logo.png" alt="Aqua-Bill Logo" class="sidebar-logo" />
    <span class="logo-text">AQUA-BILL</span>
  </div>
  <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
  <a href="households.php"><i class="fas fa-users"></i> Households</a>
  <a href="collectors.php" class="active"><i class="fas fa-user-tie"></i> Collectors</a>
  <a href="announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a>
  <a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a>
  <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
  <a href="payment.php"><i class="fas fa-upload"></i> Payment</a>
</div>


<div class="main-content">
  <div class="topbar">
    <div class="topbar-left">
      <i class="fas fa-bars burger" onclick="toggleSidebar()"></i>
      <span>Collectors Management</span>
    </div>
    <div class="user-dropdown">
      <img src="profile.jpg" alt="Admin" class="user-logo" onclick="toggleDropdown()" />
      <div class="dropdown-menu" id="dropdownMenu">
        <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>
  </div>

  <div class="container">
    <button class="add-btn" onclick="openModal()"><i class="fas fa-plus"></i> Add Collector</button>
    <table>
      <thead>
        <tr>
          <th>Photo</th>
          <th>Name</th>
          <th>Email</th>
          <th>Contact</th>
          <th>Age</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($collectors as $collector): ?>
          <tr>
            <td>
              <img src="<?= $collector['profile_photo'] ? 'uploads/collectors/' . htmlspecialchars($collector['profile_photo']) : 'default.png' ?>" width="40" height="40" style="border-radius: 50%;">
            </td>
            <td><?= htmlspecialchars($collector['fullname']) ?></td>
            <td><?= htmlspecialchars($collector['email']) ?></td>
            <td><?= htmlspecialchars($collector['contact_number']) ?></td>
            <td><?= htmlspecialchars($collector['age']) ?></td>
            <td><?= htmlspecialchars($collector['status']) ?></td>
            <td>
              <a href="collectors.php?delete=<?= $collector['id'] ?>" onclick="return confirm('Delete this collector?');" class="delete-btn">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (count($collectors) === 0): ?>
          <tr><td colspan="7">No collectors found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>


<div id="collectorModal">
  <div class="modal-content">
    <h3 style="margin-bottom: 1rem;">Add Collector</h3>
    <form method="post" enctype="multipart/form-data">
      <input type="file" name="collector_photo" accept="image/*" required style="width:100%; padding:10px; margin-bottom:10px;" />
      <input type="text" name="collector_name" placeholder="Full Name" required style="width:100%; padding:10px; margin-bottom:10px;" />
      <input type="email" name="collector_email" placeholder="Email" required style="width:100%; padding:10px; margin-bottom:10px;" />
      <input type="text" name="collector_contact" placeholder="Contact Number" required style="width:100%; padding:10px; margin-bottom:10px;" />
      <input type="number" name="collector_age" placeholder="Age" required style="width:100%; padding:10px; margin-bottom:10px;" />
      <select name="collector_status" required style="width:100%; padding:10px; margin-bottom:10px;">
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
        <option value="Suspended">Suspended</option>
      </select>
      <div style="text-align:right;">
        <button type="button" onclick="closeModal()" style="margin-right:10px; padding:8px 16px;">Cancel</button>
        <button type="submit" name="add_collector" style="padding:8px 16px; background:#0077B6; color:white; border:none; border-radius:4px;">Add</button>
      </div>
    </form>
  </div>
</div>


<script>
  function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("hide");
  }
  function toggleDropdown() {
    const menu = document.getElementById("dropdownMenu");
    menu.style.display = menu.style.display === "flex" ? "none" : "flex";
  }
  document.addEventListener("click", function(e) {
    const menu = document.getElementById("dropdownMenu");
    const logo = document.querySelector(".user-logo");
    if (!menu.contains(e.target) && !logo.contains(e.target)) {
      menu.style.display = "none";
    }
  });
  function openModal() {
    document.getElementById("collectorModal").style.display = "flex";
  }
  function closeModal() {
    document.getElementById("collectorModal").style.display = "none";
  }
</script>

</body>
</html>
