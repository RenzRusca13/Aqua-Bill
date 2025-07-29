<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $posted_by = $_SESSION['user'];
    $date_posted = date('Y-m-d');

    if (!empty($title) && !empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, posted_by, date_posted) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $posted_by, $date_posted]);
        header("Location: announcements.php");
        exit();
    }
}

$announcements = $pdo->query("SELECT * FROM announcements ORDER BY date_posted DESC")->fetchAll();
$current = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Announcements | Aqua-Bill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7fa;
        }

        .sidebar {
            width: 240px;
            background: #0077B6;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 1rem;
        }

        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1rem;
        }

        .sidebar-logo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid white;
            margin-bottom: 0.5rem;
        }

        .logo-text {
            font-weight: bold;
            font-size: 1.2rem;
            color: white;
            letter-spacing: 1px;
        }

        .sidebar-divider {
            width: 100%;
            height: 1px;
            background-color: white;
            margin: 0.5rem 0 1rem;
            opacity: 0.4;
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
            margin-left: 240px;
            min-height: 100vh;
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
            position: relative;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
     .topbar-right {
      display: flex;
      align-items: center;
      gap: 20px;
    }

        .burger {
            font-size: 1.5rem;
            cursor: pointer;
        }

        .user-menu {
            position: relative;
            display: inline-block;
        }

        .user-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            object-fit: cover;
            border: 2px solid #fff;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 140px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.15);
            z-index: 1000;
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
        }

        .dropdown-menu a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-weight: 500;
        }

        .dropdown-menu a:hover {
            background-color: #f0f0f0;
        }
        

        .container {
            padding: 2rem;
        }

        .announcement-form input,
        .announcement-form textarea {
            width: 100%;
            padding: 0.7rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .announcement-form button {
            padding: 0.7rem 1.5rem;
            background: #0077B6;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .announcement-card {
            background: white;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .announcement-card h3 {
            margin: 0;
            color: #1a237e;
        }

        .announcement-card small {
            color: #666;
        }

        .announcement-card p {
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="logo-container">
        <img src="logo.png" class="sidebar-logo" alt="Logo">
        <div class="logo-text">AQUA-BILL</div>
        <div class="sidebar-divider"></div>
    </div>
    <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="households.php" class="<?= $current === 'households.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Households</a>
    <a href="collectors.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'collectors.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Collectors</a>
    <a href="announcements.php" class="<?= $current === 'announcements.php' ? 'active' : '' ?>"><i class="fas fa-bullhorn"></i> Announcements</a>
    <a href="reports.php" class="<?= $current === 'reports.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Reports</a>
    <a href="notifications.php" class="<?= basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : '' ?>">
  <i class="fas fa-bell"></i> Notifications
</a>
    <a href="payment.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'payment.php' ? 'active' : ''; ?>">
    <i class="fas fa-upload"></i> Payment
  </a>
</div>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <div class="burger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></div>
            <div>Post New Announcement</div>
        </div>
        <div class="user-menu">
            <img src="profile.jpg" alt="User Logo" class="user-icon" onclick="toggleDropdown()" />
            <div id="dropdown" class="dropdown-menu">
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <form class="announcement-form" method="POST">
            <input type="text" name="title" placeholder="Announcement Title" required>
            <textarea name="content" rows="5" placeholder="Write your announcement here..." required></textarea>
            <button type="submit">Post Announcement</button>
        </form>

        <hr style="margin: 2rem 0;">
        <h2>All Announcements</h2>
        <?php foreach ($announcements as $a): ?>
            <div class="announcement-card">
                <h3><?= htmlspecialchars($a['title']) ?></h3>
                <small>Posted by <?= htmlspecialchars($a['posted_by']) ?> on <?= htmlspecialchars($a['date_posted']) ?></small>
                <p><?= nl2br(htmlspecialchars($a['content'])) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function toggleDropdown() {
    var dropdown = document.getElementById("dropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

document.addEventListener("click", function(event) {
    var isClickInside = document.querySelector(".user-menu").contains(event.target);
    if (!isClickInside) {
        document.getElementById("dropdown").style.display = "none";
    }
});

function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const main = document.querySelector(".main-content");
    const isHidden = sidebar.style.display === "none";

    sidebar.style.display = isHidden ? "flex" : "none";
    main.style.marginLeft = isHidden ? "240px" : "0";
}
</script>

</body>
</html>
