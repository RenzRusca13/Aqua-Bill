<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';


$total_collected = $pdo
  ->query("SELECT IFNULL(SUM(amount),0) FROM payment_history")
  ->fetchColumn();


$paid = $pdo
  ->query("SELECT COUNT(DISTINCT resident_id) FROM payment_history")
  ->fetchColumn();

$unpaid = $pdo
  ->query("
    SELECT COUNT(*) 
      FROM residents r
     WHERE NOT EXISTS (
       SELECT 1
         FROM payment_history ph
        WHERE ph.resident_id = r.id
     )
  ")
  ->fetchColumn();


$payments = $pdo
  ->query("
    SELECT 
      r.name             AS resident_name,
      ph.payment_mode,
      ph.paid_at,
      ph.amount
    FROM payment_history ph
    JOIN residents r 
      ON ph.resident_id = r.id
    ORDER BY ph.paid_at DESC
  ")
  ->fetchAll(PDO::FETCH_ASSOC);


$table_total = array_sum(array_column($payments, 'amount'));


$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Reports | Aqua-Bill</title>
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
      flex-wrap: wrap;
      margin-bottom: 2rem;
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
    .card h3 {
      font-size: 2rem;
      margin: 0;
      color: #1a237e;
    }
    .card p {
      margin: 0.5rem 0 0;
      color: #555;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: 10px;
      overflow: hidden;
    }
    th, td {
      padding: 1rem;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }
    th {
      background: #0077B6;
      color: white;
    }
    td.text-right {
      text-align: right;
    }
    @media (max-width: 768px) {
      .sidebar {
        position: absolute;
        height: 100%;
        top: 0;
        left: 0;
        transition: transform 0.3s ease-in-out;
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
    <a href="dashboard.php" class="<?= $current==='dashboard.php'?'active':'' ?>">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="households.php" class="<?= $current==='households.php'?'active':'' ?>">
      <i class="fas fa-users"></i> Households
    </a>
    <a href="collectors.php" class="<?= $current==='collectors.php'?'active':'' ?>">
      <i class="fas fa-users"></i> Collectors
    </a>
    <a href="announcements.php" class="<?= $current==='announcements.php'?'active':'' ?>">
      <i class="fas fa-bullhorn"></i> Announcements
    </a>
    <a href="reports.php" class="<?= $current==='reports.php'?'active':'' ?>">
      <i class="fas fa-chart-line"></i> Reports
    </a>
    <a href="notifications.php" class="<?= $current==='notifications.php'?'active':'' ?>">
      <i class="fas fa-bell"></i> Notifications
    </a>
    <a href="payment.php" class="<?= $current==='payment.php'?'active':'' ?>">
      <i class="fas fa-upload"></i> Payment
    </a>
  </div>

 
  <div class="main-content">
    <div class="topbar">
      <div class="topbar-left">
        <i class="fas fa-bars burger" onclick="toggleSidebar()"></i>
        <span>Reports Summary</span>
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
        <div class="card">
          <h3>₱<?php echo number_format($total_collected,2); ?></h3>
          <p>Total Collected</p>
        </div>
        <div class="card">
          <h3><?php echo $paid; ?></h3>
          <p>Paid Accounts</p>
        </div>
        <div class="card">
          <h3><?php echo $unpaid; ?></h3>
          <p>Unpaid Accounts</p>
        </div>
      </div>

     
      <h2>Recent Payments</h2>
      <table>
        <thead>
          <tr>
            <th>Resident Name</th>
            <th>Mode</th>
            <th>Paid At</th>
            <th class="text-right">Amount (₱)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($payments)): ?>
            <tr>
              <td colspan="4" style="text-align:center;">No payments recorded.</td>
            </tr>
          <?php else: ?>
            <?php foreach($payments as $row): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['resident_name']); ?></td>
                <td><?php echo htmlspecialchars($row['payment_mode']); ?></td>
                <td><?php echo htmlspecialchars($row['paid_at']); ?></td>
                <td class="text-right"><?php echo number_format($row['amount'],2); ?></td>
              </tr>
            <?php endforeach; ?>
           
            <tr style="font-weight:bold; background:#f1f1f1;">
              <td colspan="3" style="text-align:right;">Total:</td>
              <td class="text-right">₱<?php echo number_format($table_total,2); ?></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function toggleDropdown() {
      const menu = document.getElementById("dropdownMenu");
      menu.style.display = menu.style.display==="flex"?"none":"flex";
    }
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("hide");
    }
    document.addEventListener("click", e=>{
      const menu = document.getElementById("dropdownMenu");
      const logo = document.querySelector(".user-logo");
      if(!menu.contains(e.target)&&!logo.contains(e.target)) menu.style.display="none";
    });
  </script>

</body>
</html>
