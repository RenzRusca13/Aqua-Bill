<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'aquabill');
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'DB error']); exit();
}
$resident_id = $_GET['resident_id'];
$sql = "SELECT id FROM bills WHERE resident_id = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $resident_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    echo json_encode(['status' => 'success', 'data' => $row]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No bill found']);
}
