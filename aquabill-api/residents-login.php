<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];
$password = $data['password'];

$mysqli = new mysqli("localhost", "root", "", "aquabill");

if ($mysqli->connect_error) {
  echo json_encode(["status" => "error", "message" => "Connection failed."]);
  exit();
}

// 🔍 Check in collectors table
$stmt = $mysqli->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $user = $result->fetch_assoc();
  echo json_encode([
    "status" => "success",
    "user" => [
      "id" => $user['id'],
      "email" => $user['email'],
      "type" => "collector"
    ]
  ]);
  exit();
}

// 🔍 If not found, check in residents table
$stmt = $mysqli->prepare("SELECT * FROM residents WHERE email = ? AND password = ?");
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $resident = $result->fetch_assoc();
  echo json_encode([
    "status" => "success",
    "user" => [
      "id" => $resident['id'],
      "email" => $resident['email'],
      "type" => "resident"
    ]
  ]);
  exit();
}

// ❌ If not found in either
echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
exit();
?>
