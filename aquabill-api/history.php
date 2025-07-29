<?php
header('Content-Type: application/json');

$conn = new mysqli('localhost','root','','aquabill');
if ($conn->connect_error) {
  echo json_encode(['status'=>'error','message'=>'DB connection failed']);
  exit;
}

$sql = "
  SELECT
    ph.id,
    r.name AS resident_name,
    ph.amount,            -- use `amount` instead of `total`
    ph.paid_at  AS date   -- use `paid_at` instead of `payment_date`
  FROM payment_history ph
  JOIN residents r ON ph.resident_id = r.id
  ORDER BY ph.paid_at DESC
";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode(['status'=>'success', 'data'=>$data]);
