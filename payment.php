<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$_SESSION['resident_id'] = 1; 
$resident_id = $_SESSION['resident_id'];
$success = '';
$uploadedImage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['proof'])) {
    $amount = $_POST['amount'];
    $date_paid = $_POST['date_paid'];

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uniqueName = uniqid() . '-' . basename($_FILES['proof']['name']);
    $targetPath = $uploadDir . $uniqueName;

    if (move_uploaded_file($_FILES['proof']['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO payments (resident_id, amount, date_paid, proof_image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$resident_id, $amount, $date_paid, $uniqueName]);
        $success = 'Payment proof uploaded successfully!';
        $uploadedImage = $targetPath;
    } else {
        $success = 'Failed to upload image.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Payment | Aqua-Bill</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
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
      transition: transform 0.3s ease-in-out;
    }
    .sidebar.hide {
      transform: translateX(-100%);
      position: absolute;
      z-index: 1000;
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
    .form-card {
      max-width: 550px;
      margin: auto;
      background: white;
      padding: 2rem 2.5rem;
      border-radius: 12px;
      box-shadow: 0 3px 15px rgba(0, 0, 0, 0.05);
    }
    .form-label {
      font-weight: 600;
      margin-bottom: 0.5rem;
      display: block;
    }
    .img-preview {
      display: none;
      max-height: 240px;
      object-fit: contain;
    }
    input[type="file"]::file-selector-button {
      background-color: #007bff;
      border: none;
      color: white;
      padding: 0.3rem 0.75rem;
      margin-right: 1rem;
      border-radius: 6px;
      cursor: pointer;
    }
    @media (max-width: 768px) {
      .sidebar {
        position: absolute;
        height: 100%;
        top: 0;
        left: 0;
      }
    }
  </style>
</head>
<body>


<div class="sidebar" id="sidebar">
  <div class="logo-container">
    <img src="logo.png" alt="Aqua-Bill Logo" class="sidebar-logo" />
    <span class="logo-text">AQUA-BILL</span>
  </div>

  <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
    <i class="fas fa-tachometer-alt"></i> Dashboard
  </a>
  <a href="households.php" class="<?= basename($_SERVER['PHP_SELF']) == 'households.php' ? 'active' : '' ?>">
    <i class="fas fa-users"></i> Households
  </a>
  <a href="collectors.php" class="<?= basename($_SERVER['PHP_SELF']) == 'collectors.php' ? 'active' : '' ?>">
    <i class="fas fa-user-tie"></i> Collectors
  </a>
  <a href="announcements.php" class="<?= basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : '' ?>">
    <i class="fas fa-bullhorn"></i> Announcements
  </a>
  <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
    <i class="fas fa-chart-line"></i> Reports
  </a>
  <a href="notifications.php" class="<?= basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : '' ?>">
  <i class="fas fa-bell"></i> Notifications
</a>
  <a href="payment.php" class="<?= basename($_SERVER['PHP_SELF']) == 'payment.php' ? 'active' : '' ?>">
    <i class="fas fa-upload"></i> Payment
  </a>
</div>


<div class="main-content">
  <div class="topbar">
    <div class="topbar-left">
      <i class="fas fa-bars burger" onclick="toggleSidebar()"></i>
      <span>Payment</span>
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
    <div class="form-card">
      <h3 class="mb-4 text-center">Upload Proof of Payment</h3>

      <?php if ($success): ?>
        <div class="alert alert-info"><?= $success ?></div>
      <?php endif; ?>

      <?php if ($uploadedImage): ?>
        <div class="mb-3 text-center">
          <img src="<?= htmlspecialchars($uploadedImage) ?>" class="img-fluid border rounded" style="max-height:300px;" alt="Uploaded Proof" />
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <div class="mb-4">
          <label class="form-label" for="amount">Amount Paid</label>
          <input type="number" name="amount" id="amount" class="form-control rounded" placeholder="e.g. 200.00" step="0.01" required>
        </div>

        <div class="mb-4">
          <label class="form-label" for="date_paid">Date Paid</label>
          <input type="date" name="date_paid" id="date_paid" class="form-control rounded" required>
        </div>

        <div class="mb-4">
          <label class="form-label" for="proofInput">Upload Screenshot / Receipt</label>
          <input type="file" name="proof" id="proofInput" class="form-control rounded" accept="image/*" required>
          <img id="previewImg" class="img-preview img-fluid mt-3 border rounded" />
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">Submit Payment</button>
      </form>
    </div>
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

  const input = document.getElementById('proofInput');
  const preview = document.getElementById('previewImg');
  input.addEventListener('change', () => {
    const file = input.files[0];
    if (file) {
      preview.style.display = 'block';
      preview.src = URL.createObjectURL(file);
    }
  });
</script>

</body>
</html>
