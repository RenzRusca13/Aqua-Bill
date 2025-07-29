<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'aquabill');

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit();
}

// Total residents
$res = $conn->query("SELECT COUNT(*) AS total FROM residents");
$total = $res->fetch_assoc()['total'] ?? 0;

// Paid / Unpaid
$paid = $conn->query("SELECT COUNT(*) AS count FROM residents WHERE is_verified = 1")->fetch_assoc()['count'] ?? 0;
$unpaid = $conn->query("SELECT COUNT(*) AS count FROM residents WHERE is_verified = 0")->fetch_assoc()['count'] ?? 0;

// Total amount collected for current month
$month = date('Y-m');
$amount = $conn->query("SELECT SUM(total) AS total FROM bills WHERE status = 'Paid' AND billing_date LIKE '$month%'")->fetch_assoc()['total'] ?? 0;

echo json_encode([
    'status' => 'success',
    'data' => [
        'total_residents' => (int)$total,
        'paid' => (int)$paid,
        'unpaid' => (int)$unpaid,
        'amount_collected' => number_format((float)$amount, 2)
    ]
]);

$conn->close();
?>
