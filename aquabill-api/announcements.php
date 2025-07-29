<?php
// announcements.php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

// DB credentials
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'aquabill';

// connect
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_errno) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'DB connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// 1) get just the total count
$countRes = $conn->query("SELECT COUNT(*) AS cnt FROM announcements");
if (! $countRes) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Count query error: ' . $conn->error
    ]);
    $conn->close();
    exit;
}
$countRow = $countRes->fetch_assoc();
$total = (int)$countRow['cnt'];

// 2) fetch full announcement list
$sql = "
  SELECT id, title, content, posted_by, date_posted
  FROM announcements
  ORDER BY date_posted DESC, id DESC
";
$result = $conn->query($sql);
if (! $result) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Query error: ' . $conn->error
    ]);
    $conn->close();
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id'          => (string)$row['id'],
        'title'       => $row['title'],
        'content'     => $row['content'],
        'posted_by'   => $row['posted_by'],
        'date_posted' => $row['date_posted'],
    ];
}

// close connection before output
$conn->close();

// 3) output JSON with both count and full data
echo json_encode([
    'status' => 'success',
    'count'  => $total,
    'data'   => $data
]);
