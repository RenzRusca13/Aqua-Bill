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

// Check collectors by email only
$stmt = $mysqli->prepare("SELECT * FROM collectors WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $user = $result->fetch_assoc();
  // Verify password hash
  if (password_verify($password, $user['password'])) {
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
}

// Check residents by email only
$stmt = $mysqli->prepare("SELECT * FROM residents WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $resident = $result->fetch_assoc();
  // Verify password hash
  if (password_verify($password, $resident['password'])) {
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
}

// Invalid login
echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
exit();
?>
