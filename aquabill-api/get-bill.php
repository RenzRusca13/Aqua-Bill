<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "aquabill");

if ($conn->connect_error) {
  echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
  exit;
}

$id = $_GET['id'] ?? '';

if (!$id) {
  echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
  exit;
}

$sql = "SELECT 
          bills.*, 
          residents.name AS resident_name, 
          residents.meter_no 
        FROM bills
        JOIN residents ON bills.resident_id = residents.id
        WHERE bills.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $bill = $result->fetch_assoc();
  echo json_encode(['status' => 'success', 'data' => $bill]);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Bill not found']);
}

$stmt->close();
$conn->close();
