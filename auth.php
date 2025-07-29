<?php
session_start();
require 'db.php'; 

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo "<script>alert('Please enter both email and password.'); window.location.href='login.php';</script>";
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM admin_accounts WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    
    $_SESSION['user'] = $user['username'];   
    $_SESSION['admin_id'] = $user['id'];     

    header("Location: dashboard.php");
    exit();
} else {
    
    echo "<script>alert('Invalid email or password.'); window.location.href='login.php';</script>";
    exit();
}
?>
