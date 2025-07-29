<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$archived = $pdo->query("SELECT * FROM residents WHERE is_archived = 1")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Archived Households | Aqua-Bill</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="p-4">
  <div class="container">
    <h2 class="mb-4">📦 Archived Household Members</h2>
    <a href="households.php" class="btn btn-secondary mb-3">
      <i class="fas fa-arrow-left"></i> Back to Active Households
    </a>

    <table class="table table-bordered table-hover">
      <thead class="table-dark">
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Contact</th>
          <th>Gender</th>
          <th>Age</th>
          <th>Meter No.</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($archived as $row): ?>
        <tr>
          <td><?= $row['name'] ?></td>
          <td><?= $row['email'] ?></td>
          <td><?= $row['contact'] ?></td>
          <td><?= $row['gender'] ?></td>
          <td><?= $row['age'] ?></td>
          <td><?= $row['meter_no'] ?></td>
          <td>
            <a href="households.php?action=unarchive_resident&id=<?= $row['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Restore this household?')">
              <i class="fas fa-undo"></i> Restore
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
