<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Use `username` to fetch admin info
$stmt = $pdo->prepare("SELECT * FROM admin_accounts WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("<p style='color:red; font-weight:bold;'>❌ Admin not found for username: " . htmlspecialchars($_SESSION['user']) . "</p>");
}

$updateMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['password'] ?? '';

    if (!empty($email)) {
        $stmt = $pdo->prepare("UPDATE admin_accounts SET email = ? WHERE username = ?");
        $stmt->execute([$email, $_SESSION['user']]);
        $updateMessage = "✅ Email updated successfully.";
    }

    if (!empty($newPassword)) {
        if (!password_verify($oldPassword, $admin['password'])) {
            $updateMessage .= " ❌ Incorrect old password. Password not changed.";
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admin_accounts SET password = ? WHERE username = ?");
            $stmt->execute([$hashed, $_SESSION['user']]);
            $updateMessage .= " ✅ Password updated successfully.";
        }
    }

    if (!empty($_FILES['profile']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES["profile"]["name"]);
        $targetFile = $targetDir . $filename;

        if (move_uploaded_file($_FILES["profile"]["tmp_name"], $targetFile)) {
            $stmt = $pdo->prepare("UPDATE admin_accounts SET profile_photo = ? WHERE username = ?");
            $stmt->execute([$targetFile, $_SESSION['user']]);
            $updateMessage .= " ✅ Profile photo updated.";
        } else {
            $updateMessage .= " ❌ Failed to upload photo.";
        }
    }

    header("Location: settings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Settings | Aqua-Bill</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #e9f5ff;
    }

    .container {
      max-width: 500px;
      margin: 4rem auto;
      background: white;
      padding: 2rem;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      color: #0077B6;
      margin-bottom: 1.5rem;
    }

    .profile-wrapper {
      position: relative;
      width: 140px;
      height: 140px;
      margin: 0 auto 1.5rem;
      border-radius: 50%;
      overflow: hidden;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .profile-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 50%;
      display: block;
    }

    .profile-wrapper input[type="file"] {
      display: none;
    }

    .camera-icon {
      position: absolute;
      bottom: 10px;
      right: 10px;
      background-color: white;
      border-radius: 50%;
      padding: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      cursor: pointer;
      transition: background 0.3s;
    }

    .camera-icon i {
      color: #0077B6;
      font-size: 1rem;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    label {
      font-weight: 600;
      margin-bottom: 0.4rem;
      display: block;
      color: #003049;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 0.7rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 0.95rem;
    }

    .btn {
      background: #0077B6;
      color: white;
      border: none;
      padding: 0.7rem 1.5rem;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      font-size: 1rem;
      width: 100%;
      margin-top: 1.5rem;
    }

    .btn:hover {
      background: #005f91;
    }

    .btn-back {
      display: inline-block;
      margin-top: 1rem;
      padding: 0.5rem 1rem;
      background: #fff;
      color: #0077B6;
      border: 1px solid #0077B6;
      border-radius: 6px;
      text-decoration: none;
    }

    .btn-back:hover {
      background: #0077B6;
      color: #fff;
    }

    .message {
      text-align: center;
      color: green;
      margin-bottom: 1rem;
      font-weight: 600;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Admin Settings</h2>

  <?php if ($updateMessage): ?>
    <div class="message"><?php echo htmlspecialchars($updateMessage); ?></div>
  <?php endif; ?>

  <form action="settings.php" method="POST" enctype="multipart/form-data">
    <div class="profile-wrapper">
      <img id="profilePreview" src="<?php echo (!empty($admin['profile_photo']) && file_exists($admin['profile_photo'])) ? htmlspecialchars($admin['profile_photo']) : 'profile.jpg'; ?>" alt="Profile" />
      <input type="file" name="profile" id="profile" onchange="previewImage(this)" />
      <label for="profile" class="camera-icon">
        <i class="fas fa-camera"></i>
      </label>
    </div>

    <div class="form-group">
      <label>Username</label>
      <input type="text" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled />
    </div>

    <div class="form-group">
      <label>Full Name</label>
      <input type="text" value="<?php echo htmlspecialchars($admin['full_name']); ?>" disabled />
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" />
    </div>

    <div class="form-group">
      <label>Old Password</label>
      <input type="password" name="old_password" placeholder="Enter current password to change it" />
    </div>

    <div class="form-group">
      <label>New Password</label>
      <input type="password" name="password" placeholder="Leave blank to keep current" />
    </div>

    <button type="submit" class="btn">Update Settings</button>
  </form>

  <a href="dashboard.php" class="btn-back">Back</a>
</div>

<script>
function previewImage(input) {
  const file = input.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById('profilePreview').src = e.target.result;
    };
    reader.readAsDataURL(file);
  }
}
</script>

</body>
</html>
