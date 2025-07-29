<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

$id = $_GET['id'] ?? 0;

// Get resident info (include meter_no)
$stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
$stmt->execute([$id]);
$resident = $stmt->fetch();

if (!$resident) {
    die("Resident not found.");
}

// Get bills with join (optional: already have resident_id)
$bills = $pdo->prepare("SELECT * FROM bills WHERE resident_id = ? ORDER BY billing_date DESC");
$bills->execute([$id]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Resident Billing Info</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; background: #f4f7fa; }
        h2 { color: #1a237e; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 1rem; }
        th, td { padding: 0.8rem 1.2rem; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #1a237e; color: white; }
        tr:hover { background-color: #e3f2fd; }
        .back-btn { margin-top: 1rem; display: inline-block; background: #1a237e; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 5px; }
        .info-card { margin-bottom: 1rem; padding: 1rem; background: white; border-left: 6px solid #1a237e; }
    </style>
</head>
<body>

<h2>Billing History for <?php echo htmlspecialchars($resident['fullname']); ?></h2>

<div class="info-card">
    <strong>Email:</strong> <?php echo htmlspecialchars($resident['email']); ?><br>
    <strong>Status:</strong> <?php echo htmlspecialchars($resident['status']); ?><br>
    <strong>Meter No:</strong> <?php echo htmlspecialchars($resident['meter_no']); ?>
</div>

<table>
    <thead>
        <tr>
            <th>Month</th>
            <th>Billing Date</th>
            <th>Due Date</th>
            <th>Prev. Reading</th>
            <th>Pres. Reading</th>
            <th>Rate</th>
            <th>Consumption</th>
            <th>Penalty</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>OR #</th>
            <th>Remarks</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($bills as $b): 
            $consumption = $b['present_reading'] - $b['previous_reading'];
            $total = ($consumption * $b['rate_per_cubic']) + $b['penalty'];
        ?>
        <tr>
            <td><?php echo htmlspecialchars($b['month']); ?></td>
            <td><?php echo htmlspecialchars($b['billing_date']); ?></td>
            <td><?php echo htmlspecialchars($b['due_date']); ?></td>
            <td><?php echo $b['previous_reading']; ?> m³</td>
            <td><?php echo $b['present_reading']; ?> m³</td>
            <td>₱<?php echo number_format($b['rate_per_cubic'], 2); ?></td>
            <td><?php echo $consumption; ?> m³</td>
            <td>₱<?php echo number_format($b['penalty'], 2); ?></td>
            <td><strong>₱<?php echo number_format($total, 2); ?></strong></td>
            <td style="color: <?php echo $b['status'] == 'Paid' ? 'green' : 'red'; ?>">
                <?php echo htmlspecialchars($b['status']); ?>
            </td>
            <td><?php echo htmlspecialchars($b['or_number']); ?></td>
            <td><?php echo htmlspecialchars($b['remarks']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="households.php" class="back-btn">← Back to Households</a>

</body>
</html>
