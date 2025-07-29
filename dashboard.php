<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';


$residents = $pdo->query("SELECT * FROM residents")->fetchAll();
$total_households = count($residents);


$unpaid_households = $pdo
  ->query("SELECT COUNT(*) FROM residents WHERE is_verified = 0")
  ->fetchColumn();
$paid_households = $pdo
  ->query("SELECT COUNT(*) FROM residents WHERE is_verified = 1")
  ->fetchColumn();


$total_collected = $pdo
  ->query("SELECT IFNULL(SUM(amount),0) FROM payment_history")
  ->fetchColumn();


$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard | Aqua-Bill</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
  />
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
      border-right: 1px solid #ddd;
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
    .card-container {
      display: flex;
      gap: 1.5rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }
    .card {
      flex: 1;
      min-width: 200px;
      background: white;
      padding: 1.5rem;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      text-align: center;
    }
    .card h2 {
      font-size: 2rem;
      color: #1a237e;
    }
    .card p {
      color: #555;
    }
    @media (max-width: 768px) {
      .sidebar {
        position: absolute;
        height: 100%;
        top: 0;
        left: 0;
      }
      .main-content {
        margin-left: 0;
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
  <a href="dashboard.php" class="<?php echo $current==='dashboard.php'?'active':''; ?>">
    <i class="fas fa-tachometer-alt"></i> Dashboard
  </a>
  <a href="households.php" class="<?php echo $current==='households.php'?'active':''; ?>">
    <i class="fas fa-users"></i> Households
  </a>
  <a href="collectors.php" class="<?php echo $current==='collectors.php'?'active':''; ?>">
    <i class="fas fa-user-tie"></i> Collectors
  </a>
  <a href="announcements.php" class="<?php echo $current==='announcements.php'?'active':''; ?>">
    <i class="fas fa-bullhorn"></i> Announcements
  </a>
  <a href="reports.php" class="<?php echo $current==='reports.php'?'active':''; ?>">
    <i class="fas fa-chart-line"></i> Reports
  </a>
  <a href="notifications.php" class="<?php echo $current==='notifications.php'?'active':''; ?>">
    <i class="fas fa-bell"></i> Notifications
  </a>
  <a href="payment.php" class="<?php echo $current==='payment.php'?'active':''; ?>">
    <i class="fas fa-upload"></i> Payment
  </a>
</div>

<div class="main-content">
  <div class="topbar">
    <div class="topbar-left">
      <i class="fas fa-bars burger" onclick="toggleSidebar()"></i>
      <span>Welcome Rojer Completo!</span>
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
     <div class="card-container">
      <div class="card" style="background:#caf0f8;">
        <h2><?= $total_households ?></h2>
        <p>Total Households</p>
      </div>
      <div class="card" style="background:#ade8f4;">
        <h2><?= $paid_households ?></h2>
        <p>Paid Accounts</p>
      </div>
      <div class="card" style="background:#90e0ef;">
        <h2><?= $unpaid_households ?></h2>
        <p>Unpaid Accounts</p>
      </div>
      <div class="card" style="background:#48cae4;">
        <h2>₱<?= number_format($total_collected, 2) ?></h2>
        <p>Total Collected</p>
      </div>
    </div>
  </div>
</div>

<script>
  function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("hide");
  }
  function toggleDropdown() {
    const menu = document.getElementById("dropdownMenu");
    menu.style.display = menu.style.display==='flex'?'none':'flex';
  }
  document.addEventListener('click', function(e) {
    const menu = document.getElementById("dropdownMenu");
    const logo = document.querySelector(".user-logo");
    if (!menu.contains(e.target) && !logo.contains(e.target)) {
      menu.style.display = 'none';
    }
  });
</script>

</body>
</html>
