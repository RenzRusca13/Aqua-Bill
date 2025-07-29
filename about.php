<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About Us | Aqua-Bill</title>
  <link href="https://fonts.googleapis.com/css2?family=Host+Grotesk:wght@300;500;700&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Host Grotesk', 'Segoe UI', sans-serif;
      min-height: 100vh;
      overflow: hidden;
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
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

    .container {
      background-color: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(6px);
      border-radius: 20px;
      padding: 2.5rem 3rem;
      max-width: 850px;
      width: 100%;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
    }

    h1 {
      text-align: center;
      color: #0077B6;
      font-size: 2rem;
      margin-bottom: 1rem;
    }

    p {
      font-size: 1rem;
      line-height: 1.7;
      color: #003049;
      text-align: justify;
      margin-bottom: 1.2rem;
    }

    ul {
      padding-left: 1.2rem;
      color: #003049;
    }

    ul li {
      margin-bottom: 0.5rem;
    }

    .back-button {
      display: inline-block;
      margin-top: 2rem;
      text-align: center;
      background-color: #0077B6;
      color: white;
      padding: 0.6rem 1.4rem;
      border-radius: 10px;
      text-decoration: none;
      font-weight: bold;
      transition: background-color 0.3s;
    }

    .back-button:hover {
      background-color: #005f91;
    }

    @media (max-width: 600px) {
      .container {
        padding: 1.5rem;
      }

      h1 {
        font-size: 1.6rem;
      }

      p, ul li {
        font-size: 0.95rem;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>About Aqua-Bill</h1>
    <p>
      <strong>Aqua-Bill</strong> is a Water Billing Management System developed for Magahis III WEST Water System. It is designed to improve the efficiency and transparency of water billing and consumption tracking within the community.
    </p>
    <p>The system features include:</p>
    <ul>
      <li>Billing and payment monitoring</li>
      <li>Meter number and household management</li>
      <li>Leak detection and water usage analytics</li>
      <li>Secure user login and admin access control</li>
    </ul>
    <p>
      Aqua-Bill simplifies billing for both residents, collectors and admin, ensuring accurate records and faster payment tracking contributing to sustainable water management and better service delivery.
    </p>
    <div style="text-align: center;">
      <a href="login.php" class="back-button">Back to Login</a>
    </div>
  </div>

</body>
</html>
