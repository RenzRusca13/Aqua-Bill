<?php
// 🔧 Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// 🔌 DB Connection
$conn = new mysqli('localhost', 'root', '', 'aquabill');

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit();
}

// 📥 Decode JSON body
$data = json_decode(file_get_contents("php://input"), true);

// ✅ Check required fields
$required = ['resident_id', 'coverage_from', 'coverage_to', 'reading_date', 'due_date', 'consumption', 'price_per_cubic', 'total'];
foreach ($required as $field) {
    if (empty($data[$field]) && $data[$field] !== 0 && $data[$field] !== "0") {
        echo json_encode(['status' => 'error', 'message' => "Missing or empty field: $field"]);
        exit();
    }
}

// 🧼 Sanitize & Prepare data
$resident_id = intval($data['resident_id']);
$coverage_from = $conn->real_escape_string($data['coverage_from']);
$coverage_to = $conn->real_escape_string($data['coverage_to']);
$reading_date = $conn->real_escape_string($data['reading_date']);
$due_date = $conn->real_escape_string($data['due_date']);
$consumption = floatval($data['consumption']);
$price_per_cubic = floatval($data['price_per_cubic']);
$total = floatval($data['total']);

// 🧾 Insert bill
$stmt = $conn->prepare("INSERT INTO bills (
    resident_id, coverage_from, coverage_to,
    reading_date, due_date, consumption,
    price_per_cubic, total
) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param(
    "issssddd",
    $resident_id, $coverage_from, $coverage_to,
    $reading_date, $due_date, $consumption,
    $price_per_cubic, $total
);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Bill saved.',
        'data' => ['id' => $conn->insert_id]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
