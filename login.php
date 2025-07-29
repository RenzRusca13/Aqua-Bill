<?php
session_start();

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login | Aqua-Bill</title>
  <link href="https://fonts.googleapis.com/css2?family=Host+Grotesk:wght@300;500;700&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Host Grotesk', 'Segoe UI', sans-serif;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      overflow: hidden;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url('waters.jpg') no-repeat center center/cover;
      filter: blur(8px);
      z-index: -1;
    }

    .overlay-container {
      width: 90%;
      max-width: 1000px;
      display: flex;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 20px;
      backdrop-filter: blur(8px);
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.25);
      overflow: hidden;
    }

    .left-panel {
      flex: 1;
      padding: 2rem;
      background: white;
      color: #003049;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .left-panel img {
      width: 90px;
      margin-bottom: 1rem;
    }

    .left-panel h1 {
      text-align: center;
      font-size: 1.4rem;
      margin-bottom: 1rem;
    }

    .left-panel p {
      font-size: 0.95rem;
      text-align: justify;
      max-width: 400px;
      line-height: 1.6;
    }

    .left-panel a.about-button {
      margin-top: 2rem;
      padding: 0.6rem 1.5rem;
      background: #0077B6;
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: bold;
      text-decoration: none;
      display: inline-block;
      text-align: center;
    }

    .left-panel a.about-button:hover {
      background-color: #005f91;
    }

    .right-panel {
      flex: 1;
      padding: 3rem 2rem;
      background: #0077B6;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-card {
      width: 100%;
      max-width: 350px;
    }

    .login-card h2 {
      color: white;
      text-align: center;
      margin-bottom: 1.5rem;
    }

    .login-card input,
    .login-card button {
      width: 100%;
      height: 45px;
      margin: 0.5rem 0;
      border-radius: 10px;
      font-size: 1rem;
      box-sizing: border-box;
    }

    .login-card input {
      border: 1px solid #ccc;
      padding: 0 1rem;
    }

    .login-card button {
      background-color: white;
      color: #0077B6;
      border: none;
      font-weight: 500;
      cursor: pointer;
      padding: 0 1rem;
    }

    .login-card button:hover {
      background-color: #e0f4ff;
    }

    .login-card p {
      margin-top: 1.2rem;
      font-size: 0.95rem;
      text-align: center;
      color: white;
    }

    .login-card a {
      color: #FFD60A;
      text-decoration: none;
      font-weight: 500;
    }

    .login-card a:hover {
      text-decoration: underline;
    }

    @media (max-width: 800px) {
      .overlay-container {
        flex-direction: column;
        margin: 1rem;
      }

      .left-panel,
      .right-panel {
        width: 100%;
        padding: 2rem;
      }
    }
  </style>
</head>
<body>

  <div class="overlay-container">
    <div class="left-panel">
      <img src="logo.png" alt="Logo">
      <h1>AQUA-BILL: Developing a Water Billing Management System for Magahis III WEST Water System.</h1>
      <p>
        Ensure your home or community enjoys efficient and sustainable water services!
        Our advanced water management system offers monitoring, billing automation, leak detection, and consumption tracking. All designed to save you time, money, and resources.
      </p>
      <a href="about.php" class="about-button">About Us</a>
    </div>

   
    <div class="right-panel">
      <div class="login-card">
        <h2>Admin Log In</h2>
        <form action="auth.php" method="POST">
          <input type="email" name="email" placeholder="Email" required>
          <input type="password" name="password" placeholder="Password" required>
          <button type="submit">Sign In</button>
        </form>
      </div>
    </div>
  </div>

</body>
</html>
