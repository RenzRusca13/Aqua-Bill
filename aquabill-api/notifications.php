<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost','root','','aquabill');
if ($conn->connect_error) {
  echo json_encode(['status'=>'error','message'=>'DB connection failed']);
  exit;
}

$type = $_GET['type'] ?? '';
$user = intval($_GET['user_id'] ?? 0);
if (!in_array($type, ['resident','collector']) || !$user) {
  echo json_encode(['status'=>'error','message'=>'Bad parameters']);
  exit;
}

$sql = "
  (SELECT
     n.id,
     'Payment Proof'        AS title,
     n.message              AS message,
     n.image_url            AS image_url,
     n.created_at           AS date
   FROM notifications AS n
   WHERE n.receiver_type = ? AND n.receiver_id = ?
  )
  UNION ALL
  (SELECT
     a.id,
     a.title                AS title,
     a.content              AS message,
     NULL                   AS image_url,
     a.date_posted          AS date
   FROM announcements AS a
  )
  ORDER BY date DESC
  LIMIT 50
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $type, $user);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
  $data[] = [
    'id'        => $row['id'],
    'title'     => $row['title'],
    'message'   => $row['message'],
    'image_url' => $row['image_url'],    // <-- now comes through
    'date'      => $row['date'],
  ];
}

echo json_encode(['status'=>'success','data'=>$data]);
