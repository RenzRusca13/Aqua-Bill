<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$resident_id = $_GET['id'] ?? null;

if (!$resident_id) {
    echo "Invalid resident ID.";
    exit();
}

$stmt = $pdo->prepare("SELECT name, meter_no FROM residents WHERE id = ?");
$stmt->execute([$resident_id]);
$resident = $stmt->fetch();

if (!$resident) {
    echo "Resident not found.";
    exit();
}

$monthFilter = $_GET['month'] ?? null;
$params = [$resident_id];

if ($monthFilter) {
    $query = "SELECT amount, payment_mode, paid_at FROM payment_history WHERE resident_id = ? AND DATE_FORMAT(paid_at, '%Y-%m') = ? ORDER BY paid_at DESC";
    $params[] = $monthFilter;
} else {
    $query = "SELECT amount, payment_mode, paid_at FROM payment_history WHERE resident_id = ? ORDER BY paid_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Payment History | Aqua-Bill</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f7fa;
      margin: 0;
      padding: 0;
    }

    .wrapper {
      max-width: 960px;
      margin: 40px auto;
      background: #ffffff;
      border-radius: 12px;
      padding: 30px 40px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.08);
      animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .back-link {
      display: inline-block;
      margin-bottom: 1rem;
      color: #0077B6;
      text-decoration: none;
      font-weight: 600;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    h2 {
      margin: 0 0 20px;
      font-size: 26px;
      color: #0077B6;
      border-bottom: 2px solid #0077B6;
      padding-bottom: 8px;
    }

    .filter-form {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
      margin-bottom: 20px;
      margin-top: 10px;
    }

    .filter-form label {
      font-weight: 600;
      font-size: 14px;
    }

    .filter-form input[type="month"] {
      padding: 10px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .filter-form button {
      padding: 10px 16px;
      background-color: #0077B6;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .filter-form button:hover {
      background-color: #005f8a;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      background-color: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }

    th {
      background-color: #0077B6;
      color: white;
      text-transform: uppercase;
      font-size: 13px;
      padding: 12px;
    }

    td {
      padding: 12px;
      font-size: 14px;
      text-align: center;
      color: #333;
    }

    tr:nth-child(even) {
      background-color: #eaf7ff;
    }

    .badge {
      display: inline-block;
      padding: 6px 14px;
      background: #d4edda;
      color: #155724;
      font-weight: bold;
      font-size: 13px;
      border-radius: 50px;
    }

    @media (max-width: 600px) {
      .wrapper {
        padding: 20px;
      }

      .filter-form {
        flex-direction: column;
        align-items: flex-start;
      }

      .filter-form input,
      .filter-form button {
        width: 100%;
      }

      table, thead, tbody, th, td, tr {
        font-size: 13px;
      }
    }
  </style>
</head>
<body>

<div class="wrapper">
  <a class="back-link" href="households.php"> Back to Households</a>

  <h2>Payment History for <?= htmlspecialchars($resident['name']) ?> (Meter No: <?= htmlspecialchars($resident['meter_no']) ?>)</h2>

  <form method="get" class="filter-form">
    <input type="hidden" name="id" value="<?= $resident_id ?>">
    <label for="month">Filter by Month/Year:</label>
    <input type="month" name="month" id="month" value="<?= $monthFilter ?>">
    <button type="submit"><i class="fas fa-filter"></i> Filter</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>Bill Amount</th>
        <th>Payment Mode</th>
        <th>Paid At</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($payments) > 0): ?>
        <?php foreach ($payments as $row): ?>
          <tr>
            <td>₱<?= number_format($row['amount'], 2) ?></td>
            <td><?= htmlspecialchars($row['payment_mode']) ?></td>
            <td><?= htmlspecialchars($row['paid_at']) ?></td>
            <td><span class="badge">Paid</span></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="4">No records found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</body>
</html>
