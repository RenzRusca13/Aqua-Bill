<?php
// CORS & preflight
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
header('Content-Type: application/json');

// 1) PDO connection (no external config.php)
try {
  $pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=aquabill;charset=utf8mb4',
    'root',
    '',                        // XAMPP default has no password
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );
} catch (Exception $e) {
  http_response_code(500);
  exit(json_encode(['status'=>'error','message'=>'DB error: '.$e->getMessage()]));
}

// 2) Validate
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['proof'])) {
  http_response_code(400);
  exit(json_encode(['status'=>'error','message'=>'No file uploaded.']));
}
$residentId  = $_POST['resident_id']  ?? null;
$collectorId = $_POST['collector_id'] ?? null;
if (!$residentId || !$collectorId) {
  http_response_code(400);
  exit(json_encode(['status'=>'error','message'=>'Missing resident_id or collector_id.']));
}

// 3) Save file
$dir = __DIR__.'/uploads/';
if (!is_dir($dir)) mkdir($dir,0755,true);
$file = $_FILES['proof'];
$ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
$fn   = uniqid('proof_').".$ext";
if (!move_uploaded_file($file['tmp_name'], $dir.$fn)) {
  http_response_code(500);
  exit(json_encode(['status'=>'error','message'=>'Could not save file.']));
}

// 4) Public URL
$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off')?'https':'http';
$host  = $_SERVER['HTTP_HOST'];                  // e.g. 192.168.1.16
$base  = dirname($_SERVER['SCRIPT_NAME']);        // e.g. /aquabill-api
$imageUrl = "$proto://$host$base/uploads/$fn";

// 5) Insert proof_uploads
$stmt = $pdo->prepare("INSERT INTO proof_uploads (resident_id,image_url) VALUES (?,?)");
$stmt->execute([$residentId, $imageUrl]);

// 6) Insert notification
$stmt = $pdo->prepare("
  INSERT INTO notifications
    (sender_type,sender_id,receiver_type,receiver_id,message,image_url)
  VALUES (?,?,?,?,?,?)
");
$stmt->execute([
  'resident',
  $residentId,
  'collector',
  $collectorId,
  'New payment proof uploaded.',
  $imageUrl
]);

// 7) Success
echo json_encode(['status'=>'success','image_url'=>$imageUrl]);
