<?php
session_start();
if (!isset($_SESSION['user'])) header("Location: index.html");

$user = $_SESSION['user'];
include 'db.php';

$sql = "SELECT * FROM bills WHERE resident_id={$user['id']} ORDER BY id DESC";
$bills = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Billing History</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header>
    <h1>Billing History</h1>
    <a href="dashboard.php" class="back">← Back</a>
  </header>
  <main>
    <table>
      <tr><th>Month</th><th>Amount</th><th>Status</th></tr>
      <?php while ($row = $bills->fetch_assoc()): ?>
        <tr>
          <td><?= $row['month'] ?></td>
          <td>₱<?= $row['amount'] ?></td>
          <td><?= $row['status'] ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  </main>
</body>
</html>
